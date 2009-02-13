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

public static function getStats($pid = false, $tid = false, $cid = false, $mid = false, $trid = false)
{
    return array_shift(Stats::get($pid, $tid, $cid, $mid, $trid, false, false ,false));
}

public static function getLeaders($grp = false, $n = false, $sortRule = array(), $mkObjs = false)
{
    $leaders = Stats::get(false, false, false, false, false, $grp, $n ,$sortRule);
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

private static function get($pid = false, $tid = false, $cid = false, $mid = false, $trid = false, $grp = false, $n = false, $sortRule = array())
{
    switch ($grp)
    {
        case STATS_PLAYER:  $grp = 'f_player_id'; break;
        case STATS_TEAM:    $grp = 'f_team_id'; break;
        case STATS_COACH:   $grp = 'f_coach_id'; break;
        default: $grp = false;
    }
    
    if (!empty($sortRule)) {
        for ($i = 0; $i < count($sortRule); $i++) {
            $str = $sortRule[$i];
            $sortRule[$i] = 'SUM('.substr($str, 1, strlen($str)) .') '. (($str[0] == '+') ? 'ASC' : 'DESC');
        }
    }
    
    $query = " 
        SELECT 
            SUM(mvp)    AS 'mvp', 
            SUM(cp)     AS 'cp', 
            SUM(td)     AS 'td', 
            SUM(intcpt) AS 'intcpt', 
            SUM(bh)     AS 'bh', 
            SUM(si)     AS 'si', 
            SUM(ki)     AS 'ki'
            ".((!empty($grp)) 
                ? ",f_player_id AS 'pid',
                    f_team_id   AS 'tid',
                    f_coach_id  AS 'cid',
                    f_match_id  AS 'mid',
                    f_tour_id   AS 'trid'"
                : '')."
        FROM 
            match_data"; 
    if ($pid || $tid || $cid || $mid || $trid) {
        $query .= " WHERE ";
        $and = false;
        foreach (array('pid' => 'player', 'tid' => 'team', 'cid' => 'coach', 'mid' => 'match', 'trid' => 'tour', ) as $short => $long) {
            if ($$short) {
                $query .= (($and) ? ' AND' : '')." f_${long}_id = ".($$short).' ';
                $and = true;
            }
        }
    }
    $query .= " 
        ".((!empty($grp))       ? " GROUP BY $grp" : '')." 
        ".((!empty($sortRule))  ? ' ORDER BY '.implode(', ', $sortRule) : '')." 
        ".((is_numeric($n))     ? " LIMIT $n" : '')." 
    ";

    $result = mysql_query($query);
    $ret = array();
    if (is_resource($result) && mysql_num_rows($result) > 0) {
        while ($r = mysql_fetch_assoc($result)) {
            $r['cas'] = $r['bh'] + $r['si'] + $r['ki'];
            $r['tdcas'] = $r['td'] + $r['cas'];
            $r['spp'] =   $r['cp']     * 1
                        + $r['cas']    * 2
                        + $r['intcpt'] * 2
                        + $r['td']     * 3
                        + $r['mvp']    * 5;
                        
            array_push($ret, $r);
        }
    }

    // Make zero if unset.
    for ($i = 0; $i < count($ret); $i++) {
        foreach ($ret[$i] as $k => $v) {
            if (!$v)
                $ret[$i][$k] = 0;
        }
    }

    return $ret;
}

public static function getMatchStats($grp, $id, $trid = false)
{
    $s = array();
    $query_suffix = ($trid) ? " AND matches.f_tour_id = $trid" : '';

    switch ($grp)
    {
        case STATS_PLAYER:  
            $from   = 'matches, teams, players'; 
            $where  = " AND player_id = $id AND owned_by_team_id = team_id AND (team1_id = team_id OR team2_id = team_id) ";
            $tid    = 'team_id';
            // We must add the below, else above is equivalent to the player's team match stats. Ie. matches this player did not compete in will be counted as played!
            $from .= ', match_data';
            $where .= ' AND f_match_id = match_id AND f_player_id = player_id ';
            break;
            
        case STATS_TEAM:    
            $from   = 'matches'; 
            $where  = '';
            $tid    = $id;
            break;
            
        case STATS_COACH:  
            $from   = 'matches, teams, coaches'; 
            $where  = " AND coach_id = $id AND owned_by_coach_id = coach_id AND (team1_id = team_id OR team2_id = team_id) ";
            $tid    = 'team_id';
            break;
    }
    
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
                SUM(IF(team1_id = $tid, ffactor1, IF(team2_id = $tid, ffactor2, 0))) AS 'fan_factor', 
                SUM(IF(team1_id = $tid, smp1, IF(team2_id = $tid, smp2, 0))) AS 'smp', 
                SUM(IF(team1_id = $tid, tcas1, IF(team2_id = $tid, tcas2, 0))) AS 'tcas', 
                SUM(IF(team1_id = $tid, team1_score, IF(team2_id = $tid, team2_score, 0))) AS 'score_team', 
                SUM(IF(team1_id = $tid, team2_score, IF(team2_id = $tid, team1_score, 0))) AS 'score_opponent' 
                FROM $from WHERE date_played IS NOT NULL $query_suffix $where";

    $result = mysql_query($query);
    $row    = mysql_fetch_assoc($result);
    foreach ($row as $col => $val) $s[$col] = $val ? $val : 0;
    $s['draw']       = $s['played'] - ($s['won'] + $s['lost']);
    $s['score_diff'] = $s['score_team'] - $s['score_opponent'];
    $s['win_percentage'] = ($s['played'] == 0) ? 0 : 100*$s['won']/$s['played'];

    // Points definitions depending on ranking system.
    $s['points'] = 0;
    if ($trid) {
        switch (get_alt_col('tours', 'tour_id', $trid, 'rs'))
        {
            case '2': $s['points'] = $s['won']*3 + $s['draw']; break;
            case '3': $s['points'] = ($s['played'] == 0) ? 0 : $s['won']/$s['played'] + $s['draw']/(2*$s['played']); break;
            case '4':
                /* 
                    Although none of the points definitions make sense for other $grp types than = STATS_TEAM, it 
                    is anyway necessary for this case to only be executed if $grp = team.
                    
                    pts += Win 10p, Draw 5p, Loss 0p, 1p per TD up to 3p, 1p per (player, not team) CAS up to 3p.
                */
                if ($grp == STATS_TEAM) {
                    $query = "
                        SELECT SUM(td) AS 'td', SUM(cas) AS 'cas' FROM 
                        (
                        SELECT 
                            f_match_id, 
                            IF(SUM(td) > 3, 3, SUM(td)) AS 'td', 
                            IF(SUM(bh+ki+si) > 3, 3, SUM(bh+ki+si)) AS 'cas'
                        FROM match_data WHERE f_team_id = $id AND f_tour_id = $trid GROUP BY f_match_id
                        ) AS tmpTable
                        ";
                    $result = mysql_query($query);
                    $row = mysql_fetch_assoc($result);
                    $s['points'] = $s['won']*10 + $s['draw']*5 + $row['td'] + $row['cas'];
                }
                break;
        }    
    }

    return $s;
}

