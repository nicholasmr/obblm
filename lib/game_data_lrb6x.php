<?php

/*
 *  Copyright (c) Daniel Straalman <email is protected> 2008-2009. All Rights Reserved.
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

/*
 * Game data replacement for LRB6 experimental rules (LRB5b).
 */

require_once('lib/game_data.php');

//
// Changes to present teams/positionals in LRB5b.
//

$DEA['Dwarf']['players']['Deathroller']['Def skills'] = array ('Loner', 'Break Tackle', 'Dirty Player', 'Juggernaut', 'Mighty Blow', 'No Hands', 'Secret Weapon', 'Stand Firm');
$DEA['Dwarf']['other']['RerollCost'] = 50000;
$DEA['Goblin']['players']['Bombardier']['Def skills'] = array ('Bombardier', 'Dodge', 'Secret Weapon', 'Stunty');
$DEA['Goblin']['players']['Looney']['Def skills'] = array ('Chainsaw', 'Secret Weapon', 'Stunty');
$DEA['Goblin']['players']['Pogoer']['Def skills'] = array ('Dodge', 'Leap', 'Stunty', 'Very Long Legs');
$DEA['Goblin']['players']['Pogoer']['cost'] = 70000;
$DEA['Halfling']['players']['Treeman']['Def skills'] = array ('Mighty Blow', 'Stand Firm', 'Strong Arm', 'Take Root', 'Thick Skull', 'Throw Team-Mate');
$DEA['Khemri']['players']['Skeleton']['cost'] = 40000;
$DEA['Khemri']['players']['Skeleton']['Def skills'] = array ('Regeneration', 'Thick Skull');
$DEA['Khemri']['players']['Tomb Guardian'] = $DEA['Khemri']['players']['Mummie'];
unset($DEA['Khemri']['players']['Mummie']);
$DEA['Khemri']['players']['Tomb Guardian']['cost'] = 100000;
$DEA['Khemri']['players']['Tomb Guardian']['ma'] = 4;
$DEA['Khemri']['players']['Tomb Guardian']['Def skills'] = array ('Decay', 'Regeneration');
$DEA['Necromantic']['players']['Flesh Golem']['cost'] = 100000;
$DEA['Skaven']['players']['Rat Ogre']['cost'] = 150000;
$DEA['Undead']['players']['Skeleton']['cost'] = 40000;
$DEA['Undead']['players']['Skeleton']['Def skills'] = array ('Regeneration', 'Thick Skull');
$DEA['Undead']['players']['Mummie']['cost'] = 120000;
$DEA['Wood Elf']['players']['Catcher']['ma'] = 8;
$DEA['Wood Elf']['players']['Catcher']['Def skills'] = array ('Catch', 'Dodge', 'Sprint');

//
// One new skill in LRB5b - Animosity
// Only used in the three new teams
//

array_push($skillarray['Extraordinay'], 'Animosity');
sort($skillarray['Extraordinay'], SORT_STRING);

//
// Three new teams in LRB5b.
//

