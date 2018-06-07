<?php

// DO NOT DELETE THIS FILE!!!
// Use this local settings file for leagues which have no local settings file.
$settings['banner_title']             = 'No settings file exists for the selected league';
$settings['banner_subtitle']          = 'No settings file exists for the selected league';
$settings['league_name']              = 'No settings file exists for the selected league';
$settings['league_url']               = '';
$settings['league_url_name']          = 'League URL disabled';
$settings['stylesheet']               = 1;
$settings['lang']                     = 'en-GB';
$settings['fp_links']                 = true;
$settings['welcome']                  = 'Could not find the local league settings file for the selected league at <i>localsettings/settings_&lt;LEAGUE ID&gt;.php</i>';
$settings['rules']                    = 'No settings file exists for the selected league';
$settings['tourlist_foldup_fin_divs'] = false;
$settings['tourlist_hide_nodes']      = array('league', 'division', 'tournament');

$rules['max_team_players']      = 16;
$rules['static_rerolls_prices'] = false;
$rules['player_refund']         = 0;
$rules['journeymen_limit']      = 11;
$rules['post_game_ff']          = false;

$rules['initial_treasury']      = 1000000;
$rules['initial_rerolls']       = 0;
$rules['initial_fan_factor']    = 0;
$rules['initial_ass_coaches']   = 0;
$rules['initial_cheerleaders']  = 0;

$rules['max_rerolls']           = -1;
$rules['max_fan_factor']        = 9;
$rules['max_ass_coaches']       = -1;
$rules['max_cheerleaders']      = -1;

$rules['initial_team_treasury'] = array(	//	0			=>	1000000,	// Amazon
											//	1			=>	1000000,	// Chaos
											//	2			=>	1000000,	// Chaos Dwarf
											//	3			=>	1000000,	// Dark Elf
											//	4			=>	1000000,	// Dwarf
											//	5			=>	1000000,	// Elf
											//	6			=>	1000000,	// Goblin
											//	7			=>	1000000,	// Halfling
											//	8			=>	1000000,	// High Elf
											//	9			=>	1000000,	// Human
											//	10			=>	1000000,	// Khemri
											//	11			=>	1000000,	// Lizardman
											//	12			=>	1000000,	// Orc
											//	13			=>	1000000,	// Necromantic
											//	14			=>	1000000,	// Norse
											//	15			=>	1000000,	// Nurgle
											//	16			=>	1000000,	// Ogre
											//	17			=>	1000000,	// undead
											//	18			=>	1000000,	// Vampire
											//	19			=>	1000000,	// Skaven
											//	20			=>	1000000,	// Wood Elf
											//	21			=>	1000000,	// Chaos Pact
											//	22			=>	1000000,	// Slann
											//	23			=>	1000000,	// Underworld
											//	24			=>	1000000,	// Bretonnia
											//	25			=>	1000000,	// Daemons of Khorne
											//	26			=>	1000000,	// Apes of Wrath
										);

$settings['standings']['length_players'] = 30;
$settings['standings']['length_teams']   = 30;
$settings['standings']['length_coaches'] = 30;

$settings['fp_messageboard']['length']               = 5;
$settings['fp_messageboard']['show_team_news']       = true;
$settings['fp_messageboard']['show_match_summaries'] = true;

$settings['fp_standings']   = array();
$settings['fp_leaders']     = array();
$settings['fp_events']      = array();
$settings['fp_latestgames'] = array();
