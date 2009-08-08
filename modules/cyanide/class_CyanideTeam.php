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

require_once 'modules/cyanide/lib_cyanide.php';

class CyanideTeam
{
	public $id = 0;
	public $is_rdy = false;
	public $is_new = true;
	public $prefix = "";

	public $info = array (
		'coach_id' => 0,
		'name' => 0,
		'race' => 0);

	public $init = array (
		'won' => 0,
		'lost' => 0,
		'draw' => 0,
		'sw' => 0,
		'sl' => 0,
		'sd' => 0,
		'wt' => 0,
		'gf' => 0,
		'ga' => 0,
		'elo' => 0,
		'tcas' => 0 );

	public $players = array();

	private $prefix_list = array (
		0 => "",
		1 => "Home_",
		2 => "Away_"
	);

	function __construct($sqliteFile, $type, $coach_id=false)
	{
		$this->prefix = $this->prefix_list[$type];

		$team_db = new PDO("sqlite:" . $sqliteFile);

		$results = cyanidedb_query_teamlisting($team_db, $this->prefix);
		if(!$results) { return false; }

		$this->info['name'] = $results['name'];
		$this->info['race'] = $results['race'];

		print $this->info['race'];

		$results = obblm_find_team_by_name($this->info['name']);
		if($results)
		{
			$this->id = $results[0];
			$this->info['coach_id'] = $results[1];
		} else
		{
			if($coach_id)
			{
				$this->info['coach_id'] = $coach_id;
			}
		}

		$this->players = cyanidedb_query_playerlisting($team_db, $this->prefix);

		return true;
	}

	public function create()
	{
		global $coach;

		if( !$this->id )
		{
			if(!$this->info['coach_id'])
			{
				$this->info['coach_id'] = $coach->coach_id;
			}

			if($this->is_new){ $this->id = Team::create($this->info); }
			else { $this->id = Team::create($this->info, $this->init); }
		}
		else
		{
			if($coach->coach_id !== $this->info['coach_id'] &&
				($coach->ring === RING_COACH) )
			{
				return false;
			}
		}

		return $this->id;
	}

	public function populate()
	{
		if( $this->id )
		{
			$team = new Team($this->id);
			foreach($this->players as $player)
			{
				$player['team_id'] = $this->id;
				$player['forceCreate'] = true;

				foreach(array_keys($player) as $key)
				{
					print $key." = ".$player[$key]."<br>";
				}
				$player_id = Player::create($player, false);
			}

			return true;
		}
		return false;
	}

}

?>