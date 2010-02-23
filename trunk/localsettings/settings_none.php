<?php

// DO NOT DELETE THIS FILE!!!
// Use this local settigns file for leagues which have no local settings file.

$settings['league_name'] = 'No settings file exists for the selected league';
$settings['forum_url'] = 'http://localhost';
$settings['stylesheet'] = 1;
$settings['lang'] = 'en-GB';
$settings['welcome'] = 'Could not find the local league settings file for the selected league at <i>localsettings/settings_&lt;LEAGUE ID&gt;.php</i>';
$settings['rules'] = 'No settings file exists for the selected league';

$settings['entries'] = array(
    'messageboard'      => 0,   // Number of entries on the main page messageboard.
    'latestgames'       => 0,   // Number of entries in the main page table "latest games".
    'standings_players' => 30,  // Number of entries on the general players stadings table.
    'standings_teams'   => 30,  // Number of entries on the general teams   stadings table.
    'standings_coaches' => 30,  // Number of entries on the general coaches stadings table.
);

$settings['fp_standings'] = array();
$settings['fp_leaders'] = array();

$settings['fp_boxes_order'] = array('standings', 'latestgames', 'leaders');

$settings['show_sold_journeymen']  = true;  // Default is true. Show sold journeymen on rosters in detailed view mode.
$settings['show_stars_mercs']      = true;  // Default is true. Show summed up stats for earlier hired star players and mercenaries on rosters in detailed view mode.
$settings['fp_team_news']          = true;  // Default is true. Show team news on front page.
$settings['fp_links']              = true;  // Default is true. Generate coach, team and player links on the front page?
$settings['hide_retired']		   = false; // Default is false. Hides retired coaches and teams from standings tables.

?>
