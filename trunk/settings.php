<?php

/**************************
 * MySQL database settings 
 **************************/

$db_name   = 'obblmdb';
$db_user   = 'root';
$db_passwd = '';
$db_host   = 'localhost';

/*************************
 * OBBLM settings 
 *************************/

$settings['site_name'] = 'UNNAMED BBL';     // Name of the site or the league name if only one league is being managed.
$settings['forum_url'] = 'http://localhost';// URL of league forum, if you have such. If not then leave this empty, that is = '' (two quotes only).
$settings['stylesheet'] = 1;                // Default is 1. OBBLM CSS stylesheet for non-logged in guests. Currently stylesheet 1 and 2 are the only existing stylesheets.
$settings['lang'] = 'en-GB';                // Deafult language. Existing: en-GB.

$settings['entries'] = array(
    'messageboard'      => 5,   // Number of entries on the main page messageboard.
    'latestgames'       => 5,   // Number of entries in the main page table "latest games".
    'standings_players' => 30,  // Number of entries on the general players stadings table.
    'standings_teams'   => 30,  // Number of entries on the general teams   stadings table.
    'standings_coaches' => 30,  // Number of entries on the general coaches stadings table.
);

$settings['fp_standings'] = array(
    # This would display a standings box of the top 6 teams in tournament with ID=1.
    1 => array(
        'length' => 6,
        'fields' => array(
            'Name' => 'name', 'PTS' => 'mv_pts', 'CAS' => 'mv_cas', 
            'W' => 'mv_won', 'L' => 'mv_lost', 'D' => 'mv_draw', 'GF' => 'mv_gf', 'GA' => 'mv_ga'
        ),
    ),
);

$settings['fp_leaders'] = array(
    'mv_cas' => array('title' => 'Most casualties',    'length' => 5),
#    'mv_td'  => array('title' => 'Most touchdowns',    'length' => 5),
#    'mv_cp'  => array('title' => 'Most completions',   'length' => 5),
#    'mv_ki'  => array('title' => 'Most killed',        'length' => 5),
);


$settings['default_leagues'] = array(1,3); // When creating a coach the coach will automatically become a regular coach in leauges with these IDs.
$settings['show_sort_rule']        = true;  // Default is true. Print in table footers what tables are sorted against?
$settings['show_sold_journeymen']  = true;  // Default is true. Show sold journeymen on rosters in detailed view mode.
$settings['show_stars_mercs']      = true;  // Default is true. Show summed up stats for earlier hired star players and mercenaries on rosters in detailed view mode.
$settings['fp_team_news']          = true;  // Default is true. Show team news on front page.
$settings['fp_links']              = true;  // Default is true. Generate coach, team and player links on the front page?
$settings['hide_retired']		   = false; // Defailt is false. Hides retired coaches and teams from standings tables.

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
    
        mvp, cp, td, intcpt, bh, si, ki, cas (sum of player cas), tcas (total team cas), tdcas (td + player cas),
        played, won, lost, draw, win_pct, gf (total score made by this team), ga (total score made against this team),
        sdiff (equals to the arithmetic value of gf - ga), smp (sportsmanship points), pts (points)
        
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
        
    PLEASE NOTE: If you do not need the points field, because it is not included in the rule field of your ranking system, 
    then simply leave the "points" definition be equal to '' (that's two single quotes only).
    
*/

// Rule 1
$hrs[1]['rule']   = array('-pts', '-td', '+smp');    // Sort teams against: most points, then most TDs and then least sportsmanship points.
$hrs[1]['points'] = '3*[won] + 2*[draw] + 1*[lost]'; // The definition of points.

// Rule 2
$hrs[2]['rule']   = array('-pts', '-ki', '-mvp'); // Sort teams against: most points, then most killed and then by most MVPs.
$hrs[2]['points'] = '2*[gf] - 1*[ga]';            // The definition of points.

// Rule 3
$hrs[3]['rule']   = array('-sdiff', '-smp'); // Sort teams against: largest score difference, then most sportsmanship points.
$hrs[3]['points'] = '';                      // Points not used.

// Add you own rules here...

/*****************
 * Enable/disable modules
 *****************/
// Change value from true to false if you wish to disable a module.

$settings['modules_enabled'] = array(
    'IndcPage'      => true, # Inducements try-out
    'PDFroster'     => true, # Team PDF roster
    'RSSfeed'       => true, # Site RSS feed
    'SGraph'        => true, # Graphical statistics
    'Memmatches'    => true, # Memorable matches viewer
    'Wanted'        => true, # Wanted list
    'HOF'           => true, # Hall of fame
    'Prize'         => true, # Tournament prizes list
    'UPLOAD_BOTOCS' => true, # Allow upload of a BOTOCS match
    'XML_BOTOCS'    => true, # BOTOCS XML export of team
    'Registration'  => true, # Allows users to register on the site.
);

?>
