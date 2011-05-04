<?php

/*************************
 * Local settings for league with ID = 1
 *************************/

/*********************
 *   General
 *********************/

$settings['league_name']   = 'Edinboro Castle Blood Bowl League'; // Name of the site or the league name if only one league is being managed.
$settings['forum_url']     = 'http://ecbbl.doubleskulls.net/forum/index.php'; // URL of league forum, if you have such. If not then leave this empty, that is = '' (two quotes only).
$settings['stylesheet']    = 1;                  // Default is 1. OBBLM CSS stylesheet for non-logged in guests. Currently stylesheet 1 is the only existing stylesheet.
$settings['lang']          = 'en-GB';            // Default language. Existing: en-GB, es, de, fr.
$settings['fp_links']      = true;               // Default is true. Generate coach, team and player links on the front page?
$settings['welcome']       = 'Who: Blood Bowl League in North London<BR />Where: Mason Arms, 58 Devonshire Street, London W1W 5EA<BR />When: Tuesday nights from 6:30pm<BR />The ECBBL is the leading London Blood Bowl League';
$settings['rules']         = '<a href="http://ecbbl.doubleskulls.net/forum/viewforum.php?f=7">See the forum for the latest rules</a>';
$settings['tourlist_foldup_fin_divs'] = false; // Default is false. If true the division nodes in the tournament lists section will automatically be folded up if all child tournaments in that division are marked as finished.
$settings['tourlist_hide_nodes'] = array('league', 'division', 'tournament'); // Default is array('league', 'division', 'tournament'). In the section tournament lists these nodes will be hidden if their contents (children) are finished. Example: If 'division' is chosen here, and all tours in a given division are finished, then the division entry will be hidden.
$settings['coach_schedule_tours'] = array(70); // List of tournament IDs of FFA tours (from this league), in which regular coaches are able to schedule matches between their OWN teams and others teams.

/*********************
 *   Rules
 *********************/

// Please use the boolean values "true" and "false" wherever default values are boolean.

$rules['max_team_players']      = 16;       // Default is 16.
$rules['static_rerolls_prices'] = false;    // Default is "false". "true" forces re-roll prices to their un-doubled values.
$rules['player_refund']         = 0;        // Player sell value percentage. Default is 0 = 0%, 0.5 = 50%, and so on.
$rules['journeymen_limit']      = 11;       // Until a team can field this number of players, it may fill team positions with journeymen.
$rules['post_game_ff']          = false;    // Default is false. Allows teams to buy and drop fan factor even though their first game has been played.

$rules['initial_treasury']      = 1000000;  // Default is 1000000.
$rules['initial_rerolls']       = 0;        // Default is 0.
$rules['initial_fan_factor']    = 0;        // Default is 0.
$rules['initial_ass_coaches']   = 0;        // Default is 0.
$rules['initial_cheerleaders']  = 0;        // Default is 0.

// For the below limits, the following applies: -1 = unlimited. 0 = disabled.
$rules['max_rerolls']           = -1;       // Default is -1.
$rules['max_fan_factor']        = 9;        // Default is 9.
$rules['max_ass_coaches']       = -1;       // Default is -1.
$rules['max_cheerleaders']      = -1;       // Default is -1.

/*********************
 *   Standings pages
 *********************/

$settings['standings']['length_players'] = 30;  // Number of entries on the general players standings table.
$settings['standings']['length_teams']   = 30;  // Number of entries on the general teams   standings table.
$settings['standings']['length_coaches'] = 30;  // Number of entries on the general coaches standings table.

/*********************
 *   Front page messageboard
 *********************/

$settings['fp_messageboard']['length']               = 8;    // Number of entries on the front page message board.
$settings['fp_messageboard']['show_team_news']       = true; // Default is true. Show team news on the front page message board.
$settings['fp_messageboard']['show_match_summaries'] = true; // Default is true. Show match summaries on the front page message board.

/*********************
 *   Front page boxes
 *********************/

/*
    The below settings define which boxes to show on the right side of the front page.

    Note, every box MUST have a unique 'box_ID' number.
    The box IDs are used to determine the order in which the boxes are shown on the front page.
    The box with 'box_ID' = 1 is shown at the top of the page, the box with 'box_ID' = 2 is displayed underneath it and so forth.
*/

/*********************
 *   Front page: tournament standings boxes
 *********************/

