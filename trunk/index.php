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

/* 
    Includes, constants, error_reporting() level, session_start(), OBBLM run requirements, MySQL connection, language load.
*/
require('header.php');

/********************
 *   Main routine
 ********************/

// Make 'main' the default section if no GET section request was sent.
if (!isset($_GET['section'])) {
    $_GET['section'] = 'main';
}

// Login?
Coach::cookieLogin(); # If not already logged in then check for login-cookie and try to log in using the stored credentials.
if (isset($_POST['login'])) {
    if (get_magic_quotes_gpc()) {
        $_POST['coach'] = stripslashes($_POST['coach']);
        $_POST['passwd'] = stripslashes($_POST['passwd']);
    }
    if (!Coach::login($_POST['coach'], $_POST['passwd'], isset($_POST['remember']))) {
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
    case 'standings':    sec_standings();       break; // All-time team standings
    case 'teams':        sec_teams();           break; // Teams list.
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
    case 'comparison':   sec_comparison();      break;
    default:             sec_main();
}

HTMLOUT::frame_end(); // Spit out all the end-tags.
mysql_close($conn);

?>