$DEA['Chaos Pact'] = array (
    'other'	=> array (
        'RerollCost' => 70000,
        'icon' => RACE_ICONS.'/chaos.png',
    ),
    'players'	=> array (
  			'Marauder'	=> array (
    				'ma'        	=> 6,
    				'st'        	=> 3,
    				'ag'        	=> 3,
    				'av'        	=> 8,
    				'Def skills'	=> array (),
    				'N skills'		=> array ('General', 'Strength', 'Passing', 'Mutation'),
    				'D skills'		=> array ('Agility'),
    				'qty'			    => 12,
    				'cost'			  => 50000,
    				'icon'			  => 'nlineman1an'
  			),
  			'Goblin Renegade'	=> array (
    				'ma'        	=> 6,
    				'st'        	=> 2,
    				'ag'        	=> 3,
    				'av'        	=> 7,
    				'Def skills'	=> array ('Animosity', 'Dodge', 'Right Stuff', 'Stunty'),
    				'N skills'		=> array ('Agility', 'Mutation'),
    				'D skills'		=> array ('General', 'Strength', 'Passing'),
    				'qty'			    => 1,
    				'cost'			  => 40000,
    				'icon'			  => 'goblin1an'
  			),
  			'Skaven Renegade'	=> array (
    				'ma'        	=> 7,
    				'st'        	=> 3,
    				'ag'        	=> 3,
    				'av'        	=> 7,
    				'Def skills'	=> array ('Animosity'),
    				'N skills'		=> array ('General', 'Mutation'),
    				'D skills'		=> array ('Agility', 'Strength', 'Passing'),
    				'qty'			    => 1,
    				'cost'			  => 50000,
    				'icon'			  => 'sklineman1an'
  			),
  			'Dark Elf Renegade'	=> array (
    				'ma'        	=> 6,
    				'st'        	=> 3,
    				'ag'        	=> 4,
    				'av'        	=> 8,
    				'Def skills'	=> array ('Animosity'),
    				'N skills'		=> array ('General', 'Agility', 'Mutation'),
    				'D skills'		=> array ('Strength', 'Passing'),
    				'qty'			    => 1,
    				'cost'			  => 70000,
    				'icon'			  => 'delineman1an'
  			),
  			'Chaos Troll'	=> array (
    				'ma'        	=> 4,
    				'st'        	=> 5,
    				'ag'        	=> 1,
    				'av'        	=> 9,
    				'Def skills'	=> array ('Loner', 'Always Hungry', 'Mighty Blow', 'Really Stupid', 'Regeneration', 'Throw Team-Mate'),
    				'N skills'		=> array ('Strength'),
    				'D skills'		=> array ('General', 'Agility', 'Mutation', 'Passing'),
    				'qty'			    => 1,
    				'cost'			  => 110000,
    				'icon'			  => 'troll1an'
  			),
  			'Chaos Ogre'	=> array (
    				'ma'        	=> 5,
    				'st'        	=> 5,
    				'ag'        	=> 2,
    				'av'        	=> 9,
    				'Def skills'	=> array ('Loner', 'Bone-head', 'Mighty Blow', 'Thick Skull', 'Throw Team-Mate'),
    				'N skills'		=> array ('Strength'),
    				'D skills'		=> array ('General', 'Agility', 'Mutation', 'Passing'),
    				'qty'			    => 1,
    				'cost'			  => 140000,
    				'icon'			  => 'ogre4an'
  			),
  			'Minotaur'	=> array (
    				'ma'        	=> 5,
    				'st'        	=> 5,
    				'ag'        	=> 2,
    				'av'        	=> 8,
    				'Def skills'	=> array ('Loner', 'Frenzy', 'Horns', 'Mighty Blow', 'Thick Skull', 'Wild Animal'),
    				'N skills'		=> array ('Strength'),
    				'D skills'		=> array ('General', 'Agility', 'Mutation', 'Passing'),
    				'qty'			    => 1,
    				'cost'			  => 150000,
    				'icon'			  => 'minotaur2an'
  			)
    )
);

