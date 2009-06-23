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
 
error_reporting(E_ALL);
 
/********************* 
 *   General
 *********************/

define('OBBLM_VERSION', '0.75d');
$credits = array('Pierluigi Masia', 'Mag Merli', 'Lars Scharrenberg', 'Tim Haini', 'Daniel Straalman', 'Juergen Unfried', 'Sune Radich Christensen', 'Michael Bielec');
define('MAX_RECENT_GAMES', 15); // This limits the number of rows shown in the "recent games" tables.
define('MAX_MEM_MATCHES', 3); // For each mem. match category: If the number of matches with equal records exceed this value, no matches are shown at all.
define('MAX_TNEWS', 3); // This number of entries are shown on the team news board.

/********************* 
 *   Stats types. Used by Stats class.
 *********************/

define('STATS_PLAYER', 1);
define('STATS_TEAM',   2);
define('STATS_COACH',  3);
define('STATS_RACE',   4);
# Match groupings (nodes):
define('STATS_TOUR',     1);
define('STATS_DIVISION', 2);
define('STATS_LEAGUE',   3);

/********************* 
 *   Prize types. Used by Prize class.
 *********************/

define('PRIZE_1ST',     1);
define('PRIZE_2ND',     2);
define('PRIZE_3RD',     3);
define('PRIZE_LETHAL',  4);
define('PRIZE_FAIR',    5);

/********************* 
 *   Images
 *********************/

define('IMG', 'images');

define('RACE_ICONS',    IMG.'/race_icons');
define('PLAYER_ICONS',  IMG.'/player_icons');

define('NO_PIC', IMG.'/nopic.jpg');
define('UPLOAD_DIR', IMG);

define('IMG_PLAYERS',   UPLOAD_DIR.'/players');
define('IMG_TEAMS',     UPLOAD_DIR.'/teams'); // team togo
define('IMG_STADIUMS',  UPLOAD_DIR.'/stadiums');
define('IMG_COACHES',   UPLOAD_DIR.'/coaches');
define('IMG_MATCHES',   UPLOAD_DIR.'/matches');
define('IMG_PRIZES',    UPLOAD_DIR.'/prizes');

/********************* 
 *   Roster colors
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


define('COLOR_ROSTER_NORMAL',   COLOR_HTML_NORMAL);
define('COLOR_ROSTER_READY',    '');
define('COLOR_ROSTER_MNG',      COLOR_HTML_MNG);
define('COLOR_ROSTER_DEAD',     '');
define('COLOR_ROSTER_SOLD',     '');
define('COLOR_ROSTER_STARMERC', '');
define('COLOR_ROSTER_JOURNEY',  COLOR_HTML_JOURNEY);
define('COLOR_ROSTER_NEWSKILL', COLOR_HTML_NEWSKILL);
//-----
define('COLOR_ROSTER_CHR_EQP1', COLOR_HTML_CHR_EQP1); // Characteristic equal plus one.
define('COLOR_ROSTER_CHR_GTP1', COLOR_HTML_CHR_GTP1); // Characteristic greater than plus one.
define('COLOR_ROSTER_CHR_EQM1', COLOR_HTML_CHR_EQM1); // Characteristic equal minus one.
define('COLOR_ROSTER_CHR_LTM1', COLOR_HTML_CHR_LTM1); // Characteristic less than minus one.

/********************* 
 *   For texts (table)
 *********************/

// Table "text" type definitions.
define('T_TEXT_MSG',    1);
define('T_TEXT_COACH',  2);
define('T_TEXT_TEAM',   3);
define('T_TEXT_PLAYER', 4);
define('T_TEXT_HOF',    5); // Hall of fame.
define('T_TEXT_WANTED', 6);
define('T_TEXT_MSMR',   7); // Match summary.
define('T_TEXT_TOUR',   8);
define('T_TEXT_GUEST',  9);
define('T_TEXT_LOG',    10);
define('T_TEXT_MSMRC',  11); // Match summary comments.
define('T_TEXT_TNEWS',  12); // Team news board messages.

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

