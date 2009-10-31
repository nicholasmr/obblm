<?php 

/*
    Load only this file on demand.
*/

class SQLCore
{

public static function setTriggers($set = true)
{
    global $CT_cols;
    
    // Shortcuts
    $trig_MV_sync_block = '
        BEGIN 
            DECLARE retval BOOLEAN; 
            DECLARE tid '.$CT_cols[T_OBJ_PLAYER].' DEFAULT NULL;
            DECLARE cid '.$CT_cols[T_OBJ_PLAYER].' DEFAULT NULL;
            DECLARE rid '.$CT_cols[T_OBJ_PLAYER].' DEFAULT NULL;
            CALL getObjParents('.T_OBJ_PLAYER.', REGEX_REPLACE.f_player_id, tid, cid, rid);
            SET retval = syncMVplayer(REGEX_REPLACE.f_player_id, REGEX_REPLACE.f_tour_id);
            SET retval = syncMVteam(tid, REGEX_REPLACE.f_tour_id);
            SET retval = syncMVcoach(cid, REGEX_REPLACE.f_tour_id);
            SET retval = syncMVrace(rid, REGEX_REPLACE.f_tour_id);
        END';
    $trig_DPROPS_player_b_block = '
        BEGIN
            CALL getPlayerDProps(NEW.player_id, NEW.inj_ma,NEW.inj_av,NEW.inj_ag,NEW.inj_st,NEW.inj_ni, NEW.ma,NEW.av,NEW.ag,NEW.st, NEW.value, NEW.status, NEW.date_died);
        END';
    $run_DPROPS_team_trigger = 'UPDATE teams SET teams.name = teams.name WHERE teams.team_id = REGEX_REPLACE.owned_by_team_id';
    $trig_DPROPS_player_a_conditional_block = '
        BEGIN
            IF OLD.value != NEW.value THEN
                '.$run_DPROPS_team_trigger.';
            END IF;
        END';
    $trig_DPROPS_player_a_block = '
        BEGIN
            '.$run_DPROPS_team_trigger.';
        END';
    $trig_DPROPS_team_block = '
        BEGIN
            CALL getTeamDProps(NEW.team_id, NEW.tv);
        END';
        
    $triggers = array(
        // These sync the MV tables
        'CREATE TRIGGER MV_sync_insert AFTER INSERT ON match_data FOR EACH ROW '.preg_replace('/REGEX_REPLACE/', 'NEW', $trig_MV_sync_block),
        'CREATE TRIGGER MV_sync_update AFTER UPDATE ON match_data FOR EACH ROW '.preg_replace('/REGEX_REPLACE/', 'OLD', $trig_MV_sync_block),
        'CREATE TRIGGER MV_sync_delete AFTER DELETE ON match_data FOR EACH ROW '.preg_replace('/REGEX_REPLACE/', 'OLD', $trig_MV_sync_block),
        // These sync dynamic team and player properties (team+player values, current player inj count, current player status etc.).
            # Player DPROPS
        'CREATE TRIGGER DPROPS_player_b_update BEFORE UPDATE ON players FOR EACH ROW '.$trig_DPROPS_player_b_block,
        'CREATE TRIGGER DPROPS_player_b_insert BEFORE INSERT ON players FOR EACH ROW '.$trig_DPROPS_player_b_block,
            # Team DPROPS
        'CREATE TRIGGER DPROPS_player_a_delete AFTER DELETE ON players FOR EACH ROW '.preg_replace('/REGEX_REPLACE/', 'OLD', $trig_DPROPS_player_a_block),
        'CREATE TRIGGER DPROPS_player_a_update AFTER UPDATE ON players FOR EACH ROW '.preg_replace('/REGEX_REPLACE/', 'NEW', $trig_DPROPS_player_a_conditional_block),
        'CREATE TRIGGER DPROPS_player_a_insert AFTER INSERT ON players FOR EACH ROW '.preg_replace('/REGEX_REPLACE/', 'NEW', $trig_DPROPS_player_a_block),
        'CREATE TRIGGER DPROPS_team_update BEFORE UPDATE ON teams FOR EACH ROW '.$trig_DPROPS_team_block,
        'CREATE TRIGGER DPROPS_team_insert BEFORE INSERT ON teams FOR EACH ROW '.$trig_DPROPS_team_block,
    );
    
    $status = true;
    foreach ($triggers as $t) {
        $matches = array();
        preg_match('/^CREATE TRIGGER (\w*) /', $t, $matches);
        $status &= mysql_query('DROP TRIGGER IF EXISTS '.$matches[1]);
    }
        
    if (!$set) {
        return $status;
    }
    
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
    global $CT_cols, $core_tables, $rules;
    
    $status = true;
    
    foreach (array('getPlayerStatus', 'syncMVplayer', 'syncMVteam', 'syncMVcoach', 'syncMVrace') as $f)
        $status &= mysql_query('DROP FUNCTION IF EXISTS '.$f);
        
    foreach (array('getTourParentNodes', 'getObjParents', 'syncMVall', 'getTeamDProps', 'getPlayerDProps') as $p)
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
        'CREATE FUNCTION getPlayerStatus(pid '.$CT_cols[T_OBJ_PLAYER].', mid '.$CT_cols[T_NODE_MATCH].') 
            RETURNS '.$core_tables['players']['status'].' 
            NOT DETERMINISTIC
            READS SQL DATA
        BEGIN
            DECLARE status '.$core_tables['players']['status'].' DEFAULT NULL;

            IF mid != -1 AND EXISTS(SELECT match_id FROM matches WHERE match_id = mid AND date_played IS NULL) THEN 
                RETURN getPlayerStatus(pid, -1);
            END IF;

            IF mid = -1 THEN
                SELECT inj INTO status FROM match_data, matches WHERE 
                    f_player_id = pid AND
                    match_id = f_match_id AND
                    date_played IS NOT NULL
                    ORDER BY date_played DESC LIMIT 1;
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
        
        'CREATE PROCEDURE getTourParentNodes(IN trid '.$CT_cols[T_NODE_TOURNAMENT].', OUT did '.$CT_cols[T_NODE_DIVISION].', OUT lid '.$CT_cols[T_NODE_LEAGUE].')
            NOT DETERMINISTIC
            READS SQL DATA
        BEGIN
            SELECT divisions.did,divisions.f_lid INTO did,lid FROM tours,divisions WHERE tours.tour_id = trid AND tours.f_did = divisions.did;
        END',

        'CREATE PROCEDURE getObjParents(IN obj TINYINT UNSIGNED, IN pid '.$CT_cols[T_OBJ_PLAYER].', INOUT tid '.$CT_cols[T_OBJ_TEAM].', OUT cid '.$CT_cols[T_OBJ_COACH].', OUT rid '.$CT_cols[T_OBJ_RACE].')
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
                 
        'CREATE FUNCTION syncMVplayer(pid '.$CT_cols[T_OBJ_PLAYER].', trid '.$CT_cols[T_NODE_TOURNAMENT].')
            RETURNS BOOLEAN
            NOT DETERMINISTIC
            CONTAINS SQL
        BEGIN
            DECLARE did '.$CT_cols[T_NODE_DIVISION].' DEFAULT NULL;
            DECLARE lid '.$CT_cols[T_NODE_LEAGUE].' DEFAULT NULL;
            DECLARE tid '.$CT_cols[T_OBJ_TEAM].' DEFAULT NULL;
            DECLARE cid '.$CT_cols[T_OBJ_COACH].' DEFAULT NULL;
            DECLARE rid '.$CT_cols[T_OBJ_RACE].' DEFAULT NULL;
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
        
        'CREATE FUNCTION syncMVteam(tid '.$CT_cols[T_OBJ_TEAM].', trid '.$CT_cols[T_NODE_TOURNAMENT].')
            RETURNS BOOLEAN
            NOT DETERMINISTIC
            CONTAINS SQL
        BEGIN
            DECLARE did '.$CT_cols[T_NODE_DIVISION].' DEFAULT NULL;
            DECLARE lid '.$CT_cols[T_NODE_LEAGUE].' DEFAULT NULL;
            DECLARE cid '.$CT_cols[T_OBJ_COACH].' DEFAULT NULL;
            DECLARE rid '.$CT_cols[T_OBJ_RACE].' DEFAULT NULL;
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
        
        'CREATE FUNCTION syncMVcoach(cid '.$CT_cols[T_OBJ_COACH].', trid '.$CT_cols[T_NODE_TOURNAMENT].')
            RETURNS BOOLEAN
            NOT DETERMINISTIC
            CONTAINS SQL
        BEGIN
            DECLARE did '.$CT_cols[T_NODE_DIVISION].' DEFAULT NULL;
            DECLARE lid '.$CT_cols[T_NODE_LEAGUE].' DEFAULT NULL;
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

        'CREATE FUNCTION syncMVrace(rid '.$CT_cols[T_OBJ_RACE].', trid '.$CT_cols[T_NODE_TOURNAMENT].')
            RETURNS BOOLEAN
            NOT DETERMINISTIC
            CONTAINS SQL
        BEGIN
            DECLARE did '.$CT_cols[T_NODE_DIVISION].' DEFAULT NULL;
            DECLARE lid '.$CT_cols[T_NODE_LEAGUE].' DEFAULT NULL;
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
         *  Dynamic (object) properties calculators
         */
         
        'CREATE PROCEDURE getPlayerDProps(
            IN pid '.$CT_cols[T_OBJ_PLAYER].',
            OUT inj_ma '.$CT_cols['chr'].', OUT inj_av '.$CT_cols['chr'].', OUT inj_ag '.$CT_cols['chr'].', OUT inj_st '.$CT_cols['chr'].', OUT inj_ni '.$CT_cols['chr'].',
            OUT ma '.$CT_cols['chr'].',     OUT av '.$CT_cols['chr'].',     OUT ag '.$CT_cols['chr'].',     OUT st '.$CT_cols['chr'].',
            OUT value '.$CT_cols['pv'].', OUT status '.$core_tables['players']['status'].', OUT date_died '.$core_tables['players']['date_died'].'
        )
            NOT DETERMINISTIC
            READS SQL DATA
        BEGIN
            DECLARE ach_ma,ach_st,ach_ag,ach_av '.$CT_cols['chr'].';
            DECLARE ach_nor_skills, ach_dob_skills '.$CT_cols['skills'].';
            DECLARE cnt_ach_nor_skills, cnt_ach_dob_skills TINYINT UNSIGNED;
            DECLARE extra_val '.$CT_cols['pv'].';
            DECLARE pos_id '.$CT_cols['pos_id'].';

