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
    
    $matches = ($FOR_OBJ = $obj && $obj_id) 
        ? $matches = Stats::getMatches($obj, $obj_id, $node, $node_id, $opp_obj, $opp_obj_id, $opts['n'], true, false)
        : $matches = Match::getMatches($opts['n'], ($node) ? $node : false, ($node) ? $node_id : false, false);

    foreach ($matches as $m) {
        $m->score = "$m->team1_score - $m->team2_score";
        $m->mlink = "<a href='index.php?section=fixturelist&amp;match_id=$m->match_id'>[".$lng->getTrn('secs/recent/view')."]</a>";
        $m->tour_name = get_alt_col('tours', 'tour_id', $m->f_tour_id, 'name');
        if ($FOR_OBJ) {
            $m->result = matchresult_icon($m->result);
        }
    }

    $fields = array(
        'date_played' => array('desc' => 'Date played'), 
        'tour_name'   => array('desc' => 'Tournament'),
        'team1_name'  => array('desc' => 'Home', 'href' => array('link' => 'index.php?section=coachcorner', 'field' => 'team_id', 'value' => 'team1_id')), 
        'team2_name'  => array('desc' => 'Away', 'href' => array('link' => 'index.php?section=coachcorner', 'field' => 'team_id', 'value' => 'team2_id')),
        'gate'        => array('desc' => 'Gate', 'kilo' => true, 'suffix' => 'k', 'href' => false), 
        'score'       => array('desc' => 'Score', 'nosort' => true), 
    );
    if ($FOR_OBJ) {$fields['result'] = array('desc' => 'Result', 'nosort' => true);}
    $fields['mlink'] = array('desc' => 'Match', 'nosort' => true); # Must be last!
    
    HTMLOUT::sort_table(
        'Recent matches', 
        $opts['url'], 
        $matches, 
        $fields, 
        sort_rule('match'), 
        (isset($_GET["sort$opts[GET_SS]"])) ? array((($_GET["dir$opts[GET_SS]"] == 'a') ? '+' : '-') . $_GET["sort$opts[GET_SS]"]) : array(),
        $extra
    );
}

