<?php

/*
 *  Copyright (c) Niels Orsleff Justesen <njustesen@gmail.com> and Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2007-2009. All Rights Reserved.
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

if (version_compare(PHP_VERSION, '5.0.0') == -1)
    die('<font color="red"><b>Sorry. OBBLM requires PHP version 5, you are running version ' . PHP_VERSION . '.</b></font>');
    
$iniRG = ini_get('register_globals');
if (strtolower($iniRG) == 'on' || $iniRG == 1)
    die('<font color="red">Sorry. OBBLM requires the PHP configuration directive <i>register_globals</i> set <b><u>off</u></b> in the <i>php.ini</i> configuration file. Please contact your web host.</font>');

session_start();
error_reporting(E_ALL);
require('header.php'); // Includes and constants.

if (!is_writable(IMG))
    fatal('Sorry. OBBLM needs to be able to write to the <i>images</i> directory in order to work probably. Please check the directory permissions.');

/********************
 *   Main routine
 ********************/

// MySQL connect.
$conn = mysql_up(true);

// Load language.
$lng = new Translations($settings['lang']);

// Make 'main' the default section if no GET section request was sent.
if (!isset($_GET['section'])) {
    $_GET['section'] = 'main';
}

// Login?
if (isset($_POST['login'])) {
    if (get_magic_quotes_gpc()) {
        $_POST['coach'] = stripslashes($_POST['coach']);
        $_POST['passwd'] = stripslashes($_POST['passwd']);
    }
    if (!login($_POST['coach'], $_POST['passwd'], true)) {
        $_GET['section'] = 'login';
    }
}

// Logout?
if (isset($_GET['logout'])) {
    $_GET['section'] = 'main'; # Redirect logged out users to the main page.
    session_unset();
    session_destroy();
}

// Create coach object.
$coach = (isset($_SESSION['logged_in'])) ? new Coach($_SESSION['coach_id']) : null;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
    <title><?php echo $settings['site_name']; ?> Blood Bowl League</title>
    <link type="text/css" href="css/stylesheet<?php echo (isset($_SESSION['logged_in'])) ? $coach->settings['theme'] : $settings['stylesheet']; ?>.css" rel="stylesheet">
    <link rel="alternate" type="application/rss+xml" title="RSS Feed"href="rss.xml" />
    <script type="text/javascript" src="lib/misc_functions.js"></script>
    
    <!-- CSS MENU -->
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
        <div style="width:100%; float:left;">
            <ul id="nav" class="dropdown dropdown-horizontal">
                <?php 
                if (isset($_SESSION['logged_in'])) { ?><li><a href="index.php?logout=1">     <?php echo $lng->getTrn('global/secLinks/logout');?></a></li><?php }
                else                               { ?><li><a href="index.php?section=login"><?php echo $lng->getTrn('global/secLinks/login');?></a></li><?php }
                ?>
                <?php
                if (isset($_SESSION['logged_in'])) {
                    if (is_object($coach) && $coach->ring <= RING_COM) {
                        echo "<li><a href='index.php?section=admin'>".$lng->getTrn('global/secLinks/admin')."</a></li>";
                    }
                    echo "<li><a href='index.php?section=coachcorner'>".$lng->getTrn('global/secLinks/cc')."</a></li>";
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
                                    echo "<li><a href='index.php?section=fixturelist&amp;tour_id=$t->tour_id'>$t->name</a></li>\n";
                                }
                                ?>
                            </ul>
                        </li>
                        <li><a href="index.php?section=recent"><?php echo $lng->getTrn('global/secLinks/recent');?></a></li>
                        <li><a href="index.php?section=players"><?php echo $lng->getTrn('global/secLinks/players');?></a></li>
                        <li><a href="index.php?section=coaches"><?php echo $lng->getTrn('global/secLinks/coaches');?></a></li>
                        <li><a href="index.php?section=races"><?php echo $lng->getTrn('global/secLinks/races');?></a></li>
                        <?php
                        if ($rules['enable_stars_mercs']) {
                            ?><li><a href="index.php?section=stars"><?php echo $lng->getTrn('global/secLinks/stars');?></a></li><?php
                        }
                        ?>
                    </ul>
                </li>
                <li><span class="dir"><?php echo $lng->getTrn('global/secLinks/records');?></span>
                    <ul>
                        <li><a href="index.php?section=records&amp;subsec=hof"><?php echo $lng->getTrn('secs/records/d_hof');?></a></li>
                        <li><a href="index.php?section=records&amp;subsec=wanted"><?php echo $lng->getTrn('secs/records/d_wanted');?></a></li>
                        <li><a href="index.php?section=records&amp;subsec=memm"><?php echo $lng->getTrn('secs/records/d_memma');?></a></li>
                        <li><a href="index.php?section=records&amp;subsec=prize"><?php echo $lng->getTrn('secs/records/d_prizes');?></a></li>
                        <li><a href="handler.php?type=graph&amp;gtype=<?php echo SG_T_LEAGUE;?>&amp;id=none"><?php echo $lng->getTrn('secs/records/d_gstats');?></a></li>
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
        </div>
        <div class="section"> <!-- This container holds the section specific content -->
        <?php
        // Check if a menu-link was picked, and execute section code from sections.php accordingly.
        switch ($_GET['section']) 
        {
            case 'login':        sec_login();        break;
            case 'admin':        sec_admin();        break;
            case 'coachcorner':  sec_coachcorner();  break;
            case 'fixturelist':  sec_fixturelist();  break; // Tournaments
            case 'standings':    sec_standings();    break;
            case 'teams':        sec_teams();        break;
            case 'players':      sec_players();      break;
            case 'coaches':      sec_coaches();      break;
            case 'races':        sec_races();        break;
            case 'stars':        sec_stars();        break;
            case 'records':      sec_records();      break;
            case 'rules':        sec_rules();        break;
            case 'gallery':      sec_gallery();      break;
            case 'about':        sec_about();        break;
            case 'guest':        if($settings['enable_guest_book']){sec_guest(); break;} 
            case 'recent':       sec_recentmatches();break;
            default:             sec_main();
        }
        ?>
        <!-- Pseudo container to force parent container to have the correct height for (potential) floating children -->
        <div style="clear: both;"></div> 
        </div>
    </div>
</body>
</html>
<?php

mysql_close($conn);

?>