// Maximum and minimum allowed rounds in a Round-Robin tournament.
define('MIN_ALLOWED_ROUNDS', 1);
define('MAX_ALLOWED_ROUNDS', 10);

// Minimum required teams to create tournament.
define('MIN_TOUR_TEAMS', 3); # DO NOT change this value to less than 3!

// Tournament Types for MySQL tournament "type" column:
define('TT_NOFINAL',    1); # Round-Robin WITHOUT final
define('TT_FINAL',      2); # Round-Robin w. final
define('TT_SEMI',       3); # Round-Robin w. final + semi-final
define('TT_KNOCKOUT',   4); # Knock-out
define('TT_SINGLE',     5); # Umbrella for grouping free for all (FFA) matches.

// Max and min type values:
define('TT_MIN', TT_NOFINAL);
define('TT_MAX', TT_SINGLE);

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

// Round types
define('RT_FINAL', 255);
define('RT_3RD_PLAYOFF', 254); # 3rd place playoff: The two knock-out matches between the final four teams with the winners progressing to the grand final. The losers are knocked-out, though take part in a third place play-off.
define('RT_SEMI', 253); # Semi-finals.
define('RT_QUARTER', 252); # Quarter-finals.
define('RT_ROUND16', 251); # Round of 16.

define('MAX_ROUNDNR', RT_ROUND16); # This should have the value of the smallest reserved round number.

// NON-reserved round numbers, used only for knock-out tournaments:
define('RT_PLAYIN', 0); # Play-in round.
define('RT_FIRST', 1); # First round.

// Reserved (non-real) matches:
define('MATCH_ID_IMPORT', -1);

/******************** 
 *   For matches
 ********************/
 
// Privilege rings (ie. coach access level)
define('RING_SYS',   0); // Admins
define('RING_COM',   1); // Commissioners.
define('RING_COACH', 2); // Coach/ordinary user

/******************** 
 *   For graphical statistics
 ********************/

// SG stands for Stats Graphs.

// Types
define('SG_T_PLAYER', 1); 
define('SG_T_TEAM',   2); 
define('SG_T_COACH',  3); 
define('SG_T_LEAGUE', 4); 
 
// Module setup
define('SG_MULTIBAR_HIST_LENGTH', 6); // Number of months to show history from.
define('SG_CNT_HORIZ', 3); // Number of graphs to place hirozontally next to each other.
# Graph dimensions
define('SG_DIM_X', 600);
define('SG_DIM_Y', 400);

/******************** 
 *   RSS related.
 ********************/

define('RSS_SIZE', 20); // Number of entries in feed.
define('RSS_FEEDS', implode(',', array(T_TEXT_MSG, T_TEXT_HOF, T_TEXT_WANTED, T_TEXT_MSMR, T_TEXT_TNEWS))); // Create feeds from the text types.

/********************
 *  Dependencies
 ********************/

// General OBBLM routines and data structures.
require_once('settings.php');
require_once('lib/game_data.php'); // LRB5
if ($rules['enable_lrb6x']) { 
    require_once('lib/game_data_lrb6x.php');
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
require_once('lib/class_prize.php');
//require_once('lib/class_statsgraph.php'); // Should not be included here due to unnecessary load. Is included in handler.php.
require_once('lib/class_rrobin.php');
require_once('lib/class_knockout.php');

// External libraries.
require_once('lib/class_arraytojs.php');
require_once('lib/class_elo.php');    // by Daniel S.
require_once('pdf/bb_pdf_class.php'); // Roster by Daniel S.
require_once('pdf/pdf_roster.php');   // Roster by Daniel S.
require_once('lang/class_translations.php'); // Juergen Unfried
require_once('lib/class_rss.php'); // Juergen Unfried

// HTML interface routines.
require_once('lib/class_htmlout.php');
require_once('sections.php'); // Main file. Some of the subroutines in this file are quite large and are therefore split into the files below.
require_once('matches.php');
require_once('teams.php');
require_once('records.php');
require_once('admin.php');

?>
