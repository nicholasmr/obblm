<?php

/*
 *  Copyright (c) Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2009. All Rights Reserved.
 *
 *
 *  This file is part of OBBLM.
 *
 *  OBBLM is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  OBBLM is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

class Stats
{

/***************
 *   Pull out leaders of a specific stat (or multiple).
 ***************/
public static function getLeaders($grp = false, $n = false, $sortRule = array(), $mkObjs = false)
{
    $leaders = Stats::getStatsNaked(array(), $grp, $n ,$sortRule);
    $objs = array();

    if ($mkObjs) {
        foreach ($leaders as $l) {
            switch ($grp)
            {
                case STATS_PLAYER:  array_push($objs, new Player($l['pid'])); break;
                case STATS_TEAM:    array_push($objs, new Team($l['tid'])); break;
                case STATS_COACH:   array_push($objs, new Coach($l['cid'])); break;
                default: continue;
            }
        }
        
        return $objs;
    }
    else {
        return $leaders;
    }
}

/***************
 *   Fetches summed data from match_data by applying the filter specifications in $filter and optionally groups data around the specified column $grp.
 ***************/
public static function getStatsNaked(array $filter, $grp = false, $n = false, $sortRule = array())
{
    // Translation between $filter entries and corresponding MySQL columns in match_data.
    $filt_trans = array(
        'pid' => 'f_player_id',
        'tid' => 'f_team_id',
        'cid' => 'f_coach_id',
        'rid' => 'f_race_id',
        
        'mid'  => 'f_match_id',
        'trid' => 'f_tour_id',
        'did'  => 'f_did',
        'lid'  => 'f_lid',
    );
    
    switch ($grp)
    {
        case STATS_PLAYER:  $grp = 'f_player_id'; break;
        case STATS_TEAM:    $grp = 'f_team_id'; break;
        case STATS_COACH:   $grp = 'f_coach_id'; break;
        case STATS_RACE:    $grp = 'f_race_id'; break;
        default: $grp = false;
    }
    
    if (!empty($sortRule)) {
        for ($i = 0; $i < count($sortRule); $i++) {
            $str = $sortRule[$i];
            $sortRule[$i] = 'SUM('.substr($str, 1, strlen($str)) .') '. (($str[0] == '+') ? 'ASC' : 'DESC');
        }
    }
    
    $query = ' 
        SELECT 
            IFNULL(SUM(mvp),0)    AS \'mvp\', 
            IFNULL(SUM(cp),0)     AS \'cp\', 
            IFNULL(SUM(td),0)     AS \'td\', 
            IFNULL(SUM(intcpt),0) AS \'intcpt\', 
            IFNULL(SUM(bh),0)     AS \'bh\', 
            IFNULL(SUM(si),0)     AS \'si\', 
            IFNULL(SUM(ki),0)     AS \'ki\',

            IFNULL(SUM(bh+si+ki),0)    AS \'cas\',
            IFNULL(SUM(bh+si+ki+td),0) AS \'tdcas\',
            IFNULL(SUM(cp*1+(bh+si+ki)*2+intcpt*2+td*3+mvp*5),0) AS \'spp\'            
            '.((!empty($grp)) 
                ? ','.implode(',', array_map(create_function('$filt, $mysql', 'return "$mysql AS \'$filt\'";'), array_keys($filt_trans), array_values($filt_trans)))
                : '')."
        FROM 
            match_data"; 

    if (!empty($filter)) {
        $query .= " WHERE ";
        $and = false;
        foreach ($filter as $filter_key => $id) {
            if (is_numeric($id)) {
                $query .= (($and) ? ' AND ' : ' ').$filt_trans[$filter_key]." = $id ";
                $and = true;
            }
        }
    }
    $query .= " 
        ".((!empty($grp))       ? " GROUP BY $grp" : '')." 
        ".((!empty($sortRule))  ? ' ORDER BY '.implode(', ', $sortRule) : '')." 
        ".((is_numeric($n))     ? " LIMIT $n" : '')." 
    ";

    $ret = array();
    if (($result = mysql_query($query)) && is_resource($result) && mysql_num_rows($result) > 0) {
        while ($r = mysql_fetch_assoc($result)) {
            array_push($ret, $r);
        }
    }

    return $ret;
}

/*
 *
 *  The below methods use the by ($obj, $obj_id) against ($opp_obj, $opp_obj_id) in ($node, $node_id) format for fetching data.
 *  $obj and $obj_id are required!
 *
 */

