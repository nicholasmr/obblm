<?php

/*
 *  Copyright (c) Gregory Romé <email protected> 2009. All Rights Reserved.
 *  Author(s): Gregory Romé
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

	public $has_err = false;
	public $err = Array (
		'new_player_err' => array(),
		'get_player_err' => array()
	);

	public $info = array (
			'name' => 0,
			'race' => 0,
			'coach_id' => false,
			'comment' => "",
			'tv' => 0,
			'ff' => 0,
			'treasury' => 0,
			'cheerleaders' => 0,
			'apothecary' => 0,
			'ass_coaches' => 0,
			'rerolls' => 0 );

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

		$this->info = cyanidedb_query_teamlisting($team_db, $this->prefix);
		if(!$this->info) { return false; }

		$results = obblm_find_team_by_name($this->info['name']);
		if($results)
		{
			print "<h3>Team '".$this->info['name']."' exists!</h3>";
			$this->id = $results['team_id'];
			$this->info['coach_id'] = $results['coach_id'];
		} else
		{
			$this->info['coach_id'] = false;
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
			print "<h3>Create new team</h3>";
			if( Team::create($this->info) )
			{
				$results = obblm_find_team_by_name($this->info['name']);
				$this->id = $results['team_id'];
			}
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

			if(!$this->is_new)
			{
				$team->dtreasury(1000000);
			}

			if($this->info['apothecary'] > $team->apothecary)
			{
				print "<p>Buy Apothecary</p>";
				$team->buy('apothecary');
			}

			$diff = $this->info['rerolls'] - $team->rerolls;
			if($diff)
			{
				for($i = 0; $i <$diff; $i++)
				{
					print "<p>Buy Reroll</p>";
					$team->buy('rerolls');
				}
			}

			$diff = $this->info['ass_coaches'] - $team->ass_coaches;
			if($diff)
			{
				for($i = 0; $i <$diff; $i++)
				{
					print "<p>Buy Assistant Coaches</p>";
					$team->buy('ass_coaches');
				}
			}

			$diff = $this->info['cheerleaders'] - $team->cheerleaders;
			if($diff)
			{
				for($i = 0; $i <$diff; $i++)
				{
					print "<p>Buy Cheerleaders</p>";
					$team->buy('cheerleaders');
				}
			}

			foreach($this->players as $player)
			{
				$player['team_id'] = $this->id;
				$player['forceCreate'] = !$this->is_new;

				$results = obblm_find_player_by_number($player['nr'], $this->id);

				if($results)
				{
					$player_id = $results['player_id'];
				}
				else
				{
					print "<p>Create Player</p>";
					 $results = Player::create($player, false);

					if(!$results)
					{
						$has_err = true;
						array_push($this->err['new_player_err'], $player['nr']);
						continue;
					} else
					{
						$player_id=$results[1];
					}

				}

				$new_player = new Player($player_id);

				if(!$new_player)
				{
					$has_err = true;
					array_push($this->err['get_player_err'], $player_id);
					continue;
				}

				if($new_player->name !== $player['name'])
				{
					print "<p>Rename Player</p>";
					$new_player->rename($player['name']);
				}
			}

			$team->setStats(false,false,false);
			$diff = $this->info['ff'] - $team->fan_factor;
			for($i = 0; $i <$diff; $i++)
			{
				print "<p>Buy Fan Factor</p>";
				$this>force_buy_ff();
			}

			if(!$this->is_new)
			{
				$team->dtreasury(1000000);
			
				/* Required the updated treasury */
				$team = New Team($this->id);
				$diff = $this->info['treasury'] - $team->treasury;
				if($diff)
				{
					print "<p>Update Treasury</p>";
					$team->dtreasury($diff);
				}
			}

			return true;
		}

		return false;
	}
	
	private function force_buy_ff() 
	{
		$thing == 'fan_factor';
        $team_goods = $this->getGoods();
    
        if (!array_key_exists($thing, $team_goods))
            return false;

        $price = $team_goods[$thing]['cost'];
        $query = "
        	UPDATE teams 
        	SET treasury = treasury - $price, $thing = $thing + 1 
        	WHERE team_id = $this->team_id";
        if (mysql_query($query))
        {
            $this->$thing++;
            $this->treasury -= $price;
            return true;
        }
        else {
            return false;
        }
    }

}

?>