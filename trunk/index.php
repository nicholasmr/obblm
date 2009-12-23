<?php

/*
 *  Copyright (c) Niels Orsleff Justesen <njustesen@gmail.com> and Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2007-2010. All Rights Reserved.
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

$time_start = microtime(true); # Used by MTS().

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
    setupGlobalVars();
}

// Logout?
if (isset($_GET['logout'])) {
    $_GET['section'] = 'main'; # Redirect logged out users to the main page.
    Coach::logout();
    setupGlobalVars();
}

HTMLOUT::frame_begin(isset($_SESSION['logged_in']) ? $coach->settings['theme'] : $settings['stylesheet']); # Make page frame, banner and menu.
MTS('Header loaded, login auth, html frame generated');

// Check if a menu-link was picked, and execute section code from sections.php accordingly.
switch ($_GET['section']) 
{
    case 'login':        sec_login();           break;
    case 'admin':        sec_admin();           break;
    case 'teamlist':     sec_teamlist();        break;
    case 'coachlist':    sec_coachlist();       break;
    case 'rules':        sec_rules();           break;
    case 'about':        sec_about();           break;    
    case 'matches':      sec_matcheshandler();  break; // Tournaments, matches, match reports, recent matches, upcoming matches etc.
    case 'objhandler':   sec_objhandler();      break; // Object profiles, object standings.
    default:             sec_main();
}

HTMLOUT::frame_end(); // Spit out all the end-tags.
mysql_close($conn);
MTS('END OF SCRIPT');
?>