$DEA['Slann'] = array (
    'other'	=> array (
        'RerollCost' => 50000,
        'icon' => RACE_ICONS.'/slann.png',
    ),
    'players'	=> array (
  			'Lineman'	=> array (
    				'ma'        	=> 6,
    				'st'        	=> 3,
    				'ag'        	=> 3,
    				'av'        	=> 8,
    				'Def skills'	=> array ('Leap', 'Very Long Legs'),
    				'N skills'		=> array ('General'),
    				'D skills'		=> array ('Agility', 'Strength', 'Passing'),
    				'qty'			    => 16,
    				'cost'			  => 60000,
    				'icon'			  => 'lmskink2an'
  			),
  			'Catcher'	=> array (
    				'ma'        	=> 7,
    				'st'        	=> 2,
    				'ag'        	=> 4,
    				'av'        	=> 7,
    				'Def skills'	=> array ('Diving Catch', 'Leap', 'Very Long Legs'),
    				'N skills'		=> array ('General', 'Agility'),
    				'D skills'		=> array ('Strength', 'Passing'),
    				'qty'			    => 4,
    				'cost'			  => 80000,
    				'icon'			  => 'lmskink1an'
  			),
  			'Blitzer'	=> array (
    				'ma'        	=> 7,
    				'st'        	=> 3,
    				'ag'        	=> 3,
    				'av'        	=> 8,
    				'Def skills'	=> array ('Diving Tackle', 'Jump Up', 'Leap', 'Very Long Legs'),
    				'N skills'		=> array ('General', 'Agility', 'Strength'),
    				'D skills'		=> array ('Passing'),
    				'qty'			    => 4,
    				'cost'			  => 110000,
    				'icon'			  => 'lmskink1ban'
  			),
  			'Kroxigor'	=> array (
    				'ma'        	=> 6,
    				'st'        	=> 5,
    				'ag'        	=> 1,
    				'av'        	=> 9,
    				'Def skills'	=> array ('Loner', 'Bone-head', 'Mighty Blow', 'Prehensile Tail', 'Thick Skull'),
    				'N skills'		=> array ('Strength'),
    				'D skills'		=> array ('General', 'Agility', 'Passing'),
    				'qty'			    => 1,
    				'cost'			  => 140000,
    				'icon'			  => 'kroxigor1an'
  			)
    )
);

$DEA['Underworld'] = array (
    'other'	=> array (
        'RerollCost' => 70000,
        'icon' => RACE_ICONS.'/underworld.png',
    ),
    'players'	=> array (
  			'Underworld Goblin'	=> array (
    				'ma'        	=> 6,
    				'st'        	=> 2,
    				'ag'        	=> 3,
    				'av'        	=> 7,
    				'Def skills'	=> array ('Right Stuff', 'Dodge', 'Stunty'),
    				'N skills'		=> array ('Agility', 'Mutation'),
    				'D skills'		=> array ('General', 'Strength', 'Passing'),
    				'qty'			    => 12,
    				'cost'			  => 40000,
    				'icon'			  => 'goblin1an'
  			),
  			'Skaven Lineman'	=> array (
    				'ma'        	=> 7,
    				'st'        	=> 3,
    				'ag'        	=> 3,
    				'av'        	=> 7,
    				'Def skills'	=> array ('Animosity'),
    				'N skills'		=> array ('General', 'Mutation'),
    				'D skills'		=> array ('Agility', 'Strength', 'Passing'),
    				'qty'			    => 2,
    				'cost'			  => 50000,
    				'icon'			  => 'sklineman1an'
  			),
  			'Skaven Thrower'	=> array (
    				'ma'        	=> 7,
    				'st'        	=> 3,
    				'ag'        	=> 3,
    				'av'        	=> 7,
    				'Def skills'	=> array ('Animosity', 'Pass', 'Sure Hands'),
    				'N skills'		=> array ('General', 'Passing', 'Mutation'),
    				'D skills'		=> array ('Agility', 'Strength'),
    				'qty'			    => 2,
    				'cost'			  => 70000,
    				'icon'			  => 'skthrower1an'
  			),
  			'Skaven Blitzer'	=> array (
    				'ma'        	=> 7,
    				'st'        	=> 3,
    				'ag'        	=> 3,
    				'av'        	=> 8,
    				'Def skills'	=> array ('Animosity', 'Block'),
    				'N skills'		=> array ('General', 'Strength', 'Mutation'),
    				'D skills'		=> array ('Agility', 'Passing'),
    				'qty'			    => 2,
    				'cost'			  => 90000,
    				'icon'			  => 'skstorm1an'
  			),
  			'Warpstone Troll'	=> array (
    				'ma'        	=> 4,
    				'st'        	=> 5,
    				'ag'        	=> 1,
    				'av'        	=> 9,
    				'Def skills'	=> array ('Loner', 'Always Hungry', 'Mighty Blow', 'Really Stupid', 'Regeneration', 'Throw Team-Mate'),
    				'N skills'		=> array ('Strength', 'Mutation'),
    				'D skills'		=> array ('General', 'Agility', 'Passing'),
    				'qty'			    => 1,
    				'cost'			  => 110000,
    				'icon'			  => 'troll1an'
  			)
    )
);


