<?php

/*
 *  Copyright (c) Ian Williams <email is protected> 2011. All Rights Reserved.
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

require('lib/game_data_lrb6.php'); # Depends on full LRB6 rules.

/*
 * Additional data for Brettonians see  - http://www.plasmoids.dk/bbowl/BBBretonnians.htm
 */

define('T_RACE_BRETTONIA', 24);

$DEA['Brettonia'] = array (
    'other'	=> array (
        'rr_cost' => 60000,
        'icon' => 'brettonian.jpg',
        'race_id' => T_RACE_BRETTONIA, # (Brettonia)
    ),
    'players'	=> array (
  			'Lineman'	=> array (
    				'ma'        	=> 6,
    				'st'        	=> 3,
    				'ag'        	=> 2,
    				'av'        	=> 7,
    				'def'	    => array (4),
    				'norm'		=> array ('G'),
    				'doub'		=> array ('A', 'S', 'P'),
    				'qty'			  => 16,
    				'cost'			  => 40000,
    				'icon'			  => 'hlineman1an',
    			    'pos_id'          => 235,
  			),
  			'Yeoman'	=> array (
    				'ma'        	=> 6,
    				'st'        	=> 3,
    				'ag'        	=> 3,
    				'av'        	=> 8,
    				'def'	    => array (14),
    				'norm'		=> array ('G', 'S'),
    				'doub'		=> array ('A', 'P'),
    				'qty'			  => 4,
    				'cost'			  => 70000,
    				'icon'			  => 'hblitzer1an',
    			    'pos_id'          => 236,
  			),
  			'Blitzer'	=> array (
    				'ma'        	=> 8,
    				'st'        	=> 3,
    				'ag'        	=> 3,
    				'av'        	=> 8,
    				'def'	    => array (1, 20, 2),
    				'norm'		=> array ('G', 'S'),
    				'doub'		=> array ('A', 'P'),
    				'qty'			  => 4,
    				'cost'			  => 120000,
    				'icon'			  => 'hcatcher1an',
    			    'pos_id'          => 237,
  			),
    )
);

//
// Changes to LRB5 star players in LRB5b
//
$stars['Dolfar Longstride']['races']	= array(5, 8, 20, 24);
$stars['Griff Oberwald']['races']		= array (9, 24);
$stars['Mighty Zug']['races']			= array (9, 24);
$stars['Morg \'n\' Thorg']['races']		= array (0, 1, 2, 21, 3, 4, 5, 6, 7, 8, 9, 11, 12, 14, 15, 16, 18, 19, 22, 23, 20, 24);
$stars['Willow Rosebark']['races']		= array(0, 7, 20,24);
$stars['Zara the Slayer']['races']		= array (0, 4, 7, 8, 9, 14, 20, 24);

ksort($stars, SORT_STRING);
ksort($DEA, SORT_STRING);
$raceididx = array();
foreach (array_keys($DEA) as $race) {
    $raceididx[$DEA[$race]['other']['race_id']] = $race;
}

?>
