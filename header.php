<?php

/*
 *  Copyright (c) Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2008-2009. All Rights Reserved.
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

if (version_compare(PHP_VERSION, '5.1.0') == -1)
    die('OBBLM requires PHP version 5.1.0, you are running version '.PHP_VERSION);

if (strtolower($iniRG = ini_get('register_globals')) == 'on' || $iniRG == 1)
    die('OBBLM requires the PHP configuration directive <i>register_globals</i> set <b>off</b> in the <i>php.ini</i> configuration file. Please contact your web host.');

if (!defined('NO_STARTUP') && file_exists('install.php'))
    die('Please remove <i>install.php</i> before using OBBLM.');

error_reporting(E_ALL);
session_start();

/*********************
 *   General
 *********************/

define('OBBLM_VERSION', '0.8 $Rev$');
$credits = array('Pierluigi Masia', 'Mag Merli', 'Lars Scharrenberg', 'Tim Haini', 'Daniel Straalman', 'Juergen Unfried', 'Sune Radich Christensen', 'Michael Bielec', 'William Leonard', 'Grégory Romé');
define('MAX_RECENT_GAMES', 15); // This limits the number of rows shown in the "recent/upcomming games" tables.
define('MAX_TNEWS', 3); // This number of entries are shown on the team news board.

/*********************
 *   Node and object types
 *********************/

define('T_OBJ_PLAYER',  1);
define('T_OBJ_TEAM',    2);
define('T_OBJ_COACH',   3);

define('T_OBJ_RACE',   4);
define('T_OBJ_STAR',   5);

define('T_NODE_MATCH',      11);
define('T_NODE_TOURNAMENT', 12);
define('T_NODE_DIVISION',   13);
define('T_NODE_LEAGUE',     14);

/*********************
 *   Stats types. Used by Stats class.
 *********************/
// Repalce these by the T_[OBJ|NODE]_* constants.
define('STATS_PLAYER', 1);
define('STATS_TEAM',   2);
define('STATS_COACH',  3);
define('STATS_RACE',   4);
define('STATS_STAR',   5);
# Match groupings (nodes):
define('STATS_MATCH',    11);
define('STATS_TOUR',     12);
define('STATS_DIVISION', 13);
define('STATS_LEAGUE',   14);

// Translation between MySQL column match data references in the match_data table to PHP STATS_* constants.
$STATS_TRANS = array(
    STATS_PLAYER    => 'match_data.f_player_id',
    STATS_TEAM      => 'match_data.f_team_id',
    STATS_COACH     => 'match_data.f_coach_id',
    STATS_RACE      => 'match_data.f_race_id',

    STATS_MATCH     => 'match_data.f_match_id',
    STATS_TOUR      => 'match_data.f_tour_id',
    STATS_DIVISION  => 'match_data.f_did',
    STATS_LEAGUE    => 'match_data.f_lid',
);

/*********************
 *   Images
 *********************/

define('IMG', 'images');
define('RACE_ICONS', IMG.'/race_icons');
define('PLAYER_ICONS', IMG.'/player_icons');

/*********************
 *   Roster/status colors
 *********************/

define('COLOR_HTML_NORMAL',   '#FFFFFF'); // Color used when not in detailed view mode.
define('COLOR_HTML_READY',    '#83b783');
define('COLOR_HTML_MNG',      '#6495ED');
define('COLOR_HTML_DEAD',     '#F78771');
define('COLOR_HTML_SOLD',     '#D2B477');
define('COLOR_HTML_STARMERC', '#bb99bb');
define('COLOR_HTML_JOURNEY',  '#99BBBB');
define('COLOR_HTML_NEWSKILL', '#BBBBBB');
//-----
define('COLOR_HTML_CHR_EQP1', '#90EE90'); // Characteristic equal plus one.
define('COLOR_HTML_CHR_GTP1', '#50FF50'); // Characteristic greater than plus one.
define('COLOR_HTML_CHR_EQM1', '#FF8888'); // Characteristic equal minus one.
define('COLOR_HTML_CHR_LTM1', '#FF4444'); // Characteristic less than minus one.

/*********************
 *   HTML BOX types
 *********************/

define('T_HTMLBOX_INFO',  1);
define('T_HTMLBOX_COACH', 2);
define('T_HTMLBOX_ADMIN', 3);
define('T_HTMLBOX_STATS', 4);
define('T_HTMLBOX_OTHER', 5);

/*********************
 *   For players
 *********************/

// Maximum player-number a player can be assigned.
define("MAX_PLAYER_NR", 100);

// Stars and mercenaries.
define('ID_MERCS',       -1); // Mercenaries player_id.
define('ID_STARS_BEGIN', -5); // First star's player_id, second id is one smaller and so on.