//
// New star players in LRB5b.
//

$stars['Bertha Bigfist'] = array (
    'id'            => -47, 
    'ma'            => 6,
    'st'            => 5,
    'ag'            => 3,
    'av'            => 9,
    'Def skills'    => array ('Loner', 'Bone-head', 'Break Tackle', 'Dodge', 'Mighty Blow', 'Thick Skull', 'Throw Team-Mate'),
    'cost'          => 260000,
    'icon'          => 'star',
    'teams'         => array('Amazon', 'Halfling', 'Ogre')
);
$stars['Crazy Igor'] = array (
    'id'            => -48, 
    'ma'            => 6,
    'st'            => 3,
    'ag'            => 3,
    'av'            => 8,
    'Def skills'    => array ('Loner', 'Dauntless', 'Regeneration', 'Thick Skull'),
    'cost'          => 130000,
    'icon'          => 'star',
    'teams'         => array('Vampire', 'Chaos Pact')
);
$stars['Dolfar Longstride'] = array (
    'id'            => -49, 
    'ma'            => 7,
    'st'            => 3,
    'ag'            => 4,
    'av'            => 7,
    'Def skills'    => array ('Loner', 'Diving Catch', 'Hail Mary Pass', 'Kick', 'Kick-off Return', 'Pass Block'),
    'cost'          => 170000,
    'icon'          => 'star',
    'teams'         => array('Elf', 'High Elf', 'Wood Elf')
);

