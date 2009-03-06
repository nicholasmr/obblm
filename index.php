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

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
    <title><?php echo $settings['league_name']; ?> Blood Bowl League</title>
    <link type="text/css" href="css/stylesheet<?php echo $settings['stylesheet']; ?>.css" rel="stylesheet">
    <link rel="alternate" type="application/rss+xml" title="RSS Feed"href="rss.xml" />
    <script type="text/javascript" src="lib/misc_functions.js"></script>
    
</head>
<body>
    <div class="everything">
        <table class="menu"> <!-- Banner and menu -->
        <tr>
        <td class="menu">
            <?php 
            $coach = null;
            if (isset($_SESSION['logged_in'])) { ?><a class='menuSpecial' href="index.php?logout=1">       <b><?php echo $lng->getTrn('global/secLinks/logout');?></b></a>&nbsp;<?php }
            else                               { ?><a class='menuSpecial' href="index.php?section=login">  <b><?php echo $lng->getTrn('global/secLinks/login');?></b></a>&nbsp;<?php }
            ?>
            <?php
            if (isset($_SESSION['logged_in'])) {
                if (is_object($coach = new Coach($_SESSION['coach_id'])) && $coach->ring <= RING_COM) {
                    echo "<a class='menuSpecial' href='index.php?section=admin'><b>".$lng->getTrn('global/secLinks/admin')."</b></a> &nbsp;\n";
                }
                echo "<a class='menu' href='index.php?section=coachcorner'><b>".$lng->getTrn('global/secLinks/cc')."</b></a> &nbsp;\n";
            }
            ?>
            <font class="white"><b>|</b></font>&nbsp;
            <a class="menu" href="index.php?section=main">         <b><?php echo $lng->getTrn('global/secLinks/home');?></b></a>         &nbsp;
            <a class="menu" href="index.php?section=fixturelist">  <b><?php echo $lng->getTrn('global/secLinks/fixtures');?></b></a>     &nbsp;
            <a class="menu" href="index.php?section=standings">    <b><?php echo $lng->getTrn('global/secLinks/standings');?></b></a>    &nbsp;
            <a class="menu" href="index.php?section=teams">        <b><?php echo $lng->getTrn('global/secLinks/teams');?></b></a>        &nbsp;
            <a class="menu" href="index.php?section=players">      <b><?php echo $lng->getTrn('global/secLinks/players');?></b></a>      &nbsp;
            <a class="menu" href="index.php?section=coaches">      <b><?php echo $lng->getTrn('global/secLinks/coaches');?></b></a>      &nbsp;                
            <a class="menu" href="index.php?section=races">        <b><?php echo $lng->getTrn('global/secLinks/races');?></b></a>        &nbsp;
            <?php
            if ($rules['enable_stars_mercs']) {
                ?><a class="menu" href="index.php?section=stars">  <b><?php echo $lng->getTrn('global/secLinks/stars');?></b></a>        &nbsp;<?php
            }
            ?>
            <a class="menu" href="index.php?section=records">      <b><?php echo $lng->getTrn('global/secLinks/records');?></b></a>      &nbsp;
            <a class="menu" href="index.php?section=rules">        <b><?php echo $lng->getTrn('global/secLinks/rules');?></b></a>        &nbsp;
            <a class="menu" href="index.php?section=about">        <b>OBBLM</b></a> &nbsp;
            <?php 
            if ($settings['enable_guest_book']) {
                ?><a class="menu" href="index.php?section=guest"><b><?php echo $lng->getTrn('global/secLinks/gb');?></b></a> &nbsp;<?php
            }
            ?>
        </td>
        </tr>
        </table>
        <div class="section"> <!-- This container holds the section specific content -->
        <?php
        // Check if a menu-link was picked, and execute section code from sections.php accordingly.
        switch ($_GET['section']) 
        {
            case 'login':        sec_login();        break;
            case 'admin':        sec_admin();        break;
            case 'coachcorner':  sec_coachcorner();  break;
            case 'fixturelist':  sec_fixturelist();  break;
            case 'standings':    sec_standings();    break;
            case 'teams':        sec_teams();        break;
            case 'players':      sec_players();      break;
            case 'coaches':      sec_coaches();      break;
            case 'races':        sec_races();        break;
            case 'stars':        sec_stars();        break;
            case 'records':      sec_records();      break;
            case 'rules':        sec_rules();        break;
            case 'about':        sec_about();        break;
            case 'guest':        if($settings['enable_guest_book']){sec_guest(); break;} 
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
