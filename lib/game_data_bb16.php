<?php

require_once('lib/game_data_lrb6.php');

// New skills added in BB2016 DZ1
$skillarray['E'][114] = $skillididx[114] = 'Monstrous Mouth';
$skillarray['E'][115] = $skillididx[115] = 'Timmm-ber';
$skillarray['E'][116] = $skillididx[116] = 'Weeping Blades';
//New skills added in BB2016 DZ2
$skillarray['E'][117] = $skillididx[117] = 'Swoop';
// Changes to present teams/positionals from LRB6 to BB2016.
$DEA['Human']['players']['Catcher']['cost'] = 60000;
$DEA['Skaven']['players']['Gutter Runner']['def'] = array (23, 116);
$DEA['Halfling']['players']['Treeman']['def'] = array (54, 57, 58, 109, 59, 110, 115);
// New positionals from BB2016 DZ2
$DEA['Goblin']['players']['Doom Diver'] = array (
	'ma'        	=> 6,
	'st'        	=> 2,
	'ag'        	=> 3,
	'av'        	=> 7,
	'def'	    => array (104, 108, 117 ),
	'norm'		=> array ('A'),
	'doub'		=> array ('G', 'S', 'P'),
	'qty'			=> 1,
	'cost'			=> 60000,
	'icon'			=> 'goblin1',
	'pos_id'        => 66,
);
$DEA['Goblin']['players']['Ooligan'] = array (
	'ma'        	=> 6,
	'st'        	=> 2,
	'ag'        	=> 3,
	'av'        	=> 7,
	'def'	    => array (23, 104, 108, 72, 97 ),
	'norm'		=> array ('A'),
	'doub'		=> array ('G', 'S', 'P'),
	'qty'			=> 1,
	'cost'			=> 70000,
	'icon'			=> 'goblin1',
	'pos_id'        => 67,
);
$DEA['Chaos Pact']['players']['Orc Renegade'] = array (
    'ma'        	=> 5,
	'st'        	=> 3,
	'ag'        	=> 3,
	'av'        	=> 9,
	'def'	    => array (113),
	'norm'		=> array ('G', 'M'),
	'doub'		=> array ('S', 'P'),
	'qty'			  => 1,
	'cost'			  => 50000,
	'icon'			  => 'orc1',
	'pos_id'          => 237
);
// New star players in BB2016
$stars += array(
    'Rasta Tailspike' => array (
        'id'    => -62,
        'ma'    => 8,
        'st'    => 3,
        'ag'    => 3,
        'av'    => 7,
        'def'   => array (99, 20, 73),
        'cost'  => 110000,
        'icon'  => 'star',
        'races' => array(19),
    ),
    'Frank N Stein' => array (
        'id'    => -63,
        'ma'    => 4,
        'st'    => 5,
        'ag'    => 1,
        'av'    => 9,
        'def'   => array (99, 50, 54, 103, 57, 59),
        'cost'  => 210000,
        'icon'  => 'star',
        'races' => array(9, 13, 17),
    ),
    'Bilerot Vomitflesh' => array (
        'id'    => -64,
        'ma'    => 4,
        'st'    => 5,
        'ag'    => 2,
        'av'    => 9,
        'def'   => array (99, 3, 72, 74),
        'cost'  => 180000,
        'icon'  => 'star',
        'races' => array(15, 1),
    ),
	// New star players in BB2016 DZ1
    'Guffle Pusmaw' => array (
        'id'    => -65,
        'ma'    => 5,
        'st'    => 3,
        'ag'    => 4,
        'av'    => 9,
        'def'   => array (99, 74, 101, 114),
        'cost'  => 220000,
        'icon'  => 'star',
        'races' => array(15),
    ),
	// New star players in BB2016 DZ2
	'Glart Smashrip Sr' => array (
        'id'    => -66,
        'ma'    => 5,
        'st'    => 4,
        'ag'    => 2,
        'av'    => 8,
        'def'   => array (99, 1, 71, 51, 53, 57),
        'cost'  => 190000,
        'icon'  => 'star',
        'races' => array(19, 23),
    ),
    'Lucien Swift (+ Valen Swift)' => array (
        'id'    => -67,
        'ma'    => 7,
        'st'    => 3,
        'ag'    => 4,
        'av'    => 8,
        'def'   => array (99, 1, 54, 13),
        'cost'  => 390000,
        'icon'  => 'star',
        'races' => array(5, 8, 20),
    ),
    'Valen Swift (included with Lucien Swift)' => array (
        'id'    => -68,
        'ma'    => 7,
        'st'    => 3,
        'ag'    => 5,
        'av'    => 7,
        'def'   => array (99, 40, 44, 45, 46, 12),
        'cost'  => 0,
        'icon'  => 'star',
        'races' => array(5, 8, 20),
    ),
    'Kreek Rustgouger' => array (
        'id'    => -69,
        'ma'    => 5,
        'st'    => 7,
        'ag'    => 2,
        'av'    => 9,
        'def'   => array (99, 91, 54, 100, 76, 105),
        'cost'  => 130000,
        'icon'  => 'star',
        'races' => array(19, 23),
    ),
	'Bo Gallante' => array (
        'id'    => -70,
        'ma'    => 8,
        'st'    => 3,
        'ag'    => 4,
        'av'    => 7,
        'def'   => array (99, 23, 26, 28, 29),
        'cost'  => 160000,
        'icon'  => 'star',
        'races' => array(8),
    ),
	'Karla von Kill' => array (
        'id'    => -71,
        'ma'    => 6,
        'st'    => 4,
        'ag'    => 3,
        'av'    => 8,
        'def'   => array (99, 1, 23, 2, 24),
        'cost'  => 220000,
        'icon'  => 'star',
        'races' => array(0, 4, 9, 14),
    ),
	'Madcap Miggs' => array (
        'id'    => -72,
        'ma'    => 6,
        'st'    => 4,
        'ag'    => 3,
        'av'    => 8,
        'def'   => array (99, 50, 71, 25, 100, 79, 112),
        'cost'  => 170000,
        'icon'  => 'star',
        'races' => array(6, 23),
    ),
	'Kari Coldsteel' => array (
        'id'    => -73,
        'ma'    => 6,
        'st'    => 2,
        'ag'    => 3,
        'av'    => 7,
        'def'   => array (99, 1, 2, 5),
        'cost'  => 50000,
        'icon'  => 'star',
        'races' => array(0, 4, 9, 14),
    ),
);

// Inducements from BB2016 DZ2
$inducements += array (
    'Horatio X Schottenheim' => array (
        'cost' => 80000,
        'max'  => 1,
        'reduced_cost' => 80000,
        'reduced_cost_races' => array(),
    ),
    'Fink Da Fixer' => array (
        'cost' => 0,
        'max'  => 1,
        'reduced_cost' => 50000,
        'reduced_cost_races' => array(6, 16, 12, 23), # Goblins, Ogres, Orcs, Underworld
    ),
    'Papa Skullbones' => array (
        'cost' => 0,
        'max'  => 1,
        'reduced_cost' => 80000,
        'reduced_cost_races' => array(1, 15, 21), 
    ),
    'Galandril Silverwater' => array (
        'cost' => 0,
        'max'  => 1,
        'reduced_cost' => 50000,
        'reduced_cost_races' => array(5, 8, 20),
    ),
    'Krot Shockwhisker' => array (
        'cost' => 0,
        'max'  => 1,
        'reduced_cost' => 80000,
        'reduced_cost_races' => array(6, 23),
    )
);