$stars['Fezglitch'] = array (
    'id'            => -50, 
    'ma'            => 4,
    'st'            => 7,
    'ag'            => 3,
    'av'            => 7,
    'Def skills'    => array ('Loner', 'Ball & Chain', 'Disturbing Presence', 'Foul Appearance', 'No Hands', 'Secret Weapon'),
    'cost'          => 80000,
    'icon'          => 'star',
    'teams'         => array('Skaven', 'Underworld')
);
$stars['Glart Smashrip Jr.'] = array (
    'id'            => -51, 
    'ma'            => 7,
    'st'            => 4,
    'ag'            => 3,
    'av'            => 8,
    'Def skills'    => array ('Loner', 'Block', 'Claw', 'Juggernaut'),
    'cost'          => 200000,
    'icon'          => 'star',
    'teams'         => array('Skaven', 'Underworld')
);
$stars['Humerus Carpal'] = array (
    'id'            => -52, 
    'ma'            => 7,
    'st'            => 2,
    'ag'            => 3,
    'av'            => 7,
    'Def skills'    => array ('Loner', 'Catch', 'Dodge', 'Regeneration', 'Nerves of Steel'),
    'cost'          => 130000,
    'icon'          => 'star',
    'teams'         => array('Khemri')
);
$stars['Ithaca Benoin'] = array (
    'id'            => -53, 
    'ma'            => 7,
    'st'            => 3,
    'ag'            => 3,
    'av'            => 7,
    'Def skills'    => array ('Loner', 'Accurate', 'Dump-Off', 'Nerves of Steel', 'Pass', 'Regeneration', 'Sure Hands'),
    'cost'          => 220000,
    'icon'          => 'star',
    'teams'         => array('Dark Elf', 'Khemri')
);
$stars['J Earlice'] = array (
    'id'            => -54, 
    'ma'            => 8,
    'st'            => 3,
    'ag'            => 3,
    'av'            => 7,
    'Def skills'    => array ('Loner', 'Catch', 'Diving Catch', 'Dodge', 'Sprint'),
    'cost'          => 180000,
    'icon'          => 'star',
    'teams'         => array('Necromantic', 'Undead', 'Vampire')
);
$stars['Lewdgrip Whiparm'] = array (
    'id'            => -55, 
    'ma'            => 6,
    'st'            => 3,
    'ag'            => 3,
    'av'            => 9,
    'Def skills'    => array ('Loner', 'Pass', 'Strong Arm', 'Sure Hands', 'Tentacles'),
    'cost'          => 150000,
    'icon'          => 'star',
    'teams'         => array('Chaos', 'Nurgle', 'Chaos Pact')
);
$stars['Lottabottol'] = array (
    'id'            => -56, 
    'ma'            => 8,
    'st'            => 3,
    'ag'            => 3,
    'av'            => 8,
    'Def skills'    => array ('Loner', 'Catch', 'Diving Tackle', 'Jump Up', 'Leap', 'Pass Block', 'Shadowing', 'Very Long Legs'),
    'cost'          => 220000,
    'icon'          => 'star',
    'teams'         => array('Lizardman', 'Slann')
);
$stars['Quetzal Leap'] = array (
    'id'            => -57, 
    'ma'            => 8,
    'st'            => 2,
    'ag'            => 4,
    'av'            => 7,
    'Def skills'    => array ('Loner', 'Catch', 'Diving Catch', 'Fend', 'Kick-off Return', 'Leap', 'Nerves of Steel', 'Very Long Legs'),
    'cost'          => 250000,
    'icon'          => 'star',
    'teams'         => array('Lizardman', 'Slann')
);
$stars['Roxanna Darknail'] = array (
    'id'            => -58, 
    'ma'            => 8,
    'st'            => 3,
    'ag'            => 5,
    'av'            => 7,
    'Def skills'    => array ('Loner', 'Dodge', 'Frenzy', 'Jump Up', 'Juggernaut', 'Leap'),
    'cost'          => 250000,
    'icon'          => 'star',
    'teams'         => array('Amazon', 'Dark Elf')
);
$stars['Sinnedbad'] = array (
    'id'            => -59, 
    'ma'            => 6,
    'st'            => 3,
    'ag'            => 2,
    'av'            => 7,
    'Def skills'    => array ('Loner', 'Block', 'Jump Up', 'Pass Block', 'Regeneration', 'Secret Weapon', 'Side Step', 'Stab'),
    'cost'          => 80000,
    'icon'          => 'star',
    'teams'         => array('Khemri', 'Undead')
);
$stars['Soaren Hightower'] = array (
    'id'            => -60, 
    'ma'            => 6,
    'st'            => 3,
    'ag'            => 4,
    'av'            => 8,
    'Def skills'    => array ('Loner', 'Fend', 'Kick-off Return', 'Pass', 'Safe Throw', 'Sure Hands', 'Strong Arm'),
    'cost'          => 180000,
    'icon'          => 'star',
    'teams'         => array('High Elf')
);
$stars['Willow Rosebark'] = array (
    'id'            => -61, 
    'ma'            => 5,
    'st'            => 4,
    'ag'            => 3,
    'av'            => 8,
    'Def skills'    => array ('Loner', 'Dauntless', 'Side Step', 'Thick Skull'),
    'cost'          => 150000,
    'icon'          => 'star',
    'teams'         => array('Amazon', 'Halfling', 'Wood Elf')
);



//
// Changes to LRB5 star players in LRB5b
//