public static function getPlayedMatches($grp, $id, $n = false, $mkObjs = false, $trid = false, $opid = false)
{
    $query = "SELECT DISTINCT match_id ";
    switch ($grp)
    {
        case STATS_PLAYER:  
            $query .= "FROM matches, match_data WHERE f_match_id = match_id AND f_player_id = $id";
            break;
            
        case STATS_TEAM:    
            $query .= "FROM matches WHERE (team1_id = $id OR team2_id = $id)";
            break;
            
        case STATS_COACH:  
            $query .= "FROM matches, teams WHERE owned_by_coach_id = $id AND (team1_id = team_id OR team2_id = team_id)";
            break;
    }
    $addOn = '';
    $addOn .= ($opid) ? " AND (team1_id = $opid OR team2_id = $opid) " : '';
    $addOn .= ($trid) ? " AND f_tour_id = $trid " : '';
    $query .= " AND date_played IS NOT NULL AND match_id > 0 $addOn ORDER BY date_played DESC ".(($n) ? " LIMIT $n" : '');

    $result = mysql_query($query);
    $ret = array();
    if (is_resource($result) && mysql_num_rows($result) > 0) {
        while ($r = mysql_fetch_assoc($result)) {
            array_push($ret, new Match($r['match_id']));
        }
    }
    
    return $ret;
}

public static function getStreaks($grp, $id, $trid = false)
{
    /**
    * Counts most won, lost and draw matches in a row.
    **/
    
    switch ($grp)
    {
        case STATS_PLAYER:  
            $from   = 'matches, teams, players'; 
            $where  = " AND player_id = $id AND owned_by_team_id = team_id AND (team1_id = team_id OR team2_id = team_id) ";
            $tid    = 'team_id';
            // We must add the below, else above is equivalent to the player's team match stats. Ie. matches this player did not compete in will be counted as played!
            $from .= ', match_data';
            $where .= ' AND f_match_id = match_id AND f_player_id = player_id ';
            break;
            
        case STATS_TEAM:    
            $from   = 'matches'; 
            $where  = '';
            $tid    = $id;
            break;
            
        case STATS_COACH:  
            $from   = 'matches, teams, coaches'; 
            $where  = " AND coach_id = $id AND owned_by_coach_id = coach_id AND (team1_id = team_id OR team2_id = team_id) ";
            $tid    = 'team_id';
            break;
    }
    
    $query = "SELECT 
                IF(team1_score = team2_score, 3, 
                    IF(team1_id = $tid AND team1_score > team2_score OR team2_id = $tid AND team2_score > team1_score, 1, 2
                    )
                ) AS 'result' 
                FROM 
                    $from 
                WHERE 
                    date_played IS NOT NULL 
                    AND (team1_id = $tid OR team2_id = $tid) 
                    ".(($trid) ? " AND matches.f_tour_id = $trid" : '')."
                    $where 
                ORDER BY date_played DESC";

    $result = mysql_query($query);
    $ret['won'] = $ret['lost'] = $ret['draw'] = 0;
    if ($result && mysql_num_rows($result) > 0) {
        $res = array();
        while ($row = mysql_fetch_assoc($result)) {
            array_push($res, $row['result']);
        }
        foreach (array('won' => 1, 'lost' => 2, 'draw' => 3) as $t => $v) {
            $cnt = 0;
            foreach ($res as $r) {
                if ($r == $v) {
                    $cnt++;
                    if ($ret[$t] < $cnt)
                        $ret[$t] = $cnt;
                }
                else {
                    $cnt = 0;
                }
            }
        }
    }
    
    return array('row_won' => $ret['won'], 'row_lost' => $ret['lost'], 'row_draw' => $ret['draw']);
}

    
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
            
            // Not yet supported:
            - Most fans
            - Fewest fans
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
//            'mfans'     => array(), // most fans
//            'ffans'     => array(), // fewest fans
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

    /* Create an array to loop through containing the queries to throw at mysql. */
    
    $qryarr = array();
    foreach (array_keys(array_slice($m, 0, 5)) as $k) {
        $qryarr[$k] = preg_replace('/REPLACE_BY_ACH/', $k, $ach);
    }
    $qryarr['svic'] = $svic;
    $qryarr['inc'] = $inc;
    $qryarr['gate'] = $gate;
    
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
