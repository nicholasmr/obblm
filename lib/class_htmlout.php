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

/*
 THIS FILE is used for HTML-helper routines.
 */

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
    
    if ($obj && $obj_id) {
        $matches = Stats::getPlayedMatches($obj, $obj_id, $node, $node_id, $opp_obj, $opp_obj_id, $opts['n'], true);
        foreach ($matches as $m) {
             $m->score = "$m->team1_score - $m->team2_score";
             $m->result = matchresult_icon($m->result);
             $m->mlink = "<a href='index.php?section=fixturelist&amp;match_id=$m->match_id'>[".$lng->getTrn('secs/recent/view')."]</a>";
             $m->tour_name = get_alt_col('tours', 'tour_id', $m->f_tour_id, 'name');
        }
        $fields = array(
            'date_played'       => array('desc' => 'Date played'), 
            'tour_name'         => array('desc' => 'Tournament'),
            'team1_name'        => array('desc' => 'Home'),
            'team2_name'        => array('desc' => 'Away'),
            'gate'              => array('desc' => 'Gate', 'kilo' => true, 'suffix' => 'k', 'href' => false), 
            'score'             => array('desc' => 'Score', 'nosort' => true), 
            'result'            => array('desc' => 'Result', 'nosort' => true), 
            'mlink'             => array('desc' => 'Match', 'nosort' => true), 
        );
    }
    else {
        $matches = Match::getMatches($opts['n'], ($node) ? $node : false, ($node) ? $node_id : false);
        foreach ($matches as $m) {
             $m->score = "$m->team1_score - $m->team2_score";
             $m->mlink = "<a href='index.php?section=fixturelist&amp;match_id=$m->match_id'>[".$lng->getTrn('secs/recent/view')."]</a>";
             $m->tour_name = get_alt_col('tours', 'tour_id', $m->f_tour_id, 'name');
        }
        $fields = array(
            'date_played'       => array('desc' => 'Date played'), 
            'tour_name'         => array('desc' => 'Tournament'),
            'team1_name'        => array('desc' => 'Home'),
            'team2_name'        => array('desc' => 'Away'),
            'gate'              => array('desc' => 'Gate', 'kilo' => true, 'suffix' => 'k', 'href' => false), 
            'score'             => array('desc' => 'Score', 'nosort' => true), 
            'mlink'             => array('desc' => 'Match', 'nosort' => true), 
        );
    }
    
    sort_table(
        'Recent matches', 
        $opts['url'], 
        $matches, 
        $fields, 
        sort_rule('match'), 
        (isset($_GET["sort$opts[GET_SS]"])) ? array((($_GET["dir$opts[GET_SS]"] == 'a') ? '+' : '-') . $_GET["sort$opts[GET_SS]"]) : array(),
        $extra
    );
}

