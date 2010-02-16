<?php

/*************************
 * Local settings for league with ID = 1
 *************************/

$settings['league_name'] = 'Placeholder title for league with ID = 1'; // Name of the site or the league name if only one league is being managed.
$settings['forum_url'] = 'http://localhost'; // URL of league forum, if you have such. If not then leave this empty, that is = '' (two quotes only).
$settings['stylesheet'] = 1;                 // Default is 1. OBBLM CSS stylesheet for non-logged in guests. Currently stylesheet 1 and 2 are the only existing stylesheets.
$settings['lang'] = 'en-GB';                 // Deafult language. Existing: en-GB, es.
$settings['welcome'] = 'Please replace this line in your local league settings file, <i>localsettings/settings_1.php</i>, with your own league greeting message for the league with ID = 1';
$settings['rules'] = 'Please replace this line in your local league settings file, <i>localsettings/settings_1.php</i>, with your own league rules description for the league with ID = 1';

$settings['entries'] = array(
    'messageboard'      => 5,   // Number of entries on the main page messageboard.
    'latestgames'       => 5,   // Number of entries in the main page table "latest games".
    'standings_players' => 30,  // Number of entries on the general players stadings table.
    'standings_teams'   => 30,  // Number of entries on the general teams   stadings table.
    'standings_coaches' => 30,  // Number of entries on the general coaches stadings table.
);

// Front Page (FP) tournament standings boxes
$settings['fp_standings'] = array(
    # This would display a standings box of the top 6 teams in tournament with ID = 1.
    1 => array(
        'length' => 6,
        'fields' => array(
            # Displayed table column name => OBBLM field name.
            'Name' => 'name', 'PTS' => 'mv_pts', 'CAS' => 'mv_cas', 
            'W' => 'mv_won', 'L' => 'mv_lost', 'D' => 'mv_draw', 'GF' => 'mv_gf', 'GA' => 'mv_ga'
        ),
    ),
);

// Front Page (FP) leaders boxes
$settings['fp_leaders'] = array(
    /* 
        Note, you can NOT make expressions out of leader fields e.g.:
            'mv_cas+mv_td' => array('title' => 'Most CAS+TD', 'length' => 5)
    */
    
    'mv_cas' => array('title' => 'Most casualties',  'length' => 5),
#    'mv_td'  => array('title' => 'Most touchdowns',  'length' => 5),
#    'mv_cp'  => array('title' => 'Most completions', 'length' => 5),
#    'mv_ki'  => array('title' => 'Most killed',      'length' => 5),
);

$settings['show_sold_journeymen']  = true;  // Default is true. Show sold journeymen on rosters in detailed view mode.
$settings['show_stars_mercs']      = true;  // Default is true. Show summed up stats for earlier hired star players and mercenaries on rosters in detailed view mode.
$settings['fp_team_news']          = true;  // Default is true. Show team news on front page.
$settings['fp_links']              = true;  // Default is true. Generate coach, team and player links on the front page?
$settings['hide_retired']		   = false; // Default is false. Hides retired coaches and teams from standings tables.

?>
