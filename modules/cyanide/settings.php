<?php
global $settings;
global $DEA;
global $raceididx;

$settings['cyanide_enabled'] = true; // Enable Cyanide support. @FIXME Default is false.
$settings['cyanide_debug']   = true; // Enable module debug.

$settings['cyanide_races'] = array( // Races supported by Cyanide
	'Chaos',
	'Dwarf',
	'Goblin',
	'Human',
	'Lizardman',
	'Orc',
	'Skaven',
	'Wood Elf');

if($settings['cyanide_enabled'])
{
	foreach ($DEA as $race_name => $attrs) {

		if (!in_array($race_name, $settings['cyanide_races'])) {
			unset($DEA[$race_name]);
		}
	}

	$raceididx = array();
	foreach (array_keys($DEA) as $race) {
		$raceididx[$DEA[$race]['other']['race_id']] = $race;
	}
}
?>