            SELECT 
                players.pos_id, players.extra_val, players.ach_nor_skills, players.ach_dob_skills, players.ach_ma, players.ach_st, players.ach_ag, players.ach_av,
                (LENGTH(players.ach_nor_skills) - LENGTH(REPLACE(players.ach_nor_skills, ",", "")) + IF(LENGTH(players.ach_nor_skills) = 0, 0, 1)),
                (LENGTH(players.ach_dob_skills) - LENGTH(REPLACE(players.ach_dob_skills, ",", "")) + IF(LENGTH(players.ach_dob_skills) = 0, 0, 1))
            INTO
                pos_id, extra_val, ach_nor_skills, ach_dob_skills, ach_ma, ach_st, ach_ag, ach_av,
                cnt_ach_nor_skills, cnt_ach_dob_skills
            FROM players WHERE player_id = pid;
        
            SELECT 
                IFNULL(SUM(IF(inj = '.NI.', 1, 0) + IF(agn1 = '.NI.', 1, 0) + IF(agn2 = '.NI.', 1, 0)), 0), 
                IFNULL(SUM(IF(inj = '.MA.', 1, 0) + IF(agn1 = '.MA.', 1, 0) + IF(agn2 = '.MA.', 1, 0)), 0), 
                IFNULL(SUM(IF(inj = '.AV.', 1, 0) + IF(agn1 = '.AV.', 1, 0) + IF(agn2 = '.AV.', 1, 0)), 0), 
                IFNULL(SUM(IF(inj = '.AG.', 1, 0) + IF(agn1 = '.AG.', 1, 0) + IF(agn2 = '.AG.', 1, 0)), 0), 
                IFNULL(SUM(IF(inj = '.ST.', 1, 0) + IF(agn1 = '.ST.', 1, 0) + IF(agn2 = '.ST.', 1, 0)), 0)
            INTO 
                inj_ni,inj_ma,inj_av,inj_ag,inj_st
            FROM match_data WHERE f_player_id = pid;