/***************
 *   Returns object (team, coach, player and race) stats in array form ready to be assigned as respective object properties/fields.
 ***************/
public static function getAllStats($obj, $obj_id, $node, $node_id, $opp_obj, $opp_obj_id, $set_avg = false)
{
    $stats = array_merge(
        Stats::getStats(     $obj, $obj_id, $node, $node_id, $opp_obj, $opp_obj_id),
        Stats::getMatchStats($obj, $obj_id, $node, $node_id, $opp_obj, $opp_obj_id),
        Stats::getStreaks(   $obj, $obj_id, $node, $node_id, $opp_obj, $opp_obj_id),
        ($obj == STATS_COACH || $obj == STATS_RACE) ? Stats::getTeamsCnt($obj, $obj_id, $node, $node_id, $opp_obj, $opp_obj_id) : array()
    );
    
    if ($set_avg) {
        foreach (array('td', 'cp', 'intcpt', 'cas', 'bh', 'si', 'ki', 'score_team', 'score_opponent') as $key) {
            $stats[$key] = ($stats['played'] == 0) ? 0 : $stats[$key]/$stats['played'];
        }
    }
    
    return $stats;
}

/***************
 *   Fetches summed data from match_data by applying the filter specifications.
 ***************/
public static function getStats($obj, $obj_id, $node, $node_id, $opp_obj, $opp_obj_id)
{
    if ($opp_obj && $opp_obj_id) {list($from,$where,$tid,$tid_opp) = Stats::buildCrossRefQueryComponents($obj, $obj_id, $node, $node_id, $opp_obj, $opp_obj_id);}
    else                         {list($from,$where,$tid)          = Stats::buildQueryComponents($obj, $obj_id, $node, $node_id);}
  
    $query = '
        SELECT 
            IFNULL(SUM(mvp),0)    AS \'mvp\', 
            IFNULL(SUM(cp),0)     AS \'cp\', 
            IFNULL(SUM(td),0)     AS \'td\', 
            IFNULL(SUM(intcpt),0) AS \'intcpt\', 
            IFNULL(SUM(bh),0)     AS \'bh\', 
            IFNULL(SUM(si),0)     AS \'si\', 
            IFNULL(SUM(ki),0)     AS \'ki\',

            IFNULL(SUM(bh+si+ki),0)    AS \'cas\',
            IFNULL(SUM(bh+si+ki+td),0) AS \'tdcas\',
            IFNULL(SUM(cp*1+(bh+si+ki)*2+intcpt*2+td*3+mvp*5),0) AS \'spp\'
        FROM 
            match_data AS md,'.implode(',', $from).'
        WHERE
            '.implode(' AND ', $where).' AND md.f_match_id = matches.match_id AND md.f_team_id = '.$tid.' '.(($obj == STATS_PLAYER) ? " AND md.f_player_id = $obj_id" : '').'
    ';
    # Note: If we do not add the above player exeption to the query, player stats will be the same as team stats from the point in time where the player in question was bought.
    $result = mysql_query($query);
    $r = mysql_fetch_assoc($result);

    return $r;
}

