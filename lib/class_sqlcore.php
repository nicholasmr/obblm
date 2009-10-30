<?php 

class SQLCore
{
public static function setTriggers($set = true)
{
    $status = true;
    foreach (array('MD_insert', 'MD_update', 'MD_delete') as $t)
        $status &= mysql_query('DROP TRIGGER IF EXISTS '.$t);
        
    if (!$set) {
        return $status;
    }

    # Shortcut
    $trig_MD_body = '
        BEGIN
            DECLARE retval BOOLEAN;
            SET retval = syncMVplayer(REGEX_REPLACE.f_player_id, REGEX_REPLACE.f_tour_id);
        END';
    $triggers = array(
        'CREATE TRIGGER MD_insert AFTER INSERT ON match_data FOR EACH ROW '.preg_replace('/REGEX_REPLACE/', 'NEW', $trig_MD_body),
        'CREATE TRIGGER MD_update AFTER UPDATE ON match_data FOR EACH ROW '.preg_replace('/REGEX_REPLACE/', 'OLD', $trig_MD_body),
        'CREATE TRIGGER MD_delete AFTER DELETE ON match_data FOR EACH ROW '.preg_replace('/REGEX_REPLACE/', 'OLD', $trig_MD_body),
    );
    foreach ($triggers as $t) {
        $status &= (mysql_query($t) or die(mysql_error()));
    }
    
    return $status;
}

/*
    Synchronizes PHP stored BB game date with DB game data. 
    These MUST be in sync thus this routine MUST be run whenever the PHP-stored game data is modified.
*/
public static function syncGameData() 
{
    global $core_tables, $DEA, $stars;
    
    $players  = 'game_data_players';
    $races    = 'game_data_races';
    $starstbl = 'game_data_stars';
    
    $status = true;
    // Drop and re-create game data tables.
    $status &= Table::createTable($players, $core_tables[$players]);
    $status &= Table::createTable($races,   $core_tables[$races]);
    $status &= Table::createTable($starstbl,   $core_tables[$starstbl]);

    foreach ($DEA as $race_name => $race_details) {
        $query = "INSERT INTO $races(race_id, name, cost_rr) VALUES (".$race_details['other']['race_id'].", '".mysql_real_escape_string($race_name)."', ".$race_details['other']['rr_cost'].")";
        $status &= mysql_query($query);
        foreach ($race_details['players'] as $player_name => $PD) { # Player Details
            $query = "INSERT INTO $players(
                    pos_id, f_race_id, pos, cost, qty, ma,st,ag,av, skills,norm,doub
                ) VALUES (
                    $PD[pos_id], ".$race_details['other']['race_id'].", '".mysql_real_escape_string($player_name)."', $PD[cost], $PD[qty], $PD[ma],$PD[st],$PD[ag],$PD[av],
                    '".implode(',',$PD['def'])."', '".implode('',$PD['norm'])."', '".implode('',$PD['doub'])."'
                )";
            $status &= mysql_query($query);
        }
    }

    foreach ($stars as $star_name => $SD) {
        $query = "INSERT INTO $starstbl(star_id, name, cost, races, ma,st,ag,av, skills) VALUES (
            $SD[id], '".mysql_real_escape_string($star_name)."', $SD[cost], '".implode(',', $SD['races'])."', $SD[ma],$SD[st],$SD[ag],$SD[av], '".implode(',', $SD['def'])."'
        )";
        $status = mysql_query($query);
    }
    
    return $status;
}