public static function standings($obj, $node, $node_id, array $opts) 
{    
    /*
         Makes various kinds of standings tables.   
         $obj and $node types are STATS_* types.
         
         $opts = array(
            'url' => page URL on which table is to be displayed (required!)
            'GET_SS' => GET Sorting suffix
            'hidemenu' => bool
            'team_from' => STATS_*
            'team_from_id => obj ID
         );
     */
     
    global $lng;
    
    $tblTitle = $tblSortRule = '';
    $objs = $fields = $extra = array();
    $fields_before = $fields_after = array(); // To be merged with $fields.
    $CUSTOM_SORT = false;

    if (!array_key_exists('GET_SS', $opts)) {$opts['GET_SS'] = '';}
    else {$extra['GETsuffix'] = $opts['GET_SS'];} # GET Sorting Suffix
    
    $hidemenu = (array_key_exists('hidemenu', $opts) && $opts['hidemenu']);
    echo '<div ' . (($hidemenu) ? "style='display:none;'" : '').'>';
    list($sel_node, $sel_node_id) = HTMLOUT::nodeSelector($node, $node_id, $hidemenu, '');
    echo '</div>';

    $set_avg = (isset($_GET['pms']) && $_GET['pms']); // Per match stats?
    echo '<br><a href="'.$opts['url'].'&amp;pms='.(($set_avg) ? 0 : 1).'"><b>'.$lng->getTrn('global/misc/'.(($set_avg) ? 'oas' : 'pms'))."</b></a><br><br>\n";

    // Common $obj type fields.
    $fields = array(
        'won'               => array('desc' => 'W'), 
        'lost'              => array('desc' => 'L'), 
        'draw'              => array('desc' => 'D'), 
        'played'            => array('desc' => 'GP'), 
        'win_percentage'    => array('desc' => 'WIN%'), 
        'row_won'           => array('desc' => 'SW'), 
        'row_lost'          => array('desc' => 'SL'), 
        'row_draw'          => array('desc' => 'SD'), 
        'score_team'        => array('desc' => 'GF'.(($set_avg) ? '*' : '')),
        'score_opponent'    => array('desc' => 'GA'.(($set_avg) ? '*' : '')),
        'won_tours'         => array('desc' => 'WT'), 
        'td'                => array('desc' => 'Td'.(($set_avg) ? '*' : '')), 
        'cp'                => array('desc' => 'Cp'.(($set_avg) ? '*' : '')), 
        'intcpt'            => array('desc' => 'Int'.(($set_avg) ? '*' : '')), 
        'cas'               => array('desc' => 'Cas'.(($set_avg) ? '*' : '')), 
        'bh'                => array('desc' => 'BH'.(($set_avg) ? '*' : '')), 
        'si'                => array('desc' => 'Si'.(($set_avg) ? '*' : '')), 
        'ki'                => array('desc' => 'Ki'.(($set_avg) ? '*' : '')), 
    );
    
    switch ($obj)
    {
        case STATS_PLAYER:
            
            break;
            
        case STATS_TEAM:
            $tblTitle = 'Team standings';
            $tblSortRule = 'team';
            $fields_before = array('name' => array('desc' => 'Name', 'href' => array('link' => 'index.php?section=coachcorner', 'field' => 'team_id', 'value' => 'team_id')));
            $fields_after = array('tcas'  => array('desc' => 'tcas'), 'value' => array('desc' => 'Value', 'kilo' => true, 'suffix' => 'k'));
            $ALL_TIME = ($sel_node == STATS_LEAGUE && ($sel_node_id == 0 || $sel_node_id === false));
            if ($USE_ELO = ($sel_node == STATS_TOUR || $ALL_TIME)) {
                $fields_after['elo'] = array('desc' => 'ELO');
            }
            switch ((array_key_exists('teams_from', $opts)) ? $opts['teams_from'] : false)
            {
                case STATS_COACH:
                    $fields_before['race'] = array('desc' => 'Race', 'href' => array('link' => 'index.php?section=races', 'field' => 'race', 'value' => 'f_race_id'));
                    $c = new Coach($opts['teams_from_id']);
                    $objs = $c->getTeams();
                    break;
                    
                case STATS_RACE:
                    $fields_before['coach_name'] = array('desc' => 'Coach', 'href' => array('link' => 'index.php?section=coaches', 'field' => 'coach_id', 'value' => 'owned_by_coach_id'));
                    $r = new Race($opts['teams_from_id']);
                    $objs = $r->getTeams();                
                    break;
                
                // All teams
                default:
                    $objs = Team::getTeams();
            }
            // Unless all-time team standings is wanted, then don't print teams who have not played in (for example) the tournament.
            if (!$ALL_TIME) {
                $extra['remove'] = array('condField' => 'played', 'fieldVal' => 0);
            }
            if($node == STATS_TOUR) {
                $tr = new Tour($node_id);
                $CUSTOM_SORT = $tr->getRSSortRule(false);
                if ($tr->isRSWithPoints()) {
                    $fields_after['points'] = array('desc' => 'PTS');
                }
                $fields_after['smp'] = array('desc' => 'SMP');
            }
            
            if ($USE_ELO) {$ELORanks = ELO::getRanks(($sel_node == STATS_TOUR) ? $sel_node_id : false);}
            foreach ($objs as $o) {
                if ($USE_ELO) {$o->elo = $ELORanks[$o->team_id];}
                $o->setStats($sel_node, $sel_node_id, $set_avg);
            }            
            break;
            
        case STATS_RACE:
            $tblTitle = 'Race standings';
            $tblSortRule = 'race';
            $fields = array_merge(array(
                'name'      => array('desc' => 'Race', 'href' => array('link' => 'index.php?section=races', 'field' => 'race', 'value' => 'race_id')), 
                'teams_cnt' => array('desc' => 'Teams'),
            ), $fields);
            $extra['dashed'] = array('condField' => 'teams_cnt', 'fieldVal' => 0, 'noDashFields' => array('name'));
            
            $objs = Race::getRaces(true);
            foreach ($objs as $o) {
                $o->setStats($sel_node, $sel_node_id, $set_avg);
            }
                
            break;
            
        case STATS_COACH:
            $tblTitle = 'Coaches standings';
            $tblSortRule = 'coach';
            $fields = array_merge(array(
                'name'      => array('desc' => 'Coach', 'href' => array('link' => 'index.php?section=coaches', 'field' => 'coach_id', 'value' => 'coach_id')),
                'teams_cnt' => array('desc' => 'Teams'), 
            ), $fields);        
            $objs = Coach::getCoaches();
            foreach ($objs as $o) {
                $o->setStats($sel_node, $sel_node_id, $set_avg);
            }
            break;
    }
    
    $fields = array_merge($fields_before, $fields, $fields_after);
    sort_table(
       $tblTitle, 
       $opts['url'].(($set_avg) ? '&amp;pms=1' : ''), 
       $objs, 
       $fields, 
       (empty($CUSTOM_SORT)) ? sort_rule($tblSortRule) : $CUSTOM_SORT, 
       (isset($_GET["sort$opts[GET_SS]"])) ? array((($_GET["dir$opts[GET_SS]"] == 'a') ? '+' : '-') . $_GET["sort$opts[GET_SS]"]) : array(),
       $extra
    );
}

