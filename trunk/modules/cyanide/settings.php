<?php

/*
 *  Copyright (c) Gregory Romé <email protected> 2009. All Rights Reserved.
 *  Author(s): Frederic Morel, Gregory Romé
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

global $settings;

// Enable Cyanide game support.
// Default is false.
$settings['cyanide_enabled']       = false; //@FIXME Default is false.

// If true the module is in charge to compute some team data:
//   * cash earned,
//   * fame,
//   * fans.
// If false the match is not locked.
// Default is true.
$settings['cyanide_public_league'] = false; //@FIXME Default is true.

// Uploads report to a scheduled match.
// The options are [false|true|"strict"]:
//   * false does not check for scheduled matches
//   * true checks for scheduled matches and will create a match if not found
//   * "strict" will allow only scheduled matches to be used
// Default is true.
$settings['cyanide_schedule']      = true;

// Allow to select a scheduled match with reverse Home/Away
// Default is true.
$settings['cyanide_allow_reverse'] = true;

// Enable player creation if it does not exist during Match import.
// This feature allows to update the team in case of missing player.
// To use with caution.
// Default is false.
$settings['cyanide_allow_new_player'] = false;

// Races supported by Cyanide.
$settings['cyanide_races'] = array (
	1 => 'Human',
	2 => 'Dwarf',
	3 => 'Skaven',
	4 => 'Orc',
	5 => 'Lizardman',
	6 => 'Goblin',
	7 => 'Wood Elf',
	8 => 'Chaos' );
	
?>