public static function getMatchStats($obj, $obj_id, $node, $node_id, $opp_obj, $opp_obj_id)
{
    $s = array(); // Stats array to be returned.

    if ($opp_obj && $opp_obj_id) {list($from,$where,$tid,$tid_opp) = Stats::buildCrossRefQueryComponents($obj, $obj_id, $node, $node_id, $opp_obj, $opp_obj_id);}
    else                         {list($from,$where,$tid)          = Stats::buildQueryComponents($obj, $obj_id, $node, $node_id);}
    
    // Match result stats
    $query  = "SELECT 
                SUM(IF(team1_id = $tid, 1, IF(team2_id = $tid, 1, 0))) AS 'played', 
                SUM(
                    IF(team1_id = $tid, 
                        IF(team1_score > team2_score, 1, 0), 
                    IF(team2_id = $tid, 
                        IF(team2_score > team1_score, 1, 0), 0))
                ) AS 'won', 
                SUM(
                    IF(team1_id = $tid, 
                        IF(team1_score < team2_score, 1, 0), 
                    IF(team2_id = $tid, 
                        IF(team2_score < team1_score, 1, 0), 0))
                ) AS 'lost', 
                SUM(IF(round = ".RT_FINAL." AND (team1_id = $tid AND team1_score > team2_score OR team2_id = $tid AND team1_score < team2_score), 1, 0)) AS 'won_tours',
                SUM(IF(team1_id = $tid, ffactor1, IF(team2_id = $tid, ffactor2, 0))) AS 'fan_factor', 
                SUM(IF(team1_id = $tid, smp1, IF(team2_id = $tid, smp2, 0))) AS 'smp', 
                SUM(IF(team1_id = $tid, tcas1, IF(team2_id = $tid, tcas2, 0))) AS 'tcas', 
                SUM(IF(team1_id = $tid, team1_score, IF(team2_id = $tid, team2_score, 0))) AS 'score_team', 
                SUM(IF(team1_id = $tid, team2_score, IF(team2_id = $tid, team1_score, 0))) AS 'score_opponent' 
                FROM ".implode(',', $from)." WHERE date_played IS NOT NULL AND ".implode(' AND ', $where);

    $result = mysql_query($query);
    $row    = mysql_fetch_assoc($result);
    foreach ($row as $col => $val) $s[$col] = $val ? $val : 0;
    $s['draw']       = $s['played'] - ($s['won'] + $s['lost']);
    $s['score_diff'] = $s['score_team'] - $s['score_opponent'];
    $s['win_percentage'] = ($s['played'] == 0) ? 0 : 100*$s['won']/$s['played'];

    /******************** 
     * Points definitions depending on ranking system.
     ********************/
     
    $s['points'] = 0;
    if ($node == STATS_TOUR) {

        // First we need to investigate if the RS's points def. requires fields not yet loaded, and if so load them.
        
        global $hrs; // All house RSs
        $rs_all  = Tour::getRSSortRules(false, false); // All RSs.
        $hrs_nr  = 0; // Current HRS nr.
        $rs_nr   = get_alt_col('tours', 'tour_id', $node_id, 'rs'); // Current RS nr.
        $fields  = array('mvp', 'cp', 'td', 'intcpt', 'bh', 'si', 'ki', 'cas', 'tdcas');
        $limit   = count($rs_all) - count($hrs); // If rs_nr is larger than this value, then the RS for this tournament is a house RS -> set extra fields.
        
        
        // Is the ranking system a house ranking system? If not then don't load extra fields since non-house RSs don't use the extra fields at all.
        if (($hrs_nr = $rs_nr - $limit) > 0 && preg_match('/'.implode('|', $fields).'/', $hrs[$hrs_nr]['points'])) {
            $s = array_merge($s, Stats::getStats($obj, $obj_id, STATS_TOUR, $node_id));
        }

        switch ($rs_nr)
        {
            case '2': $s['points'] = $s['won']*3 + $s['draw']; break;
            case '3': $s['points'] = ($s['played'] == 0) ? 0 : $s['won']/$s['played'] + $s['draw']/(2*$s['played']); break;
            case '4':
                /* 
                    Although none of the points definitions make sense for other $obj types than = STATS_TEAM, it 
                    is anyway necessary for this case to only be executed if $obj = team.
                    
                    pts += Win 10p, Draw 5p, Loss 0p, 1p per TD up to 3p, 1p per (player, not team) CAS up to 3p.
                */
                if ($obj == STATS_TEAM) {
                    $query = "
                        SELECT SUM(td) AS 'td', SUM(cas) AS 'cas' FROM 
                        (
                        SELECT 
                            f_match_id, 
                            IF(SUM(td) > 3, 3, SUM(td)) AS 'td', 
                            IF(SUM(bh+ki+si) > 3, 3, SUM(bh+ki+si)) AS 'cas'
                        FROM match_data WHERE f_team_id = $obj_id AND f_tour_id = $node_id GROUP BY f_match_id
                        ) AS tmpTable
                        ";
                    $result = mysql_query($query);
                    $row = mysql_fetch_assoc($result);
                    $s['points'] = $s['won']*10 + $s['draw']*5 + $row['td'] + $row['cas'];
                }
                break;
                
            default:
                // Only for house RS.
                if ($hrs_nr < 1) {
                    break;
                }
                $fields = array_merge($fields, array('played', 'won', 'lost', 'draw', 'fan_factor', 'smp', 'cas', 'score_team', 'score_opponent', 'score_diff', 'win_percentage'));
                eval("\$s['points'] = ".
                    preg_replace(
                        array_map(create_function('$val', 'return "/\[$val\]/";'), $fields), 
                        array_map(create_function('$val', 'return "\$s[\'$val\']";'), $fields),
                        $hrs[$hrs_nr]['points']
                    )
                .";");
                break;
        }
    }

    return $s;
}

