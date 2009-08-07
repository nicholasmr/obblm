<?php

/*
 *  Copyright (c) Grégory Romé <email protected> 2009. All Rights Reserved.
 *  Author(s): Frederic Morel, Grégory Romé
 *
 *
 *  This file is part of OBBLM.
 *
 *  OBBLM is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  OBBLM is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

global $DEA;
global $raceididx;

$cyanide_player_type = array(
	1 => array('Human', 'Lineman'),
	2 => array('Human', 'Catcher'),
	3 => array('Human', 'Thrower'),
	4 => array('Human', 'Blitzer'),
	5 => array('Human', 'Ogre'),
	6 => array('Dwarf', 'Blocker'),
	7 => array('Dwarf', 'Runner'),
	8 => array('Dwarf', 'Blitzer'),
	9 => array('Dwarf', 'Troll Slayer'),
	10 => array('Dwarf', 'Deathroller'),
	11 => array('Wood Elf', 'Lineman'),
	12 => array('Wood Elf', 'Catcher'),
	13 => array('Wood Elf', 'Thrower'),
	14 => array('Wood Elf', 'Wardancer'),
	15 => array('Wood Elf', 'Treeman'),
	16 => array('Skaven', 'Lineman'),
	17 => array('Skaven', 'Thrower'),
	18 => array('Skaven', 'Gutter Runner'),
	19 => array('Skaven', 'Blitzer'),
	20 => array('Skaven', 'Rat Ogre'),
	21 => array('Orc', 'Lineman'),
	22 => array('Orc', 'Goblin'),
	23 => array('Orc', 'Thrower'),
	24 => array('Orc', 'Black Orc Blocker'),
	25 => array('Orc', 'Blitzer'),
	26 => array('Orc', 'Troll'),
	27 => array('Lizardman', 'Skink'),
	28 => array('Lizardman', 'Saurus'),
	29 => array('Lizardman', 'Kroxigor'),
	30 => array('Goblin', 'Gob'),
	31 => array('Goblin', 'Looney'),
	32 => array('Chaos', 'Beastman'),
	33 => array('Chaos', 'Chaos Warrior'),
	34 => array('Chaos', 'Minotaur'),
	44 => array('Goblin', 'Troll'),
	45 => array('Goblin', 'Pogoer'),
	46 => array('Goblin', 'Fanatic')
	);

foreach ($DEA as $race_name => $attrs)
{

	if (!in_array($race_name, $settings['cyanide_races']))
	{
		unset($DEA[$race_name]);
	}
}

unset ($DEA['Goblin']['players']['Bombardier']);

$raceididx = array();
foreach (array_keys($DEA) as $race)
{
	$raceididx[$DEA[$race]['other']['race_id']] = $race;
}

?>