            SET value = (SELECT cost FROM game_data_players WHERE game_data_players.pos_id = pos_id)
                + (ach_ma + ach_av)  * 30000
                + ach_ag             * 40000
                + ach_st             * 50000
                + cnt_ach_nor_skills * 20000
                + cnt_ach_dob_skills * 30000
                + extra_val;

            SET ma = ach_ma + (SELECT ma FROM game_data_players WHERE game_data_players.pos_id = pos_id) - inj_ma;
            SET st = ach_st + (SELECT st FROM game_data_players WHERE game_data_players.pos_id = pos_id) - inj_st;
            SET ag = ach_ag + (SELECT ag FROM game_data_players WHERE game_data_players.pos_id = pos_id) - inj_ag;
            SET av = ach_av + (SELECT av FROM game_data_players WHERE game_data_players.pos_id = pos_id) - inj_av;
                
            SET status = getPlayerStats(pid, -1);
            
            IF status = '.DEAD.' THEN
                SET date_died = (SELECT date_played FROM matches, match_data WHERE f_match_id = match_id AND f_player_id = pid AND inj = '.DEAD.');
            ELSE
                SET date_died = NULL;
            END IF;
        END',
        
        'CREATE PROCEDURE getTeamDProps(IN tid '.$CT_cols[T_OBJ_TEAM].', OUT tv '.$CT_cols['tv'].')
            NOT DETERMINISTIC
            READS SQL DATA
        BEGIN
            DECLARE f_race_id '.$CT_cols[T_OBJ_RACE].';
            DECLARE rerolls '.$core_tables['teams']['rerolls'].';
            DECLARE fan_factor '.$core_tables['teams']['fan_factor'].';
            DECLARE cheerleaders '.$core_tables['teams']['cheerleaders'].';
            DECLARE apothecary '.$core_tables['teams']['apothecary'].';
            DECLARE ass_coaches '.$core_tables['teams']['ass_coaches'].';

            SELECT 
                teams.f_race_id, teams.rerolls, teams.fan_factor, teams.cheerleaders, teams.apothecary, teams.ass_coaches
            INTO 
                f_race_id, rerolls, fan_factor, cheerleaders, apothecary, ass_coaches
            FROM teams WHERE team_id = tid;
            
            SET tv = (SELECT SUM(value) FROM players WHERE owned_by_team_id = tid AND players.status != '.MNG.')
                + rerolls      * (SELECT cost_rr FROM game_data_races WHERE game_data_race.race_id = f_race_id)
                + fan_factor   * '.$rules['cost_fan_factor'].'
                + cheerleaders * '.$rules['cost_cheerleaders'].'
                + apothecary   * '.$rules['cost_apothecary'].'
                + ass_coaches  * '.$rules['cost_ass_coaches'].';
        END',
    );

    foreach ($functions as $f) {
        $status &= (mysql_query($f) or die(mysql_error()."\nCODE:\n-----\n\n".$f));
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

?>