//public static function getPlayedMatches($obj, $obj_id, $node = false, $node_id = false, $op_tid = false, $n = false, $mkObjs = false)
public static function getPlayedMatches($obj, $obj_id, $node, $node_id, $opp_obj, $opp_obj_id, $n = false, $mkObjs = false)
{
    $matches = array(); // Return structure.
    
    if ($opp_obj && $opp_obj_id) {list($from,$where,$tid,$tid_opp) = Stats::buildCrossRefQueryComponents($obj, $obj_id, $node, $node_id, $opp_obj, $opp_obj_id);}
    else                         {list($from,$where,$tid)          = Stats::buildQueryComponents($obj, $obj_id, $node, $node_id);}

    $query = "
        SELECT 
            DISTINCT(match_id) AS 'match_id', 
            IF((team1_id = $tid AND team1_score > team2_score) OR (team2_id = $tid AND team1_score < team2_score), 'W', IF(team1_score = team2_score, 'D', 'L')) AS 'result' 
        FROM ".implode(', ', $from)." 
        WHERE 
                date_played IS NOT NULL 
            AND match_id > 0 
            AND ".implode(' AND ', $where)." 
        ORDER BY date_played DESC ".(($n) ? " LIMIT $n" : '');
    $result = mysql_query($query);
    if (is_resource($result) && mysql_num_rows($result) > 0) {
        while ($r = mysql_fetch_assoc($result)) {
            if ($mkObjs) {
                $m = new Match($r['match_id']);
                $m->result = $r['result'];
                $matches[] = $m;
            }
            else {
                $matches[] = $r['match_id'];
            }
        }
    }

    return $matches;
}

public static function getStreaks($obj, $obj_id, $node, $node_id, $opp_obj, $opp_obj_id)
{
    /**
     * Counts most won, lost and draw matches (streaks) in a row.
     *
     * http://www.sqlteam.com/article/detecting-runs-or-streaks-in-your-data
     **/
    
    if ($opp_obj && $opp_obj_id) {list($from,$where,$tid,$tid_opp) = Stats::buildCrossRefQueryComponents($obj, $obj_id, $node, $node_id, $opp_obj, $opp_obj_id);}
    else                         {list($from,$where,$tid)          = Stats::buildQueryComponents($obj, $obj_id, $node, $node_id);}
    
    // Sample table.
    $GD1 = "(
            SELECT 
                date_played, 
                IF((team1_id = $tid AND team1_score > team2_score) OR (team2_id = $tid AND team1_score < team2_score), 'W', IF(team1_score = team2_score, 'D', 'L')) AS 'result'
            FROM 
                ".implode(', ', $from)." 
            WHERE 
                    date_played IS NOT NULL AND ".implode(' AND ', $where)." 
            ORDER BY 
                date_played
        )";

    // Game data with run-group column.
    $GD2 = "(
            SELECT 
                *,
                (
                    SELECT COUNT(*) 
                    FROM $GD1 AS G
                    WHERE G.result <> GR.result 
                    AND G.date_played <= GR.date_played
                ) AS RunGroup 
            FROM $GD1 AS GR
        ) AS GD2";
    
    // Accumulated (grouped) RunGroup stats
    $accum_rg = "(
            SELECT 
                result, 
                MIN(date_played) as StartDate, 
                MAX(date_played) as EndDate, 
                COUNT(*) as games
            FROM $GD2
            GROUP BY result, RunGroup
            ORDER BY date_played
        ) AS RG";
// DEV NOTE: MySQL neasting limit reached with this addition:    
#    $query = "SELECT 
#            (SELECT MAX(games) FROM $accum_rg WHERE result = 'W') AS 'W', 
#            (SELECT MAX(games) FROM $accum_rg WHERE result = 'L') AS 'L', 
#            (SELECT MAX(games) FROM $accum_rg WHERE result = 'D') AS 'D', 
#            (SELECT games FROM $accum_rg WHERE result = 'W' AND EndDate = (SELECT Max(EndDate) FROM $accum_rg)) AS 'C'
#        ";
    $query = "SELECT 
            (SELECT MAX(games) FROM $accum_rg WHERE result = 'W') AS 'W', 
            (SELECT MAX(games) FROM $accum_rg WHERE result = 'L') AS 'L', 
            (SELECT MAX(games) FROM $accum_rg WHERE result = 'D') AS 'D'
        ";

    $result = mysql_query($query);
    $row = mysql_fetch_assoc($result);
        
    return array(
        'row_won' => ($row['W']) ? $row['W'] : 0, 
        'row_lost' => ($row['L']) ? $row['L'] : 0, 
        'row_draw' => ($row['D']) ? $row['D'] : 0, 
#        'row_current_win' => ($row['C']) ? $row['C'] : 0
    );
}

