<?php

/**************************
 * MySQL database settings 
 **************************/

$db_name   = 'fbbl';
$db_user   = 'root';
$db_passwd = '';
$db_host   = 'localhost';

// For local settings, ie. per league settings, edit the localsettings/settings_<LEAGUE ID>.php files.

/*************************
 * Global settings 
 *************************/

$settings['site_name'] = 'BB portal';     // Site name.
$settings['default_visitor_league'] = 1;  // ID of default league to show on front page when not logged in/coach has not selected a home league.
$settings['default_leagues'] = array(1);  // When creating a coach the coach will automatically become a regular coach in leauges with these IDs.
$settings['hide_ES_extensions'] = false;  // Default is false. Hides ES (Extra Stats) tables and ES references.

/*****************
 * Global rules
 *****************/

/*
    The default OBBLM rule set is the rule set provided by the LRB6.
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

/* 
    Whenever the below cost values are changed you must run the 
    "Re-install DB back-end procedures and functions" under "DB maintenance" from the "Admin -> Core panel".
*/

$rules['cost_apothecary']       = 50000;    // Default is 50000.
$rules['cost_fan_factor']       = 10000;    // Default is 10000.
$rules['cost_ass_coaches']      = 10000;    // Default is 10000.
$rules['cost_cheerleaders']     = 10000;    // Default is 10000.

/*
    Whenever a player sustains a stat decrease the players value will be reduced by these amounts.
*/

$rules['value_reduction_ma'] = 0; // Default is 0.
$rules['value_reduction_av'] = 0; // Default is 0.
$rules['value_reduction_ag'] = 0; // Default is 0.
$rules['value_reduction_st'] = 0; // Default is 0.


/*****************
 * House ranking systems
 *****************/

/*
    In the case of the already implemented ranking systems not fitting the needs of your league, you may define house ranking systems.
    The fields/properties which you may sort teams against are:
    
        mvp, cp, td, intcpt, bh, si, ki, cas (sum of PLAYER cas), tdcas ("td" + "cas"), 
        tcasf (total TEAM cas by this team), tcasa (total TEAM cas against this team), tcdiff (equals to the arithmetic value of "tcasf" - "tcasa"), 
        gf (total score made by this team), ga (total score made against this team), sdiff (equals to the arithmetic value of "gf" - "ga")
        played, won, lost, draw, win_pct, elo, smp (sportsmanship points), pts (points)
        
    The last field, points, is a special field displayed in tournament standings which is defined to be the value of some arithmetical combination of other fields.
    For example, a typical points field could be constructed as so: points = '3*[won] + 2*[draw] + 1*[lost]'
    But, you may of course use any of the above listed fields. 

    The fields you will be defining, in order to make a working ranking system, are:
    
        the rule:
        ---------
            This field must take the form of: 
                array('+field1', '-field2', '+field3')
            This should be interpreted as:
                Sort first    by least of field1
                Sort secondly by most  of field2
                Sort at last  by least of field3
            Note: "+" prefix indicates least of (ascending) and "-" most of (descending). You may NOT omit the +/- prefixes. They are required for every field!
            Note: You may define as many entries in the rule you want. It's not limited to 3, like in this example. 
            
        points:
        -------
            This field must take the form of:
                'X*[field1] + Y*[field2] + [field3]'
            Where X and Y may by either integers, floating point numbers or another field itself. 
            A points definition does not have to be a linear combination of fields, points = '[field1]/([field2]*[field3])' is 100% valid.
        
            PLEASE NOTE: If you do not need the points field, because it is not included in the rule field of your ranking system, 
            then simply leave the "points" definition be equal to '' (that's two single quotes only).
    
    -------------------------------------------------------------------------------------------------
    
    IMPORTANT!!!
    
    Once you have changed the below ranking systems you must notify OBBLM. 
    This is done via the admin menu: Admin -> OBBLM core panel. 
    Here you must: 
        - ALWAYS select the "Re-install DB back-end procedures and functions" under "DB maintenance".
        - IF changes have been made to a points definition which is used in a tournament, you must also run "syncAll()" under "DB synchronisation procedures".
    ALSO:
        - IF changing/deleting rule numbers you must always make sure tournaments are up-to-date with the correct ranking system. This may be done via. the admin menu "Admin -> Management: Tournaments".
*/

// Rule #1
$hrs[1]['rule']   = array('-pts', '-td', '+smp');    // Sort teams against: most points, then most TDs and then least sportsmanship points.
$hrs[1]['points'] = '3*[won] + 2*[draw] + 1*[lost]'; // The definition of points.

// Rule #2
$hrs[2]['rule']   = array('-pts', '-ki', '-mvp'); // Sort teams against: most points, then most killed and then by most MVPs.
$hrs[2]['points'] = '2*[gf] - 1*[ga]';            // The definition of points.

// Rule #3
$hrs[3]['rule']   = array('-sdiff', '-smp'); // Sort teams against: largest score difference, then most sportsmanship points.
$hrs[3]['points'] = '';                      // Points not used.

// Add you own rules here...

/*****************
 * Enable/disable modules
 *****************/
// Change value from true to false if you wish to disable a module.

$settings['modules_enabled'] = array(
    'IndcPage'           => true, # Inducements try-out
    'PDFroster'          => true, # Team PDF roster
    'RSSfeed'            => true, # Site RSS feed
    'SGraph'             => true, # Graphical statistics
    'Memmatches'         => true, # Memorable matches viewer
    'Wanted'             => true, # Wanted list
    'HOF'                => true, # Hall of fame
    'Prize'              => true, # Tournament prizes list
    'UPLOAD_BOTOCS'      => true, # Allow upload of a BOTOCS match
    'XML_BOTOCS'         => true, # BOTOCS XML export of team
    'Registration'       => true, # Allows users to register on the site.
    'Search'             => true, # Search for coaches and teams.
    'UserScheduledGames' => true, # Allows users to schedule games
);

?>