public static function upcommingGames($obj, $obj_id, $node, $node_id, $opp_obj, $opp_obj_id, array $opts) 
{
    /*
        Make upcomming games table.
        
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
        $m->mlink = "<a href='index.php?section=fixturelist&amp;match_id=$m->match_id'>[".$lng->getTrn('secs/recent/view')."]</a>";
        $m->tour_name = get_alt_col('tours', 'tour_id', $m->f_tour_id, 'name');
    }

    $fields = array(
        'date_created'      => array('desc' => 'Date created'), 
        'tour_name'         => array('desc' => 'Tournament'),
        'team1_name'        => array('desc' => 'Home'),
        'team2_name'        => array('desc' => 'Away'),
        'mlink'             => array('desc' => 'Match', 'nosort' => true), 
    );
    
    HTMLOUT::sort_table(
        'Upcomming matches', 
        $opts['url'], 
        $matches, 
        $fields, 
        array('+date_created'), 
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
     
    global $lng, $settings;
    
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
            $tblTitle = 'Player standings';
            $tblSortRule = 'player_overall';
            $DIS_VAL = !($sel_node == false && $sel_node_id == false);
            $fields_before = array(
                'name'      => array('desc' => 'Player', 'href' => array('link' => 'index.php?section=coachcorner', 'field' => 'player_id', 'value' => 'player_id')),
                'team_name' => array('desc' => 'Team',   'href' => array('link' => 'index.php?section=coachcorner', 'field' => 'team_id', 'value' => 'owned_by_team_id')), 
            );
            $fields_after = array(
                'mvp'   => array('desc' => 'MVP'.(($set_avg) ? '*' : '')), 
                'spp'   => array('desc' => 'SPP'.(($set_avg) ? '*' : '')),
                'value' => array('desc' => 'Value', 'nosort' => $DIS_VAL, 'kilo' => !$DIS_VAL, 'suffix' => (!$DIS_VAL) ? 'k' : ''), 
            );
            global $settings;
            $extra['limit'] = $settings['entries_players'];
            $objs = Player::getPlayers();
            foreach ($objs as $o) {
                if     ($o->is_sold) $o->HTMLbcolor = COLOR_HTML_SOLD;
                elseif ($o->is_dead) $o->HTMLbcolor = COLOR_HTML_DEAD; 
                if ($DIS_VAL) $o->value = '-';
                $o->setStats($sel_node, $sel_node_id, $set_avg);
            }
            break;
            
        case STATS_TEAM:
            $tblTitle = 'Team standings';
            $tblSortRule = 'team';
            $fields_before = array('name' => array('desc' => 'Name', 'href' => array('link' => 'index.php?section=coachcorner', 'field' => 'team_id', 'value' => 'team_id')));
            $fields_after = array('tcas'  => array('desc' => 'tcas'), 'value' => array('desc' => 'Value', 'kilo' => true, 'suffix' => 'k'));
            $ALL_TIME = ($sel_node == false && ($sel_node_id == 0 || $sel_node_id === false));
            if ($USE_ELO = ($sel_node == STATS_TOUR || $ALL_TIME)) {
                $fields_after['elo'] = array('desc' => 'ELO');
            }
            // Show teams standings list only for teams owned by... ?
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
            // OPTIONALLY hide retired teams.
            if ($ALL_TIME && $settings['hide_retired']) {$objs = array_filter($objs, create_function('$obj', 'return !$obj->is_retired;'));}
            // Unless all-time team standings is wanted, then don't print teams who have not played in (for example) the tournament.
            if (!$ALL_TIME) {
                $extra['remove'] = array('condField' => 'played', 'fieldVal' => 0);
            }
            if ($node == STATS_TOUR) {
                $tr = new Tour($node_id);
                $CUSTOM_SORT = $tr->getRSSortRule(false);
                if ($tr->isRSWithPoints()) {
                    $fields_after['points'] = array('desc' => 'PTS');
                }
                $fields_after['smp'] = array('desc' => 'SMP');
                unset($fields_after['value']);
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
            $fields_before = array(
                'name'      => array('desc' => 'Race', 'href' => array('link' => 'index.php?section=races', 'field' => 'race', 'value' => 'race_id')), 
                'teams_cnt' => array('desc' => 'Teams'),
            );
            $extra['dashed'] = array('condField' => 'teams_cnt', 'fieldVal' => 0, 'noDashFields' => array('name'));
            
            $objs = Race::getRaces(true);
            foreach ($objs as $o) {
                $o->setStats($sel_node, $sel_node_id, $set_avg);
            }
                
            break;
            
        case STATS_COACH:
            $tblTitle = 'Coaches standings';
            $tblSortRule = 'coach';
            $fields_before = array(
                'name'      => array('desc' => 'Coach', 'href' => array('link' => 'index.php?section=coaches', 'field' => 'coach_id', 'value' => 'coach_id')),
                'teams_cnt' => array('desc' => 'Teams'), 
            );
            $objs = Coach::getCoaches();
            // OPTIONALLY hide retired coaches.
            if ($settings['hide_retired']) {$objs = array_filter($objs, create_function('$obj', 'return !$obj->retired;'));}
            foreach ($objs as $o) {
                $o->setStats($sel_node, $sel_node_id, $set_avg);
            }
            break;
            
        case STATS_STAR:
            $tblTitle = 'Star standings';
            $tblSortRule = 'star';
            $fields_before = array(
                'name' => array('desc' => 'Star', 'href' => array('link' => 'index.php?section=stars', 'field' => 'sid', 'value' => 'star_id')), 
            );
            $fields_after = array('mvp' => array('desc' => 'MVP'), 'spp' => array('desc' => 'SPP'));
            unset($fields['score_team']); unset($fields['score_opponent']); unset($fields['won_tours']);
            unset($fields['row_won']); unset($fields['row_lost']); unset($fields['row_draw']);
            $extra['dashed'] = array('condField' => 'played', 'fieldVal' => 0, 'noDashFields' => array('name'));
            
            $objs = Star::getStars(false,false,false,false);
            foreach ($objs as $o) {
                $o->setStats(false, false, $sel_node, $sel_node_id, $set_avg);
                $o->name = preg_replace('/\s/', '&nbsp;', $o->name);
            }
            break;
    }
    
    $fields = array_merge($fields_before, $fields, $fields_after);
    HTMLOUT::sort_table(
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
    switch ($_SESSION[$s_node] = ($NEW) ? (int) $_POST['node'] : (($_SESSION[$s_node]) ? $_SESSION[$s_node] : STATS_LEAGUE))
    {
        case STATS_TOUR:        if ($NEW) {$_SESSION[$s_node_id] = (int) $_POST['tour_in'];} break;
        case STATS_DIVISION:    if ($NEW) {$_SESSION[$s_node_id] = (int) $_POST['division_in'];} break;
        case STATS_LEAGUE:      if ($NEW) {$_SESSION[$s_node_id] = (int) $_POST['league_in'];} break;
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
    if ($_SESSION[$s_node] == STATS_LEAGUE && $_SESSION[$s_node_id] == 0) {
        $_SESSION[$s_node] = $_SESSION[$s_node_id] = false;
    }
    return array($_SESSION[$s_node], $_SESSION[$s_node_id]);
}

public static function frame_begin($stylesheet = false) 
{
    global $settings;
    ?>
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
        <title><?php echo $settings['site_name']; ?> Blood Bowl League</title>
        <link type="text/css" href="css/stylesheet<?php echo ($stylesheet) ? $stylesheet : $settings['stylesheet']; ?>.css" rel="stylesheet">
        <link rel="alternate" type="application/rss+xml" title="RSS Feed"href="rss.xml" />
        <script type="text/javascript" src="lib/misc_functions.js"></script>
        
        <!-- CSS MENU (./cssmenu extension) -->
        <link href="cssmenu/css/dropdown/dropdown.css" media="all" rel="stylesheet" type="text/css" />
        <link href="cssmenu/css/dropdown/themes/default/default.ultimate.css" media="all" rel="stylesheet" type="text/css" />
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

    global $lng, $coach, $settings, $rules;
    
    ?>
    <ul id="nav" class="dropdown dropdown-horizontal">
        <?php 
        if (isset($_SESSION['logged_in'])) { ?><li><a href="index.php?logout=1">     <?php echo $lng->getTrn('global/secLinks/logout');?></a></li><?php }
        else                               { ?><li><a href="index.php?section=login"><?php echo $lng->getTrn('global/secLinks/login');?></a></li><?php }
        if (isset($_SESSION['logged_in'])) { 
            ?>
            <li><span class="dir"><?php echo $lng->getTrn('global/secLinks/cc');?></span>
                <ul>
                    <li><a href='index.php?section=coachcorner'><?php echo $lng->getTrn('global/secLinks/coachcorner');?></a></li>
                    <li><span class="dir"><?php echo $lng->getTrn('global/secLinks/myteams');?></span>
                        <ul>
                            <?php
                            foreach ($coach->getTeams() as $t) {
                                echo "<li><a href='index.php?section=coachcorner&amp;team_id=$t->team_id'>$t->name</a></li>\n";
                            }
                            echo "<li><a style='font-style: italic;' href='index.php?section=coachcorner&amp;team_id=new'>".$lng->getTrn('secs/cc/main/start_new')."</a></li>\n";
                            ?>
                        </ul>
                    </li>
                    <li><a href='index.php?section=coaches&amp;coach_id=<?php echo $coach->coach_id;?>'><?php echo $lng->getTrn('global/secLinks/myprofile');?></a></li>
                </ul>
            </li>
            <?php
        }
        if (isset($_SESSION['logged_in'])) {
            $ring_sys_access = array('usrman' => $lng->getTrn('secs/admin/um'), 'ldm' => $lng->getTrn('secs/admin/ldm'), 'chtr' => $lng->getTrn('secs/admin/th'), 'import' => $lng->getTrn('secs/admin/import'), 'ctman' => $lng->getTrn('secs/admin/delete'));
            $ring_com_access = array('tournament' => $lng->getTrn('secs/admin/schedule'), 'log' => $lng->getTrn('secs/admin/log'));
            if (is_object($coach) && $coach->ring <= RING_COM) {
                ?>
                <li><span class="dir"><?php echo $lng->getTrn('global/secLinks/admin');?></span>
                    <ul>
                        <?php
                        foreach ($ring_com_access as $lnk => $desc) {
                            echo "<li><a href='index.php?section=admin&amp;subsec=$lnk'>$desc</a></li>\n";
                        }
                        if ($coach->ring == RING_SYS) {
                            foreach ($ring_sys_access as $lnk => $desc) {
                                echo "<li><a style='font-style: italic;' href='index.php?section=admin&amp;subsec=$lnk'>$desc</a></li>\n";
                            }            
                        }
                        ?>
                    </ul>
                </li>
                <?php
            }
        }
        ?>
        <li><a href="index.php?section=main"><?php echo $lng->getTrn('global/secLinks/home');?></a></li>
        <li><a href="index.php?section=teams"><?php echo $lng->getTrn('global/secLinks/teams');?></a></li>
        <li><a href="index.php?section=fixturelist"><?php echo $lng->getTrn('global/secLinks/fixtures');?></a></li>
        <li><span class="dir"><?php echo $lng->getTrn('global/secLinks/statistics');?></span>
            <ul>
                <li><a href="index.php?section=standings"><?php echo $lng->getTrn('global/secLinks/standings');?></a></li>
                <li><span class="dir"><?php echo $lng->getTrn('global/secLinks/specstandings');?></span>
                    <ul>
                        <?php
                        foreach (Tour::getTours() as $t) {
                            echo "<li><a href='index.php?section=fixturelist&amp;tour_id=$t->tour_id'>$t->name&nbsp;&nbsp;<i>(".get_alt_col('leagues', 'lid', get_alt_col('divisions', 'did', $t->f_did, 'f_lid'), 'name').")</i></a></li>\n";
                        }
                        ?>
                    </ul>
                </li>
                <li><span class="dir">Division standings</span>
                    <ul>
                        <?php
                        foreach (Division::getDivisions() as $d) {
                            echo "<li><a href='index.php?section=fixturelist&amp;did=$d->did'>$d->name&nbsp;&nbsp;<i>($d->league_name)</i></a></li>\n";
                        }
                        ?>
                    </ul>
                </li>
                <li><span class="dir">League standings</span>
                    <ul>
                        <?php
                        foreach (League::getLeagues() as $l) {
                            echo "<li><a href='index.php?section=fixturelist&amp;lid=$l->lid'>$l->name</a></li>\n";
                        }
                        ?>
                    </ul>
                </li>
                <li><a href="index.php?section=recent"><?php echo $lng->getTrn('global/secLinks/recent');?></a></li>
                <li><a href="index.php?section=upcomming"><?php echo $lng->getTrn('global/secLinks/upcomming');?></a></li>
                <li><a href="index.php?section=players"><?php echo $lng->getTrn('global/secLinks/players');?></a></li>
                <li><a href="index.php?section=coaches"><?php echo $lng->getTrn('global/secLinks/coaches');?></a></li>
                <li><a href="index.php?section=races"><?php echo $lng->getTrn('global/secLinks/races');?></a></li>
                <?php
                if ($rules['enable_stars_mercs']) {
                    ?><li><a href="index.php?section=stars"><?php echo $lng->getTrn('global/secLinks/stars');?></a></li><?php
                }
                ?>
                <li><a href="index.php?section=comparison"><?php echo 'Comparison';?></a></li>
                <li><a href="handler.php?type=graph&amp;gtype=<?php echo SG_T_LEAGUE;?>&amp;id=none"><?php echo $lng->getTrn('secs/records/d_gstats');?></a></li>
            </ul>
        </li>
        <li><span class="dir"><?php echo $lng->getTrn('global/secLinks/records');?></span>
            <ul>
                <li><a href="index.php?section=records&amp;subsec=hof"><?php echo $lng->getTrn('secs/records/d_hof');?></a></li>
                <li><a href="index.php?section=records&amp;subsec=wanted"><?php echo $lng->getTrn('secs/records/d_wanted');?></a></li>
                <li><a href="index.php?section=records&amp;subsec=memm"><?php echo $lng->getTrn('secs/records/d_memma');?></a></li>
                <li><a href="index.php?section=records&amp;subsec=prize"><?php echo $lng->getTrn('secs/records/d_prizes');?></a></li>
            </ul>
        </li>
        
        <li><a href="index.php?section=rules"><?php echo $lng->getTrn('global/secLinks/rules');?></a></li>
        <li><a href="index.php?section=gallery"><?php echo $lng->getTrn('global/secLinks/gallery');?></a></li>
        <li><a href="index.php?section=about">OBBLM</a></li>
        <?php 
        if ($settings['enable_guest_book']) {
            ?><li><a href="index.php?section=guest"><?php echo $lng->getTrn('global/secLinks/gb');?></a></li><?php
        }
        if (!empty($settings['forum_url'])) {
            ?><li><a href="<?php echo $settings['forum_url'];?>"><?php echo $lng->getTrn('global/secLinks/forum');?></a></li><?php
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
    */
    global $settings;
    
    if (array_key_exists('remove', $extra)) {
        $objs = array_filter($objs, create_function('$obj', 'return ($obj->'.$extra['remove']['condField'].' != '.$extra['remove']['fieldVal'].');'));
    }
    $MASTER_SORT = array_merge($sort, $std_sort);
    objsort($objs, $MASTER_SORT);
    $no_print_fields = array();
    $DONR = (!array_key_exists('doNr', $extra) || $extra['doNr']) ? true : false;
    $LIMIT = (array_key_exists('limit', $extra)) ? $extra['limit'] : -1;
    $ANCHOR = (array_key_exists('anchor', $extra)) ? $extra['anchor'] : false;
    
    if ($DONR) {
        $fields = array_merge(array('nr' => array('desc' => 'Nr.')), $fields);
        array_push($no_print_fields, 'nr');
    }

    $CP = count($fields);
    
    ?>
    <table class="sort" <?php echo (array_key_exists('tableWidth', $extra)) ? "style='width: $extra[tableWidth];'" : '';?>>
        <tr>
            <td class="light" colspan="<?php echo $CP;?>"><b>
            <?php echo $title;?>&nbsp;
            <?php
            if (!array_key_exists('noHelp', $extra) || !$extra['noHelp']) {
                ?><a href="javascript:void(0);" onclick="window.open('html/table_desc.html','tableColumnDescriptions','width=600,height=400')">[?]</a><?php
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
            echo "<tr>\n";
            if ($DONR) {
                echo $td.$i."</td>\n";
            }
            foreach ($fields as $f => $a) { // Field => attributes
                if (!in_array($f, $no_print_fields)) {
                    if ($DASH && !in_array($f, $extra['dashed']['noDashFields'])) {
                        echo $td."-</td>\n";
                        continue;
                    }
                    $cpy = $o->$f; // Don't change the objects themselves! Make copies!
                    if (array_key_exists('kilo', $a) && $a['kilo'])
                        $cpy /= 1000;
                    if (is_float($cpy))
                        $cpy = sprintf("%1.2f", $cpy);
                    if (array_key_exists('suffix', $a) && $a['suffix'])
                        $cpy .= $a['suffix'];
                    if (array_key_exists('color', $a) && $a['color']) 
                        $cpy = "<font color='$a[color]'>".$cpy."</font>\n";
                    if (array_key_exists('href', $a) && $a['href']) 
                        $cpy  = "<a href='" . $a['href']['link'] . "&amp;" . $a['href']['field'] . "=" . $o->{$a['href']['value']} . "'>". $cpy . "</a>";

                    if (isset($o->{"${f}_color"})) {
                        echo "<td style='background-color: ".$o->{"${f}_color"}."; color: black;'>".$cpy."</td>\n";
                    }
                    else {
                        echo $td.$cpy."</td>\n";
                    }
                }
            }
            echo "</tr>\n";
            if ($i++ == $LIMIT) {
                break;
            }
        }
        if ($settings['show_sort_rule']) {
        ?>
        <tr>
            <td colspan="<?php echo $CP;?>">
            <hr>
            </td>
        </tr>
        <tr>
            <td align="right" colspan="<?php echo $CP;?>">
            <i>Sorted against: <?php echo implode(', ', rule_dict($MASTER_SORT));?></i>
            </td>
        </tr>
        <?php
        }
    echo "</table>\n";
}