$stars['Bomber Dribblesnot']['Def skills']          = array ('Loner', 'Accurate', 'Bombardier', 'Dodge', 'Right Stuff', 'Secret Weapon', 'Stunty');
$stars['Bomber Dribblesnot']['teams']               = array ('Goblin', 'Ogre', 'Orc', 'Chaos Pact', 'Underworld');
$stars['Boomer Eziasson']['Def skills']             = array ('Loner', 'Accurate', 'Block', 'Bombardier', 'Secret Weapon', 'Thick Skull');
$stars['Count Luthor Von Drakenborg']['Def skills'] = array ('Loner', 'Block', 'Hypnotic Gaze', 'Regeneration', 'Side Step');
$stars['Flint Churnblade']['Def skills']            = array ('Loner', 'Block', 'Chainsaw', 'Secret Weapon', 'Thick Skull');
$stars['Flint Churnblade']['cost']                  = 130000;
$stars['Grim Ironjaw']['Def skills']                = array ('Loner', 'Block', 'Dauntless', 'Frenzy', 'Multiple Block', 'Thick Skull');
$stars['Hack Enslash']['Def skills']                = array ('Loner', 'Chainsaw', 'Regeneration', 'Secret Weapon', 'Side Step');
$stars['Hack Enslash']['cost']                      = 120000;
$stars['Helmut Wulf']['Def skills']                 = array ('Loner', 'Chainsaw', 'Secret Weapon', 'Stand Firm');
$stars['Helmut Wulf']['cost']                       = 110000;
$stars['Helmut Wulf']['teams']                      = array ('Amazon', 'Human', 'Lizardman', 'Norse', 'Slann', 'Vampire');
$stars['Hemlock']['teams']                          = array ('Lizardman', 'Slann');
$stars['Hthark the Unstoppable']['cost']            = 330000;
$stars['Icepelt Hammerblow']['Def skills']          = array ('Loner', 'Claws', 'Disturbing Presence', 'Frenzy', 'Regeneration', 'Thick Skull');
$stars['Jordell Freshbreeze']['cost']               = 260000;
$stars['Lord Borak the Despoiler']['cost']          = 300000;
$stars['Max Spleenripper']['Def skills']            = array ('Loner', 'Chainsaw', 'Secret Weapon');
$stars['Max Spleenripper']['cost']                  = 130000;
$stars['Mighty Zug']['cost']                        = 260000;
$stars['Morg \'n\' Thorg']['cost']                  = 450000;
$stars['Morg \'n\' Thorg']['teams']                 = array ('Amazon', 'Chaos', 'Chaos Dwarf', 'Chaos Pact', 'Dark Elf', 'Dwarf', 'Elf', 'Goblin', 'Halfling', 'High Elf', 'Human', 'Lizardman', 'Orc', 'Norse', 'Nurgle', 'Ogre', 'Vampire', 'Skaven', 'Slann', 'Underworld', 'Wood Elf');
$stars['Nobbla Blackwart']['Def skills']            = array ('Loner', 'Block', 'Dodge', 'Chainsaw', 'Secret Weapon', 'Stunty');
$stars['Nobbla Blackwart']['cost']                  = 130000;
$stars['Nobbla Blackwart']['teams']                 = array ('Chaos Dwarf', 'Goblin', 'Ogre', 'Underworld');
$stars['Ripper']['teams']                           = array ('Goblin', 'Orc');
$stars['Scrappa Sorehead']['Def skills']            = array ('Loner', 'Dirty Player', 'Dodge', 'Leap', 'Right Stuff', 'Sprint', 'Stunty', 'Sure Feet', 'Very Long Legs');
$stars['Scrappa Sorehead']['cost']                  = 150000;
$stars['Skitter Stab-Stab']['teams']                = array ('Skaven', 'Underworld');
$stars['Slibli']['teams']                           = array ('Lizardman', 'Slann');
$stars['Ugroth Bolgrot']['Def skills']              = array ('Loner', 'Chainsaw', 'Secret Weapon');
$stars['Ugroth Bolgrot']['cost']                    = 100000;
$stars['Ugroth Bolgrot']['teams']                   = array ('Orc', 'Chaos Pact');
$stars['Varag Ghoul-Chewer']['cost']                = 290000;
$stars['Zara the Slayer']['teams']                  = array ('Amazon', 'Dwarf', 'Halfling', 'High Elf', 'Human', 'Norse', 'Wood Elf');
$stars['Zzarg Madeye']['teams']                     = array ('Chaos Dwarf', 'Chaos Pact');

ksort($stars, SORT_STRING);

?>
