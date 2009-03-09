<?php

/**************************
 * MySQL database settings 
 **************************/

$db_name   = 'test';
$db_user   = 'root';
$db_passwd = '';
$db_host   = 'localhost';

/*************************
 * OBBLM display settings 
 *************************/

$settings['site_url'] = 'http://localhost/~nicholas/obblm2'; // URL to where OBBLM can be accessed.
$settings['lang'] = 'en-GB';          // Language. Existing: en-GB.
$settings['league_name'] = 'UNNAMED BBL'; // League name.
$settings['stylesheet'] = 2;          // Default is 1. OBBLM CSS stylesheet for non-logged in guests. Currently stylesheet 1 and 2 are the only existing stylesheets.
$settings['show_sort_rule'] = true;   // Default is true. Print in table footers what tables are sorted against?

$settings['entries_messageboard']   = 5;  // Number of entries on the main page messageboard in normal view mode. Note: A value of 0 shows all messages.
$settings['entries_standings']      = 5;  // Number of entries in the main page table(s) "standings".
$settings['entries_latest']         = 5;  // Number of entries in the main page table "latest games".
$settings['entries_casualties']     = 5;  // Number of entries in the main page table "casualties".
$settings['entries_touchdown']      = 5;  // Number of entries in the main page table "touchdowns".
$settings['entries_players']        = 20; // Number of entries in the players standings table. Note: A value of 0 shows all players.

$settings['show_sold_journeymen'] = true;  // Default is true. Show sold journeymen on rosters in detailed view mode.
$settings['show_stars_mercs']     = true;  // Default is true. Show summed up stats for earlier hired star players and mercenaries on rosters in detailed view mode.
$settings['enable_guest_book']    = false; // Default is false. Enable the guest book?
$settings['show_active_tours']    = true;  // Default is true. Show not only the overall team standings table on the front page, but show also standings tables for active tournaments?

/*****************
 * OBBLM rule set
 *****************/

/*
    The default OBBLM rule set is the rule set provided by the LRB 5.
    Please use the boolean values "true" and "false" wherever default values are boolean.
*/

$rules['initial_treasury']      = 1000000;  // Default is 1000000.
$rules['max_team_players']      = 16;       // Default is 16.
$rules['static_rerolls_prices'] = false;    // Default is "false". "true" = Re-roll prices remain the same throughout the whole tournament.
$rules['player_refund']         = 0;        // Player sell value percentage. Default is 0 = 0%, 0.5 = 50%, and so on.
$rules['journeymen_limit']      = 11;       // Until a team can field this number of players, it may fill team positions with journeymen.

$rules['initial_rerolls']       = 0;        // Default is 0.
$rules['initial_fan_factor']    = 0;        // Default is 0.
$rules['initial_ass_coaches']   = 0;        // Default is 0.
$rules['initial_cheerleaders']  = 0;        // Default is 0.

// For the below limits, the following applies: -1 = unlimited. 0 = disabled.
$rules['max_rerolls']           = -1;       // Default is -1.
$rules['max_fan_factor']        = 9;        // Default is 9.
$rules['max_ass_coaches']       = -1;       // Default is -1.
$rules['max_cheerleaders']      = -1;       // Default is -1.

$rules['cost_apothecary']       = 50000;    // Default is 50000.
$rules['cost_fan_factor']       = 10000;    // Default is 10000.
$rules['cost_ass_coaches']      = 10000;    // Default is 10000.
$rules['cost_cheerleaders']     = 10000;    // Default is 10000.

$rules['enable_stars_mercs'] = true; // Default is true. Enable star players and mercenaries.

/*
    Enable the partial implementation of the LRB6 experimental rule set.
    This implies acknowledging the differences between the LRB5 and 6 with respect to the initial properties of teams, players and their skills.
    
    IMPORTANT: This setting must not be changed while using OBBLM. Please enable/disable it and leave it that way throughout the use of this software.
 */

$rules['enable_lrb6x'] = false; // Default is false.

?>
