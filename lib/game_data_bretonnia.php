<?php

// Additional data for Bretonnians see  - http://www.plasmoids.dk/bbowl/BBBretonnians.htm
define('T_RACE_BRETONNIA', 24);

$DEA['Bretonnia'] = array (
    'other'    => array (
        'rr_cost' => 70000,
        'icon'    => 'bretonnian.jpg',
        'race_id' => T_RACE_BRETONNIA, # (Bretonnia)
    ),
    'players'    => array (
        'Lineman'   => array (
            'ma'        => 6,
            'st'        => 3,
            'ag'        => 2,
            'av'        => 7,
            'def'       => array (4),
            'norm'      => array ('G'),
            'doub'      => array ('A', 'S', 'P'),
            'qty'       => 16,
            'cost'      => 40000,
            'icon'      => 'bretlineman',
            'pos_id'    => 235,
        ),
        'Blocker'   => array (
            'ma'        => 6,
            'st'        => 3,
            'ag'        => 3,
            'av'        => 8,
            'def'       => array (14),
            'norm'      => array ('G', 'S'),
            'doub'      => array ('A', 'P'),
            'qty'       => 4,
            'cost'      => 70000,
            'icon'      => 'bretblocker',
            'pos_id'    => 236,
        ),
        'Blitzer'   => array (
            'ma'        => 7,
            'st'        => 3,
            'ag'        => 3,
            'av'        => 8,
            'def'       => array (1, 20, 2),
            'norm'      => array ('G', 'A', 'P'),
            'doub'      => array ('S'),
            'qty'       => 4,
            'cost'      => 110000,
            'icon'      => 'bretblitzer',
            'pos_id'    => 237,
        ),
    )
);

// Stars allowed
foreach (array('Dolfar Longstride', 'Griff Oberwald', 'Mighty Zug', 'Morg \'n\' Thorg', 'Willow Rosebark', 'Zara the Slayer', 'Bob Bifford') as $s) {
    array_push($stars[$s]['races'], T_RACE_BRETONNIA);
}