public static function starHireHistory($obj, $obj_id, $node, $node_id, $star_id = false, $opts = array())
{
    /* If $star_id is false, then the HH from all stars of $obj = $obj_id will be displayed, instead of only the HH of star = $star_id */
    
    if (!array_key_exists('GET_SS', $opts)) {$opts['GET_SS'] = '';}
    else {$extra['GETsuffix'] = $opts['GET_SS'];} # GET Sorting Suffix
    $extra['doNr'] = false;
    $extra['noHelp'] = true;
    if ($ANC = array_key_exists('anchor', $opts)) {$extra['anchor'] = $opts['anchor'];}
    
    $mdat = array();
    
    foreach ((($star_id) ? array(new Star($star_id)) : Star::getStars($obj, $obj_id, $node, $node_id)) as $s) {
        foreach ($s->getHireHistory($obj, $obj_id, $node, $node_id) as $m) {
            $o = (object) array();
            foreach (array('match_id', 'date_played', 'hiredByName', 'hiredAgainstName') as $k) {
                $o->$k = $m->$k;
            }
            $s->setStats(false, false, STATS_MATCH, $m->match_id);
            foreach (array('td', 'cp', 'intcpt', 'cas', 'bh', 'si', 'ki', 'mvp', 'spp') as $k) {
                $o->$k = $s->$k;
            }
            $o->match = '[view]';
            $o->tour = get_alt_col('tours', 'tour_id', $m->f_tour_id, 'name');
            $o->score = "$m->team1_score - $m->team2_score";
            $o->result = matchresult_icon(
                (
                ($m->team1_id == $m->hiredBy && $m->team1_score > $m->team2_score) ||
                ($m->team2_id == $m->hiredBy && $m->team1_score < $m->team2_score)
                ) 
                    ? 'W'
                    : (($m->team1_score == $m->team2_score) ? 'D' : 'L')
            );
            $o->star_id = $s->star_id;
            $o->name = $s->name;
            array_push($mdat, $o);
        }
    }
    $fields = array(
        'date_played'       => array('desc' => 'Hire date'), 
        'name'              => array('desc' => 'Star', 'href' => array('link' => 'index.php?section=stars', 'field' => 'sid', 'value' => 'star_id')),
        'tour'              => array('desc' => 'Tournament'),
        'hiredByName'       => array('desc' => 'Hired by'), 
        'hiredAgainstName'  => array('desc' => 'Opponent team'), 
        'cp'     => array('desc' => 'Cp'), 
        'td'     => array('desc' => 'Td'), 
        'intcpt' => array('desc' => 'Int'), 
        'cas'    => array('desc' => 'Cas'), 
        'bh'     => array('desc' => 'BH'), 
        'si'     => array('desc' => 'Si'), 
        'ki'     => array('desc' => 'Ki'), 
        'mvp'    => array('desc' => 'MVP'),
        'score'  => array('desc' => 'Score', 'nosort' => true),
        'result' => array('desc' => 'Result', 'nosort' => true),
        'match'  => array('desc' => 'Match', 'href' => array('link' => 'index.php?section=fixturelist', 'field' => 'match_id', 'value' => 'match_id'), 'nosort' => true),
    );
    if ($star_id) {unset($fields['name']);}
    if ($obj && $obj_id) {unset($fields['hiredByName']);}
    $title = "Star hire history";
    if ($ANC) {$title = "<a name='$opts[anchor]'>".$title.'<a>';}
    HTMLOUT::sort_table(
        $title, 
        $opts['url'], 
        $mdat, 
        $fields, 
        sort_rule('star_HH'), 
        (isset($_GET["sort$opts[GET_SS]"])) ? array((($_GET["dir$opts[GET_SS]"] == 'a') ? '+' : '-') . $_GET["sort$opts[GET_SS]"]) : array(),
        $extra
    );
}

