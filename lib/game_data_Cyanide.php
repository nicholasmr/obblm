<?php

/*
 * Game data replacement for Cyanide game
 */

//
// Removal of teams not supported in Cyanide
//
$cyanide_races = array('Chaos', 'Dwarf', 'Goblin', 'Human', 'Lizardman', 'Orc', 'Skaven', 'Wood Elf'); // Races supported by Cyanide
foreach ($DEA as $race_name => $attrs) {
	if (!in_array($race_name, $cyanide_races)) {
		unset($DEA[$race_name]);
	}
}

//
// Changes to present teams in Cyanide
//
unset ($DEA['Goblin']['players']['Bombardier']);
$DEA['Goblin']['players']['Fanatic']['cost'] = 40000;

?>