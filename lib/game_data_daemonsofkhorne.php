<?php

define('T_RACE_DKHORNE', 25);

$DEA['Daemons of Khorne'] = array (
    'other'     => array (
        'rr_cost'   => 70000,
        'icon'      => 'naf.png',
        'race_id'   => T_RACE_DKHORNE, # (Daemons of Khorne)
    ),
    'players'    => array (
        'Pit Fighter'        => array (
            'ma'                => 6,
            'st'                => 3,
            'ag'                => 3,
            'av'                => 8,
            'def'               => array (5),
            'norm'              => array ('G', 'P'),
            'doub'              => array ('A', 'S'),
            'qty'               => 16,
            'cost'              => 60000,
            'icon'              => 'pitfighter',
            'pos_id'            => 250,
        ),
        'Bloodletter Daemon' => array (
            'ma'                => 6,
            'st'                => 3,
            'ag'                => 3,
            'av'                => 7,
            'def'               => array (75,53,103),
            'norm'              => array ('G', 'A', 'S'),
            'doub'              => array ('P'),
            'qty'               => 4,
            'cost'              => 80000,
            'icon'              => 'bloodletter',
            'pos_id'            => 251,
        ),
        'Khorne Herald'      => array (
            'ma'                => 6,
            'st'                => 3,
            'ag'                => 3,
            'av'                => 8,
            'def'               => array (5,75,53),
            'norm'              => array ('G', 'S'),
            'doub'              => array ('A', 'P'),
            'qty'               => 2,
            'cost'              => 90000,
            'icon'              => 'herald',
            'pos_id'            => 252,
        ),
        'Bloodthirster'      => array (
            'ma'                => 6,
            'st'                => 5,
            'ag'                => 1,
            'av'                => 9,
            'def'               => array (99, 112, 71, 5, 75, 53, 103),
            'norm'              => array ('S'),
            'doub'              => array ('G', 'A', 'P'),
            'qty'               => 1,
            'cost'              => 180000,
            'icon'              => 'bloodthirster',
            'pos_id'            => 253,
        ),
    )
);

// Stars allowed
foreach (array(	'Brick Far\'th (+ Grotty)',
				'Grotty (included in Brick Far\'th)',
				'Grashnak Blackhoof',
				'Lewdgrip Whiparm',
				'Morg \'n\' Thorg',
				'Lord Borak the Despoiler',
				'Max Spleenripper',
				'Bob Bifford',
				'Slave Giant',
				) as $s) {
    array_push($stars[$s]['races'], T_RACE_DKHORNE);
}