public static function getTeamsCnt($obj, $obj_id, $node, $node_id, $opp_obj, $opp_obj_id)
{
    if ($opp_obj && $opp_obj_id) {list($from,$where,$tid,$tid_opp) = Stats::buildCrossRefQueryComponents($obj, $obj_id, $node, $node_id, $opp_obj, $opp_obj_id);}
    else                         {list($from,$where,$tid)          = Stats::buildQueryComponents($obj, $obj_id, $node, $node_id);}
    
    $query = 'SELECT COUNT(DISTINCT(team_id)) AS \'teams_cnt\' FROM '.implode(',',$from).' WHERE '.implode(' AND ', $where);
    $result = mysql_query($query);
    return mysql_fetch_assoc($result);
}

/***************
 *   Below are query builder helper functions.
 ***************/
private static function buildQueryComponents($obj, $obj_id, $node, $node_id, $TTS = '') # TTS = Teams Table Suffix
{
    /*
        Builds query components allowing you to address specific groups of matches in the "matches" table.
        For example if wanted matches by team_id = 5 then set $obj = STATS_TEAM and $obj_id = 5, 
            if further matches only from a specific node type are wanted, say division_id = 7, then set $node = STATS_DIVISION with $node_id = 7.

        
        $TTS (teams table suffix):
        Because this function can be used to compile two query sets via buildCrossRefQueryComponents() and joined we need to be able to 
            differentiate between two different "teams" tables, for example, which we can do by allowing custom suffixes for all tables.
        The name "TEAMS table suffix" is a little misleading - just forget it says "teams", it's for all tables, not just "teams" tables.
    */
    
    $from = array('matches','tours','divisions'); // We don't need to add the "leagues" table, since league IDs can be referenced via the "f_lid" key in the divisions table.
    $where = array("matches.f_tour_id = tours.tour_id", "tours.f_did = divisions.did");
    if ($node && $node_id) {
        switch ($node)
        {
            case STATS_TOUR:        $where[] = "matches.f_tour_id   = $node_id"; break;
            case STATS_DIVISION:    $where[] = "tours.f_did         = $node_id"; break;
            case STATS_LEAGUE:      $where[] = "divisions.f_lid     = $node_id"; break;
        }
    }

    switch ($obj)
    {
        case STATS_PLAYER:  
            array_push($from, "teams AS teams$TTS", "players AS players$TTS"); # Also use $TTS for players table: If not included calling buildCrossRefQueryComponents() will accidentally create two tables with identical names (if comparing two players).
            $tid = "teams$TTS.team_id";
            array_push($where, "players$TTS.player_id = $obj_id", "players$TTS.owned_by_team_id = $tid");
            
            // We must add the below, else above is equivalent to the player's team match stats. Ie. matches this player did not compete in will be counted as played!
            $from[]    = "(SELECT DISTINCT(f_match_id) AS 'p_mid' FROM match_data WHERE f_player_id = $obj_id) AS pmatches$TTS"; # Also use $TTL here, same reason as above for players table.
            $where[]   = "match_id = pmatches$TTS.p_mid";
            break;
            
        case STATS_TEAM:
//            array_push($from, "");
            $tid = $obj_id;
//            array_push($where, "");
            break;
            
        case STATS_RACE:
            array_push($from, "teams AS teams$TTS");
            $tid = "teams$TTS.team_id";
            array_push($where, "teams$TTS.f_race_id = $obj_id");
            break;
            
        case STATS_COACH:  
            array_push($from, "teams AS teams$TTS");
            $tid = "teams$TTS.team_id";
            array_push($where, "teams$TTS.owned_by_coach_id = $obj_id");
            break;
    }
    
    $where[] = "(team1_id = $tid OR team2_id = $tid)";

    return array($from,$where,$tid);
}

