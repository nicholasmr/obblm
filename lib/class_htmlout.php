<?php

/*
 *  Copyright (c) Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2009-2010. All Rights Reserved.
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

/*
 THIS FILE is used for HTML-helper routines.
 */
 
// Special dropdown states for nodeSelector().
define('T_STATE_ALLTIME', 1);
define('T_STATE_ACTIVE', 2);
define('T_NODE_ALL', -1);  # All nodes.
define('T_RACE_ALL', -1);  # All races.
 
class HTMLOUT
{

public static function recentGames($obj, $obj_id, $node, $node_id, $opp_obj, $opp_obj_id, array $opts)
{
    /*
        Make recent games table.

         $opts = array(
            'url' => The URL of the page on which this table is to be printed.
            'n' => (int) Fetch the n most recent games. If not specified all matches are displayed.
            'GET_SS' => GET Sorting suffix
         );
    */

    global $lng;

    $extra = array('doNr' => false, 'noHelp' => true);

    if (!array_key_exists('GET_SS', $opts)) {$opts['GET_SS'] = '';}
    else {$extra['GETsuffix'] = $opts['GET_SS'];} # GET Sorting Suffix
    if (!(array_key_exists('n', $opts) && $opts['n'])) {$opts['n'] = false;}

    $matches = ($FOR_OBJ = $obj && $obj_id)
        ? $matches = Stats::getMatches($obj, $obj_id, $node, $node_id, $opp_obj, $opp_obj_id, $opts['n'], true, false)
        : $matches = Match::getMatches($opts['n'], ($node) ? $node : false, ($node) ? $node_id : false, false);

    foreach ($matches as $m) {
        $m->score = "$m->team1_score&mdash;$m->team2_score";
        $m->mlink = "<a href='index.php?section=matches&amp;type=report&amp;mid=$m->match_id'>".$lng->getTrn('common/view')."</a>";
        $m->tour_name = get_alt_col('tours', 'tour_id', $m->f_tour_id, 'name');
        if ($FOR_OBJ) {
            $m->result = matchresult_icon($m->result);
        }
    }

    $fields = array(
        'date_played' => array('desc' => $lng->getTrn('common/dateplayed')),
        'tour_name'   => array('desc' => $lng->getTrn('common/tournament')),
        'team1_name'  => array('desc' => $lng->getTrn('common/home'), 'href' => array('link' => urlcompile(T_URL_PROFILE,T_OBJ_TEAM,false,false,false), 'field' => 'obj_id', 'value' => 'team1_id')),
        'team2_name'  => array('desc' => $lng->getTrn('common/away'), 'href' => array('link' => urlcompile(T_URL_PROFILE,T_OBJ_TEAM,false,false,false), 'field' => 'obj_id', 'value' => 'team2_id')),
        'gate'        => array('desc' => $lng->getTrn('common/gate'), 'kilo' => true, 'suffix' => 'k', 'href' => false),
        'score'       => array('desc' => $lng->getTrn('common/score'), 'nosort' => true),
    );
    if ($FOR_OBJ) {$fields['result'] = array('desc' => $lng->getTrn('common/result'), 'nosort' => true);}
    $fields['mlink'] = array('desc' => $lng->getTrn('common/match'), 'nosort' => true); # Must be last!

    HTMLOUT::sort_table(
        $lng->getTrn('common/recentmatches'),
        $opts['url'],
        $matches,
        $fields,
        sort_rule('match'),
        (isset($_GET["sort$opts[GET_SS]"])) ? array((($_GET["dir$opts[GET_SS]"] == 'a') ? '+' : '-') . $_GET["sort$opts[GET_SS]"]) : array(),
        $extra
    );
}

public static function upcomingGames($obj, $obj_id, $node, $node_id, $opp_obj, $opp_obj_id, array $opts)
{
    /*
        Make upcoming games table.

         $opts = array(
            'url' => The URL of the page on which this table is to be printed.
            'n' => (int) Fetch the n most recent games. If not specified all matches are displayed.
            'GET_SS' => GET Sorting suffix
         );
    */

    global $lng;

    $extra = array('doNr' => false, 'noHelp' => true);

    if (!array_key_exists('GET_SS', $opts)) {$opts['GET_SS'] = '';}
    else {$extra['GETsuffix'] = $opts['GET_SS'];} # GET Sorting Suffix
    if (!(array_key_exists('n', $opts) && $opts['n'])) {$opts['n'] = false;}

    $matches = ($obj && $obj_id)
        ? Stats::getMatches($obj, $obj_id, $node, $node_id, $opp_obj, $opp_obj_id, $opts['n'], true, true)
        : Match::getMatches($opts['n'], ($node) ? $node : false, ($node) ? $node_id : false, true);

    foreach ($matches as $m) {
        $m->mlink = "<a href='index.php?section=matches&amp;type=report&amp;mid=$m->match_id'>".$lng->getTrn('common/view')."</a>";
        $m->tour_name = get_alt_col('tours', 'tour_id', $m->f_tour_id, 'name');
    }

    $fields = array(
        'date_created'      => array('desc' => $lng->getTrn('common/datecreated')),
        'tour_name'         => array('desc' => $lng->getTrn('common/tournament')),
        'team1_name'        => array('desc' => $lng->getTrn('common/home'), 'href' => array('link' => urlcompile(T_URL_PROFILE,T_OBJ_TEAM,false,false,false), 'field' => 'obj_id', 'value' => 'team1_id')),
        'team2_name'        => array('desc' => $lng->getTrn('common/away'), 'href' => array('link' => urlcompile(T_URL_PROFILE,T_OBJ_TEAM,false,false,false), 'field' => 'obj_id', 'value' => 'team2_id')),
        'mlink'             => array('desc' => $lng->getTrn('common/match'), 'nosort' => true),
    );

    HTMLOUT::sort_table(
        $lng->getTrn('common/upcomingmatches'),
        $opts['url'],
        $matches,
        $fields,
        array('+date_created'),
        (isset($_GET["sort$opts[GET_SS]"])) ? array((($_GET["dir$opts[GET_SS]"] == 'a') ? '+' : '-') . $_GET["sort$opts[GET_SS]"]) : array(),
        $extra
    );
}

private static function _getDefFields($obj, $node, $node_id)
{
    /* 
        Shared use by standings() and nodeSelector(). 
        These are the default fields (general/regular stats) in standings() and the "having" filters of nodeSelector().
        
        mv_ fields are accumulated stats from MV tables. rg_ (regular) are regular/static/all-time fields from non mv-tables (players, teams, coaches etc.)
    */
    
    global $lng;
    
    $fields = array(
        'mv_won'     => array('desc' => 'W'),
        'mv_draw'    => array('desc' => 'D'),
        'mv_lost'    => array('desc' => 'L'),
        'mv_played'  => array('desc' => 'GP'),
        'rg_win_pct' => array('desc' => 'WIN%'),
        'rg_swon'    => array('desc' => 'SW'),
        'rg_sdraw'   => array('desc' => 'SD'),
        'rg_slost'   => array('desc' => 'SL'),
        'mv_gf'      => array('desc' => 'GF'),
        'mv_ga'      => array('desc' => 'GA'),
        'mv_td'      => array('desc' => 'Td'),
        'mv_cp'      => array('desc' => 'Cp'),
        'mv_intcpt'  => array('desc' => 'Int'),
        'mv_cas'     => array('desc' => 'Cas'),
        'mv_bh'      => array('desc' => 'BH'),
        'mv_si'      => array('desc' => 'Si'),
        'mv_ki'      => array('desc' => 'Ki'),
    );
    
    // These fields are not summable!!! 
    //ie. you dont get the division/league value of these fields by summing over the related/underlying tournaments field's values.
    global $objFields_notsum;
        # Look non-summable field and remove them.
    $ALL_TIME = self::_isNodeAllTime($obj, $node, $node_id);
    if (!$ALL_TIME) {
        if ($node == T_NODE_TOURNAMENT) {
            $new_fields = array();
            foreach ($fields as $fname => $fcont) {
                $f = preg_replace('/^\w\w\_/', '', $fname);
                $new_fields[in_array($f, $objFields_notsum) ? "mv_$f" : $fname] = $fcont;
            }
            $fields = $new_fields;
        }
        foreach ($objFields_notsum as $f) {
            unset($fields["rg_$f"]);
        }
    }   

    $fields_before = $fields_after = array();
    switch ($obj)
    {
        case STATS_PLAYER:
            $fields_after = array(
                'mv_mvp' => array('desc' => 'MVP'),
                'mv_spp' => array('desc' => 'SPP'),
                'value'  => array('desc' => $lng->getTrn('common/value'), 'nosort' => !$ALL_TIME, 'kilo' => true, 'suffix' => 'k'),
            );
            foreach (array('won', 'lost', 'draw') as $f) {
                unset($fields["rg_s$f"]);
                unset($fields["mv_s$f"]);
            }

            break;

        case STATS_TEAM:
            $fields_before = array(
                'tv' => array('desc' => $lng->getTrn('common/value'), 'kilo' => true, 'suffix' => 'k'),                
            );
            $fields_after = array(
                'mv_tcasf' => array('desc' => 'tcasf'), 
                'mv_tcasa' => array('desc' => 'tcasa'), 
                'mv_tcdiff' => array('desc' => 'tcdiff'), 
            	'mv_smp' => array('desc' => 'SMP'),
            );
            if ($ALL_TIME) {
                $fields_after['wt_cnt'] = array('desc' => 'WT');
                $fields_after['rg_elo'] = array('desc' => 'ELO');
            }
            else if ($node == T_NODE_TOURNAMENT) {
                $fields_after['mv_elo'] = array('desc' => 'ELO');
                $tr = new Tour($node_id);
                if ($tr->isRSWithPoints()) {
                    $fields_after['mv_pts'] = array('desc' => 'PTS');
                }
            	unset($fields_after['tv']);
            }
            break;

        case STATS_RACE:
            if ($node == T_NODE_TOURNAMENT) {
                $fields_before['mv_team_cnt'] = array('desc' => $lng->getTrn('common/teams'));
            }
            else if ($ALL_TIME) {
                $fields_before['rg_team_cnt'] = array('desc' => $lng->getTrn('common/teams'));
            }
            foreach (array('won', 'lost', 'draw') as $f) {
                unset($fields["rg_s$f"]);
                unset($fields["mv_s$f"]);
            }
            break;

        case STATS_COACH:
            $fields_after = array(
                'mv_tcasf' => array('desc' => 'tcasf'), 
                'mv_tcasa' => array('desc' => 'tcasa'), 
                'mv_tcdiff' => array('desc' => 'tcdiff'), 
            	'mv_smp' => array('desc' => 'SMP'),
            );
            if ($node == T_NODE_TOURNAMENT) {
                $fields_before['mv_team_cnt'] = array('desc' => $lng->getTrn('common/teams'));
                $fields_after['mv_elo'] = array('desc' => 'ELO');
            }
            else if ($ALL_TIME) {
                $fields_before['rg_team_cnt'] = array('desc' => $lng->getTrn('common/teams'));
                $fields_after['rg_elo'] = array('desc' => 'ELO');
            }
            break;

        case STATS_STAR:
            $fields_after = array(
                'mv_mvp' => array('desc' => 'MVP'), 
                'mv_spp' => array('desc' => 'SPP'),
            );
            foreach (array('won', 'lost', 'draw', 'ga', 'gf') as $f) {
                unset($fields["rg_s$f"]);
                unset($fields["mv_s$f"]);
            }
            unset($fields["rg_win_pct"]);
            break;
    }

    return array_merge($fields_before, $fields, $fields_after);
}

private static function _isNodeAllTime($obj, $node, $node_id) 
{
    # Teams may not cross leagues, so a team's league stats is equal to its all-time stats.
    return (!$node || !$node_id || ($node == T_NODE_LEAGUE && $node_id == T_NODE_ALL) || ($obj == T_OBJ_TEAM && $node == T_NODE_LEAGUE));
}

public static function standings($obj, $node, $node_id, array $opts)
{
    /*
         Makes various kinds of standings tables.
         $obj and $node types are STATS_* types.

         $opts = array(
            'url' => page URL on which table is to be displayed (required!)
            'GET_SS' => GET Sorting suffix
            'return_objects' => bool
            'teams_from' => [T_OBJ_COACH|T_OBJ_RACE] when $obj = T_OBJ_TEAM and this is set, only teams related to this object type (teams_from), of ID = $opts[teams_from_id] are fetched.
            'teams_from_id' => ID (int) see "teams_from" for details.
         );
     */

    global $lng, $settings, $objFields_avg;

    $tblTitle = '';
    $objs = $fields = $extra = array();
    $fields_before = $fields_after = array(); // To be merged with $fields.

    if (!array_key_exists('GET_SS', $opts)) {$opts['GET_SS'] = '';}
    else {$extra['GETsuffix'] = $opts['GET_SS'];} # GET Sorting Suffix
    
    $extra['noHelp'] = false;
    
    $enableRaceSelector = ($obj == T_OBJ_PLAYER || $obj == T_OBJ_TEAM && (!isset($opts['teams_from']) || $opts['teams_from'] != T_OBJ_RACE));
    list($sel_node, $sel_node_id, $sel_state, $sel_race, $sel_sgrp, $sel_ff_field, $sel_ff_ineq, $sel_ff_limit) = HTMLOUT::nodeSelector(array('race' => $enableRaceSelector, 'sgrp' => true, 'ffilter' => true, 'obj' => $obj));
    $filter_node = array($sel_node => $sel_node_id);
    $filter_race = ($sel_race != T_RACE_ALL) ? array(T_OBJ_RACE => $sel_race) : array();
    $filter_having = array('having' => array($sel_ff_field.(($sel_ff_ineq == self::T_NS__ffilter_ineq_gt) ? '>=' : '<=').$sel_ff_limit));
    $SGRP_GEN = ($sel_sgrp == 'GENERAL');

    $ALL_TIME = self::_isNodeAllTime($obj, $sel_node, $sel_node_id);

    $manualSort = isset($_GET["sort$opts[GET_SS]"]);
    $sortRule = array_merge(
        ($manualSort) ? array((($_GET["dir$opts[GET_SS]"] == 'a') ? '+' : '-') . $_GET["sort$opts[GET_SS]"]) : array(), 
        sort_rule($obj)
    );

    $set_avg = (isset($_GET['pms']) && $_GET['pms']); // Per match stats?
    echo '<br><a href="'.$opts['url'].'&amp;pms='.(($set_avg) ? 0 : 1).'"><b>'.$lng->getTrn('common/'.(($set_avg) ? 'ats' : 'pms'))."</b></a><br><br>\n";

    // Common $obj type fields.
    $fields = self::_getDefFields($obj, $sel_node, $sel_node_id);
    
    // Was a different (non-general) stats group selected?
    if (!$SGRP_GEN) {
        $grps_short = getESGroups(true,true);
        $grps_long = getESGroups(true,false);
        $fields_short = $grps_short[$sel_sgrp];
        $fields_long = $grps_long[$sel_sgrp];
        
        $fields = array_combine(
            array_strpack('mv_%s', $fields_long), 
            array_map(create_function('$f', 'return array("desc" => $f);'), $fields_short)
        );
        $objFields_avg = array_merge($objFields_avg, array_map(create_function('$k', 'return substr($k, 3);'), array_keys($fields)));
    }

    switch ($obj)
    {
        case STATS_PLAYER:
            $tblTitle = $lng->getTrn('menu/statistics_menu/player_stn');
            $fields_before = array(
                'name'    => array('desc' => $lng->getTrn('common/player'), 'href' => array('link' => urlcompile(T_URL_PROFILE,T_OBJ_PLAYER,false,false,false), 'field' => 'obj_id', 'value' => 'player_id')),
                'f_tname' => array('desc' => $lng->getTrn('common/team'),   'href' => array('link' => urlcompile(T_URL_PROFILE,T_OBJ_TEAM,false,false,false), 'field' => 'obj_id', 'value' => 'owned_by_team_id')),
            );
            $objs = Stats::getRaw(T_OBJ_PLAYER, $filter_node+$filter_having+$filter_race, $settings['entries']['standings_players'], $sortRule, $set_avg);
            break;

        case STATS_TEAM:
            $tblTitle = $lng->getTrn('menu/statistics_menu/team_stn');
            $fields_before = array(
                'name' => array('desc' => $lng->getTrn('common/name'), 'href' => array('link' => urlcompile(T_URL_PROFILE,T_OBJ_TEAM,false,false,false), 'field' => 'obj_id', 'value' => 'team_id')),
            );

            // Show teams standings list only for teams owned by... ?
            switch ((array_key_exists('teams_from', $opts)) ? $opts['teams_from'] : false)
            {
                case T_OBJ_COACH:
                    $fields_before['f_rname'] = array('desc' => $lng->getTrn('common/race'), 'href' => array('link' => urlcompile(T_URL_PROFILE,T_OBJ_RACE,false,false,false), 'field' => 'obj_id', 'value' => 'f_race_id'));
                    $objs = Stats::getRaw(T_OBJ_TEAM, $filter_node+$filter_having+$filter_race+array(T_OBJ_COACH => (int) $opts['teams_from_id']), false, $sortRule, $set_avg);
                    break;

                case T_OBJ_RACE:
                    $fields_before['f_cname'] = array('desc' => $lng->getTrn('common/coach'), 'href' => array('link' => urlcompile(T_URL_PROFILE,T_OBJ_COACH,false,false,false), 'field' => 'obj_id', 'value' => 'owned_by_coach_id'));
                    $objs = Stats::getRaw(T_OBJ_TEAM, $filter_node+$filter_having+array(T_OBJ_RACE => (int) $opts['teams_from_id']), $settings['entries']['standings_teams'], $sortRule, $set_avg);
                    break;

                // All teams
                default:
                    $objs = Stats::getRaw(T_OBJ_TEAM, $filter_node+$filter_having+$filter_race, $settings['entries']['standings_teams'], $sortRule, $set_avg);
            }
            break;

        case STATS_RACE:
            $tblTitle = $lng->getTrn('menu/statistics_menu/race_stn');
            $fields_before = array(
                'name' => array('desc' => $lng->getTrn('common/race'), 'href' => array('link' => urlcompile(T_URL_PROFILE,T_OBJ_RACE,false,false,false), 'field' => 'obj_id', 'value' => 'race_id')),
            );
            $dash_empty = false;
            if ($sel_node == T_NODE_TOURNAMENT) {
                $dash_empty = 'mv_team_cnt';
            }
            else if ($ALL_TIME) {
                $dash_empty = 'rg_team_cnt';
            }
            if ($dash_empty) {
                $extra['dashed'] = array('condField' => $dash_empty, 'fieldVal' => 0, 'noDashFields' => array('name'));
            }
            $objs = Stats::getRaw(T_OBJ_RACE, $filter_node+$filter_having, false, $sortRule, $set_avg);

            break;

        case STATS_COACH:
            $tblTitle = $lng->getTrn('menu/statistics_menu/coach_stn');
            $fields_before = array(
                'name' => array('desc' => $lng->getTrn('common/coach'), 'href' => array('link' => urlcompile(T_URL_PROFILE,T_OBJ_COACH,false,false,false), 'field' => 'obj_id', 'value' => 'coach_id')),
            );
            $objs = Stats::getRaw(T_OBJ_COACH, $filter_node+$filter_having, $settings['entries']['standings_coaches'], $sortRule, $set_avg);
            // OPTIONALLY hide retired coaches.
            if ($settings['hide_retired']) {$objs = array_filter($objs, create_function('$obj', 'return !$obj["retired"];'));}
            break;

        case STATS_STAR:
            $tblTitle = $lng->getTrn('menu/statistics_menu/star_stn');
            $fields_before = array(
                'name' => array('desc' => $lng->getTrn('common/star'), 'href' => array('link' => urlcompile(T_URL_PROFILE,T_OBJ_STAR,false,false,false), 'field' => 'obj_id', 'value' => 'star_id')),
                // Statics
                'cost'   => array('desc' => 'Price', 'kilo' => true, 'suffix' => 'k'),
                'ma'     => array('desc' => 'Ma'),
                'st'     => array('desc' => 'St'),
                'ag'     => array('desc' => 'Ag'),
                'av'     => array('desc' => 'Av'),
            );
            $extra['dashed'] = array('condField' => 'mv_played', 'fieldVal' => 0, 'noDashFields' => array('name'));
            $objs = Stats::getRaw(T_OBJ_STAR, $filter_node+$filter_having, false, $sortRule, $set_avg);

            break;
    }

    foreach ($objs as $idx => $obj) {$objs[$idx] = (object) $obj;}
    if (!$SGRP_GEN) {
        $tmp = $fields_before['name'];
        $fields_before = $fields_after = array();
        $fields_before['name'] = $tmp;
    }
    $fields = array_merge($fields_before, $fields, $fields_after);
    // Add average marker on fields (*).
    if ($set_avg) {
        foreach (array_keys($fields) as $f) {
            $f_cut = preg_replace('/^\w\w\_/', '', $f);
            if (in_array($f_cut, $objFields_avg)) {
                $fields[$f]['desc'] .= '*';
            }
        }
    }
    HTMLOUT::sort_table(
       $tblTitle,
       $opts['url'].(($set_avg) ? '&amp;pms=1' : ''),
       $objs,
       $fields,
       $sortRule,
       array(),
       $extra
    );

    return (array_key_exists('return_objects', $opts) && $opts['return_objects']) ? $objs : true;
}

const T_NSStr__node    = 'NS_node';
const T_NSStr__node_id = 'NS_node_id';

public static function simpleLeagueSelector(array $opts)
{
    global $lng, $leagues, $coach, $settings;
    
    $lids = array_keys($leagues); # Used multiple times below to determine selected FP league.
    # Default league.
    $sel_lid = (is_object($coach) && isset($coach->settings['home_lid']) && in_array($coach->settings['home_lid'], $lids)) ? $coach->settings['home_lid'] : $settings['default_visitor_league'];
    # Update league view?
    if (isset($_POST['SLS_lid']) && in_array($_POST['SLS_lid'], $lids)) {
        $sel_lid = (int) $_POST['SLS_lid'];
    }
    else if ($_lid = self::getSelectedNodeLid()) { 
        $sel_lid = $_lid;
    }
    # Save league view.
    $_SESSION[self::T_NSStr__node]    = T_NODE_LEAGUE;
    $_SESSION[self::T_NSStr__node_id] = (int) $sel_lid;
    
    $HTMLselector = "<form name='SLS' method='POST' style='display:inline; margin:0px;'>".$lng->getTrn('common/league')." <select name='SLS_lid' onChange='document.SLS.submit();'>\n";
    foreach ($leagues as $lid => $desc) {
        $HTMLselector .= "<option value='$lid' ".(($lid == $sel_lid) ? 'SELECTED' : '').">$desc[lname]</option>\n";
    }
    $HTMLselector .= "</select></form>\n";
    
    if (isset($opts['loadLeagueSettings']) && $opts['loadLeagueSettings'])
        setupGlobalVars(T_SETUP_GLOBAL_VARS__LOAD_LEAGUE_SETTINGS, array('lid' => $sel_lid)); # Force load league settings.
        
    return array($sel_lid, $HTMLselector);
}

public static function getSelectedNodeLid()
{
    global $leagues;
    
    $lids = array_keys($leagues);
    $_lid = false;
    
    if (isset($_SESSION[self::T_NSStr__node_id]) && (int) $_SESSION[self::T_NSStr__node_id] > 0) { 
        $_lid = ((int) $_SESSION[self::T_NSStr__node] != T_NODE_LEAGUE) 
            ? get_parent_id((int) $_SESSION[self::T_NSStr__node], (int) $_SESSION[self::T_NSStr__node_id], T_NODE_LEAGUE) 
            : (int) $_SESSION[self::T_NSStr__node_id];
            
        if (!in_array($_lid, $lids)) {
            $_lid = false;
        }
    }
            
    return $_lid;
}

// Node Selector constants
const T_NS__ffilter_ineq_gt = 1; # Greater than.
const T_NS__ffilter_ineq_lt = 2; # Less than.

public static function nodeSelector(array $opts)
{
    global $lng, $raceididx, $coach;

    // Set defaults
    $s_node     = self::T_NSStr__node;
    $s_node_id  = self::T_NSStr__node_id;
    $s_state    = "NS_state";
    $s_race     = "NS_race";
    $s_sgrp     = "NS_sgrp";
        # Field filter
    $s_ffilter_field = "NS_ffilter__field"; # Field name, e.g. "mv_played".
    $s_ffilter_ineq  = "NS_ffilter__ineq"; # inequality direction (">=" or "<="), self::T_NS__ffilter_ineq_* values.
    $s_ffilter_limit = "NS_ffilter__limit"; # RHS of ineq, e.g. "20" in the expression mv_played > 20
    
    // Options
    $setState = (array_key_exists('state', $opts) && $opts['state']);
    $setRace = (array_key_exists('race', $opts) && $opts['race']);
    $setSGrp = (array_key_exists('sgrp', $opts) && $opts['sgrp']);
    $setFFilter = (array_key_exists('ffilter', $opts) && $opts['ffilter']);
    $obj = ($setFFilter) ? $opts['obj'] : null;
    
    // Defaults
    $def_node    = T_NODE_LEAGUE;
    $def_node_id = (is_object($coach) && isset($coach->settings['home_lid'])) ? $coach->settings['home_lid'] : T_NODE_ALL;
    $def_state   = T_STATE_ALLTIME;
    $def_race    = T_RACE_ALL;
    $def_sgrp    = 'GENERAL';
    $def_ffilter_field = 'mv_played';
    $def_ffilter_ineq  = self::T_NS__ffilter_ineq_gt;
    $def_ffilter_limit = '0';

    $NEW = isset($_POST['select']);
    $_SESSION[$s_state] = ($NEW && $setState) ? (int) $_POST['state_in'] : (isset($_SESSION[$s_state]) ? $_SESSION[$s_state] : $def_state);
    $_SESSION[$s_race]  = ($NEW && $setRace)  ? (int) $_POST['race_in']  : (isset($_SESSION[$s_race])  ? $_SESSION[$s_race]  : $def_race);
    $_SESSION[$s_sgrp]  = ($NEW && $setSGrp)  ? $_POST['sgrp_in']        : (isset($_SESSION[$s_sgrp])  ? $_SESSION[$s_sgrp]  : $def_sgrp);
    $_SESSION[$s_node]  = ($NEW)              ? (int) $_POST['node']     : (isset($_SESSION[$s_node])  ? $_SESSION[$s_node]  : $def_node);
    $rel = array(T_NODE_TOURNAMENT => 'tour', T_NODE_DIVISION => 'division', T_NODE_LEAGUE => 'league');
    $_SESSION[$s_node_id] = ($NEW) 
        ? (int) $_POST[$rel[$_SESSION[$s_node]].'_in']
        : (isset($_SESSION[$s_node_id])  ? $_SESSION[$s_node_id]  : $def_node_id);
        
    $_SESSION[$s_ffilter_field] = ($NEW && $setFFilter) ? $_POST['ffilter_field_in'] : (isset($_SESSION[$s_ffilter_field]) ? $_SESSION[$s_ffilter_field] : $def_ffilter_field);
    $_SESSION[$s_ffilter_ineq]  = ($NEW && $setFFilter) ? $_POST['ffilter_ineq_in']  : (isset($_SESSION[$s_ffilter_ineq])  ? $_SESSION[$s_ffilter_ineq]  : $def_ffilter_ineq);
    $_SESSION[$s_ffilter_limit] = ($NEW && $setFFilter) ? $_POST['ffilter_limit_in'] : (isset($_SESSION[$s_ffilter_limit]) ? $_SESSION[$s_ffilter_limit] : $def_ffilter_limit);
    
    // Fetch contents of node selector
    list($leagues,$divisions,$tours) = Coach::allowedNodeAccess(Coach::NODE_STRUCT__FLAT, is_object($coach) ? $coach->coach_id : false);
    
    ?>
    <form method="POST">
    <?php echo $lng->getTrn('common/displayfrom');?>
    <select name="node" onChange="
        selConst = Number(this.options[this.selectedIndex].value);
        disableall();
        switch(selConst)
        {
            case <?php echo T_NODE_TOURNAMENT;?>: document.getElementById('tour_in').style.display = 'inline'; break;
            case <?php echo T_NODE_DIVISION;?>:   document.getElementById('division_in').style.display = 'inline'; break;
            case <?php echo T_NODE_LEAGUE;?>:     document.getElementById('league_in').style.display = 'inline'; break;
        }
    ">
        <?php
        foreach (array(T_NODE_LEAGUE => $lng->getTrn('common/league'), T_NODE_DIVISION => $lng->getTrn('common/division'), T_NODE_TOURNAMENT => $lng->getTrn('common/tournament')) as $const => $name) {
            echo "<option value='$const' ".(($_SESSION[$s_node] == $const) ? 'SELECTED' : '').">$name</option>\n";
        }
        ?>
    </select>
    :
    <select style='display:none;' name="tour_in" id="tour_in">
        <?php
        foreach ($tours as $trid => $desc) {
            echo "<option value='$trid' ".
                (($_SESSION[$s_node] == T_NODE_TOURNAMENT && $_SESSION[$s_node_id] == $trid) ? 'SELECTED' : '')
                .">$desc[tname]</option>\n";
        }
        ?>
    </select>
    <select style='display:none;' name="division_in" id="division_in">
        <?php
        foreach ($divisions as $did => $desc) {
            echo "<option value='$did' ".
                (($_SESSION[$s_node] == T_NODE_DIVISION && $_SESSION[$s_node_id] == $did) ? 'SELECTED' : '')
                .">$desc[dname]</option>\n";
        }
        ?>
    </select>
    <select style='display:none;' name="league_in" id="league_in">
        <?php
        echo "<option value='".T_NODE_ALL."'>-".$lng->getTrn('common/all')."-</option>\n";
        foreach ($leagues as $lid => $desc) {
            echo "<option value='$lid' ".
                (($_SESSION[$s_node] == T_NODE_LEAGUE && $_SESSION[$s_node_id] == $lid) ? 'SELECTED' : '')
                .">$desc[lname]</option>\n";
        }
        ?>
    </select>
    <?php 
    if ($setState) {
        echo $lng->getTrn('common/type');
        ?>
        <select name="state_in" id="state_in">
            <?php
            echo "<option value='".T_STATE_ALLTIME."' ".(($_SESSION[$s_state] == T_STATE_ALLTIME) ? 'SELECTED' : '').">".$lng->getTrn('common/alltime')."</option>\n";
            echo "<option value='".T_STATE_ACTIVE."'  ".(($_SESSION[$s_state] == T_STATE_ACTIVE) ? 'SELECTED' : '').">".$lng->getTrn('common/active')."</option>\n";
            ?>
        </select> 
        <?php 
    }
    if ($setRace) {
        echo $lng->getTrn('common/race');
        ?>
        <select name="race_in" id="race_in">
            <?php
            echo "<option value='".T_RACE_ALL."'>-".$lng->getTrn('common/all')."-</option>\n";
            foreach ($raceididx as $rid => $rname) {
                echo "<option value='$rid'".(($_SESSION[$s_race] == $rid) ? 'SELECTED' : '').">$rname</option>\n";
            }
            ?>
        </select>
        <?php
    }
    if ($setSGrp) {
        echo $lng->getTrn('common/sgrp');
        ?>
        <select name="sgrp_in" id="sgrp_in">
            <?php
            echo "<option value='GENERAL'>".$lng->getTrn('common/general')."</option>\n";
            foreach (getESGroups(false) as $f) {
                echo "<option value='$f'".(($_SESSION[$s_sgrp] == $f) ? 'SELECTED' : '').">$f</option>\n";
            }
            ?>
        </select>
        <?php
    }
    if ($setFFilter) {
        echo $lng->getTrn('common/having');
        ?>
        <select name="ffilter_field_in" id="ffilter_field_in">
            <?php
            foreach (self::_getDefFields($obj, $_SESSION[$s_node], $_SESSION[$s_node_id]) as $f => $desc) {
                echo "<option value='$f'".(($_SESSION[$s_ffilter_field] == $f) ? 'SELECTED' : '').">$desc[desc]</option>\n";
            }
            ?>
        </select>
        <select name="ffilter_ineq_in" id="ffilter_ineq_in">
            <option value="<?php echo self::T_NS__ffilter_ineq_gt;?>" <?php echo ($_SESSION[$s_ffilter_ineq] == self::T_NS__ffilter_ineq_gt) ? 'SELECTED' : '';?>>>=</option>
            <option value="<?php echo self::T_NS__ffilter_ineq_lt;?>" <?php echo ($_SESSION[$s_ffilter_ineq] == self::T_NS__ffilter_ineq_lt) ? 'SELECTED' : '';?>><=</option>
        </select>
        <input type='text' name="ffilter_limit_in" id="ffilter_limit_in" size='2' value="<?php echo $_SESSION[$s_ffilter_limit];?>">
        <?php
    }
    ?>
    &nbsp;
    <input type="submit" name="select" value="<?php echo $lng->getTrn('common/select');?>">
    </form>
    <script language="JavaScript" type="text/javascript">
        var open;
        <?php
        echo '
            switch('.$_SESSION[$s_node].')
            {
                case '.T_NODE_TOURNAMENT.': open = "tour"; break;
                case '.T_NODE_DIVISION.':   open = "division"; break;
                case '.T_NODE_LEAGUE.':     open = "league"; break;
            }
        ';
        ?>
        document.getElementById(open+'_in').style.display = 'inline';
        function disableall()
        {
            document.getElementById('tour_in').style.display = 'none';
            document.getElementById('division_in').style.display = 'none';
            document.getElementById('league_in').style.display = 'none';
            return true;
        }
    </script>
    <?php

    $allNodes = ($_SESSION[$s_node] == T_NODE_LEAGUE && $_SESSION[$s_node_id] == T_NODE_ALL);
    return array(
        ($allNodes) ? false : $_SESSION[$s_node], 
        ($allNodes) ? false : $_SESSION[$s_node_id], 
        ($setState) ? $_SESSION[$s_state] : false, 
        ($setRace) ? $_SESSION[$s_race] : false,
        ($setSGrp) ? $_SESSION[$s_sgrp] : false,
        ($setFFilter) ? $_SESSION[$s_ffilter_field] : false,
        ($setFFilter) ? $_SESSION[$s_ffilter_ineq]  : false,
        ($setFFilter) ? $_SESSION[$s_ffilter_limit] : false,
    );
}

public static function frame_begin($stylesheet = false)
{
    global $settings;
    ?>
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
        <title><?php echo $settings['site_name']; ?></title>
        <link type="text/css" href="css/stylesheet<?php echo ($stylesheet) ? $stylesheet : $settings['stylesheet']; ?>.css" rel="stylesheet">
        <link rel="alternate" type="application/rss+xml" title="RSS Feed"href="rss.xml">
        <script type="text/javascript" src="lib/misc_functions.js"></script>
        <script type="text/javascript" src="lib/jquery-1.3.2.min.js"></script>
        <script type="text/javascript" src="lib/jquery.autocomplete-min.js"></script>
        <link type="text/css" href="css/autocomplete.css" rel="stylesheet">

        <!-- CSS MENU (./cssmenu extension) -->
        <link href="cssmenu/css/dropdown/dropdown.css" media="all" rel="stylesheet" type="text/css">
        <link href="cssmenu/css/dropdown/themes/default/default.ultimate.css" media="all" rel="stylesheet" type="text/css">
        <!--[if lt IE 7]>
        <script type="text/javascript" src="cssmenu/js/jquery/jquery.js"></script>
        <script type="text/javascript" src="cssmenu/js/jquery/jquery.dropdown.js"></script>
        <![endif]-->
    </head>
    <body>
        <div class="everything">
            <div class="banner"></div>
            <div class="menu">
                <?php HTMLOUT::make_menu(); ?>
            </div> <!-- Menu div end -->
            <div class="section"> <!-- This container holds the section specific content -->
    <?php
}

public static function frame_end()
{
    ?>
                <!-- Pseudo container to force parent container to have the correct height for (potential) floating children -->
                <div style="clear: both;"></div>
            </div> <!-- End of section div -->
        </div> <!-- End of everything div -->
    </body>
    </html>
    <?php
    return true;
}

private static function make_menu()
{

    global $lng, $coach, $settings, $rules, $admin_menu;

    ?>
    <ul id="nav" class="dropdown dropdown-horizontal">
        <?php
        if (isset($_SESSION['logged_in'])) { ?><li><a href="index.php?logout=1">     <?php echo $lng->getTrn('menu/logout');?></a></li><?php }
        else                               { ?><li><a href="index.php?section=login"><?php echo $lng->getTrn('menu/login');?></a></li><?php }

        if (isset($_SESSION['logged_in']) && is_object($coach)) {
            echo '<li><a href="'.urlcompile(T_URL_PROFILE,T_OBJ_COACH,$coach->coach_id,false,false).'">'.$lng->getTrn('menu/cc').'</a></li>';
            if (!empty($admin_menu)) {
                ?>
                <li><span class="dir"><?php echo $lng->getTrn('menu/admin_menu/name');?></span>
                    <ul>
                    <?php
                    foreach ($admin_menu as $lnk => $desc) {
                        echo "<li><a href='index.php?section=admin&amp;subsec=$lnk'>$desc</a></li>\n";
                    }
                    ?>
                    </ul>
                </li>
                <?php
            }
        }
        ?>
        <li><a href="index.php?section=main"><?php echo $lng->getTrn('menu/home');?></a></li>
        <li><a href="index.php?section=teamlist"><?php echo $lng->getTrn('menu/teams');?></a></li>
        <li><a href="index.php?section=coachlist"><?php echo $lng->getTrn('menu/coaches');?></a></li>
        <li><span class="dir"><?php echo $lng->getTrn('menu/matches_menu/name');?></span>
            <ul>
                <li><a href="index.php?section=matches&amp;type=tours"><?php echo $lng->getTrn('menu/matches_menu/tours');?></a></li>
                <li><a href="index.php?section=matches&amp;type=recent"><?php echo $lng->getTrn('menu/matches_menu/recent');?></a></li>
                <li><a href="index.php?section=matches&amp;type=upcoming"><?php echo $lng->getTrn('menu/matches_menu/upcoming');?></a></li>
            </ul>
        </li>
        <li><span class="dir"><?php echo $lng->getTrn('menu/statistics_menu/name');?></span>
            <ul>
                <li><a href="<?php echo urlcompile(T_URL_STANDINGS,T_OBJ_TEAM,false,false,false);?>"><?php echo $lng->getTrn('menu/statistics_menu/team_stn');?></a></li>
                <li><a href="<?php echo urlcompile(T_URL_STANDINGS,T_OBJ_PLAYER,false,false,false);?>"><?php echo $lng->getTrn('menu/statistics_menu/player_stn');?></a></li>
                <li><a href="<?php echo urlcompile(T_URL_STANDINGS,T_OBJ_COACH,false,false,false);?>"><?php echo $lng->getTrn('menu/statistics_menu/coach_stn');?></a></li>
                <li><a href="<?php echo urlcompile(T_URL_STANDINGS,T_OBJ_RACE,false,false,false);?>"><?php echo $lng->getTrn('menu/statistics_menu/race_stn');?></a></li>
                <li><a href="<?php echo urlcompile(T_URL_STANDINGS,T_OBJ_STAR,false,false,false);?>"><?php echo $lng->getTrn('menu/statistics_menu/star_stn');?></a></li>
            </ul>
        </li>
        <li><span class="dir"><?php echo $lng->getTrn('menu/plugins');?></span>
            <ul>
                <?php if (Module::isRegistered('UPLOAD_BOTOCS') && $settings['leegmgr_enabled']) { ?><li><a href="handler.php?type=leegmgr">BOTOCS match report upload</a></li><?php } ?>
                <?php if (Module::isRegistered('Search')){ ?><li><a href="handler.php?type=search"><?php echo $lng->getTrn('name', 'Search');?></a></li><?php } ?>
                <?php if (Module::isRegistered('HOF'))   { ?><li><a href="handler.php?type=hof"><?php echo $lng->getTrn('name', 'HOF');?></a></li><?php } ?>
                <?php if (Module::isRegistered('Wanted')){ ?><li><a href="handler.php?type=wanted"><?php echo $lng->getTrn('name', 'Wanted');?></a></li><?php } ?>
                <?php if (Module::isRegistered('Prize')) { ?><li><a href="handler.php?type=prize"><?php echo $lng->getTrn('name', 'Prize');?></a></li><?php } ?>
                <?php if (Module::isRegistered('Memmatches')) { ?><li><a href="handler.php?type=memmatches"><?php echo $lng->getTrn('name', 'Memmatches');?></a></li><?php } ?>
                <?php if (Module::isRegistered('Comparison')) { ?><li><a href="handler.php?type=comparison"><?php echo $lng->getTrn('name', 'Comparison');?></a></li><?php } ?>
                <?php if (Module::isRegistered('SGraph'))     { ?><li><a href="handler.php?type=graph&amp;gtype=<?php echo SG_T_LEAGUE;?>&amp;id=none"><?php echo $lng->getTrn('name', 'SGraph');?></a></li><?php } ?>
                <?php if (Module::isRegistered('Gallery'))    { ?><li><a href="handler.php?type=gallery"><?php echo $lng->getTrn('name', 'Gallery');?></a></li><?php } ?>
            </ul>
        </li>

        <li><a href="index.php?section=rules"><?php echo $lng->getTrn('menu/rules');?></a></li>
        <li><a href="index.php?section=about">OBBLM</a></li>
        <?php
        if (!empty($settings['forum_url'])) {
            ?><li><a href="<?php echo $settings['forum_url'];?>"><?php echo $lng->getTrn('menu/forum');?></a></li><?php
        }
        ?>
    </ul>
    <?php
}

// Prints an advanced sort table.
public static function sort_table($title, $lnk, array $objs, array $fields, array $std_sort, $sort = array(), $extra = array())
{

    /*
        extra fields:
            tableWidth  => CSS style width value

            dashed => array(
                'condField' => field name,                    // When an object has this field's (condField) = fieldVal, then a "-" is put in the place of all values.
                'fieldVal'  => field value,
                'noDashFields' => array('field 1', 'field 2') // ...unless the field name is one of those specified in the array 'noDashFields'.
            );
            remove => array(
                'condField' => field name,  // When an object has this field's (condField) = fieldVal, then the entry/row is not printed in the html table.
                'fieldVal'  => field value,
            );
            GETsuffix => suffix to paste into "dir" and "sort" GET strings.

            color => true/false. Boolean telling wheter or not we should look into each object for the field "HTMLfcolor" and "HTMLbcolor", and use these color codes to color the obj's row. Note: the object must contain the two previously stated fields, or else black-on-white is used as default.

            doNr => true/false. Boolean telling wheter or not to print the "Nr." column.
            limit => int. Stop printing rows when this row number is reached.
            anchor => string. Will create table sorting links, that include this identifier as an anchor.
            noHelp => true/false. Will enable/disable help link [?].
            noSRdisp => true/false. Will force not to show the table sort rule used/parsed.
    */
    global $settings, $lng;

    if (array_key_exists('remove', $extra)) {
        $objs = array_filter($objs, create_function('$obj', 'return ($obj->'.$extra['remove']['condField'].' != '.$extra['remove']['fieldVal'].');'));
    }
    $MASTER_SORT = array_merge($sort, $std_sort);
    if (!empty($MASTER_SORT)) {
        objsort($objs, $MASTER_SORT);
    }
    $no_print_fields = array();
    $DONR = (!array_key_exists('doNr', $extra) || $extra['doNr']) ? true : false;
    $LIMIT = (array_key_exists('limit', $extra)) ? $extra['limit'] : -1;
    $ANCHOR = (array_key_exists('anchor', $extra)) ? $extra['anchor'] : false;
    $NOSRDISP = (array_key_exists('noSRdisp', $extra)) ? $extra['noSRdisp'] : false;

    if ($DONR) {
        $fields = array_merge(array('nr' => array('desc' => '#')), $fields);
        array_push($no_print_fields, 'nr');
    }

    $CP = count($fields);

    ?>
    <table class="common" <?php echo (array_key_exists('tableWidth', $extra)) ? "style='width: $extra[tableWidth];'" : '';?>>
        <tr class="commonhead">
            <td colspan="<?php echo $CP;?>"><b>
            <?php echo $title;?>&nbsp;
            <?php
            if (!array_key_exists('noHelp', $extra) || !$extra['noHelp']) {
                ?><a TARGET="_blank" href="html/table_desc.html">[?]</a><?php
            }
            ?>
            </b></td>
        </tr>
        <tr>
            <?php
            foreach ($fields as $f => $attr)
                echo "<td><i>$attr[desc]</i></td>";
            ?>
        </tr>
        <tr>
        <?php
        foreach ($fields as $f => $attr) {
            if (in_array($f, $no_print_fields) || (array_key_exists('nosort', $attr) && $attr['nosort'])) {
                echo "<td></td>";
                continue;
            }
            if (array_key_exists('GETsuffix', $extra)) {
                $sort = 'sort'.$extra['GETsuffix'];
                $dir = 'dir'.$extra['GETsuffix'];
            }
            else {
                $sort = 'sort';
                $dir = 'dir';
            }
            $anc = '';
            if ($ANCHOR) {
                $anc = "#$ANCHOR";
            }

            echo "<td><b><a href='$lnk&amp;$sort=$f&amp;$dir=a$anc' title='Sort ascending'>+</a>/<a href='$lnk&amp;$sort=$f&amp;$dir=d$anc' title='Sort descending'>-</a></b></td>";
        }
        ?>
        </tr>
        <tr><td colspan="<?php echo $CP;?>"><hr></td></tr>
        <?php
        $i = 1;
        foreach ($objs as $o) {
            $DASH = (array_key_exists('dashed', $extra) && $o->{$extra['dashed']['condField']} == $extra['dashed']['fieldVal']) ? true : false;
            if (array_key_exists('color', $extra)) {
                $td = "<td style='background-color: ".(isset($o->HTMLbcolor) ? $o->HTMLbcolor : 'white')."; color: ".(isset($o->HTMLfcolor) ? $o->HTMLfcolor : 'black').";'>";
            }
            else {
                $td = '<td>';
            }
            echo "<tr>";
            if ($DONR) {
                echo $td.$i."</td>";
            }
            foreach ($fields as $f => $a) { // Field => attributes
                if (!in_array($f, $no_print_fields)) {
                    if ($DASH && !in_array($f, $extra['dashed']['noDashFields'])) {
                        echo $td."-</td>";
                        continue;
                    }
                    $cpy = $o->$f; // Don't change the objects themselves! Make copies!
                    if (array_key_exists('kilo', $a) && $a['kilo'])
                        $cpy /= 1000;
                        $cpy = (string) $cpy;
                    if (is_numeric($cpy) && !ctype_digit(($cpy[0] == '-') ? substr($cpy,1) : $cpy))
                        $cpy = sprintf("%1.2f", $cpy);
                    if (array_key_exists('suffix', $a) && $a['suffix'])
                        $cpy .= $a['suffix'];
                    if (array_key_exists('color', $a) && $a['color'])
                        $cpy = "<font color='$a[color]'>".$cpy."</font>";
                    if (array_key_exists('href', $a) && $a['href'])
                        $cpy  = "<a href='" . $a['href']['link'] . ((isset($a['href']['field'])) ? '&amp;'.$a['href']['field'].'='.$o->{$a['href']['value']} : '') . "'>". $cpy . "</a>";

                    if (isset($o->{"${f}_color"})) {
                        echo "<td style='background-color: ".$o->{"${f}_color"}."; color: black;'>".$cpy."</td>";
                    }
                    else {
                        echo $td.$cpy."</td>";
                    }
                }
            }
            echo "</tr>\n";
            if ($i++ == $LIMIT) {
                break;
            }
        }
        if (!$NOSRDISP) {
        ?>
        <tr>
            <td colspan="<?php echo $CP;?>">
            <hr>
            </td>
        </tr>
        <tr>
            <td align="right" colspan="<?php echo $CP;?>">
            <i><?php echo $lng->getTrn('common/sortedagainst');?>: <?php echo implode(', ', rule_dict($MASTER_SORT));?></i>
            </td>
        </tr>
        <?php
        }
    echo "</table>\n";
}

public static function generateEStable($obj)
{
    global $ES_fields, $lng;
    echo "<table>\n";
    echo "<tr><td><i>".$lng->getTrn('common/stat')."</i></td> 
        <td><i>".$lng->getTrn('common/alltime')."</i></td> 
        <td>&nbsp;<i>".$lng->getTrn('common/matchavg')."</i>&nbsp;</td> 
        <td><i>".$lng->getTrn('common/desc')."</i></td></tr>\n";
            echo "<tr><td colspan='4'><hr></td></tr>\n";    
    $grp = null;
    $objAVG = clone $obj;
    $objAVG->setStats(false,false,true);
    # Require that fields are already sorted against group type!!!
    foreach ($ES_fields as $ESf => $def) {
        if ($grp != $def['group']) {
            echo "<tr><td colspan='4'><br><b>$def[group]</b></td></tr>\n";
            $grp = $def['group'];
        }
        echo "<tr valign='top'><td>$ESf</td><td align='right'>".$obj->{"mv_$ESf"}."</td><td align='right'>".sprintf("%1.2f",$objAVG->{"mv_$ESf"})."</td><td style='padding-left:10px;'>".$def['desc']."</td></tr>\n";
    }
    echo "</table>";
}

private static $helpBoxIdx = 0;
public static function helpBox($body, $link = '', $style = '')
{
    $ID = 'helpBox'.(++self::$helpBoxIdx);
    if (!empty($link)) {
        echo "<a href='javascript:void(0);' onClick='slideToggle(\"$ID\");'>$link</a><br><br>";
    }
    echo "<div id='$ID' class='helpBox' style='".(empty($link) ? '' : 'display:none').";$style'>".$body.'</div>';
    return $ID;
}

private static $assistantBoxIdx = 0;
public static function assistantBox($body, $style = '')
{
    $ID = 'assistantBox'.(++self::$assistantBoxIdx);
    echo "<div id='$ID' class='assistantBox' style='$style'>".$body.'</div>';
    return $ID;
}

}

?>