public static function nodeSelector($node, $node_id, $FORCE_FALSE = false, $prefix = '') 
{
    // Set defaults
    $s_node     = "${prefix}_node";     # _SESSION index
    $s_node_id  = "${prefix}_node_id";  # _SESSION index
    if (($node && $node_id) || !isset($_SESSION[$s_node]) || $FORCE_FALSE) {
        $_SESSION[$s_node] = $node;
        $_SESSION[$s_node_id] = $node_id;
    }
    
    $NEW = isset($_POST['select']);
    switch ($_SESSION[$s_node] = ($NEW) ? $_POST['node'] : (($_SESSION[$s_node]) ? $_SESSION[$s_node] : STATS_LEAGUE))
    {
        case STATS_TOUR:        $_SESSION[$s_node_id] = ($NEW) ? $_POST['tour_in']       : $_SESSION[$s_node_id]; break;
        case STATS_DIVISION:    $_SESSION[$s_node_id] = ($NEW) ? $_POST['division_in']   : $_SESSION[$s_node_id]; break;
        case STATS_LEAGUE:      $_SESSION[$s_node_id] = ($NEW) ? $_POST['league_in']     : $_SESSION[$s_node_id]; break;
        default:                $_SESSION[$s_node_id] = false; // All-time.
    }
    
    ?>
    <form method="POST">
    Display from 
    <select name="node" onChange="
        selConst = Number(this.options[this.selectedIndex].value); 
        disableall();
        switch(selConst) 
        {
            case <?php echo STATS_TOUR;?>:      document.getElementById('tour_in').style.display = 'inline'; break;
            case <?php echo STATS_DIVISION;?>:  document.getElementById('division_in').style.display = 'inline'; break;
            case <?php echo STATS_LEAGUE;?>:    document.getElementById('league_in').style.display = 'inline'; break;
        }
    ">
        <?php
        foreach (array(STATS_LEAGUE => 'League', STATS_DIVISION => 'Division', STATS_TOUR => 'Tournament') as $const => $name) {
            echo "<option value='$const' ".(($_SESSION[$s_node] == $const) ? 'SELECTED' : '').">$name</option>\n";
        }
        ?>
    </select>
    :
    <select style='display:none;' name="tour_in" id="tour_in">
        <?php
        foreach (Tour::getTours() as $t) {
            echo "<option value='$t->tour_id' ".
                (($_SESSION[$s_node] == STATS_TOUR && $_SESSION[$s_node_id] == $t->tour_id) ? 'SELECTED' : '')
                .">$t->name</option>\n";
        }
        ?>
    </select>
    <select style='display:none;' name="division_in" id="division_in">
        <?php
        foreach (Division::getDivisions() as $d) {
            echo "<option value='$d->did'".
                (($_SESSION[$s_node] == STATS_DIVISION && $_SESSION[$s_node_id] == $d->did) ? 'SELECTED' : '')
                .">$d->name</option>\n";
        }
        ?>
    </select>
    <select style='display:none;' name="league_in" id="league_in">
        <?php
        echo "<option value='0'>-All-</option>\n";
        foreach (League::getLeagues() as $l) {
            echo "<option value='$l->lid'".
                (($_SESSION[$s_node] == STATS_LEAGUE && $_SESSION[$s_node_id] == $l->lid) ? 'SELECTED' : '')
                .">$l->name</option>\n";
        }
        ?>
    </select> &nbsp;
    <input type="submit" name="select" value="Select">
    </form>
    <script language="JavaScript" type="text/javascript">
        <?php
        echo '
            switch('.$_SESSION[$s_node].') 
            {
                case '.STATS_TOUR.':      open = "tour"; break;
                case '.STATS_DIVISION.':  open = "division"; break;
                case '.STATS_LEAGUE.':    open = "league"; break;
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
    return array($_SESSION[$s_node], $_SESSION[$s_node_id]);
}

}

?>
