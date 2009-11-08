<?php 

/*
    Load only this file on demand.
*/

define('T_ELO_K',   20);
define('T_ELO_D',   400);
define('T_ELO_R_0', 200);
define('T_ELO_PTS_WON',  1);
define('T_ELO_PTS_LOST', 0);
define('T_ELO_PTS_DRAW', 0.5);

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
    $trig_Team_cnt_block = '
        BEGIN
            UPDATE races   SET team_cnt = getTeamCnt('.T_OBJ_RACE.',  REGEX_REPLACE.f_race_id, NULL)         WHERE race_id = REGEX_REPLACE.f_race_id;
            UPDATE coaches SET team_cnt = getTeamCnt('.T_OBJ_COACH.', REGEX_REPLACE.owned_by_coach_id, NULL) WHERE coach_id = REGEX_REPLACE.owned_by_coach_id;
        END';
    $trig_match_getRelations = '
        DECLARE rid1, rid2 '.$CT_cols[T_OBJ_RACE].';
        DECLARE cid1, cid2 '.$CT_cols[T_OBJ_COACH].';
        DECLARE tid1, tid2 '.$CT_cols[T_OBJ_TEAM].';
        DECLARE trid '.$CT_cols[T_NODE_TOURNAMENT].';

        SET trid = REGEX_REPLACE.f_tour_id;

        SELECT t1.f_race_id, t2.f_race_id, t1.owned_by_coach_id, t2.owned_by_coach_id, t1.team_id, t2.team_id
        INTO rid1, rid2, cid1, cid2, tid1, tid2
        FROM teams AS t1, teams AS t2 WHERE t1.team_id = REGEX_REPLACE.team1_id AND t2.team_id = REGEX_REPLACE.team2_id;
    ';
    # Needs $trig_match_getRelations.
    $trig_match_sync_team_cnt = '
        UPDATE mv_races   SET team_cnt = getTeamCnt('.T_OBJ_RACE.', rid1, trid) WHERE f_trid = trid AND f_rid = rid1;
        UPDATE mv_races   SET team_cnt = getTeamCnt('.T_OBJ_RACE.', rid2, trid) WHERE f_trid = trid AND f_rid = rid2;
        UPDATE mv_coaches SET team_cnt = getTeamCnt('.T_OBJ_RACE.', cid1, trid) WHERE f_trid = trid AND f_cid = cid1;
        UPDATE mv_coaches SET team_cnt = getTeamCnt('.T_OBJ_RACE.', cid2, trid) WHERE f_trid = trid AND f_cid = cid2;
    ';
    # Needs $trig_match_getRelations.
    $trig_match_sync_wt_cnt = '
        IF REGEX_REPLACE.round = '.RT_FINAL.' THEN
            UPDATE races SET wt_cnt = getWTCnt('.T_OBJ_RACE.', rid1) WHERE race_id = rid1;
            UPDATE races SET wt_cnt = getWTCnt('.T_OBJ_RACE.', rid2) WHERE race_id = rid2;
            UPDATE teams SET wt_cnt = getWTCnt('.T_OBJ_TEAM.', tid1) WHERE team_id = tid1;
            UPDATE teams SET wt_cnt = getWTCnt('.T_OBJ_TEAM.', tid2) WHERE team_id = tid2;
            UPDATE coaches SET wt_cnt = getWTCnt('.T_OBJ_COACH.', cid1) WHERE coach_id = cid1;
            UPDATE coaches SET wt_cnt = getWTCnt('.T_OBJ_COACH.', cid2) WHERE coach_id = cid2;
        END IF;
    ';
    # Needs $trig_match_getRelations.
    $trig_match_sync_win_pct = '
        UPDATE races SET win_pct = getWinPct('.T_OBJ_RACE.', rid1) WHERE race_id = rid1;
        UPDATE races SET win_pct = getWinPct('.T_OBJ_RACE.', rid2) WHERE race_id = rid2;
        UPDATE teams SET win_pct = getWinPct('.T_OBJ_TEAM.', tid1) WHERE team_id = tid1;
        UPDATE teams SET win_pct = getWinPct('.T_OBJ_TEAM.', tid2) WHERE team_id = tid2;
        UPDATE coaches SET win_pct = getWinPct('.T_OBJ_COACH.', cid1) WHERE coach_id = cid1;
        UPDATE coaches SET win_pct = getWinPct('.T_OBJ_COACH.', cid2) WHERE coach_id = cid2;
    ';
    # Needs $trig_match_getRelations.
    $trig_match_sync_setPTS = '
        UPDATE mv_teams SET pts = getPTS(tid1, trid) WHERE team_id = tid1;
        UPDATE mv_teams SET pts = getPTS(tid2, trid) WHERE team_id = tid2;
    ';
    
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
            # DPROPS, team count
        'CREATE TRIGGER Team_cnt_delete AFTER DELETE ON teams FOR EACH ROW '.preg_replace('/REGEX_REPLACE/', 'OLD', $trig_Team_cnt_block),
