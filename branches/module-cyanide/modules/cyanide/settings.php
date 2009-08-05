<?php
global $settings;

// Enable Cyanide support. @FIXME Default is false.
$settings['cyanide_enabled']       = true;

// If true the module is in charge to compute some parameters
$settings['cyanide_public_league'] = true;

// Uploads report to a scheduled match. The options are [false|true|"strict"]
// false does not check for scheduled matches
// true checks for scheduled matches and will create a match if not found
// "strict" will allow only scheduled matches to be used
$settings['cyanide_schedule']      = "strict";

// Races supported by Cyanide
$settings['cyanide_races'] = array(
	'Chaos',
	'Dwarf',
	'Goblin',
	'Human',
	'Lizardman',
	'Orc',
	'Skaven',
	'Wood Elf');

?>