public static function installProcsAndFuncs($install = true)
{
    $status = true;
    foreach (array('getPlayerStatus', 'syncMVplayer', 'syncMVteam', 'syncMVcoach', 'syncMVrace') as $f)
        $status &= mysql_query('DROP FUNCTION IF EXISTS '.$f);
    foreach (array('getTourParentNodes', 'getObjParents', 'syncMVall') as $p)
        $status &= mysql_query('DROP PROCEDURE IF EXISTS '.$p);
        
    if (!$install) {
        return $status;
    }

    // Add MySQL functions/procedures.
        # Shortcuts:
    $common_fields_keys = 'td,cp,intcpt,bh,si,ki,mvp,cas,tdcas,spp';
    $common_fields = 'SUM(td),SUM(cp),SUM(intcpt),SUM(bh),SUM(si),SUM(ki),SUM(mvp),SUM(bh+si+ki),SUM(bh+si+ki+td),SUM(cp*1+(bh+si+ki)*2+intcpt*2+td*3+mvp*5)';
    #$mstat_fields_keys = 'played,won,lost,draw,gf,ga';
    $mstat_fields_suffix_player = 'FROM matches,match_data WHERE matches.match_id = match_data.f_match_id AND match_data.f_player_id = pid AND match_data.mg IS FALSE AND matches.f_tour_id = trid';
    $mstat_fields_suffix_team   = 'FROM matches WHERE f_tour_id = trid AND (team1_id = tid OR team2_id = tid)';
    $mstat_fields_suffix_coach  = 'FROM matches,teams WHERE f_tour_id = trid AND (team1_id = tid OR team2_id = tid) AND teams.owned_by_coach_id = cid';
    $mstat_fields_suffix_race   = 'FROM matches,teams WHERE f_tour_id = trid AND (team1_id = tid OR team2_id = tid) AND teams.f_race_id = rid';
#    f_tour_id = trid AND (team1_id = teams.team_id OR team2_id = teams.team_id) AND teams.owned_by_coach_id = cid;
    $mstat_fields = '
        SET played = IFNULL((SELECT SUM(IF(team1_id = tid OR team2_id = tid, 1, 0)) REGEX_REPLACE_HERE), 0), 
            won    = IFNULL((SELECT SUM(IF((team1_id = tid AND team1_score > team2_score) OR (team2_id = tid AND team2_score > team1_score), 1, 0)) REGEX_REPLACE_HERE), 0), 
            lost   = IFNULL((SELECT SUM(IF((team1_id = tid AND team1_score < team2_score) OR (team2_id = tid AND team2_score < team1_score), 1, 0)) REGEX_REPLACE_HERE), 0), 
            draw   = IFNULL((SELECT SUM(IF((team1_id = tid OR team2_id = tid) AND team1_score = team2_score, 1, 0)) REGEX_REPLACE_HERE), 0), 
            gf     = IFNULL((SELECT SUM(IF(team1_id = tid, team1_score, IF(team2_id = tid, team2_score, 0))) REGEX_REPLACE_HERE), 0), 
            ga     = IFNULL((SELECT SUM(IF(team1_id = tid, team2_score, IF(team2_id = tid, team1_score, 0))) REGEX_REPLACE_HERE), 0) 
    ';
    $mstat_fields_player = preg_replace('/REGEX_REPLACE_HERE/', $mstat_fields_suffix_player, $mstat_fields);
    $mstat_fields_team   = preg_replace('/REGEX_REPLACE_HERE/', $mstat_fields_suffix_team,   $mstat_fields);
    $mstat_fields_coach  = preg_replace('/REGEX_REPLACE_HERE/', $mstat_fields_suffix_coach,  $mstat_fields);
    $mstat_fields_race   = preg_replace('/REGEX_REPLACE_HERE/', $mstat_fields_suffix_race,   $mstat_fields);
    $mstat_fields_coach = preg_replace('/tid/', 'teams.team_id', $mstat_fields_coach);
    $mstat_fields_race  = preg_replace('/tid/', 'teams.team_id', $mstat_fields_race);
        
    $functions = array(
    
        /* 
         *  General 
         */
         
        // Returns status of player in match and latest/current status on mid = -1 or unplayed mid.
        'CREATE FUNCTION getPlayerStatus(pid MEDIUMINT UNSIGNED, mid MEDIUMINT SIGNED) 
            RETURNS TINYINT UNSIGNED 
            NOT DETERMINISTIC
            READS SQL DATA
        BEGIN
            IF EXISTS(SELECT match_id FROM matches WHERE match_id = mid AND date_played IS NULL)
                RETURN getPlayerStatus(pid, -1);
            END IF;
            
            DECLARE status TINYINT UNSIGNED DEFAULT NULL;
            IF mid = -1
            THEN
                SELECT inj INTO status FROM match_data, matches WHERE 
                    f_player_id = pid AND
                    match_id = f_match_id AND
                    date_played IS NOT NULL
                    ORDER BY date_played DESC LIMIT 1
            ELSE
                SELECT inj INTO status FROM match_data, matches WHERE 
                    match_data.f_player_id = pid AND
                    matches.match_id = match_data.f_match_id AND
                    matches.date_played IS NOT NULL AND
                    matches.date_played < (SELECT date_played FROM matches WHERE matches.match_id = mid)
                    ORDER BY date_played DESC LIMIT 1;
            END IF;
            RETURN IF(status IS NULL, '.NONE.', status);
        END',
        
        'CREATE PROCEDURE getTourParentNodes(IN trid MEDIUMINT UNSIGNED, OUT did MEDIUMINT UNSIGNED, OUT lid MEDIUMINT UNSIGNED)
            NOT DETERMINISTIC
            READS SQL DATA
        BEGIN
            SELECT divisions.did,divisions.f_lid INTO did,lid FROM tours,divisions WHERE tours.tour_id = trid AND tours.f_did = divisions.did;
        END',

        'CREATE PROCEDURE getObjParents(IN obj MEDIUMINT UNSIGNED, IN pid MEDIUMINT SIGNED, INOUT tid MEDIUMINT UNSIGNED, OUT cid MEDIUMINT UNSIGNED, OUT rid TINYINT UNSIGNED)
            NOT DETERMINISTIC
            READS SQL DATA
        BEGIN
            CASE obj
              WHEN '.T_OBJ_PLAYER.' THEN SELECT teams.team_id,teams.owned_by_coach_id,teams.f_race_id INTO tid,cid,rid FROM players,teams WHERE players.player_id = pid AND players.owned_by_team_id = teams.team_id;
              WHEN '.T_OBJ_TEAM.'   THEN SELECT teams.owned_by_coach_id,teams.f_race_id INTO cid,rid FROM teams WHERE teams.team_id = tid;
            END CASE;
        END',

        /* 
         *  MV syncs
         */
                 
        'CREATE FUNCTION syncMVplayer(pid MEDIUMINT SIGNED, trid MEDIUMINT UNSIGNED)
            RETURNS BOOLEAN
            NOT DETERMINISTIC
            CONTAINS SQL
        BEGIN
            DECLARE did,lid, tid,cid MEDIUMINT UNSIGNED DEFAULT NULL;
            DECLARE rid TINYINT UNSIGNED DEFAULT NULL;
            CALL getTourParentNodes(trid, did, lid);
            CALL getObjParents('.T_OBJ_PLAYER.', pid,tid,cid,rid);
            
            DELETE FROM mv_players WHERE f_pid = pid AND f_trid = trid;
            
            INSERT INTO mv_players(f_pid,f_tid,f_cid,f_rid, f_trid,f_did,f_lid, '.$common_fields_keys.') 
                SELECT pid,tid,cid,rid, trid,did,lid, '.$common_fields.'
                FROM match_data 
                WHERE match_data.f_player_id = pid AND match_data.f_tour_id = trid;
            UPDATE mv_players '.$mstat_fields_player.'                      WHERE f_pid = pid AND f_trid = trid;
            UPDATE mv_players SET win_pct = IF(played = 0, 0, won/played)   WHERE f_pid = pid AND f_trid = trid;
            
            RETURN EXISTS(SELECT COUNT(*) FROM mv_players WHERE f_pid = pid AND f_trid = trid);
        END',
        
        'CREATE FUNCTION syncMVteam(tid MEDIUMINT UNSIGNED, trid MEDIUMINT UNSIGNED)
            RETURNS BOOLEAN
            NOT DETERMINISTIC
            CONTAINS SQL
        BEGIN
            DECLARE did,lid, cid MEDIUMINT UNSIGNED DEFAULT NULL;
            DECLARE rid TINYINT UNSIGNED DEFAULT NULL;
            CALL getTourParentNodes(trid, did, lid);
            CALL getObjParents('.T_OBJ_TEAM.', NULL,tid,cid,rid);
            
            DELETE FROM mv_teams WHERE f_tid = tid AND f_trid = trid;

            INSERT INTO mv_teams(f_tid,f_cid,f_rid, f_trid,f_did,f_lid, '.$common_fields_keys.') 
                SELECT tid,cid,rid, trid,did,lid, '.$common_fields.'
                FROM match_data 
                WHERE match_data.f_team_id = tid AND match_data.f_tour_id = trid;
            UPDATE mv_teams '.$mstat_fields_team.'                      WHERE f_tid = tid AND f_trid = trid;
            UPDATE mv_teams SET win_pct = IF(played = 0, 0, won/played) WHERE f_tid = tid AND f_trid = trid;

            RETURN EXISTS(SELECT COUNT(*) FROM mv_teams WHERE f_tid = tid AND f_trid = trid);
        END',
        
        'CREATE FUNCTION syncMVcoach(cid MEDIUMINT UNSIGNED, trid MEDIUMINT UNSIGNED)
            RETURNS BOOLEAN
            NOT DETERMINISTIC
            CONTAINS SQL
        BEGIN
            DECLARE did,lid MEDIUMINT UNSIGNED DEFAULT NULL;
            CALL getTourParentNodes(trid, did, lid);
            
            DELETE FROM mv_coaches WHERE f_cid = cid AND f_trid = trid;

            INSERT INTO mv_coaches(f_cid, f_trid,f_did,f_lid, '.$common_fields_keys.') 
                SELECT cid, trid,did,lid, '.$common_fields.'
                FROM match_data
                WHERE match_data.f_coach_id = cid AND match_data.f_tour_id = trid;
            UPDATE mv_coaches '.$mstat_fields_coach.'                       WHERE f_cid = cid AND f_trid = trid;
            UPDATE mv_coaches SET win_pct = IF(played = 0, 0, won/played)   WHERE f_cid = cid AND f_trid = trid;

            RETURN EXISTS(SELECT COUNT(*) FROM mv_coaches WHERE f_cid = cid AND f_trid = trid);
        END',

        'CREATE FUNCTION syncMVrace(rid TINYINT UNSIGNED, trid MEDIUMINT UNSIGNED)
            RETURNS BOOLEAN
            NOT DETERMINISTIC
            CONTAINS SQL
        BEGIN
            DECLARE did,lid MEDIUMINT UNSIGNED DEFAULT NULL;
            CALL getTourParentNodes(trid, did, lid);
            
            DELETE FROM mv_races WHERE f_rid = rid AND f_trid = trid;

            INSERT INTO mv_races(f_rid, f_trid,f_did,f_lid, '.$common_fields_keys.') 
                SELECT rid, trid,did,lid, '.$common_fields.'
                FROM match_data
                WHERE match_data.f_race_id = rid AND match_data.f_tour_id = trid;
            UPDATE mv_races '.$mstat_fields_race.'                      WHERE f_rid = rid AND f_trid = trid;
            UPDATE mv_races SET win_pct = IF(played = 0, 0, won/played) WHERE f_rid = rid AND f_trid = trid;

            RETURN EXISTS(SELECT COUNT(*) FROM mv_races WHERE f_rid = rid AND f_trid = trid);
        END',
        
        'CREATE PROCEDURE syncMVall()
            NOT DETERMINISTIC
            CONTAINS SQL
        BEGIN
            SELECT syncMVplayer(f_player_id, f_tour_id) FROM (SELECT f_player_id,f_tour_id FROM match_data GROUP BY f_player_id,f_tour_id) AS tmpTbl;
            SELECT syncMVteam(f_team_id,     f_tour_id) FROM (SELECT f_team_id,  f_tour_id FROM match_data GROUP BY f_team_id,  f_tour_id) AS tmpTbl;
            SELECT syncMVcoach(f_coach_id,   f_tour_id) FROM (SELECT f_coach_id, f_tour_id FROM match_data GROUP BY f_coach_id, f_tour_id) AS tmpTbl;
            SELECT syncMVrace(f_race_id,     f_tour_id) FROM (SELECT f_race_id,  f_tour_id FROM match_data GROUP BY f_race_id,  f_tour_id) AS tmpTbl;
        END',
        
        /* 
         *  Other syncs
         */
         
        'CREATE TRIGGER syncPlayer
            BEFORE INSERT ON players
            FOR EACH ROW
        BEGIN
            DECLARE inj_ni,inj_ma,inj_av,inj_ag,inj_st SMALLINT UNSIGNED DEFAULT 0;
            SELECT 
                IFNULL(SUM(IF(inj = '.NI.', 1, 0) + IF(agn1 = '.NI.', 1, 0) + IF(agn2 = '.NI.', 1, 0)), 0), 
                IFNULL(SUM(IF(inj = '.MA.', 1, 0) + IF(agn1 = '.MA.', 1, 0) + IF(agn2 = '.MA.', 1, 0)), 0), 
                IFNULL(SUM(IF(inj = '.AV.', 1, 0) + IF(agn1 = '.AV.', 1, 0) + IF(agn2 = '.AV.', 1, 0)), 0), 
                IFNULL(SUM(IF(inj = '.AG.', 1, 0) + IF(agn1 = '.AG.', 1, 0) + IF(agn2 = '.AG.', 1, 0)), 0), 
                IFNULL(SUM(IF(inj = '.ST.', 1, 0) + IF(agn1 = '.ST.', 1, 0) + IF(agn2 = '.ST.', 1, 0)), 0)
                INTO inj_ni,inj_ma,inj_av,inj_ag,inj_st
                FROM match_data WHERE f_player_id = NEW.player_id;
                
            DECLARE cnt_ach_nor_skills,cnt_ach_dob_skills TINYINT UNSIGNED DEFAULT 0;
            SET cnt_ach_nor_skills = LENGTH(NEW.ach_nor_skills) - LENGTH(REPLACE(NEW.ach_nor_skills, ",", "")) + IF(LENGTH(NEW.ach_nor_skills) = 0, 0, 1);
            SET cnt_ach_dob_skills = LENGTH(NEW.ach_dob_skills) - LENGTH(REPLACE(NEW.ach_dob_skills, ",", "")) + IF(LENGTH(NEW.ach_dob_skills) = 0, 0, 1);
            SET NEW.value = (SELECT cost FROM game_data_players WHERE game_data_players.pos_id = NEW.pos_id)
                + (NEW.ach_ma + NEW.ach_av) * 30000
                + NEW.ach_ag                * 40000
                + NEW.ach_st                * 50000
                + cnt_ach_nor_skills        * 20000
                + cnt_ach_dob_skills        * 30000
                + NEW.extra_val;

            SET NEW.ma = NEW.ach_ma - NEW.inj_ma + (SELECT ma FROM game_data_players WHERE game_data_players.pos_id = NEW.pos_id);
            SET NEW.st = NEW.ach_st - NEW.inj_st + (SELECT st FROM game_data_players WHERE game_data_players.pos_id = NEW.pos_id);
            SET NEW.ag = NEW.ach_ag - NEW.inj_ag + (SELECT ag FROM game_data_players WHERE game_data_players.pos_id = NEW.pos_id);
            SET NEW.av = NEW.ach_av - NEW.inj_av + (SELECT av FROM game_data_players WHERE game_data_players.pos_id = NEW.pos_id);
                
            SET NEW.status = getPlayerStats(NEW.player_id, -1);
            
            IF NEW.status = '.DEAD.' THEN
                NEW.date_died = (SELECT date_played FROM matches, match_data WHERE f_match_id = match_id AND f_player_id = NEW.player_id AND inj = '.DEAD.');
            ELSE
                NEW.date_died = NULL;
            END IF;
        END',
        
        'CREATE TRIGGER syncTeam
            BEFORE INSERT ON teams
            FOR EACH ROW
        BEGIN
            SET NEW.tv = (SELECT SUM(value) FROM players WHERE owned_by_team_id = NEW.team_id AND players.status != '.MNG.')
                + NEW.rerolls      * (SELECT cost_rr FROM game_data_races WHERE NEW.f_race_id = game_data_race.race_id)
                + NEW.fan_factor   * '.$rules['cost_fan_factor'].'
                + NEW.cheerleaders * '.$rules['cost_cheerleaders'].'
                + NEW.apothecary   * '.$rules['cost_apothecary'].'
                + NEW.ass_coaches  * '.$rules['cost_ass_coaches'].';
        END',
    );

    foreach ($functions as $f) {
        $status &= (mysql_query($f) or die(mysql_error()));
    }
    
    return $status;
}

