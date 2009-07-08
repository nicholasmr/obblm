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

#if (file_exists('install.php'))
#    die('Please remove <i>install.php</i> before using obblm.');
    
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

$conn = mysql_up(true); # MySQL connect.
$lng = new Translations($settings['lang']); # Load language.

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
    if (!Coach::login($_POST['coach'], $_POST['passwd'], true)) {
        $_GET['section'] = 'login';
    }
}

// Logout?
if (isset($_GET['logout'])) {
    $_GET['section'] = 'main'; # Redirect logged out users to the main page.
    Coach::logout();
}

$coach = (isset($_SESSION['logged_in'])) ? new Coach($_SESSION['coach_id']) : null; # Create global coach object.
HTMLOUT::frame_begin(isset($_SESSION['logged_in']) ? $coach->settings['theme'] : $settings['stylesheet']); # Make page frame, banner and menu.

// Check if a menu-link was picked, and execute section code from sections.php accordingly.
switch ($_GET['section']) 
{
    case 'login':        sec_login();           break;
    case 'admin':        sec_admin();           break;
    case 'coachcorner':  sec_coachcorner();     break;
    case 'fixturelist':  sec_fixturelist();     break; // Tournaments
    case 'standings':    sec_standings();       break;
    case 'teams':        sec_teams();           break;
    case 'players':      sec_players();         break;
    case 'coaches':      sec_coaches();         break;
    case 'races':        sec_races();           break;
    case 'stars':        sec_stars();           break;
    case 'records':      sec_records();         break;
    case 'rules':        sec_rules();           break;
    case 'gallery':      sec_gallery();         break;
    case 'about':        sec_about();           break;
    case 'guest':        if($settings['enable_guest_book']){sec_guest(); break;} 
    case 'recent':       sec_recentmatches();   break;
    case 'upcomming':    sec_upcommingmatches();break;
    case 'comparence':   sec_comparence();      break;
    default:             sec_main();
}

HTMLOUT::frame_end(); // Spit out all the end-tags.
mysql_close($conn);

?>
