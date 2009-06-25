<?php

/*
 *  Copyright (c) Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2009. All Rights Reserved.
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

class Race
{

/***************
 * Properties 
 ***************/

public $race = '';
public $name = ''; // = $this->race, used for conventional reasons.
public $race_id = 0;

/***************
 * Methods 
 ***************/

function __construct($race_id) 
{
    global $raceididx;
    
    $this->race_id = $race_id;
    $this->race = $this->name = $raceididx[$this->race_id];
    $this->setStats(false,false,false);
}

public function setStats($node, $node_id, $set_avg = false)
{
    foreach (Stats::getAllStats(STATS_RACE, $this->race_id, $node, $node_id, false, false, $set_avg) as $key => $val) {
        $this->$key = $val;
    }
    return true;
}

public function getRoster()
{
    global $DEA;
    return array_key_exists($this->race, $DEA) ? $DEA[$this->race] : array();
}

public function getGoods($double_rr_price = false)
{
    /**
     * Returns buyable stuff for this race.
     **/

    global $DEA, $rules;

    $rr_price = (array_key_exists($this->race, $DEA) ? $DEA[$this->race]['other']['RerollCost'] : 0) * (($double_rr_price && !$rules['static_rerolls_prices']) ? 2 : 1);
    $apoth = !in_array($this->race, array('Khemri', 'Necromantic', 'Nurgle', 'Undead'));

    return array(
            // MySQL column names as keys
            'apothecary'    => array('cost' => $rules['cost_apothecary'],   'max' => ($apoth ? 1 : 0),              'item' => 'Apothecary'),
            'rerolls'       => array('cost' => $rr_price,                   'max' => $rules['max_rerolls'],         'item' => 'Reroll'),
            'fan_factor'    => array('cost' => $rules['cost_fan_factor'],   'max' => $rules['max_fan_factor'],      'item' => 'Fan Factor'),
            'ass_coaches'   => array('cost' => $rules['cost_ass_coaches'],  'max' => $rules['max_ass_coaches'],     'item' => 'Assistant Coach'),
            'cheerleaders'  => array('cost' => $rules['cost_cheerleaders'], 'max' => $rules['max_cheerleaders'],    'item' => 'Cheerleader'),
    );
}

public function getTeams()
{
    return Team::getTeams($this->race_id);
}

public static function getRaces($getRaceObjs = false)
{
    /* Return race names (strings) or corresponding race objects */
    
    global $raceididx;
    $races = array_values($raceididx);
    $race_ids = array_keys($raceididx);
    return ($getRaceObjs) ? array_map(create_function('$rid', 'return (new Race($rid));'), $race_ids) : $races;
}

}
?>