#        'CREATE TRIGGER Team_cnt_update AFTER UPDATE ON teams FOR EACH ROW '.preg_replace('/REGEX_REPLACE/', 'OLD', $trig_Team_cnt_block),
        'CREATE TRIGGER Team_cnt_insert AFTER INSERT ON teams FOR EACH ROW '.preg_replace('/REGEX_REPLACE/', 'NEW', $trig_Team_cnt_block),
            # Match syncs
        'CREATE TRIGGER Match_sync_update AFTER UPDATE ON matches FOR EACH ROW 
        BEGIN
            DECLARE ret BOOLEAN;
            '.preg_replace('/REGEX_REPLACE/', 'NEW', $trig_match_getRelations).'
            '.$trig_match_sync_team_cnt.'
            '.$trig_match_sync_setPTS.'
            IF NEW.round = '.RT_FINAL.' THEN
                '.$trig_match_sync_wt_cnt.'
            END IF;
            IF (NEW.team1_score != OLD.team1_score OR NEW.team2_score != OLD.team2_score) THEN
                SET ret = syncELOMatch(NULL, OLD.match_id);
                SET ret = syncELOMatch(OLD.f_tour_id, OLD.match_id);
                CALL syncStreaksMatch(OLD.match_id);
                '.$trig_match_sync_win_pct.'
            END IF;
        END
        ',
        'CREATE TRIGGER Match_sync_delete AFTER DELETE ON matches FOR EACH ROW 
        BEGIN
            '.preg_replace('/REGEX_REPLACE/', 'OLD', $trig_match_getRelations).'
            '.$trig_match_sync_team_cnt.'
            '.$trig_match_sync_setPTS.'
            IF OLD.round = '.RT_FINAL.' THEN
                '.$trig_match_sync_wt_cnt.'
            END IF;
            IF OLD.date_played IS NOT NULL THEN
                CALL syncELOTour(NULL);
                CALL syncELOTour(OLD.f_tour_id);
                CALL syncStreaksMatch(OLD.match_id);
                '.$trig_match_sync_win_pct.'
            END IF;
        END
        ',
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
    global $core_tables, $DEA, $stars, $skillarray;
    
    $players   = 'game_data_players';
    $races     = 'races';
    $starstbl  = 'game_data_stars';
    $skillstbl = 'game_data_skills';
    
    $status = true;
    // Drop and re-create game data tables.
    $status &= Table::createTable($players,  $core_tables[$players]);
    $status &= Table::createTable($races,    $core_tables[$races]);
    $status &= Table::createTable($starstbl, $core_tables[$starstbl]);
    $status &= Table::createTable($skillstbl,$core_tables[$skillstbl]);

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

    foreach ($skillarray as $grp => $skills) {
        foreach ($skills as $id => $s) {
            $status &= mysql_query("INSERT INTO $skillstbl(skill_id, name, cat) VALUES ($id, '".mysql_real_escape_string($s)."', '$grp')");
        }
    }
    
    return $status;
}

public static function mkHRS(array $HRSs)
{
    global $CT_cols, $core_tables;
    
    $allowed_fields = array(
        'mvp', 'cp', 'td', 'intcpt', 'bh', 'si', 'ki', 'cas', 'tdcas', 'tcas', 'smp', 'elo', 
        'gf', 'ga', 'sdiff', 'won', 'lost', 'draw', 'swon', 'slost', 'sdraw', 'played', 'win_pct',
    );
    $query = 'CREATE FUNCTION getPTS(tid '.$CT_cols[T_OBJ_TEAM].', trid '.$CT_cols[T_NODE_TOURNAMENT].')
        RETURNS '.$CT_cols['pts'].'
        NOT DETERMINISTIC
        READS SQL DATA
    BEGIN
        DECLARE rs TINYINT UNSIGNED DEFAULT NULL;
        SELECT tours.rs INTO rs FROM tours WHERE tour_id = trid;
        IF rs IS NULL THEN
            RETURN NULL;
        END IF;
        
        CASE rs
    ';
    foreach ($HRSs as $nr => $rs) {
        if (empty($rs['points'])) {
            continue;
        }
        $pts = preg_replace('/\[(\w*)\]/', "IFNULL(SUM(\\1),0)", $rs['points']);
        $query .= "WHEN $nr THEN RETURN (SELECT $pts FROM mv_teams WHERE f_tid = tid AND f_trid = trid);\n";
    }
    $query .= '
        END CASE;
        
        RETURN NULL;
    END';
    
    return $query;
}

