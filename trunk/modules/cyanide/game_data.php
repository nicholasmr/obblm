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
global $hrs;

if($settings['cyanide_enabled'])
{
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

	$rule = array(
		'rule' => array('-points', '-value', '-score_diff'),
		'points' => '3*[won] + 1*[draw]',
		'points_desc' => '3*[won] + 1*[draw]'
		);

	array_push($hrs, $rule);
}

?>