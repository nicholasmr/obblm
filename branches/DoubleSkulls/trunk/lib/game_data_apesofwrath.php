<?php

/*
 *  Copyright (c) Shteve0 <no email> 2012. All Rights Reserved.
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

define('T_RACE_APESOFWRATH', 26);

$DEA['Apes of Wrath'] = array (
      'other'   => array (
         'rr_cost' => 60000,
         'icon' => 'apesofwrath.png',
         'race_id' => T_RACE_APESOFWRATH, # (Apes of Wrath)
      ),
      'players'   => array (
         'Line Ape'   => array (
            'ma'           => 6,
             'st'           => 3,
             'ag'           => 3,
             'av'           => 7,
             'def'       => array (73),
             'norm'      => array ('G'),
             'doub'      => array ('A', 'S', 'P'),
             'qty'         => 16,
            'cost'         => 50000,
            'icon'         => 'olineman1an',
             'pos_id'        => 261,
         ),
         'Thrower'   => array (
            'ma'           => 5,
             'st'           => 3,
             'ag'           => 3,
             'av'           => 8,
             'def'       => array (73, 70, 58),
             'norm'      => array ('G', 'P'),
             'doub'      => array ('A', 'S'),
             'qty'         => 2,
            'cost'         => 70000,
            'icon'         => 'othrower1an',
             'pos_id'        => 262,
         ),
         'Gorilla'   => array (
            'ma'           => 5,
             'st'           => 4,
             'ag'           => 2,
             'av'           => 8,
             'def'       => array (73, 51, 112),
             'norm'      => array ('G', 'S'),
             'doub'      => array ('A', 'P'),
             'qty'         => 4,
            'cost'         => 80000,
            'icon'         => 'oblackorc1an',
             'pos_id'        => 263,
         ),
         'Runner'   => array (
            'ma'           => 7,
             'st'           => 3,
             'ag'           => 3,
             'av'           => 7,
             'def'       => array (73, 14),
             'norm'      => array ('G', 'A'),
             'doub'      => array ('S', 'P'),
             'qty'         => 2,
            'cost'         => 80000,
            'icon'         => 'oblitzer1an',
             'pos_id'        => 264,
         ),
         'Silverback'   => array (
            'ma'           => 5,
             'st'           => 5,
             'ag'           => 1,
             'av'           => 9,
             'def'       => array (99, 73, 51, 112, 54),
             'norm'      => array ('S'),
             'doub'      => array ('G','A', 'P'),
             'qty'         => 1,
            'cost'         => 130000,
            'icon'         => 'ogre4an',
             'pos_id'        => 265,
         )
      )
);

