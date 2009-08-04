<?php
global $settings;

$settings['cyanide_enabled'] = true; // Enable Cyanide support. @FIXME Default is false.
$settings['cyanide_debug']   = true; // Enable module debug.
$settings['cyanide_public_league'] = true; // If true the module is in charge to compute some parameters

$settings['cyanide_races'] = array( // Races supported by Cyanide
	'Chaos',
	'Dwarf',
	'Goblin',
	'Human',
	'Lizardman',
	'Orc',
	'Skaven',
	'Wood Elf');

?>
