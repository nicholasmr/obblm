<?php

/*
    This file contains the old MySQL triggers subsystem no longer used.
    This is purely for backup/ dev. reference.
*/

public static function setTriggers($set = true)
{
    global $CT_cols, $core_tables;
    
    /* 
     *  Re-useable code-chunks for triggers.
     */
     
    // Match data
    $match_data_after = '
        BEGIN 
            /* General and MV related */
            DECLARE retval BOOLEAN; 
            DECLARE pid '.$CT_cols[T_OBJ_PLAYER].' DEFAULT NULL;
            DECLARE tid '.$CT_cols[T_OBJ_TEAM].' DEFAULT NULL;
            DECLARE cid '.$CT_cols[T_OBJ_COACH].' DEFAULT NULL;
            DECLARE rid '.$CT_cols[T_OBJ_RACE].' DEFAULT NULL;
            DECLARE trid '.$CT_cols[T_NODE_TOURNAMENT].' DEFAULT NULL;
            
            /* Player DPROPS */
            DECLARE inj_ma,inj_av,inj_ag,inj_st,inj_ni, ma,av,ag,st '.$CT_cols['chr'].';
            DECLARE value '.$CT_cols['pv'].';
            DECLARE status '.$core_tables['players']['status'].';
            DECLARE date_died '.$core_tables['players']['date_died'].'; 

            /* Common used fields */
            SET pid = REGEX_REPLACE.f_player_id;
            SET trid = REGEX_REPLACE.f_tour_id;
            CALL getObjParents('.T_OBJ_PLAYER.', pid, tid, cid, rid);

            /* Update MVs */
            SET retval = syncMVplayer(pid, trid);
            SET retval = syncMVteam(tid, trid);
            SET retval = syncMVcoach(cid, trid);
            SET retval = syncMVrace(rid, trid);
            
            /* Update player DPROPS */            
            CALL getPlayerDProps(pid, inj_ma,inj_av,inj_ag,inj_st,inj_ni, ma,av,ag,st, value,status,date_died);
            UPDATE players 
                SET players.inj_ma = inj_ma, players.inj_av = inj_av, players.inj_ag = inj_ag, players.inj_st = inj_st, players.inj_ni = inj_ni,
                    players.ma = ma, players.av = av, players.ag = ag, players.st = st, 
                    players.value = value, players.status = status, players.date_died = date_died
                WHERE players.player_id = pid;
        END';
        
    // Players
    # Win pcts, streaks etc. are default = 0 by column definitions.
    $_players_relations = 'CALL getPlayerRels(NEW.player_id, NEW.f_cid, NEW.f_rid, NEW.f_cname, NEW.f_rname, NEW.f_tname, NEW.f_pos_name);';
    $_players_p_DPROPS = 'CALL getPlayerDProps(NEW.player_id, NEW.inj_ma,NEW.inj_av,NEW.inj_ag,NEW.inj_st,NEW.inj_ni, NEW.ma,NEW.av,NEW.ag,NEW.st, NEW.value, NEW.status, NEW.date_died);';
    $_players_t_DPROPS = '
        DECLARE tv '.$CT_cols['tv'].'; 
        DECLARE ff '.$core_tables['teams']['ff'].';
        CALL getTeamDProps(REGEX_REPLACE.owned_by_team_id, tv, ff); 
        UPDATE teams SET teams.tv = tv WHERE team_id = REGEX_REPLACE.owned_by_team_id;';
    $players_before = "BEGIN $_players_relations $_players_p_DPROPS END";
    $players_after = "BEGIN $_players_t_DPROPS END";
    
    // Teams
    # Win pcts, streaks etc. are default = 0 by column definitions.
    $_teams_relations = 'CALL getTeamRels(NEW.team_id, NEW.f_cname, NEW.f_rname);';
    $_teams_t_DPROPS = 'CALL getTeamDProps(NEW.team_id, NEW.tv, NEW.ff);';
    $_teams_team_cnt = '
        UPDATE races   SET team_cnt = getTeamCnt('.T_OBJ_RACE.',  REGEX_REPLACE.f_race_id, NULL)         WHERE race_id = REGEX_REPLACE.f_race_id;
        UPDATE coaches SET team_cnt = getTeamCnt('.T_OBJ_COACH.', REGEX_REPLACE.owned_by_coach_id, NULL) WHERE coach_id = REGEX_REPLACE.owned_by_coach_id;';
    $_teams_rels_team = '
    ';
        # Only for team updates on change og team's relations.
    $_teams_update_rels = '
        SET @changed_owner = (NEW.owned_by_coach_id != OLD.owned_by_coach_id);
        IF (NEW.name != OLD.name OR @changed_owner) THEN
            UPDATE players SET 
                players.f_cid = NEW.owned_by_coach_id, players.f_cname = NEW.f_cname, players.f_tname = NEW.name
            WHERE owned_by_team_id = OLD.team_id;
            IF (@changed_owner) THEN
                UPDATE mv_players SET f_cid = NEW.owned_by_coach_id WHERE f_tid = OLD.team_id;
                UPDATE mv_teams SET f_cid = NEW.owned_by_coach_id WHERE f_tid = OLD.team_id;
            END IF;
        END IF;';
    $teams_before = "BEGIN $_teams_relations $_teams_t_DPROPS END";
    $teams_after_ins = "BEGIN $_teams_team_cnt END";
    $teams_after_upd = "BEGIN $_teams_team_cnt $_teams_update_rels END";
    $teams_after_del = "BEGIN $_teams_team_cnt END";
    
    // Coaches
    $_coaches_update_rels = '
        IF (NEW.name != OLD.name) THEN
            UPDATE players SET f_cname = NEW.name WHERE f_cid = OLD.coach_id;
            UPDATE teams SET f_cname = NEW.name WHERE owned_by_coach_id = OLD.coach_id;
        END IF;';
    $coaches_after_upd = "BEGIN $_coaches_update_rels END";

    // Players skills
    $players_skills_after = '
        BEGIN
           /* Dirt trick to trigger DPROP refreshes */
           UPDATE players SET value = 0 WHERE player_id = REGEX_REPLACE.f_pid;
           UPDATE teams SET tv = 0 WHERE team_id = (SELECT owned_by_team_id FROM players WHERE player_id = REGEX_REPLACE.f_pid);
        END';

    // Matches
    $matches_setup_rels = '
        /* GENERAL */
        DECLARE ret BOOLEAN;
        DECLARE rid1, rid2 '.$CT_cols[T_OBJ_RACE].';
        DECLARE cid1, cid2 '.$CT_cols[T_OBJ_COACH].';
        DECLARE tid1, tid2 '.$CT_cols[T_OBJ_TEAM].';
        DECLARE trid '.$CT_cols[T_NODE_TOURNAMENT].';

        /* Tour DPROPS */
        DECLARE empty,begun,finished BOOLEAN;
        DECLARE winner '.$CT_cols[T_OBJ_TEAM].';
        
        /* Streaks */
        DECLARE swon,sdraw,slost '.$CT_cols['streak'].';
        
        /* MVs */
        DECLARE done INT DEFAULT 0;
        DECLARE pid '.$CT_cols[T_NODE_TOURNAMENT].';
        DECLARE cur_p1 CURSOR FOR SELECT f_pid FROM mv_players WHERE f_tid = REGEX_REPLACE.team1_id AND f_trid = REGEX_REPLACE.f_tour_id;
        DECLARE cur_p2 CURSOR FOR SELECT f_pid FROM mv_players WHERE f_tid = REGEX_REPLACE.team2_id AND f_trid = REGEX_REPLACE.f_tour_id;
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;
        
        SET trid = REGEX_REPLACE.f_tour_id;
        SELECT t1.f_race_id, t2.f_race_id, t1.owned_by_coach_id, t2.owned_by_coach_id, t1.team_id, t2.team_id
        INTO rid1, rid2, cid1, cid2, tid1, tid2
        FROM teams AS t1, teams AS t2 WHERE t1.team_id = REGEX_REPLACE.team1_id AND t2.team_id = REGEX_REPLACE.team2_id;
    ';
        # Needs $matches_setup_rels.
    $matches_tourDProps = '
        CALL getTourDProps(trid, empty, begun, finished, winner);
        UPDATE tours SET tours.empty = empty, tours.begun = begun, tours.finished = finished, tours.winner = winner WHERE tour_id = trid;
    ';
        # Needs $matches_setup_rels.
    $matches_team_cnt = '
        UPDATE mv_races   SET team_cnt = getTeamCnt('.T_OBJ_RACE.', rid1, trid) WHERE f_trid = trid AND f_rid = rid1;
        UPDATE mv_races   SET team_cnt = getTeamCnt('.T_OBJ_RACE.', rid2, trid) WHERE f_trid = trid AND f_rid = rid2;
        UPDATE mv_coaches SET team_cnt = getTeamCnt('.T_OBJ_COACH.', cid1, trid) WHERE f_trid = trid AND f_cid = cid1;
        UPDATE mv_coaches SET team_cnt = getTeamCnt('.T_OBJ_COACH.', cid2, trid) WHERE f_trid = trid AND f_cid = cid2;
    ';
        # Needs $matches_setup_rels.
    $matches_pts = '
        UPDATE mv_teams SET pts = getPTS(tid1, trid) WHERE f_tid = tid1;
        UPDATE mv_teams SET pts = getPTS(tid2, trid) WHERE f_tid = tid2;
    ';
    
        # Needs $matches_setup_rels.
    $matches_wt_cnt = '
        UPDATE races SET wt_cnt = getWTCnt('.T_OBJ_RACE.', rid1) WHERE race_id = rid1;
        UPDATE races SET wt_cnt = getWTCnt('.T_OBJ_RACE.', rid2) WHERE race_id = rid2;
        UPDATE teams SET wt_cnt = getWTCnt('.T_OBJ_TEAM.', tid1) WHERE team_id = tid1;
        UPDATE teams SET wt_cnt = getWTCnt('.T_OBJ_TEAM.', tid2) WHERE team_id = tid2;
        UPDATE coaches SET wt_cnt = getWTCnt('.T_OBJ_COACH.', cid1) WHERE coach_id = cid1;
        UPDATE coaches SET wt_cnt = getWTCnt('.T_OBJ_COACH.', cid2) WHERE coach_id = cid2;
    ';
        # Needs $matches_setup_rels.
    $matches_win_pct = '
        UPDATE races SET win_pct = getWinPct('.T_OBJ_RACE.', rid1) WHERE race_id = rid1;
        UPDATE races SET win_pct = getWinPct('.T_OBJ_RACE.', rid2) WHERE race_id = rid2;
        UPDATE teams SET win_pct = getWinPct('.T_OBJ_TEAM.', tid1) WHERE team_id = tid1;
        UPDATE teams SET win_pct = getWinPct('.T_OBJ_TEAM.', tid2) WHERE team_id = tid2;
        UPDATE coaches SET win_pct = getWinPct('.T_OBJ_COACH.', cid1) WHERE coach_id = cid1;
        UPDATE coaches SET win_pct = getWinPct('.T_OBJ_COACH.', cid2) WHERE coach_id = cid2;
    ';
        # Needs $matches_setup_rels.
    $matches_streaks = '
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
    ';
        # Needs $matches_setup_rels.
    $matches_MVs = '
        OPEN cur_p1;
        REPEAT
            FETCH cur_p1 INTO pid;
            IF NOT done THEN
                SET ret = syncMVplayer(pid, trid);
            END IF;
        UNTIL done END REPEAT;
        CLOSE cur_p1;
        SET done = 0;

        OPEN cur_p2;
        REPEAT
            FETCH cur_p2 INTO pid;
            IF NOT done THEN
                SET ret = syncMVplayer(pid, trid);
            END IF;
        UNTIL done END REPEAT;
        CLOSE cur_p2;
        SET done = 0;
            
        SET ret = syncMVteam(tid1, trid);
        SET ret = syncMVteam(tid2, trid);
        SET ret = syncMVcoach(cid1, trid);
        SET ret = syncMVcoach(cid2, trid);
        SET ret = syncMVrace(rid1, trid);
        SET ret = syncMVrace(rid2, trid);
    ';
    
    $triggers = array(
    
        // Match data
        'CREATE TRIGGER match_data_a_ins AFTER INSERT ON match_data FOR EACH ROW '.preg_replace('/REGEX_REPLACE/', 'NEW', $match_data_after),
        'CREATE TRIGGER match_data_a_upd AFTER UPDATE ON match_data FOR EACH ROW '.preg_replace('/REGEX_REPLACE/', 'OLD', $match_data_after),
        'CREATE TRIGGER match_data_a_del AFTER DELETE ON match_data FOR EACH ROW '.preg_replace('/REGEX_REPLACE/', 'OLD', $match_data_after),
        
        // Players
        'CREATE TRIGGER player_b_upd BEFORE UPDATE ON players FOR EACH ROW '.$players_before,
        'CREATE TRIGGER player_a_ins AFTER INSERT ON players FOR EACH ROW '.preg_replace('/REGEX_REPLACE/', 'NEW', $players_after),
        'CREATE TRIGGER player_a_upd AFTER UPDATE ON players FOR EACH ROW '.preg_replace('/REGEX_REPLACE/', 'NEW', $players_after),
        'CREATE TRIGGER player_a_del AFTER DELETE ON players FOR EACH ROW '.preg_replace('/REGEX_REPLACE/', 'OLD', $players_after),

        // Teams
        'CREATE TRIGGER teams_b_upd BEFORE UPDATE ON teams FOR EACH ROW '.$teams_before,
        'CREATE TRIGGER teams_a_ins AFTER INSERT ON teams FOR EACH ROW '.preg_replace('/REGEX_REPLACE/', 'NEW', $teams_after_ins),
        'CREATE TRIGGER teams_a_upd AFTER UPDATE ON teams FOR EACH ROW '.preg_replace('/REGEX_REPLACE/', 'NEW', $teams_after_upd),
        'CREATE TRIGGER teams_a_del AFTER DELETE ON teams FOR EACH ROW '.preg_replace('/REGEX_REPLACE/', 'OLD', $teams_after_del),
        
        // Coaches
        'CREATE TRIGGER coaches_a_upd AFTER UPDATE ON coaches FOR EACH ROW '.$coaches_after_upd,
        
        // Players skills
        # This changes the player value and also the team value!
        'CREATE TRIGGER players_skills_a_ins AFTER INSERT ON players_skills FOR EACH ROW '.preg_replace('/REGEX_REPLACE/', 'NEW', $players_skills_after),
        'CREATE TRIGGER players_skills_a_upd AFTER UPDATE ON players_skills FOR EACH ROW '.preg_replace('/REGEX_REPLACE/', 'NEW', $players_skills_after),
        'CREATE TRIGGER players_skills_a_del AFTER DELETE ON players_skills FOR EACH ROW '.preg_replace('/REGEX_REPLACE/', 'OLD', $players_skills_after),
        
        // Matches
        'CREATE TRIGGER match_a_upd AFTER UPDATE ON matches FOR EACH ROW 
        BEGIN
            '.preg_replace('/REGEX_REPLACE/', 'NEW', $matches_setup_rels).'
            '.$matches_tourDProps.'
            '.$matches_team_cnt.'
            '.$matches_pts.'
            IF NEW.round = '.RT_FINAL.' THEN
                '.$matches_wt_cnt.'
            END IF;
            IF (NEW.team1_score != OLD.team1_score OR NEW.team2_score != OLD.team2_score) THEN
                SET ret = syncELOMatch(NULL, OLD.match_id);
                SET ret = syncELOMatch(OLD.f_tour_id, OLD.match_id);
                '.$matches_streaks.'
                '.$matches_win_pct.'
                '.$matches_MVs.'
            END IF;
        END',
        
        'CREATE TRIGGER match_a_del AFTER DELETE ON matches FOR EACH ROW 
        BEGIN
            '.preg_replace('/REGEX_REPLACE/', 'OLD', $matches_setup_rels).'
            '.$matches_tourDProps.'            
            '.$matches_team_cnt.'
            '.$matches_pts.'
            IF OLD.round = '.RT_FINAL.' THEN
                '.$matches_wt_cnt.'
            END IF;
            IF OLD.date_played IS NOT NULL THEN
                CALL syncELOTour(NULL);
                CALL syncELOTour(OLD.f_tour_id);
                '.$matches_streaks.'
                '.$matches_win_pct.'
                '.$matches_MVs.'
            END IF;
        END',
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

?>