public static function installTableIndexes($install = true)
{
    // Drop indicies
    $status = true;
    #....

    // Add tables indicies/keys.
    $indexes = "
        ALTER TABLE texts       ADD INDEX idx_f_id                  (f_id);
        ALTER TABLE texts       ADD INDEX idx_type                  (type);
        ALTER TABLE players     ADD INDEX idx_owned_by_team_id      (owned_by_team_id);
        ALTER TABLE teams       ADD INDEX idx_owned_by_coach_id     (owned_by_coach_id);
        ALTER TABLE matches     ADD INDEX idx_f_tour_id             (f_tour_id);
        ALTER TABLE matches     ADD INDEX idx_team1_id_team2_id     (team1_id,team2_id);
        ALTER TABLE matches     ADD INDEX idx_team2_id              (team2_id);
        ALTER TABLE match_data  ADD INDEX idx_m                     (f_match_id);
        ALTER TABLE match_data  ADD INDEX idx_tr                    (f_tour_id);
        ALTER TABLE match_data  ADD INDEX idx_p_m                   (f_player_id,f_match_id);
        ALTER TABLE match_data  ADD INDEX idx_t_m                   (f_team_id,  f_match_id);
        ALTER TABLE match_data  ADD INDEX idx_r_m                   (f_race_id,  f_match_id);
        ALTER TABLE match_data  ADD INDEX idx_c_m                   (f_coach_id, f_match_id);
        ALTER TABLE match_data  ADD INDEX idx_p_tr                  (f_player_id,f_tour_id);
        ALTER TABLE match_data  ADD INDEX idx_t_tr                  (f_team_id,  f_tour_id);
        ALTER TABLE match_data  ADD INDEX idx_r_tr                  (f_race_id,  f_tour_id);
        ALTER TABLE match_data  ADD INDEX idx_c_tr                  (f_coach_id, f_tour_id);
    ";

    foreach (explode(';', $indexes) as $query) {
        $query = trim($query);
        if (!empty($query)) {
            $status &= mysql_query($query);
        }
    }
    
    return $status;
}

}