$settings['fp_standings'] = array(
    # This will display a standings box of the top 6 teams in node (league, division or tournament) with ID = 1
    array(
        'id'     => 73,
        'box_ID' => 3,
        // Please note: 'type' may be either one of: 'league', 'division' or 'tournament'
        'type'   => 'tournament', # This sets the node to be a tournament. I.e. this will make a standings box for the tournament with ID = 1
        'infocus' => true, # If true a random team from the standings will be selected and its top players displayed.
        /*
            The house ranking system (HRS) NUMBER to sort the table against.
            Note, this is ignored for "type = tournament", since tours have an assigned HRS.
            Also note that using HRSs with fields such as points (pts) for leagues/divisions standings makes no sense as they are tournament specific fields (i.e. it makes no sense to sum the points for teams across different tours to get the teams' "league/division points", as the points definitions for tours may vary).
        */
        'HRS'    => 4, # Note: this must be a existing and valid HRS number from the main settings.php file.
        'title'  => 'Spr 11 standings',
        'length' => 8,
        # Format: "Displayed table column name" => "OBBLM field name".
        'fields' => array('Name' => 'name', 'PTS' => 'pts', 'TV' => 'tv', 'CAS' => 'cas', 'W' => 'won', 'L' => 'lost', 'D' => 'draw', 'GF' => 'gf', 'GA' => 'ga',),
    ),
    # This will display a standings box of the top 6 teams in node (league, division or tournament) with ID = 1
    array(
        'id'     => 70,
        'box_ID' => 6,
        // Please note: 'type' may be either one of: 'league', 'division' or 'tournament'
        'type'   => 'tournament', # This sets the node to be a tournament. I.e. this will make a standings box for the tournament with ID = 1
        'infocus' => true, # If true a random team from the standings will be selected and its top players displayed.
        /*
            The house ranking system (HRS) NUMBER to sort the table against.
            Note, this is ignored for "type = tournament", since tours have an assigned HRS.
            Also note that using HRSs with fields such as points (pts) for leagues/divisions standings makes no sense as they are tournament specific fields (i.e. it makes no sense to sum the points for teams across different tours to get the teams' "league/division points", as the points definitions for tours may vary).
        */
        'HRS'    => 4, # Note: this must be a existing and valid HRS number from the main settings.php file.
        'title'  => 'Open League',
        'length' => 8,
        # Format: "Displayed table column name" => "OBBLM field name".
        'fields' => array('Name' => 'name', 'PTS' => 'pts', 'TV' => 'tv', 'CAS' => 'cas', 'W' => 'won', 'L' => 'lost', 'D' => 'draw', 'GF' => 'gf', 'GA' => 'ga',),
    ),
);

/*********************
 *   Front page: leaders boxes
 *********************/

$settings['fp_leaders'] = array(
    # Please note: You can NOT make expressions out of leader fields e.g.: 'field' => 'cas+td'
    # This will display a 'most CAS' player leaders box for the node (league, division or tournament) with ID = 1
    array(
        'id'        => 73,
        'box_ID'    => 5,
        // Please note: 'type' may be either one of: 'league', 'division' or 'tournament'
        'type'      => 'tournament', # This sets the node to be a tournament. I.e. this will make a leaders box for the tournament with ID = 1
        'title'     => 'Spr 11 most casualties',
        'field'     => 'cas',
        'length'    => 5,
        'show_team' => true, # Show player's team name?
    ),
    # This will display a 'most TD' player leaders box for the node (league, division or tournament) with ID = 2
    array(
        'id'        => 73,
        'box_ID'    => 4,
        // Please note: 'type' may be either one of: 'league', 'division' or 'tournament'
        'type'      => 'tournament', # This sets the node to be a tournament. I.e. this will make a leaders box for the tournament with ID = 1
        'title'     => 'Spr 11 most touchdowns',
        'field'     => 'td',
        'length'    => 5,
        'show_team' => true, # Show player's team name?
    ),
);

/*********************
 *   Front page: latest games boxes
 *********************/

$settings['fp_latestgames'] = array(
    # This will display a latest games box for the node (league, division or tournament) with ID = 1
    array(
        'id'     => 1,
        'box_ID' => 2,
        // Please note: 'type' may be either one of: 'league', 'division' or 'tournament'
        'type'   => 'league', # This sets the node to be a league. I.e. this will make a latest games box for the league with ID = 1
        'title'  => 'Recent games',
        'upcoming' => 0, # if set to 0 will show recent games, if set to 1 will show future games.
        'length' => 5,
    ),
    # This will display an  upcoming games box for the node (league, division or tournament) with ID = 1
    array(
        'id'     => 1,
        'box_ID' => 1,
        // Please note: 'type' may be either one of: 'league', 'division' or 'tournament'
        'type'   => 'league', # This sets the node to be a league. I.e. this will make a latest games box for the league with ID = 1
        'title'  => 'Upcoming games',
        'upcoming' => 1, # if set to 0 will show recent games, if set to 1 will show future games.
        'length' => 5,
    ),
);

?>
