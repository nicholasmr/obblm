<?php

/**************************
 * MySQL database settings 
 **************************/

$db_name   = 'obblmdb';
$db_user   = 'root';
$db_passwd = '';
$db_host   = 'localhost';

/*************************
 * OBBLM display settings 
 *************************/

$settings['forum_url'] = 'http://localhost';// URL of league forum, if you have such. If not then leave this empty, that is = '' (two quotes only).
$settings['lang'] = 'en-GB';                // Language. Existing: en-GB.
$settings['login_list'] = true;             // Show a list of available coaches on logins. If false coaches must type in their username on logins.
$settings['site_name'] = 'UNNAMED BBL';     // Name of the site or the league name if only one league is being managed.
$settings['stylesheet'] = 1;                // Default is 1. OBBLM CSS stylesheet for non-logged in guests. Currently stylesheet 1 and 2 are the only existing stylesheets.
$settings['show_sort_rule'] = true;         // Default is true. Print in table footers what tables are sorted against?

$settings['entries_messageboard']   = 5;  // Number of entries on the main page messageboard in normal view mode. Note: A value of 0 shows all messages.
$settings['entries_standings']      = 5;  // Number of entries in the main page table(s) "standings".
$settings['entries_latest']         = 5;  // Number of entries in the main page table "latest games".
$settings['entries_casualties']     = 5;  // Number of entries in the main page table "casualties".
$settings['entries_touchdown']      = 5;  // Number of entries in the main page table "touchdowns".
$settings['entries_completions']    = 5;  // Number of entries in the main page table "completions".
$settings['entries_players']        = 20; // Number of entries in the players standings table. Note: A value of 0 shows all players.

$settings['show_sold_journeymen']  = true;  // Default is true. Show sold journeymen on rosters in detailed view mode.
$settings['show_stars_mercs']      = true;  // Default is true. Show summed up stats for earlier hired star players and mercenaries on rosters in detailed view mode.
$settings['enable_guest_book']     = false; // Default is false. Enable the guest book?
$settings['show_active_tours']     = true;  // Default is true. Show not only the overall team standings table on the front page, but show also standings tables for active tournaments?
$settings['fp_team_news']          = true;  // Default is true. Show team news on front page.
$settings['fp_links']              = false; // Default is false. Generate coach, team and player links on the front page?
$settings['force_tour_foldout']    = false; // Default is false. Force each tournament block in tournaments section to be displayed as folded out, and not folded up.
$settings['hide_retired']		   = false; // Defailt is false. Hides retired coaches and teams from standings tables.
$settings['relate_team_to_league'] = false; // Default is false. Associate teams with leagues. Teams from different leagues can not schedule matches against each other. 

/*****************
 * OBBLM rule set
 *****************/

/*
    The default OBBLM rule set is the rule set provided by the LRB 5.
    Please use the boolean values "true" and "false" wherever default values are boolean.
*/

$rules['initial_treasury']      = 1000000;  // Default is 1000000.
$rules['max_team_players']      = 16;       // Default is 16.
$rules['static_rerolls_prices'] = false;    // Default is "false". "true" forces re-roll prices to their un-doubled values.
$rules['player_refund']         = 0;        // Player sell value percentage. Default is 0 = 0%, 0.5 = 50%, and so on.
$rules['journeymen_limit']      = 11;       // Until a team can field this number of players, it may fill team positions with journeymen.
$rules['post_game_ff']          = false;    // Default is false. Allows teams to buy and drop fan factor even though their first game has been played.

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

// Player values are decreased by the below multipliers for each type of injury sustained.
// Example: If you wish player values to go down by 15.000 for each MA, you would set the "val_reduc_ma" variable equal to 15000.
$rules['val_reduc_ma'] = 0; // Default is 0 (disabled).
$rules['val_reduc_st'] = 0; // Default is 0 (disabled).
$rules['val_reduc_av'] = 0; // Default is 0 (disabled).
$rules['val_reduc_ag'] = 0; // Default is 0 (disabled).

$rules['enable_stars_mercs'] = true; // Default is true. Enable star players and mercenaries.

/*
    Enable the partial implementation of the LRB6 experimental rule set.
    This implies acknowledging the differences between the LRB5 and 6 with respect to the initial properties of teams, players and their skills.
    
    IMPORTANT: This setting must not be changed while using OBBLM. Please enable/disable it and leave it that way throughout the use of this software.
 */

$rules['enable_lrb6x'] = true; // Default is false.

/*****************
 * House ranking systems
 *****************/

/*
    In the case of the already implemented ranking systems not fitting the needs of your league, you may define house ranking systems.
    The fields/properties which you may sort teams against are:
    
        name, mvp, cp, td, intcpt, bh, si, ki, cas (sum of player cas), tcas (total team cas), tdcas (td + player cas),
        played, won, lost, draw, win_percentage, score_team (total score made by this team), score_against (total score made against this team),
        score_diff (equals to the arithmetic value of score_team - score_against), fan_factor, smp (sportsmanship points), points
        
    The last field, points, is a special field which is defined to be the value of some arithmetical combination of other fields.
    For example, a typical points field could be constructed as so: points = '3*[won] + 2*[draw] + 1*[lost]'
    But, you may of course use any of the above fields. 

    The fields you will be defining, in order to make a working ranking system, are:
    
        rule:
            This field must take the form of: 
                array('+field1', '-field2', '+field3')
            This should be interpreted as:
                Sort first    by least of field1
                Sort secondly by most  of field2
                Sort at last  by least of field3
            Note: "+" prefix indicates least of and "-" most of. You may NOT omit any prefixes. They are required for every field!
            Note: You may define as many entries in the rule you want. It's not limited to = 3, like in this example. 
            
        points:
            This field must take the form of:
                'X*[field1] + Y*[field2] + [field3]'
            Where X and Y may by either integers, floating point numbers or another fields. 
            A points definition does not have to be a linear combination of fields, points = '[field1]/([field2]*[field3])' is 100% valid.
            Note: non-numeric fields may of course not be used in the points definition.
            
        points_desc:
            This is the text string shown in OBBLM when describing the points definition. 
            Usually the points definition is best described by setting this field to the same value as the points definition, that is:
                points_desc = points
            But, if you, for example, find it more describing writing:
                points_desc = 'Win = 3 points, draw = 2 points and losing = 1 point'
            then this too is 100% valid.
        
    PLEASE NOTE: If you do not need the points field, because it is not included in the rule field of your ranking system, 
    then simply leave both the "points" and "points_desc" definitions be equal to '' (that's two single quotes only).
    
*/

// Example 1
$hrs[1]['rule']        = array('-points', '-td', '+smp'); // Sort teams against: most points, then most TDs and then least sportsmanship points.
$hrs[1]['points']      = '3*[won] + 2*[draw] + 1*[lost]'; // The definition of points.
$hrs[1]['points_desc'] = $hrs[1]['points'];               // Set the description of the points to be just the same as the actual definition.

// Example 2
$hrs[2]['rule']        = array('-points', '-ki', '-mvp');                   // Sort teams against: most points, then most killed and then by most MVPs.
$hrs[2]['points']      = '2*[score_team] - 1*[score_opponent]';             // The definition of points.
$hrs[2]['points_desc'] = '2 pts for team score, -1 pts for opponent score'; // Set the description of the points to be this string.

// Example 3
$hrs[3]['rule']        = array('-score_diff', '-smp');  // Sort teams against: larget score difference, then most sportsmanship points.
$hrs[3]['points']      = '';                            // Points not used.
$hrs[3]['points_desc'] = '';                            // Points not used.

?>