/*
    Helper routines for writing upgrade SQL code.
*/

class SQLUpgrade
{
    public static function runIfColumnNotExists($tbl, $col, $query)
    {
        $colCheck = "SELECT EXISTS(SELECT * FROM information_schema.COLUMNS WHERE COLUMN_NAME='$col' AND TABLE_NAME='$tbl') AS 'exists'";
        $result = mysql_query($colCheck);
        $row = mysql_fetch_assoc($result);
        return ((int) $row['exists']) ? 'SELECT \'1\'' : $query;
    }
    
    // EXACTLY like runIfColumnNotExists(), but has the logic reversed at the return statement.
    public static function runIfColumnExists($tbl, $col, $query)
    {
        $colCheck = "SELECT EXISTS(SELECT * FROM information_schema.COLUMNS WHERE COLUMN_NAME='$col' AND TABLE_NAME='$tbl') AS 'exists'";
        $result = mysql_query($colCheck);
        $row = mysql_fetch_assoc($result);
        return ((int) $row['exists']) ? $query : 'SELECT \'1\'';
    }
    
    public static function runIfTrue($evalQuery, $query)
    {
        $result = mysql_query($evalQuery);
        $row = mysql_fetch_row($result);
        return ((int) $row[0]) ? $query : 'SELECT \'1\'';
    }
}

?>