public static function installProcsAndFuncs($install = true)
{
    global $CT_cols, $core_tables, $rules;

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
            ga     = IFNULL((SELECT SUM(IF(team1_id = tid, team2_score, IF(team2_id = tid, team1_score, 0))) REGEX_REPLACE_HERE), 0),  
            tcas   = IFNULL((SELECT SUM(IF(team1_id = tid, tcas1, IF(team2_id = tid, tcas2, 0))) REGEX_REPLACE_HERE), 0), 
            smp    = IFNULL((SELECT SUM(IF(team1_id = tid, smp1, IF(team2_id = tid, smp2, 0))) REGEX_REPLACE_HERE), 0)
    ';
    $mstat_fields_player = preg_replace('/REGEX_REPLACE_HERE/', $mstat_fields_suffix_player, $mstat_fields);
    $mstat_fields_team   = preg_replace('/REGEX_REPLACE_HERE/', $mstat_fields_suffix_team,   $mstat_fields);
    $mstat_fields_coach  = preg_replace('/REGEX_REPLACE_HERE/', $mstat_fields_suffix_coach,  $mstat_fields);
    $mstat_fields_race   = preg_replace('/REGEX_REPLACE_HERE/', $mstat_fields_suffix_race,   $mstat_fields);
    $mstat_fields_coach = preg_replace('/tid/', 'teams.team_id', $mstat_fields_coach);
    $mstat_fields_race  = preg_replace('/tid/', 'teams.team_id', $mstat_fields_race);
    $mstat_fields_stars = preg_replace('/tid/', 'match_data.f_team_id', $mstat_fields_player);

        # ELO
    $elo_matchsync_R0 = '
        SELECT IF(IFNULL((SELECT SUM(played) FROM mv_teams   WHERE f_tid = tid1 REGEX_REPLACE_HERE),FALSE) AND IFNULL(teams.elo,FALSE),   teams.elo, '.T_ELO_R_0.')   INTO Rt1_0 FROM teams   WHERE team_id = tid1;
        SELECT IF(IFNULL((SELECT SUM(played) FROM mv_teams   WHERE f_tid = tid2 REGEX_REPLACE_HERE),FALSE) AND IFNULL(teams.elo,FALSE),   teams.elo, '.T_ELO_R_0.')   INTO Rt2_0 FROM teams   WHERE team_id = tid2;
        SELECT IF(IFNULL((SELECT SUM(played) FROM mv_coaches WHERE f_cid = cid1 REGEX_REPLACE_HERE),FALSE) AND IFNULL(coaches.elo,FALSE), coaches.elo, '.T_ELO_R_0.') INTO Rc1_0 FROM coaches WHERE coach_id = cid1;
        SELECT IF(IFNULL((SELECT SUM(played) FROM mv_coaches WHERE f_cid = cid2 REGEX_REPLACE_HERE),FALSE) AND IFNULL(coaches.elo,FALSE), coaches.elo, '.T_ELO_R_0.') INTO Rc2_0 FROM coaches WHERE coach_id = cid2;
    ';    
    $elo_matchsync_R0_alltime = preg_replace('/REGEX_REPLACE_HERE/', '', $elo_matchsync_R0);
    $elo_matchsync_R0_tour    = preg_replace('/REGEX_REPLACE_HERE/', 'AND f_trid = trid', $elo_matchsync_R0);
    
        # Streaks
    $streaks_TBL1_team = '
        SELECT 
            date_played, 
            IF((team1_id = obj_id AND team1_score > team2_score) OR (team2_id = obj_id AND team1_score < team2_score), "W", IF(team1_score = team2_score, "D", "L")) AS "result"
        FROM matches WHERE date_played IS NOT NULL AND (team1_id = obj_id OR team2_id = obj_id) AND IF(trid IS NULL, TRUE, f_tour_id = trid) ORDER BY date_played ASC
    ';
    $streaks_TBL1_coach = '
        SELECT 
            date_played, 
            IF((team1_id = teams.team_id AND team1_score > team2_score) OR (team2_id = teams.team_id AND team1_score < team2_score), "W", IF(team1_score = team2_score, "D", "L")) AS "result"
        FROM matches, teams WHERE date_played IS NOT NULL AND IF(trid IS NULL, TRUE, f_tour_id = trid) AND owned_by_coach_id = obj_id AND (team1_id = teams.team_id OR team2_id = teams.team_id) ORDER BY date_played ASC
    ';
    $streaks_TBL2 = '
        SELECT 
            *,
            (
                SELECT COUNT(*) 
                FROM (REGEX_REPLACE_TBL1) AS G
                WHERE G.result <> TBL1.result 
                AND G.date_played <= TBL1.date_played
            ) AS RunGroup 
        FROM (REGEX_REPLACE_TBL1) AS TBL1
    ';
    $streaks_TBL3 = '
        SELECT 
            result, 
            MIN(date_played) as StartDate, 
            MAX(date_played) as EndDate, 
            COUNT(*) as games
        FROM (REGEX_REPLACE_TBL2) AS TBL2
        GROUP BY result, RunGroup
        ORDER BY date_played
    ';
    $streaks_final = '
        SET swon  = (SELECT IFNULL(MAX(games),0) FROM (REGEX_REPLACE_TBL3) AS TBL3 WHERE result = "W");
        SET slost = (SELECT IFNULL(MAX(games),0) FROM (REGEX_REPLACE_TBL3) AS TBL3 WHERE result = "L");
        SET sdraw = (SELECT IFNULL(MAX(games),0) FROM (REGEX_REPLACE_TBL3) AS TBL3 WHERE result = "D");
    ';
    $streaks_team = preg_replace('/REGEX_REPLACE_TBL3/', $streaks_TBL3, $streaks_final);
    $streaks_team = preg_replace('/REGEX_REPLACE_TBL2/', $streaks_TBL2, $streaks_team);
    $streaks_team = preg_replace('/REGEX_REPLACE_TBL1/', $streaks_TBL1_team, $streaks_team);
    $streaks_coach = preg_replace('/REGEX_REPLACE_TBL3/', $streaks_TBL3, $streaks_final);
    $streaks_coach = preg_replace('/REGEX_REPLACE_TBL2/', $streaks_TBL2, $streaks_coach);
    $streaks_coach = preg_replace('/REGEX_REPLACE_TBL1/', $streaks_TBL1_coach, $streaks_coach);
    
    $routines = array(
    
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
         *  ELO
         */        

        'CREATE PROCEDURE syncAllELOs() 
            NOT DETERMINISTIC
            CONTAINS SQL
        BEGIN
            DECLARE done INT DEFAULT 0;
            DECLARE trid '.$CT_cols[T_NODE_TOURNAMENT].';
            DECLARE cur CURSOR FOR SELECT tour_id FROM tours;
            DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
            OPEN cur;
            REPEAT
                FETCH cur INTO trid;
                IF NOT done THEN
                    CALL syncELOTour(trid);
                END IF;
            UNTIL done END REPEAT;
            CLOSE cur;
            CALL syncELOTour(NULL);
        END',
         
        'CREATE PROCEDURE syncELOTour(IN trid '.$CT_cols[T_NODE_TOURNAMENT].') 
            NOT DETERMINISTIC
            CONTAINS SQL
        BEGIN
            IF trid IS NULL THEN
                UPDATE teams SET elo = NULL;
                SELECT syncELOMatch(NULL, matches.match_id) FROM matches WHERE matches.date_played IS NOT NULL ORDER BY matches.date_played ASC;
            ELSE
                UPDATE mv_teams SET elo = NULL WHERE f_trid = trid;
                SELECT syncELOMatch(trid, matches.match_id) FROM matches WHERE matches.date_played IS NOT NULL AND matches.f_tour_id = trid ORDER BY matches.date_played ASC;
            END IF;
        END',

        // If trid is NULL the sets all-time ELO (via. all played matches).
        'CREATE FUNCTION syncELOMatch(trid '.$CT_cols[T_NODE_TOURNAMENT].', mid '.$CT_cols[T_NODE_MATCH].') 
            RETURNS BOOLEAN
            NOT DETERMINISTIC
            CONTAINS SQL
        BEGIN
            DECLARE Rt1_0, Rt2_0, Rc1_0, Rc2_0, Rt1, Rt2, Rc1, Rc2 '.$CT_cols['elo'].';
            DECLARE Et1, Et2, Ec1, Ec2 FLOAT;
            DECLARE S1, S2 FLOAT;
            DECLARE tid1, tid2 '.$CT_cols[T_OBJ_TEAM].';
            DECLARE cid1, cid2 '.$CT_cols[T_OBJ_COACH].';
            
            SELECT 
                team1_id, team2_id, t1.owned_by_coach_id, t2.owned_by_coach_id, 
                IF(team1_score = team2_score, '.T_ELO_PTS_DRAW.', IF(team1_score > team2_score,'.T_ELO_PTS_WON.','.T_ELO_PTS_LOST.')), 
                IF(team1_score = team2_score, '.T_ELO_PTS_DRAW.', IF(team1_score < team2_score,'.T_ELO_PTS_WON.','.T_ELO_PTS_LOST.'))
            INTO tid1, tid2, cid1, cid2, S1, S2 
            FROM matches, teams AS t1, teams AS t2 WHERE match_id = mid AND team1_id = t1.team_id AND team2_id = t2.team_id;
            
            IF trid IS NULL THEN '.$elo_matchsync_R0_alltime.'
            ELSE '.$elo_matchsync_R0_tour.'
            END IF;

            SET Et1 = ELO_E(Rt1_0, Rt2_0);
            SET Et2 = ELO_E(Rt2_0, Rt1_0);
            SET Ec1 = ELO_E(Rc1_0, Rc2_0);
            SET Ec2 = ELO_E(Rc2_0, Rc1_0);

            SET Rt1 = ELO_R(Rt1_0, S1, Et1);
            SET Rt2 = ELO_R(Rt2_0, S2, Et2);
            SET Rc1 = ELO_R(Rc1_0, S1, Ec1);
            SET Rc2 = ELO_R(Rc2_0, S2, Ec2);

            IF trid IS NULL THEN
                UPDATE teams   SET elo = Rt1 WHERE team_id = tid1;
                UPDATE teams   SET elo = Rt2 WHERE team_id = tid2;
                UPDATE coaches SET elo = Rc1 WHERE coach_id = cid1;
                UPDATE coaches SET elo = Rc2 WHERE coach_id = cid2;                
            ELSE
                UPDATE mv_teams   SET elo = Rt1 WHERE f_tid = tid1;
                UPDATE mv_teams   SET elo = Rt2 WHERE f_tid = tid2;
                UPDATE mv_coaches SET elo = Rc1 WHERE f_cid = cid1;
                UPDATE mv_coaches SET elo = Rc2 WHERE f_cid = cid2;
            END IF;
            
            RETURN TRUE;
        END',

        'CREATE FUNCTION ELO_E(R1 '.$CT_cols['elo'].', R2 '.$CT_cols['elo'].') 
            RETURNS FLOAT
            DETERMINISTIC
            NO SQL
        BEGIN
            RETURN 1/(1+POW(10,(R2-R1)/'.T_ELO_D.'));
        END',
        
        'CREATE FUNCTION ELO_R(R_0 '.$CT_cols['elo'].', S FLOAT, E FLOAT) 
            RETURNS '.$CT_cols['elo'].'
            DETERMINISTIC
            NO SQL
        BEGIN
            RETURN (R_0 + '.T_ELO_K.'*(S-E));
        END',

        /* 
         *  Streaks
         */
         
        'CREATE PROCEDURE syncAllStreaks()
            NOT DETERMINISTIC
            CONTAINS SQL
        BEGIN
            DECLARE done INT DEFAULT 0;
            DECLARE trid '.$CT_cols[T_NODE_TOURNAMENT].';
            DECLARE tid '.$CT_cols[T_OBJ_TEAM].';
            DECLARE cid '.$CT_cols[T_OBJ_COACH].';
            DECLARE swon,sdraw,slost '.$CT_cols['streak'].';
            DECLARE cur_t CURSOR FOR SELECT team_id FROM teams;
            DECLARE cur_c CURSOR FOR SELECT coach_id FROM coaches;
            DECLARE cur_mv_t CURSOR FOR SELECT f_trid, f_tid FROM mv_teams;
            DECLARE cur_mv_c CURSOR FOR SELECT f_trid, f_cid FROM mv_coaches;
            DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
            
            OPEN cur_c;
            REPEAT
                FETCH cur_c INTO cid;
                IF NOT done THEN
                    CALL getStreaks('.T_OBJ_COACH.', cid, NULL, swon,sdraw,slost);
                    UPDATE coaches SET coaches.swon = swon, coaches.sdraw = sdraw, coaches.slost = slost WHERE coaches.coach_id = cid;
                END IF;
            UNTIL done END REPEAT;
            CLOSE cur_c;
            SET done = 0;
            
            OPEN cur_mv_c;
            REPEAT
                FETCH cur_mv_c INTO trid, cid;
                IF NOT done THEN
                    CALL getStreaks('.T_OBJ_COACH.', cid, trid, swon,sdraw,slost);
                    UPDATE mv_coaches SET mv_coaches.swon = swon, mv_coaches.sdraw = sdraw, mv_coaches.slost = slost WHERE mv_coaches.f_cid = cid AND mv_coaches.f_trid = trid;
                END IF;
            UNTIL done END REPEAT;
            CLOSE cur_mv_c;
            SET done = 0;

            OPEN cur_t;
            REPEAT
                FETCH cur_t INTO tid;
                IF NOT done THEN
                    CALL getStreaks('.T_OBJ_TEAM.', tid, NULL, swon,sdraw,slost);
                    UPDATE teams SET teams.swon = swon, teams.sdraw = sdraw, teams.slost = slost WHERE teams.team_id = tid;
                END IF;
            UNTIL done END REPEAT;
            CLOSE cur_t;
            SET done = 0;

            OPEN cur_mv_t;
            REPEAT
                FETCH cur_mv_t INTO trid, tid;
                IF NOT done THEN
                    CALL getStreaks('.T_OBJ_TEAM.', tid, trid, swon,sdraw,slost);
                    UPDATE mv_teams SET mv_teams.swon = swon, mv_teams.sdraw = sdraw, mv_teams.slost = slost WHERE mv_teams.f_tid = tid AND mv_teams.f_trid = trid;
                END IF;
            UNTIL done END REPEAT;
            CLOSE cur_mv_t;
            SET done = 0;
        END',         
         
        'CREATE PROCEDURE syncStreaksMatch(IN mid '.$CT_cols[T_NODE_MATCH].')
            NOT DETERMINISTIC
            CONTAINS SQL
        BEGIN
            DECLARE tid1, tid2 '.$CT_cols[T_OBJ_TEAM].';
            DECLARE cid1, cid2 '.$CT_cols[T_OBJ_COACH].';
            DECLARE trid '.$CT_cols[T_NODE_TOURNAMENT].';
            DECLARE swon,sdraw,slost '.$CT_cols['streak'].';
            SELECT team1_id, team2_id, t1.owned_by_coach_id,t2.owned_by_coach_id, f_tour_id 
            INTO tid1,tid2, cid1,cid2, trid 
            FROM matches,teams AS t1, teams AS t2 WHERE matches.match_id = mid AND matches.team1_id = t1.team_id AND matches.team2_id = t2.team_id;
            
            CALL getStreaks('.T_OBJ_TEAM.', tid1, NULL, swon,sdraw,slost);
            UPDATE teams SET teams.swon = swon, teams.sdraw = sdraw, teams.slost = slost WHERE teams.team_id = tid1;
            CALL getStreaks('.T_OBJ_TEAM.', tid2, NULL, swon,sdraw,slost);
            UPDATE teams SET teams.swon = swon, teams.sdraw = sdraw, teams.slost = slost WHERE teams.team_id = tid2;

            CALL getStreaks('.T_OBJ_TEAM.', tid1, trid, swon,sdraw,slost);
            UPDATE mv_teams SET mv_teams.swon = swon, mv_teams.sdraw = sdraw, mv_teams.slost = slost WHERE mv_teams.f_tid = tid1;
            CALL getStreaks('.T_OBJ_TEAM.', tid2, trid, swon,sdraw,slost);
            UPDATE mv_teams SET mv_teams.swon = swon, mv_teams.sdraw = sdraw, mv_teams.slost = slost WHERE mv_teams.f_tid = tid2;
            
            CALL getStreaks('.T_OBJ_COACH.', cid1, NULL, swon,sdraw,slost);
            UPDATE coaches SET coaches.swon = swon, coaches.sdraw = sdraw, coaches.slost = slost WHERE coaches.coach_id = cid1;
            CALL getStreaks('.T_OBJ_COACH.', cid2, NULL, swon,sdraw,slost);
            UPDATE coaches SET coaches.swon = swon, coaches.sdraw = sdraw, coaches.slost = slost WHERE coaches.coach_id = cid2;

            CALL getStreaks('.T_OBJ_COACH.', cid1, trid, swon,sdraw,slost);
            UPDATE mv_coaches SET mv_coaches.swon = swon, mv_coaches.sdraw = sdraw, mv_coaches.slost = slost WHERE mv_coaches.f_cid = cid1;
            CALL getStreaks('.T_OBJ_COACH.', cid2, trid, swon,sdraw,slost);
            UPDATE mv_coaches SET mv_coaches.swon = swon, mv_coaches.sdraw = sdraw, mv_coaches.slost = slost WHERE mv_coaches.f_cid = cid2;
        END',
        
        // If trid is NULL returns all-time streaks (across all leagues).
        'CREATE PROCEDURE getStreaks(IN obj TINYINT UNSIGNED, IN obj_id '.$CT_cols[T_OBJ_TEAM].', IN trid '.$CT_cols[T_NODE_TOURNAMENT].',
        OUT swon '.$CT_cols['streak'].', OUT sdraw '.$CT_cols['streak'].', OUT slost '.$CT_cols['streak'].'
        )
            DETERMINISTIC
            READS SQL DATA
        BEGIN 
            IF obj = '.T_OBJ_TEAM.' THEN 
                '.$streaks_team.'
            ELSEIF obj = '.T_OBJ_COACH.' THEN 
                '.$streaks_coach.'
            END IF;
        END',

        /* 
         *  Team count sync
         */
         
        'CREATE PROCEDURE syncAllTeamCnts()
            NOT DETERMINISTIC
            CONTAINS SQL
        BEGIN
            UPDATE races      SET team_cnt = getTeamCnt('.T_OBJ_RACE.', race_id, NULL);
            UPDATE coaches    SET team_cnt = getTeamCnt('.T_OBJ_COACH.', coach_id, NULL);
            UPDATE mv_races   SET team_cnt = getTeamCnt('.T_OBJ_RACE.', f_rid, f_trid);
            UPDATE mv_coaches SET team_cnt = getTeamCnt('.T_OBJ_COACH.', f_cid, f_trid);
        END',
         
        'CREATE FUNCTION getTeamCnt(obj TINYINT UNSIGNED, obj_id '.$CT_cols[T_OBJ_TEAM].', trid '.$CT_cols[T_NODE_TOURNAMENT].')
            RETURNS '.$CT_cols['team_cnt'].'
            NOT DETERMINISTIC
            READS SQL DATA
        BEGIN
            IF obj = '.T_OBJ_RACE.' THEN 
                IF trid IS NULL THEN
                    RETURN (SELECT COUNT(*) FROM teams WHERE f_race_id = obj_id);
                ELSE
                    RETURN (SELECT COUNT(*) FROM teams WHERE f_race_id = obj_id AND 
                        0 < (SELECT COUNT(*) FROM matches WHERE f_tour_id = trid AND (team1_id = team_id OR team2_id = team_id) LIMIT 1));
                END IF;
            ELSEIF obj = '.T_OBJ_COACH.' THEN 
                IF trid IS NULL THEN
                    RETURN (SELECT COUNT(*) FROM teams WHERE owned_by_coach_id = obj_id);
                ELSE
                    RETURN (SELECT COUNT(*) FROM teams WHERE owned_by_coach_id = obj_id AND 
                        0 < (SELECT COUNT(*) FROM matches WHERE f_tour_id = trid AND (team1_id = team_id OR team2_id = team_id) LIMIT 1));
                END IF;
            END IF;
        END',

        /* 
         *  Won tours count
         */

        'CREATE PROCEDURE syncAllWTCnts()
            NOT DETERMINISTIC
            CONTAINS SQL
        BEGIN
            UPDATE races SET wt_cnt = getWTCnt('.T_OBJ_RACE.', race_id);
            UPDATE teams SET wt_cnt = getWTCnt('.T_OBJ_TEAM.', team_id);
            UPDATE coaches SET wt_cnt = getWTCnt('.T_OBJ_COACH.', coach_id);
        END',
         
        'CREATE FUNCTION getWTCnt(obj TINYINT UNSIGNED, obj_id '.$CT_cols[T_OBJ_TEAM].')
            RETURNS '.$CT_cols['team_cnt'].'
            NOT DETERMINISTIC
            READS SQL DATA
        BEGIN
            IF obj = '.T_OBJ_TEAM.' THEN 
                RETURN (SELECT COUNT(*) FROM matches WHERE round = '.RT_FINAL.' AND (team1_id = obj_id OR team2_id = obj_id));
            ELSEIF obj = '.T_OBJ_COACH.' THEN 
                RETURN (SELECT COUNT(*) FROM matches,teams WHERE round = '.RT_FINAL.' AND (team1_id = teams.team_id OR team2_id = teams.team_id) AND owned_by_coach_id = obj_id);
            ELSEIF obj = '.T_OBJ_RACE.' THEN 
                RETURN (SELECT COUNT(*) FROM matches,teams WHERE round = '.RT_FINAL.' AND (team1_id = teams.team_id OR team2_id = teams.team_id) AND f_race_id = obj_id);
            END IF;
        END',
        
        /* 
         *  Sync all-time win percentages.
         */        
        
        'CREATE PROCEDURE syncAllWinPcts()
            NOT DETERMINISTIC
            CONTAINS SQL
        BEGIN
            UPDATE races SET win_pct = getWinPct('.T_OBJ_RACE.', race_id);
            UPDATE teams SET win_pct = getWinPct('.T_OBJ_TEAM.', team_id);
            UPDATE coaches SET win_pct = getWinPct('.T_OBJ_COACH.', coach_id);
            UPDATE players SET win_pct = getWinPct('.T_OBJ_PLAYER.', player_id);
        END',
         
        'CREATE FUNCTION getWinPct(obj TINYINT UNSIGNED, obj_id '.$CT_cols[T_OBJ_TEAM].')
            RETURNS '.$CT_cols['win_pct'].'
            NOT DETERMINISTIC
            READS SQL DATA
        BEGIN
            IF obj = '.T_OBJ_TEAM.' THEN 
                RETURN (SELECT 100*IFNULL(SUM(won)/SUM(played),0) FROM mv_teams WHERE f_tid = obj_id);
            ELSEIF obj = '.T_OBJ_COACH.' THEN 
                RETURN (SELECT 100*IFNULL(SUM(won)/SUM(played),0) FROM mv_coaches WHERE f_cid = obj_id);
            ELSEIF obj = '.T_OBJ_RACE.' THEN 
                RETURN (SELECT 100*IFNULL(SUM(won)/SUM(played),0) FROM mv_races WHERE f_rid = obj_id);
            ELSEIF obj = '.T_OBJ_PLAYER.' THEN 
                RETURN (SELECT 100*IFNULL(SUM(won)/SUM(played),0) FROM mv_players WHERE f_pid = obj_id);
            END IF;
        END',        

        /* 
         *  Sync ranking system points.
         */
         
        'CREATE PROCEDURE syncAllPTS()
            NOT DETERMINISTIC
            CONTAINS SQL
        BEGIN
            UPDATE mv_teams SET pts = getPTS(f_tid, f_trid);
        END',

        /* 
         *  Object relations
         */
        
        'CREATE PROCEDURE syncAllRels()
            NOT DETERMINISTIC
            CONTAINS SQL
        BEGIN
            CALL setRels('.T_OBJ_PLAYER.', NULL);
            CALL setRels('.T_OBJ_TEAM.', NULL);
        END',
        'CREATE PROCEDURE setRels(IN obj TINYINT UNSIGNED, IN obj_id '.$CT_cols[T_OBJ_PLAYER].')
            NOT DETERMINISTIC
            CONTAINS SQL
        BEGIN
            IF obj = '.T_OBJ_PLAYER.' THEN
                UPDATE players,teams,coaches,races SET players.f_rname = races.name, players.f_tname = teams.name, players.f_cname = coaches.name, f_rid = races.race_id, f_cid = coaches.coach_id 
                WHERE owned_by_team_id = team_id AND owned_by_coach_id = coach_id AND f_race_id = race_id AND IF(obj_id,player_id = obj_id,TRUE);
            ELSEIF obj = '.T_OBJ_TEAM.' THEN
                UPDATE teams,coaches,races SET teams.f_rname = races.name, teams.f_cname = coaches.name
                WHERE owned_by_coach_id = coach_id AND f_race_id = race_id AND IF(obj_id,team_id = obj_id,TRUE);
            END IF;
        END',

        /* 
         *  MV syncs
         */

        'CREATE PROCEDURE syncAllMVs()
            NOT DETERMINISTIC
            CONTAINS SQL
        BEGIN
            SELECT syncMVplayer(f_player_id, f_tour_id) FROM (SELECT f_player_id,f_tour_id FROM match_data GROUP BY f_player_id,f_tour_id) AS tmpTbl;
            SELECT syncMVteam(f_team_id,     f_tour_id) FROM (SELECT f_team_id,  f_tour_id FROM match_data GROUP BY f_team_id,  f_tour_id) AS tmpTbl;
            SELECT syncMVcoach(f_coach_id,   f_tour_id) FROM (SELECT f_coach_id, f_tour_id FROM match_data GROUP BY f_coach_id, f_tour_id) AS tmpTbl;
            SELECT syncMVrace(f_race_id,     f_tour_id) FROM (SELECT f_race_id,  f_tour_id FROM match_data GROUP BY f_race_id,  f_tour_id) AS tmpTbl;
        END',

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
            IF pid > '.ID_MERCS.' THEN
                UPDATE mv_players '.$mstat_fields_player.' WHERE f_pid = pid AND f_trid = trid;
            ELSE
                UPDATE mv_players '.$mstat_fields_stars.' WHERE f_pid = pid AND f_trid = trid;
            END IF;
            UPDATE mv_players SET win_pct = IF(played = 0, 0, 100*won/played), sdiff = CAST(gf-ga AS SIGNED) WHERE f_pid = pid AND f_trid = trid;
            
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
            UPDATE mv_teams '.$mstat_fields_team.' WHERE f_tid = tid AND f_trid = trid;
            UPDATE mv_teams SET win_pct = IF(played = 0, 0, 100*won/played), sdiff = CAST(gf-ga AS SIGNED) WHERE f_tid = tid AND f_trid = trid;

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
            UPDATE mv_coaches '.$mstat_fields_coach.' WHERE f_cid = cid AND f_trid = trid;
            UPDATE mv_coaches SET win_pct = IF(played = 0, 0, 100*won/played), sdiff = CAST(gf-ga AS SIGNED) WHERE f_cid = cid AND f_trid = trid;

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
            UPDATE mv_races '.$mstat_fields_race.' WHERE f_rid = rid AND f_trid = trid;
            UPDATE mv_races SET win_pct = IF(played = 0, 0, 100*won/played), sdiff = CAST(gf-ga AS SIGNED) WHERE f_rid = rid AND f_trid = trid;

            RETURN EXISTS(SELECT COUNT(*) FROM mv_races WHERE f_rid = rid AND f_trid = trid);
        END',
        
        /* 
         *  Dynamic (object) properties calculators
         */
        
        'CREATE PROCEDURE syncAllDPROPS()
            NOT DETERMINISTIC
            CONTAINS SQL
        BEGIN
            UPDATE players SET players.name = players.name;
            UPDATE teams SET teams.name = teams.name;
        END',
        
        'CREATE PROCEDURE getPlayerDProps(
            IN pid '.$CT_cols[T_OBJ_PLAYER].',
            OUT inj_ma '.$CT_cols['chr'].', OUT inj_av '.$CT_cols['chr'].', OUT inj_ag '.$CT_cols['chr'].', OUT inj_st '.$CT_cols['chr'].', OUT inj_ni '.$CT_cols['chr'].',
            OUT ma '.$CT_cols['chr'].',     OUT av '.$CT_cols['chr'].',     OUT ag '.$CT_cols['chr'].',     OUT st '.$CT_cols['chr'].',
            OUT value '.$CT_cols['pv'].', OUT status '.$core_tables['players']['status'].', OUT date_died '.$core_tables['players']['date_died'].'
        )
            NOT DETERMINISTIC
            READS SQL DATA
        BEGIN
            DECLARE ach_ma,ach_st,ach_ag,ach_av, def_ma,def_st,def_ag,def_av '.$CT_cols['chr'].';
            DECLARE cnt_skills_norm, cnt_skills_doub TINYINT UNSIGNED;
            DECLARE extra_val '.$CT_cols['pv'].';
            DECLARE f_pos_id '.$CT_cols['pos_id'].';

            SELECT 
                players.f_pos_id, players.extra_val, players.ach_ma, players.ach_st, players.ach_ag, players.ach_av
            INTO
                f_pos_id, extra_val, ach_ma, ach_st, ach_ag, ach_av
            FROM players WHERE player_id = pid;
            
            SET cnt_skills_norm = (SELECT COUNT(*) FROM players_skills WHERE f_pid = pid AND type = "N");
            SET cnt_skills_doub = (SELECT COUNT(*) FROM players_skills WHERE f_pid = pid AND type = "D");
        
            SELECT 
                IFNULL(SUM(IF(inj = '.NI.', 1, 0) + IF(agn1 = '.NI.', 1, 0) + IF(agn2 = '.NI.', 1, 0)), 0), 
                IFNULL(SUM(IF(inj = '.MA.', 1, 0) + IF(agn1 = '.MA.', 1, 0) + IF(agn2 = '.MA.', 1, 0)), 0), 
                IFNULL(SUM(IF(inj = '.AV.', 1, 0) + IF(agn1 = '.AV.', 1, 0) + IF(agn2 = '.AV.', 1, 0)), 0), 
                IFNULL(SUM(IF(inj = '.AG.', 1, 0) + IF(agn1 = '.AG.', 1, 0) + IF(agn2 = '.AG.', 1, 0)), 0), 
                IFNULL(SUM(IF(inj = '.ST.', 1, 0) + IF(agn1 = '.ST.', 1, 0) + IF(agn2 = '.ST.', 1, 0)), 0)
            INTO 
                inj_ni,inj_ma,inj_av,inj_ag,inj_st
            FROM match_data WHERE f_player_id = pid;

            SET value = (SELECT cost FROM game_data_players WHERE game_data_players.pos_id = f_pos_id)
                + (ach_ma + ach_av) * 30000
                + ach_ag            * 40000
                + ach_st            * 50000
                + cnt_skills_norm   * 20000
                + cnt_skills_doub   * 30000
                + extra_val;


            SELECT 
                game_data_players.ma, game_data_players.st, game_data_players.ag, game_data_players.av
            INTO 
                def_ma,def_st,def_ag,def_av
            FROM game_data_players WHERE game_data_players.pos_id = f_pos_id;
            SET ma = (ach_ma + def_ma) - inj_ma;
            SET st = (ach_st + def_st) - inj_st;
            SET ag = (ach_ag + def_ag) - inj_ag;
            SET av = (ach_av + def_av) - inj_av;
                
            SET status = getPlayerStatus(pid, -1);
            
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
            
            SET tv = (SELECT IFNULL(SUM(value),0) FROM players WHERE owned_by_team_id = tid AND players.status != '.MNG.')
                + rerolls      * (SELECT cost_rr FROM races WHERE races.race_id = f_race_id)
                + fan_factor   * '.$rules['cost_fan_factor'].'
                + cheerleaders * '.$rules['cost_cheerleaders'].'
                + apothecary   * '.$rules['cost_apothecary'].'
                + ass_coaches  * '.$rules['cost_ass_coaches'].';
        END',
        
        // Sync all
        'CREATE PROCEDURE syncAll()
            NOT DETERMINISTIC
            CONTAINS SQL
        BEGIN
            CALL syncAllMVs();
            CALL syncAllDPROPS();
            CALL syncAllRels();
            CALL syncAllPTS();
            CALL syncAllWinPcts();
            CALL syncAllWTCnts();
            CALL syncAllTeamCnts();
            CALL syncAllStreaks();
            CALL syncAllELOs();
        END',
    );
    global $hrs;
    $routines[] = self::mkHRS($hrs);

    $status = true;
   
    foreach ($routines as $r) {
        $matches = array();
        if (preg_match('/^CREATE FUNCTION (\w*)\(/', $r, $matches)) {
            $status &= mysql_query('DROP FUNCTION IF EXISTS '.$matches[1]);
        }
        $matches = array();
        if (preg_match('/^CREATE PROCEDURE (\w*)\(/', $r, $matches)) {
            $status &= mysql_query('DROP PROCEDURE IF EXISTS '.$matches[1]);
        }
    }

    if (!$install) {
        return $status;
    }

    foreach ($routines as $r) {
        $status &= (mysql_query($r) or die(mysql_error()."\nCODE:\n-----\n\n".$r));
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

public static function installMVs($delIfExists) {
    
    global $core_tables;
    $status = true;
    foreach ($core_tables as $name => $tbl) {
        if (!preg_match('/^mv\_/', $name))
            continue;
            
        if ($delIfExists) {
            $status &= mysql_query("DROP TABLE IF EXISTS $name");
        }
        $status &= Table::createTable($name,$core_tables[$name]);
    }
    
    // Add indicies
    $indicies = array(
        'idx_p_tr' => array('tbl' => 'mv_players',  'idx' => '(f_pid,f_trid)'),
        'idx_t_tr' => array('tbl' => 'mv_teams',    'idx' => '(f_tid,f_trid)'),
        'idx_c_tr' => array('tbl' => 'mv_coaches',  'idx' => '(f_cid,f_trid)'),
        'idx_r_tr' => array('tbl' => 'mv_races',    'idx' => '(f_rid,f_trid)'),
        
    );
    foreach ($indicies as $name => $def) {
        @mysql_query("DROP INDEX $name ON $def[tbl]");
        $status &= mysql_query("ALTER TABLE $def[tbl] ADD INDEX $name $def[idx]");
    }
    
    return $status;
}

}

?>
