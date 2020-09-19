<?php

define('T_RACE_APESOFWRATH', 26);

$DEA['Simyin']  = array (
    'other'          => array (
        'rr_cost'       => 60000,
        'icon'          => 'naf.png',
        'race_id'       => T_RACE_APESOFWRATH, # (Apes of Wrath)
    ),
    'players'   => array (
        'Bonobo'     => array (
            'ma'        => 6,
            'st'        => 3,
            'ag'        => 3,
            'av'        => 7,
            'def'       => array (73),
            'norm'      => array ('G'),
            'doub'      => array ('A', 'S', 'P'),
            'qty'       => 16,
            'cost'      => 50000,
            'icon'      => 'ape_lineman1',
            'pos_id'    => 261,
        ),
        'Orangutan'  => array (
            'ma'        => 5,
            'st'        => 3,
            'ag'        => 3,
            'av'        => 8,
            'def'       => array (73, 58),
            'norm'      => array ('G', 'P'),
            'doub'      => array ('A', 'S'),
            'qty'       => 2,
            'cost'      => 70000,
            'icon'      => 'ape_orangutan',
            'pos_id'    => 262,
        ),
        'Gorilla'    => array (
            'ma'        => 5,
            'st'        => 4,
            'ag'        => 2,
            'av'        => 8,
            'def'       => array (73, 51),
            'norm'      => array ('A', 'S'),
            'doub'      => array ('G', 'P'),
            'qty'       => 4,
            'cost'      => 100000,
            'icon'      => 'ape_gorilla',
            'pos_id'    => 263,
        ),
        'Chimpanzee' => array (
            'ma'        => 7,
            'st'        => 3,
            'ag'        => 3,
            'av'        => 7,
            'def'       => array (73, 14),
            'norm'      => array ('G', 'A'),
            'doub'      => array ('S', 'P'),
            'qty'       => 2,
            'cost'      => 80000,
            'icon'      => 'ape_runner',
            'pos_id'    => 264,
        ),
        'Silverback' => array (
            'ma'        => 5,
            'st'        => 5,
            'ag'        => 1,
            'av'        => 9,
            'def'       => array (99, 73, 51, 112, 54),
            'norm'      => array ('S'),
            'doub'      => array ('G','A', 'P'),
            'qty'       => 1,
            'cost'      => 140000,
            'icon'      => 'ape_silverback',
            'pos_id'    => 265,
        )
    )
);
// Stars allowed
foreach (array(	'Bertha Bigfist',
				'Deeproot Strongbranch',
				'Puggy Baconbreath',
				'Morg \'n\' Thorg',
				'Willow Rosebark',
				'Zara the Slayer',
				'Bob Bifford',
				'Slave Giant',
				) as $s) {
    array_push($stars[$s]['races'], T_RACE_APESOFWRATH);
}
