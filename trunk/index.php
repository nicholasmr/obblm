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
            <?php make_menu(); // See lib/misc_functions.php ?>
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