public static function dispTeamList($obj, $obj_id)
{
    // Prints a list of teams owned by $obj (STATS_*) with ID = $obj_id.
    
    global $settings;
    
    $teams = array();
    switch ($obj) {
        case STATS_COACH:
            $c = new Coach($obj_id);
            $teams = $c->getTeams();
            break;
            
        case STATS_RACE:
            $r = new Race($obj_id);
            $teams = $r->getTeams();
            break;
            
        case false:
        default:
            $teams = Team::getTeams();
            break;
    }
    // OPTIONALLY hide retired teams.
    if ($settings['hide_retired']) {$teams = array_filter($teams, create_function('$t', 'return !$t->is_retired;'));}
    objsort($teams, array('+name'));
    foreach ($teams as $t) {
        $retired = (($t->is_retired) ? '<b><font color="red">[R]</font></b>' : '');
        $t->name .= "</a>&nbsp;$retired<br><small>$t->coach_name</small><a>"; // The <a> tags are a little hack so that HTMLOUT::sort_table does not create the team link on coach name too.
        $t->logo = "<img border='0px' height='50' width='50' alt='Team race picture' src='" . $t->getLogo() . "'>";
        $t->retired = ($t->is_retired) ? '<b>Yes</b>' : 'No';
        $lt = $t->getLatestTour();
        $t->latest_tour = ($lt) ? get_alt_col('tours', 'tour_id', $lt, 'name') : '-';
        $prizes = $t->getPrizes(true);
        $t->prizes = (empty($prizes)) ? '<i>None</i>' : $prizes;
        $t->rdy = ($t->rdy) ? '<font color="green">Yes</font>' : '<font color="red">No</font>';
    }
    $fields = array(
        'logo'      => array('desc' => 'Logo', 'href' => array('link' => 'index.php?section=coachcorner', 'field' => 'team_id', 'value' => 'team_id'), 'nosort' => true), 
        'name'      => array('desc' => 'Name', 'href' => array('link' => 'index.php?section=coachcorner', 'field' => 'team_id', 'value' => 'team_id')),
        'rdy'       => array('desc' => 'Ready', 'nosort' => true), 
        'race'      => array('desc' => 'Race'), 
        'latest_tour' => array('desc' => 'Latest tour'), 
        'prizes'      => array('desc' => 'Prizes', 'nosort' => true), 
        'played'    => array('desc' => 'Games'), 
        'value'     => array('desc' => 'TV', 'kilo' => true, 'suffix' => 'k'),  
    );
    HTMLOUT::sort_table(
        "Teams ". (($obj == STATS_COACH) ? "<a href='javascript:void(0);' onclick=\"window.open('html/coach_corner_teams.html','ccorner_TeamsHelp','width=350,height=400')\">[?]</a>" : ''), 
        "index.php?section=".(($obj == STATS_COACH) ? 'coachcorner' : 'teams'), 
        $teams, 
        $fields, 
        array('+name'), 
        (isset($_GET['sort'])) ? array((($_GET['dir'] == 'a') ? '+' : '-') . $_GET['sort']) : array(),
        array('doNr' => false, 'noHelp' => true)
    );
}

}

?>
