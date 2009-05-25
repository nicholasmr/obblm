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

public $mvp         = 0;
public $cp          = 0;
public $td          = 0;
public $intcpt      = 0;
public $bh          = 0;
public $si          = 0;
public $ki          = 0;
public $cas         = 0; // Sum of bh+ki+si.
public $tdcas       = 0; // Is td+cas.

public $played      = 0;
public $won         = 0;
public $lost        = 0;
public $draw        = 0;
public $win_percentage = 0;

/***************
 * Methods 
 ***************/

function __construct($race) {

    $this->race = $race;
    $this->setStats(false);
}

public function setStats($setAvgs = false)
{
    foreach ($this->getStats($setAvgs) as $field => $val) {
        $this->$field = $val;
    }
    
    return true;
}

private function getStats($setAvgs = false)
{
    /**
     * Returns an array of race stats by looking at teams' (from that race) stats in MySQL.
     **/        

    // Initialize         
    $d = array();
    $teams = $this->getTeams();
    $stats = array('won_tours', 'won', 'lost', 'draw', 'played', 'td', 'cp', 'intcpt', 'cas', 'bh', 'si', 'ki', 'value');
    $avg_calc = array_slice($stats, 5);
    
    foreach ($stats as $s) $d[$s] = 0;
         
    // Fill variables.
    foreach ($teams as $t) {
        $t->setExtraStats();
        foreach ($stats as $s) {
            $d[$s] += $t->$s;
        }
    }
         
    $c = $d['teams'] = count($teams);
    foreach ($avg_calc as $s) {
        $d[$s] = ($c == 0) ? 0 : $d[$s]/$c;
    }
        
    //$d['race'] = $this->race;
    $d['win_percentage'] = ($d['played'] == 0) ? 0 : $d['won']/$d['played'] * 100;
        
    return $d;
}

public function getTeams()
{
    return Team::getTeams($this->race);
}

public static function getRaces($getRaceObjs = false)
{
    /* Return race names (strings) or corresponding race objects */
    
    global $DEA;
    $races = array_keys($DEA);    
    return ($getRaceObjs) ? array_map(create_function('$race', 'return (new Race($race));'), $races) : $races;
}

}
?>