// Player types.
define('PLAYER_TYPE_NORMAL',  1);
define('PLAYER_TYPE_JOURNEY', 2);

/*********************
 *   For tournaments
 *********************/

// Tournament Types for MySQL tournament "type" column:
define('TT_FFA', 1);    # Free For All/manual tournament scheduling.
define('TT_RROBIN', 2); # Round-Robin

/********************
 *   For matches
 ********************/

// Injury/status constants:
define('NONE',  1);
define('MNG',   2);
define('NI',    3);
define('MA',    4);
define('AV',    5);
define('AG',    6);
define('ST',    7);
define('DEAD',  8);
define('SOLD',  9);

$STATUS_TRANS = array(
    NONE => 'NONE',
    MNG  => 'MNG',
    NI   => 'NI',
    MA   => 'MA',
    AV   => 'AV',
    AG   => 'AG',
    ST   => 'ST',
    DEAD => 'DEAD',
    SOLD => 'SOLD',
);

// Round types
define('RT_FINAL', 255);
define('RT_3RD_PLAYOFF', 254); # 3rd place playoff: The two knock-out matches between the final four teams with the winners progressing to the grand final. The losers are knocked-out, though take part in a third place play-off.
define('RT_SEMI', 253); # Semi-finals.
define('RT_QUARTER', 252); # Quarter-finals.
define('RT_ROUND16', 251); # Round of 16.

define('MAX_ROUNDNR', RT_ROUND16); # This should have the value of the smallest reserved round number.

// Reserved (non-real) matches:
define('MATCH_ID_IMPORT', -1);

/********************
 *   Security
 ********************/

// Privilege rings (ie. coach access level)
define('RING_SYS',   0); // Admins
define('RING_COM',   1); // Commissioners.
define('RING_COACH', 2); // Coach/ordinary user

define('RING_COM_NOLEAGUE', 0); // Value of commisioner's league ID reference if the commisioner is NOT assinged to any league.

/********************
 *  Dependencies
 ********************/

// General OBBLM routines and data structures.
require_once('settings.php');
require_once('lib/game_data.php'); # LRB5
if ($rules['enable_lrb6x']) {
	require_once('lib/game_data_lrb6x.php'); # LRB6
}
require_once('lib/mysql.php');
require_once('lib/misc_functions.php');

// OBBLM libraries.
require_once('lib/class_match.php');
require_once('lib/class_tournament.php');
require_once('lib/class_division.php');
require_once('lib/class_league.php');
require_once('lib/class_player.php');
require_once('lib/class_starmerc.php');
require_once('lib/class_team.php');
require_once('lib/class_coach.php');
require_once('lib/class_race.php');
require_once('lib/class_stats.php');
require_once('lib/class_text.php');
require_once('lib/class_rrobin.php');
require_once('lib/class_module.php');
require_once('lib/class_tablehandler.php');
require_once('lib/class_image.php');
require_once('lib/class_translations.php');

// External libraries.
require_once('lib/class_arraytojs.php');
require_once('lib/class_elo.php'); # Daniel S.

// HTML interface routines.
require_once('sections.php'); # Main file. Some of the subroutines in this file are quite large and are therefore split into the files below.
require_once('admin/admin.php');
require_once('lib/class_htmlout.php');
require_once('lib/class_coach_htmlout.php');
require_once('lib/class_team_htmlout.php');
require_once('lib/class_player_htmlout.php');
require_once('lib/class_starmerc_htmlout.php');
require_once('lib/class_race_htmlout.php');
require_once('lib/class_match_htmlout.php');

/********************
 *   Final setup
 ********************/

if (!is_writable(IMG))
    die('OBBLM needs to be able to write to the <i>images</i> directory in order to work probably. Please check the directory permissions.');

/********************
 *   Globals/Startup
 ********************/

if (!defined('NO_STARTUP')) {
    $conn = mysql_up(true); # MySQL connect. If constant is set before calling this header table checking will be ignored.
    $coach = (isset($_SESSION['logged_in'])) ? new Coach($_SESSION['coach_id']) : null; # Create global coach object.
}
$lng = new Translations($settings['lang']); # Load language.

// Modules.
require_once('modules/modsheader.php'); # Registration of modules.

/********************
 *   Post startup
 ********************/

// Ring access allowances.
$ring_sys_access = array('usr_man' => $lng->getTrn('menu/admin_menu/usr_man'), 'ld_man' => $lng->getTrn('menu/admin_menu/ld_man'), 'tour_man' => $lng->getTrn('menu/admin_menu/tour_man'), 'import' => $lng->getTrn('menu/admin_menu/import'), 'ct_man' => $lng->getTrn('menu/admin_menu/ct_man'));
$ring_com_access = array('schedule' => $lng->getTrn('menu/admin_menu/schedule'), 'log' => $lng->getTrn('name', 'LogSubSys'));

?>
