<?php

/*
 *  Copyright (c) Grégory Romé <email protected> 2009. All Rights Reserved.
 *  Author(s): Grégory Romé
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

function cyanidedb_query_teamlisting($db, $prefix = "")
{
	$query = "
		SELECT strName,
			idRaces,
			strLeitmotiv,
			iValue,
			iPopularity,
			iCash,
			iCheerleaders,
			iBalms,
			bApothecary,
			iRerolls
		FROM ".$prefix."Team_Listing LIMIT 1";

	$result = $db->query($query);
	if ( !$result ) { return false; }

	$array = $result->fetchAll();
	if (!$array || sizeof($array) !=1 ) { return false;	}

	$row = $array[0];

	$out = array (
			'name' => $row['strName'],
			'race' => cyanidedb_get_race($row['idRaces']),
			'comment' => $row['strLeitmotiv'],
			'tv' => $row['iValue'],
			'ff' => $row['iPopularity'],
			'treasury' => $row['iCash'],
			'cheerleaders' => $row['iCheerleaders'],
			'apothecary' => $row['bApothecary'],
			'ass_coaches' => $row['iBalms'],
			'rerolls' => $row['iRerolls'] );

	return $out;
}

function cyanidedb_query_playerlisting($db, $prefix = "")
{
	$query = "
		SELECT strName,
			idPlayer_Types,
			iNumber
		FROM ".$prefix."Player_Listing";

	$result = $db->query($query);
	if ( !$result ) { return false; }

	$array = $result->fetchAll();
	if (!$array || sizeof($array) < 1 ) { return false;	}

	$players = array();
	foreach ($array as $row)
	{
		$players[$row['iNumber']]['nr'] = $row['iNumber'];
		$players[$row['iNumber']]['name'] = $row['strName'];
		$players[$row['iNumber']]['position'] = cyanidedb_get_postion($row['idPlayer_Types']);
	}

	return $players;
}

function cyanidedb_get_race($race_id)
{
	global $settings;

	return $settings['cyanide_races'][$race_id];
}

function cyanidedb_get_postion($position_id)
{
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
		46 => array('Goblin', 'Fanatic') );

	return $cyanide_player_type[$position_id][1];
}

function obblm_find_team_by_name ( $team_name )
{
	$query = "
		SELECT owned_by_coach_id, team_id
		FROM teams
		WHERE name = '".mysql_real_escape_string($team_name)."'
		LIMIT 1";

	$results = mysql_query($query);
	if (!$results) { return false; }

	if(mysql_num_rows($results) !== 1) { return false; }

	$row = mysql_fetch_array($results);

	$out = array(
		'team_id' =>  $row['team_id'],
		'coach_id' => $row['owned_by_coach_id']);

	return $out;
}

function obblm_find_player_by_number ( $nr, $team_id )
{
	$query = "
		SELECT name, player_id
		FROM players
		WHERE nr = '".$nr."'
		AND owned_by_team_id = '".$team_id."'
		LIMIT 1";

	$results = mysql_query($query);
	if (!$results) { return false; }

	if(mysql_num_rows($results) !== 1) { return false; }

	$row = mysql_fetch_array($results);

	$out = array('player_id' => $row['player_id'], 'name' => $row['name']);

	return $out;
}


?>