private static function buildCrossRefQueryComponents($obj, $obj_id, $node, $node_id, $opp_obj, $opp_obj_id)
{
    /*
        Like buildQueryComponents() but allows further filtering by selected only those matches which 
            has a specific opponent of STATS_* type = $opp_obj with ID = $opp_obj_id.
    */

    // As ordinarally done, when not cross referencing:
    list($from,$where,$tid) = Stats::buildQueryComponents($obj, $obj_id, $node, $node_id);
    
    // Now filter matches only showing those against a specific opponent:
    list($from_opp,$where_opp,$tid_opp) = Stats::buildQueryComponents($opp_obj, $opp_obj_id, $node, $node_id, '2');
    $where[] = "(team1_id = $tid_opp OR team2_id = $tid_opp)";
    $from = array_merge($from, array_values(array_diff($from_opp, $from)));
    $where = array_merge($where, array_values(array_diff($where_opp, $where)));

    return array($from,$where,$tid,$tid_opp);
}

/***************
 *   Finds match records/mem. matches.
 ***************/
public static function getMemMatches() {
    
    /*
        Creates an array of matches for those matches which:
        
            - Most TDs (sum of both teams)
            - most cp
            - most int
            - Most killed
            - Most CAS
            - Largest score-wise victory
            - Largest match income 
            - Largest gate
            - Most fans
            - Largest TV difference in which underdog won
    */
    
    $m = array(
    'td'        => array(), 
    'cp'        => array(),
    'intcpt'    => array(),
    'ki'        => array(),
    'bh+ki+si'  => array(), // cas
    
    'svic'      => array(),
    'inc'       => array(),
    'gate'      => array(),
    'mfans'     => array(), // most fans
    'tvdiff'    => array(),
    );        

    /* Queries for finding the matches holding records. */

    $ach = "
    SELECT f_match_id AS 'match_id', SUM(REPLACE_BY_ACH) as 'sumA' FROM match_data WHERE f_match_id > 0 GROUP BY f_match_id HAVING sumA > 0 AND sumA = (

        SELECT MAX(sumB) FROM (

            SELECT SUM(REPLACE_BY_ACH) AS 'sumB' FROM match_data WHERE f_match_id > 0 GROUP BY f_match_id
        ) AS tmpTable
    )
    ";

    $str1 = 'ABS(CAST((team1_score - team2_score) AS SIGNED))';
    $str2 = '(SELECT MAX(IF(income1>income2, income1, income2)) FROM matches)';

    $svic = "
        SELECT match_id, $str1 FROM matches WHERE $str1 != 0 AND $str1 = (
            SELECT MAX($str1) AS 'mdiff' FROM matches HAVING mdiff IS NOT NULL
        )
    ";
    
    $inc = "
        SELECT match_id, income1, income2 FROM matches WHERE (income1 != 0 OR income2 != 0) AND IF(income1>income2, 
            income1 = $str2, 
            income2 = $str2
        )
    ";
    
    $gate = "SELECT match_id, gate FROM matches WHERE gate = (SELECT MAX(gate) FROM matches)";
    $mfans = "SELECT match_id, fans FROM matches WHERE fans = (SELECT MAX(fans) FROM matches)";
    
    $str3 = "((tv1 > tv2 AND team1_score < team2_score) OR (tv1 < tv2 AND team1_score > team2_score))";
    $str4 = "ABS(CAST((tv1 - tv2) AS SIGNED))";
    $tvdiff = "SELECT match_id, $str4 AS tvdiff FROM matches WHERE $str3 AND $str4 = (SELECT MAX($str4) FROM matches WHERE $str3)";

    /* Create an array to loop through containing the queries to throw at mysql. */
    
    $qryarr = array();
    foreach (array_keys(array_slice($m, 0, 5)) as $k) {
        $qryarr[$k] = preg_replace('/REPLACE_BY_ACH/', $k, $ach);
    }
    $qryarr['svic'] = $svic;
    $qryarr['inc'] = $inc;
    $qryarr['gate'] = $gate;
    $qryarr['mfans'] = $mfans;
    $qryarr['tvdiff'] = $tvdiff;
    
    /* Store match objects for record holding matches. */
    
    foreach ($qryarr as $k => $query) {

        $mObjs = array();
        
        $result = mysql_query($query);
        if ($result && mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                array_push($mObjs, new Match($row['match_id']));
            }
        }
        
        objsort($mObjs, array('+date_played'));
        $m[$k] = (count($mObjs) > MAX_MEM_MATCHES) ? array() : $mObjs;
    }

    return $m;
}

}

?>
