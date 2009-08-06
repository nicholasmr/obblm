<?php
global $settings;

// Enable Cyanide game support.
// Default is false.
$settings['cyanide_enabled']       = true; //@FIXME Default is false.

// If true the module is in charge to compute some team data:
//   * cash earned,
//   * fame,
//   * fans.
// If false the match is not locked.
// Default is true.
$settings['cyanide_public_league'] = true;

// Uploads report to a scheduled match.
// The options are [false|true|"strict"]:
//   * false does not check for scheduled matches
//   * true checks for scheduled matches and will create a match if not found
//   * "strict" will allow only scheduled matches to be used
// Default is true.
$settings['cyanide_schedule']      = "strict"; //@FIXME Default is true.
// Allow to select a scheduled match with reverse Home/Away
// Default is true.
$settings['cyanide_allow_reverse'] = true;

// Races supported by Cyanide.
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
