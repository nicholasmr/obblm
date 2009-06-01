<?php

$DEA = array (
	'Amazon'	=> array (
		'other'	=> array (
			'RerollCost' => 50000,
			'icon' => RACE_ICONS.'/amazon.png',
			'race_id' => 0, # (Amazon)
		),
		'players'	=> array (
			'Linewoman'	=> array (
				'ma'        	=> 6,
				'st'        	=> 3,
				'ag'        	=> 3,
				'av'        	=> 7,
				'Def skills'	=> array ('Dodge'),
				'N skills'		=> array ('General'),
				'D skills'		=> array ('Agility', 'Strength', 'Passing'),
				'qty'			=> 16,
				'cost'			=> 50000,
				'icon'			=> 'amlineman1an'
				
			),
			'Thrower'	=> array (
				'ma'        	=> 6,
				'st'        	=> 3,
				'ag'        	=> 3,
				'av'        	=> 7,
				'Def skills'	=> array ('Dodge', 'Pass'),
				'N skills'		=> array ('General', 'Passing'),
				'D skills'		=> array ('Agility', 'Strength'),
				'qty'			=> 2,
				'cost'			=> 70000,
				'icon'			=> 'amthrower1an'
			),
			'Catcher'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 3,
 				'ag'        	=> 3,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Dodge', 'Catch'),
 				'N skills'		=> array ('General', 'Agility'),
 				'D skills'		=> array ('Strength', 'Passing'),
 				'qty'			=> 2,
				'cost'			=> 70000,
				'icon'			=> 'amcatcher1an'
			),
			'Blitzer'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 3,
 				'ag'        	=> 3,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Dodge', 'Block'),
 				'N skills'		=> array ('General', 'Strength'),
 				'D skills'		=> array ('Agility', 'Passing'),
 				'qty'			=> 4,
				'cost'			=> 90000,
				'icon'			=> 'amblitzer1an'
			)
		)
	),

	'Chaos'	=> array (
		'other'	=> array (
			'RerollCost' => 60000,
			'icon' => RACE_ICONS.'/chaos.png',
			'race_id' => 1, # (Chaos)
		),
		'players'	=> array (
			'Beastman'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 3,
 				'ag'        	=> 3,
 				'av'        	=> 8,
 				'Def skills'	=> array ('Horns'),
 				'N skills'		=> array ('General', 'Strength', 'Mutation'),
 				'D skills'		=> array ('Agility', 'Passing'),
 				'qty'			=> 16,
				'cost'			=> 60000,
				'icon'			=> 'cbeastman1an'
			),
			'Chaos Warrior'	=> array (
				'ma'        	=> 5,
 				'st'        	=> 4,
 				'ag'        	=> 3,
 				'av'        	=> 9,
 				'Def skills'	=> array (),
 				'N skills'		=> array ('General', 'Strength', 'Mutation'),
 				'D skills'		=> array ('Agility', 'Passing'),
 				'qty'			=> 4,
				'cost'			=> 100000,
				'icon'			=> 'cwarrior4an'
			),
			'Minotaur'	=> array (
				'ma'        	=> 5,
 				'st'        	=> 5,
 				'ag'        	=> 2,
 				'av'        	=> 8,
 				'Def skills'	=> array ('Loner', 'Frenzy', 'Horns', 'Mighty Blow', 'Thick Skull', 'Wild Animal'),
 				'N skills'		=> array ('Strength', 'Mutation'),
 				'D skills'		=> array ('General', 'Agility', 'Passing'),
 				'qty'			=> 1,
				'cost'			=> 150000,
				'icon'			=> 'minotaur2an'
			)
		)	
	),
	
	'Chaos Dwarf'	=> array (
		'other'	=> array (
			'RerollCost' => 70000,
			'icon' => RACE_ICONS.'/chaosdwarf.png',
			'race_id' => 2, # (Chaos Dwarf)
		),
		'players'	=> array (
			'Hobgoblin'	=> array (
				'ma'        	=> 6,
					'st'        	=> 3,
					'ag'        	=> 3,
					'av'        	=> 7,
					'Def skills'	=> array (),
					'N skills'		=> array ('General'),
					'D skills'		=> array ('Agility', 'Strength', 'Passing'),
					'qty'			=> 16,
				'cost'			=> 40000,
				'icon'			=> 'cdhobgoblin1an'
				
			),
			'Chaos Dwarf Blocker'	=> array (
				'ma'        	=> 4,
					'st'        	=> 3,
					'ag'        	=> 2,
					'av'        	=> 9,
					'Def skills'	=> array ('Block', 'Tackle', 'Thick Skull'),
					'N skills'		=> array ('General', 'Strength'),
					'D skills'		=> array ('Agility', 'Passing', 'Mutation'),
					'qty'			=> 6,
				'cost'			=> 70000,
				'icon'			=> 'cddwarf1an'
			),
			'Bull Centaur'	=> array (
				'ma'        	=> 6,
					'st'        	=> 4,
					'ag'        	=> 2,
					'av'        	=> 9,
					'Def skills'	=> array ('Sprint', 'Sure Feet', 'Thick Skull'),
					'N skills'		=> array ('General', 'Strength'),
					'D skills'		=> array ('Agility', 'Passing'),
					'qty'			=> 2,
				'cost'			=> 130000,
				'icon'			=> 'centaur1an'
			),
			'Minotaur'	=> array (
				'ma'        	=> 5,
					'st'        	=> 5,
					'ag'        	=> 2,
					'av'        	=> 8,
					'Def skills'	=> array ('Loner', 'Frenzy', 'Horns', 'Mighty Blow', 'Thick Skull', 'Wild Animal'),
					'N skills'		=> array ('Strength'),
					'D skills'		=> array ('General', 'Agility', 'Passing', 'Mutation'),
					'qty'			=> 1,
				'cost'			=> 150000,
				'icon'			=> 'minotaur2an'
			)
		)
	),

	'Dark Elf'	=> array (
		'other'	=> array (
			'RerollCost' => 50000,
			'icon' => RACE_ICONS.'/darkelf.png',
			'race_id' => 3, # (Dark Elf)
		),
		'players'	=> array (
			'Lineman'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 3,
 				'ag'        	=> 4,
 				'av'        	=> 8,
 				'Def skills'	=> array (),
 				'N skills'		=> array ('General', 'Agility'),
 				'D skills'		=> array ('Strength', 'Passing'),
 				'qty'			=> 16,
				'cost'			=> 70000,
				'icon'			=> 'delineman1an'
			),
			'Runner'	=> array (
				'ma'        	=> 7,
 				'st'        	=> 3,
 				'ag'        	=> 4,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Dump-Off'),
 				'N skills'		=> array ('General', 'Agility', 'Passing'),
 				'D skills'		=> array ('Strength'),
 				'qty'			=> 2,
				'cost'			=> 80000,
				'icon'			=> 'deblitzer1'
			),
			'Assassin'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 3,
 				'ag'        	=> 4,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Shadowing', 'Stab'),
 				'N skills'		=> array ('General', 'Agility'),
 				'D skills'		=> array ('Strength', 'Passing'),
 				'qty'			=> 2,
				'cost'			=> 90000,
				'icon'			=> 'delineman1'
			),
			'Blitzer'	=> array (
				'ma'        	=> 7,
 				'st'        	=> 3,
 				'ag'        	=> 4,
 				'av'        	=> 8,
 				'Def skills'	=> array ('Block'),
 				'N skills'		=> array ('General', 'Agility'),
 				'D skills'		=> array ('Strength', 'Passing'),
 				'qty'			=> 4,
				'cost'			=> 100000,
				'icon'			=> 'deblitzer1an'
			),
			'Witch Elf'	=> array (
				'ma'        	=> 7,
 				'st'        	=> 3,
 				'ag'        	=> 4,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Frenzy', 'Dodge', 'Jump Up'),
 				'N skills'		=> array ('General', 'Agility'),
 				'D skills'		=> array ('Strength', 'Passing'),
 				'qty'			=> 2,
				'cost'			=> 110000,
				'icon'			=> 'dewitchelf1an'
			)
		)
	),
	
	'Dwarf'	=> array (
		'other'	=> array (
			'RerollCost' => 40000,
			'icon' => RACE_ICONS.'/dwarf.png',
			'race_id' => 4, # (Dwarf)
		),
		'players'	=> array (
			'Blocker'	=> array (
				'ma'        	=> 4,
 				'st'        	=> 3,
 				'ag'        	=> 2,
 				'av'        	=> 9,
 				'Def skills'	=> array ('Block', 'Tackle', 'Thick Skull'),
 				'N skills'		=> array ('General', 'Strength'),
 				'D skills'		=> array ('Agility', 'Passing'),
 				'qty'			=> 16,
				'cost'			=> 70000,
				'icon'			=> 'dlongbeard1an'
			),
			'Runner'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 3,
 				'ag'        	=> 3,
 				'av'        	=> 8,
 				'Def skills'	=> array ('Sure Hands', 'Thick Skull'),
 				'N skills'		=> array ('General', 'Passing'),
 				'D skills'		=> array ('Agility', 'Strength'),
 				'qty'			=> 2,
				'cost'			=> 80000,
				'icon'			=> 'drunner1an'
			),
			'Blitzer'	=> array (
				'ma'        	=> 5,
 				'st'        	=> 3,
 				'ag'        	=> 3,
 				'av'        	=> 9,
 				'Def skills'	=> array ('Block', 'Thick Skull'),
 				'N skills'		=> array ('General', 'Strength'),
 				'D skills'		=> array ('Agility', 'Passing'),
 				'qty'			=> 2,
				'cost'			=> 80000,
				'icon'			=> 'dblitzer1an'
			),
			'Troll Slayer'	=> array (
				'ma'        	=> 5,
 				'st'        	=> 3,
 				'ag'        	=> 2,
 				'av'        	=> 8,
 				'Def skills'	=> array ('Block', 'Dauntless', 'Frenzy', 'Thick Skull'),
 				'N skills'		=> array ('General', 'Strength'),
 				'D skills'		=> array ('Agility', 'Passing'),
 				'qty'			=> 2,
				'cost'			=> 90000,
				'icon'			=> 'dslayer1an'
			),
			'Deathroller'	=> array (
				'ma'        	=> 4,
 				'st'        	=> 7,
 				'ag'        	=> 1,
 				'av'        	=> 10,
 				'Def skills'	=> array ('Break Tackle', 'Dirty Player', 'Juggernaut', 'Mighty Blow', 'No Hands', 'Secret Weapon', 'Stand Firm'),
 				'N skills'		=> array ('General', 'Strength'),
 				'D skills'		=> array ('Agility', 'Passing'),
 				'qty'			=> 1,
				'cost'			=> 160000,
				'icon'			=> 'ddeathroller1an'
			)
		)
	),
	
	'Elf'	=> array (
		'other'	=> array (
			'RerollCost' => 50000,
			'icon' => RACE_ICONS.'/elf.png',
			'race_id' => 5, # (Elf)
		),
		'players'	=> array (
			'Lineman'	=> array (
				'ma'        	=> 6,
					'st'        	=> 3,
					'ag'        	=> 4,
					'av'        	=> 7,
					'Def skills'	=> array (),
					'N skills'		=> array ('General', 'Agility'),
					'D skills'		=> array ('Strength', 'Passing'),
					'qty'			=> 16,
				'cost'			=> 60000,
				'icon'			=> 'welineman1an'
				
			),
			'Thrower'	=> array (
				'ma'        	=> 6,
					'st'        	=> 3,
					'ag'        	=> 4,
					'av'        	=> 7,
					'Def skills'	=> array ('Pass'),
					'N skills'		=> array ('General', 'Agility', 'Passing'),
					'D skills'		=> array ('Strength'),
					'qty'			=> 2,
				'cost'			=> 70000,
				'icon'			=> 'wethrower1an'
				
			),
			'Catcher'	=> array (
				'ma'        	=> 8,
					'st'        	=> 3,
					'ag'        	=> 4,
					'av'        	=> 7,
					'Def skills'	=> array ('Catch', 'Nerves of Steel'),
					'N skills'		=> array ('General', 'Agility'),
					'D skills'		=> array ('Strength', 'Passing'),
					'qty'			=> 4,
				'cost'			=> 100000,
				'icon'			=> 'wecatcher1an'
				
			),
			'Blitzer'	=> array (
				'ma'        	=> 7,
					'st'        	=> 3,
					'ag'        	=> 4,
					'av'        	=> 8,
					'Def skills'	=> array ('Block', 'Side step'),
					'N skills'		=> array ('General', 'Agility'),
					'D skills'		=> array ('Strength', 'Passing'),
					'qty'			=> 2,
				'cost'			=> 110000,
				'icon'			=> 'weblitzer1an'
				
			)
		)
	),
	
	'Goblin'	=> array (
		'other'	=> array (
			'RerollCost' => 60000,
			'icon' => RACE_ICONS.'/goblin.png',
			'race_id' => 6, # (Goblin)
		),
		'players'	=> array (
			'Goblin'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 2,
 				'ag'        	=> 3,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Dodge', 'Right Stuff', 'Stunty'),
 				'N skills'		=> array ('Agility'),
 				'D skills'		=> array ('General', 'Strength', 'Passing'),
 				'qty'			=> 16,
				'cost'			=> 40000,
				'icon'			=> 'goblin4an'
			),
			'Bombardier'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 2,
 				'ag'        	=> 3,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Bombardier', 'Dodge', 'No Hands', 'Secret Weapon', 'Stunty'),
 				'N skills'		=> array ('Agility'),
 				'D skills'		=> array ('General', 'Strength', 'Passing'),
 				'qty'			=> 1,
				'cost'			=> 40000,
				'icon'			=> 'goblin3an'
			),
			'Pogoer'	=> array (
				'ma'        	=> 7,
 				'st'        	=> 2,
 				'ag'        	=> 3,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Dirty Player', 'Dodge', 'Leap', 'Secret Weapon', 'Stunty', 'Very Long Legs'),
 				'N skills'		=> array ('Agility'),
 				'D skills'		=> array ('General', 'Strength', 'Passing'),
 				'qty'			=> 1,
				'cost'			=> 40000,
				'icon'			=> 'goblin5an'
			),
			'Looney'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 2,
 				'ag'        	=> 3,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Chainsaw', 'No Hands', 'Secret Weapon', 'Stunty'),
 				'N skills'		=> array ('Agility'),
 				'D skills'		=> array ('General', 'Strength', 'Passing'),
 				'qty'			=> 1,
				'cost'			=> 40000,
				'icon'			=> 'goblin2an'
			),
			'Fanatic'	=> array (
				'ma'        	=> 3,
 				'st'        	=> 7,
 				'ag'        	=> 3,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Ball & Chain', 'No Hands', 'Secret Weapon', 'Stunty'),
 				'N skills'		=> array ('Strength'),
 				'D skills'		=> array ('General', 'Agility', 'Passing'),
 				'qty'			=> 1,
				'cost'			=> 70000,
				'icon'			=> 'goball1an'
			),
			'Troll'	=> array (
				'ma'        	=> 4,
 				'st'        	=> 5,
 				'ag'        	=> 1,
 				'av'        	=> 9,
 				'Def skills'	=> array ('Loner', 'Always Hungry', 'Mighty Blow', 'Really Stupid', 'Regeneration', 'Throw Team-Mate'),
 				'N skills'		=> array ('Strength'),
 				'D skills'		=> array ('General','Agility', 'Passing'),
 				'qty'			=> 2,
				'cost'			=> 110000,
				'icon'			=> 'troll1an'
			)
		)
	),
	
	'Halfling'	=> array (
		'other'	=> array (
			'RerollCost' => 60000,
			'icon' => RACE_ICONS.'/halfling.png',
			'race_id' => 7, # (Halfling)
		),
		'players'	=> array (
			'Halfling'	=> array (
				'ma'        	=> 5,
 				'st'        	=> 2,
 				'ag'        	=> 3,
 				'av'        	=> 6,
 				'Def skills'	=> array ('Dodge', 'Right Stuff', 'Stunty'),
 				'N skills'		=> array ('Agility'),
 				'D skills'		=> array ('General', 'Strength', 'Passing'),
 				'qty'			=> 16,
				'cost'			=> 30000,
				'icon'			=> 'halfling3an'
			),
			'Treeman'	=> array (
				'ma'        	=> 2,
 				'st'        	=> 6,
 				'ag'        	=> 1,
 				'av'        	=> 10,
 				'Def skills'	=> array ('Loner', 'Mighty Blow', 'Stand Firm', 'Strong Arm', 'Take Root', 'Thick Skull', 'Throw Team-Mate'),
 				'N skills'		=> array ('Strength'),
 				'D skills'		=> array ('General', 'Agility', 'Passing'),
 				'qty'			=> 2,
				'cost'			=> 120000,
				'icon'			=> 'treeman1an'
			)
		)
	),
	
	
	
	'High Elf'	=> array (
		'other'	=> array (
			'RerollCost' => 50000,
			'icon' => RACE_ICONS.'/highelf.png',
			'race_id' => 8, # (High Elf)
		),
		'players'	=> array (
			'Lineman'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 3,
 				'ag'        	=> 4,
 				'av'        	=> 8,
 				'Def skills'	=> array (),
 				'N skills'		=> array ('General', 'Agility'),
 				'D skills'		=> array ('Strength', 'Passing'),
 				'qty'			=> 16,
				'cost'			=> 70000,
				'icon'			=> 'helineman1an'
			),
			'Thrower'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 3,
 				'ag'        	=> 4,
 				'av'        	=> 8,
 				'Def skills'	=> array ('Pass', 'Safe Throw'),
 				'N skills'		=> array ('General', 'Agility', 'Passing'),
 				'D skills'		=> array ('Strength'),
 				'qty'			=> 2,
				'cost'			=> 90000,
				'icon'			=> 'hethrower1an'
			),
			'Catcher'	=> array (
				'ma'        	=> 8,
 				'st'        	=> 3,
 				'ag'        	=> 4,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Catch'),
 				'N skills'		=> array ('General', 'Agility'),
 				'D skills'		=> array ('Strength', 'Passing'),
 				'qty'			=> 4,
				'cost'			=> 90000,
				'icon'			=> 'hecatcher1an'
			),
			'Blitzer'	=> array (
				'ma'        	=> 7,
 				'st'        	=> 3,
 				'ag'        	=> 4,
 				'av'        	=> 8,
 				'Def skills'	=> array ('Block'),
 				'N skills'		=> array ('General', 'Agility'),
 				'D skills'		=> array ('Strength', 'Passing'),
 				'qty'			=> 2,
				'cost'			=> 100000,
				'icon'			=> 'heblitzer1an'
			)
		)
	),
	
	'Human'	=> array (
		'other'	=> array (
			'RerollCost' => 50000,
			'icon' => RACE_ICONS.'/human.png',
			'race_id' => 9, # (Human)
		),
		'players'	=> array (
			'Lineman'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 3,
 				'ag'        	=> 3,
 				'av'        	=> 8,
 				'Def skills'	=> array (),
 				'N skills'		=> array ('General'),
 				'D skills'		=> array ('Agility', 'Strength', 'Passing'),
 				'qty'			=> 16,
				'cost'			=> 50000,
				'icon'			=> 'hlineman1an'
			),
			'Catcher'	=> array (
				'ma'        	=> 8,
 				'st'        	=> 2,
 				'ag'        	=> 3,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Catch', 'Dodge'),
 				'N skills'		=> array ('General', 'Agility'),
 				'D skills'		=> array ('Strength', 'Passing'),
 				'qty'			=> 4,
				'cost'			=> 70000,
				'icon'			=> 'hcatcher1an'
			),
			'Thrower'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 3,
 				'ag'        	=> 3,
 				'av'        	=> 8,
 				'Def skills'	=> array ('Sure Hands', 'Pass'),
 				'N skills'		=> array ('General', 'Passing'),
 				'D skills'		=> array ('Agility', 'Strength'),
 				'qty'			=> 2,
				'cost'			=> 70000,
				'icon'			=> 'hthrower1an'
			),
			'Blitzer'	=> array (
				'ma'        	=> 7,
 				'st'        	=> 3,
 				'ag'        	=> 3,
 				'av'        	=> 8,
 				'Def skills'	=> array ('Block'),
 				'N skills'		=> array ('General', 'Strength'),
 				'D skills'		=> array ('Agility', 'Passing'),
 				'qty'			=> 4,
				'cost'			=> 90000,
				'icon'			=> 'hblitzer1an'
			),
			'Ogre'	=> array (
				'ma'        	=> 5,
 				'st'        	=> 5,
 				'ag'        	=> 2,
 				'av'        	=> 9,
 				'Def skills'	=> array ('Loner', 'Bone head', 'Mighty Blow', 'Thick Skull', 'Throw Team-Mate'),
 				'N skills'		=> array ('Strength'),
 				'D skills'		=> array ('General','Agility', 'Passing'),
 				'qty'			=> 1,
				'cost'			=> 140000,
				'icon'			=> 'ogre4an'
			)
		)
	),
	
	'Khemri'	=> array (
		'other'	=> array (
			'RerollCost' => 70000,
			'icon' => RACE_ICONS.'/khemri.png',
			'race_id' => 10, # (Khemri)
		),
		'players'	=> array (
			'Skeleton'	=> array (
				'ma'        	=> 5,
 				'st'        	=> 3,
 				'ag'        	=> 2,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Regeneration'),
 				'N skills'		=> array ('General'),
 				'D skills'		=> array ('Agility', 'Strength', 'Passing'),
 				'qty'			=> 16,
				'cost'			=> 30000,
				'icon'			=> 'kmskeleton1an'
			),
			'Thro-Ra'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 3,
 				'ag'        	=> 2,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Pass', 'Regeneration', 'Sure Hands'),
 				'N skills'		=> array ('General', 'Passing'),
 				'D skills'		=> array ('Agility', 'Strength'),
 				'qty'			=> 2,
				'cost'			=> 70000,
				'icon'			=> 'kmthrower1an'
			),
			'Blitz-Ra'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 3,
 				'ag'        	=> 2,
 				'av'        	=> 8,
 				'Def skills'	=> array ('Block', 'Regeneration'),
 				'N skills'		=> array ('General', 'Strength'),
 				'D skills'		=> array ('Agility', 'Passing'),
 				'qty'			=> 2,
				'cost'			=> 90000,
				'icon'			=> 'kmblitzer1an'
			),
			'Mummie'	=> array (
				'ma'        	=> 3,
 				'st'        	=> 5,
 				'ag'        	=> 1,
 				'av'        	=> 9,
 				'Def skills'	=> array ('Mighty Blow', 'Regeneration'),
 				'N skills'		=> array ('Strength'),
 				'D skills'		=> array ('General', 'Agility', 'Passing'),
 				'qty'			=> 4,
				'cost'			=> 110000,
				'icon'			=> 'kmmummy1an'
			)
		)
	),
	
	'Lizardman'	=> array (
		'other'	=> array (
			'RerollCost' => 60000,
			'icon' => RACE_ICONS.'/lizardmen.png',
			'race_id' => 11, # (Lizardman)
		),
		'players'	=> array (
			'Skink'	=> array (
				'ma'        	=> 8,
 				'st'        	=> 2,
 				'ag'        	=> 3,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Dodge', 'Stunty'),
 				'N skills'		=> array ('Agility'),
 				'D skills'		=> array ('General', 'Strength', 'Passing'),
 				'qty'			=> 16,
				'cost'			=> 60000,
				'icon'			=> 'lmskink1an'
			),
			'Saurus'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 4,
 				'ag'        	=> 1,
 				'av'        	=> 9,
 				'Def skills'	=> array (),
 				'N skills'		=> array ('General', 'Strength'),
 				'D skills'		=> array ('Agility', 'Passing'),
 				'qty'			=> 6,
				'cost'			=> 80000,
				'icon'			=> 'lmsaurus1an'
			),
			'Kroxigor'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 5,
 				'ag'        	=> 1,
 				'av'        	=> 9,
 				'Def skills'	=> array ('Loner', 'Bone-head', 'Mighty Blow', 'Prehensile Tail', 'Thick Skull'),
 				'N skills'		=> array ('Strength'),
 				'D skills'		=> array ('General', 'Agility', 'Passing'),
 				'qty'			=> 1,
				'cost'			=> 140000,
				'icon'			=> 'kroxigor1an'
			)
		)
	),
	
	'Orc'	=> array (
		'other'	=> array (
			'RerollCost' => 60000,
			'icon' => RACE_ICONS.'/orc.png',
			'race_id' => 12, # (Orc)
		),
		'players'	=> array (
			'Lineman'	=> array (
				'ma'        	=> 5,
 				'st'        	=> 3,
 				'ag'        	=> 3,
 				'av'        	=> 9,
 				'Def skills'	=> array (),
 				'N skills'		=> array ('General'),
 				'D skills'		=> array ('Agility', 'Strength', 'Passing'),
 				'qty'			=> 16,
				'cost'			=> 50000,
				'icon'			=> 'olineman1an'
			),
			'Goblin'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 2,
 				'ag'        	=> 3,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Right Stuff', 'Dodge', 'Stunty'),
 				'N skills'		=> array ('Agility'),
 				'D skills'		=> array ('General', 'Strength', 'Passing'),
 				'qty'			=> 4,
				'cost'			=> 40000,
				'icon'			=> 'goblin1an'
			),
			'Thrower'	=> array (
				'ma'        	=> 5,
 				'st'        	=> 3,
 				'ag'        	=> 3,
 				'av'        	=> 8,
 				'Def skills'	=> array ('Sure Hands', 'Pass'),
 				'N skills'		=> array ('General', 'Passing'),
 				'D skills'		=> array ('Agility', 'Strength'),
 				'qty'			=> 2,
				'cost'			=> 70000,
				'icon'			=> 'othrower1an'
			),
			'Black Orc Blocker'	=> array (
				'ma'        	=> 4,
 				'st'        	=> 4,
 				'ag'        	=> 2,
 				'av'        	=> 9,
 				'Def skills'	=> array (),
 				'N skills'		=> array ('General', 'Strength'),
 				'D skills'		=> array ('Agility', 'Passing'),
 				'qty'			=> 4,
				'cost'			=> 80000,
				'icon'			=> 'oblackorc1an'
			),
			'Blitzer'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 3,
 				'ag'        	=> 3,
 				'av'        	=> 9,
 				'Def skills'	=> array ('Block'),
 				'N skills'		=> array ('General', 'Strength'),
 				'D skills'		=> array ('Agility', 'Passing'),
 				'qty'			=> 4,
				'cost'			=> 80000,
				'icon'			=> 'oblitzer1an'
			),
			'Troll'	=> array (
				'ma'        	=> 4,
 				'st'        	=> 5,
 				'ag'        	=> 1,
 				'av'        	=> 9,
 				'Def skills'	=> array ('Loner', 'Always Hungry', 'Mighty Blow', 'Really Stupid', 'Regeneration', 'Throw Team-Mate'),
 				'N skills'		=> array ('Strength'),
 				'D skills'		=> array ('General','Agility', 'Passing'),
 				'qty'			=> 1,
				'cost'			=> 110000,
				'icon'			=> 'troll1an'
			)
		)
	),
		
	'Necromantic'	=> array (
		'other'	=> array (
			'RerollCost' => 70000,
			'icon' => RACE_ICONS.'/necromantic.png',
			'race_id' => 13, # (Necromantic)
		),
		'players'	=> array (
			'Zombie'	=> array (
				'ma'        	=> 4,
 				'st'        	=> 3,
 				'ag'        	=> 2,
 				'av'        	=> 8,
 				'Def skills'	=> array ('Regeneration'),
 				'N skills'		=> array ('General'),
 				'D skills'		=> array ('Agility', 'Strength', 'Passing'),
 				'qty'			=> 16,
				'cost'			=> 40000,
				'icon'			=> 'uzombie1'
			),
			'Ghoul'	=> array (
				'ma'        	=> 7,
 				'st'        	=> 3,
 				'ag'        	=> 3,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Dodge'),
 				'N skills'		=> array ('General', 'Agility'),
 				'D skills'		=> array ('Strength', 'Passing'),
 				'qty'			=> 2,
				'cost'			=> 70000,
				'icon'			=> 'ughoul1an'
			),
			'Wight'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 3,
 				'ag'        	=> 3,
 				'av'        	=> 8,
 				'Def skills'	=> array ('Block', 'Regeneration'),
 				'N skills'		=> array ('General', 'Strength'),
 				'D skills'		=> array ('Agility', 'Passing'),
 				'qty'			=> 2,
				'cost'			=> 90000,
				'icon'			=> 'uwight1an'
			),
			'Flesh Golem'	=> array (
				'ma'        	=> 4,
 				'st'        	=> 4,
 				'ag'        	=> 2,
 				'av'        	=> 9,
 				'Def skills'	=> array ('Regeneration', 'Stand Firm', 'Thick Skull'),
 				'N skills'		=> array ('General', 'Strength'),
 				'D skills'		=> array ('Agility', 'Passing'),
 				'qty'			=> 2,
				'cost'			=> 110000,
				'icon'			=> 'ngolem1an'
			),
			'Werewolf'	=> array (
				'ma'        	=> 8,
 				'st'        	=> 3,
 				'ag'        	=> 3,
 				'av'        	=> 8,
 				'Def skills'	=> array ('Claws', 'Frenzy', 'Regeneration'),
 				'N skills'		=> array ('General', 'Agility'),
 				'D skills'		=> array ('Strength', 'Passing'),
 				'qty'			=> 2,
				'cost'			=> 120000,
				'icon'			=> 'nwerewolf1an'
			)
		)
	),	
	
	'Norse'	=> array (
		'other'	=> array (
			'RerollCost' => 60000,
			'icon' => RACE_ICONS.'/norse.png',
			'race_id' => 14, # (Norse)
		),
		'players'	=> array (
			'Lineman'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 3,
 				'ag'        	=> 3,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Block'),
 				'N skills'		=> array ('General'),
 				'D skills'		=> array ('Agility', 'Strength', 'Passing'),
 				'qty'			=> 16,
				'cost'			=> 50000,
				'icon'			=> 'nlineman1an'
			),
			'Thrower'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 3,
 				'ag'        	=> 3,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Block', 'Pass'),
 				'N skills'		=> array ('General', 'Passing'),
 				'D skills'		=> array ('Agility', 'Strength'),
 				'qty'			=> 2,
				'cost'			=> 70000,
				'icon'			=> 'nthrower1an'
			),
			'Runner'	=> array (
				'ma'        	=> 7,
 				'st'        	=> 3,
 				'ag'        	=> 3,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Block', 'Dauntless'),
 				'N skills'		=> array ('General', 'Agility'),
 				'D skills'		=> array ('Strength', 'Passing'),
 				'qty'			=> 2,
				'cost'			=> 90000,
				'icon'			=> 'ncatcher1an'
			),
			'Berserker'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 3,
 				'ag'        	=> 3,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Block', 'Frenzy', 'Jump Up'),
 				'N skills'		=> array ('General', 'Strength'),
 				'D skills'		=> array ('Agility', 'Passing'),
 				'qty'			=> 2,
				'cost'			=> 90000,
				'icon'			=> 'ncatcher1an'
			),
			'Ulfwerener'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 4,
 				'ag'        	=> 2,
 				'av'        	=> 8,
 				'Def skills'	=> array ('Frenzy'),
 				'N skills'		=> array ('General', 'Strength'),
 				'D skills'		=> array ('Agility', 'Passing'),
 				'qty'			=> 2,
				'cost'			=> 110000,
				'icon'			=> 'nlineman2an'
			),
			'Snow Troll'	=> array (
				'ma'        	=> 5,
 				'st'        	=> 5,
 				'ag'        	=> 1,
 				'av'        	=> 8,
 				'Def skills'	=> array ('Loner', 'Claws', 'Disturbing Presence', 'Frenzy', 'Wild Animal'),
 				'N skills'		=> array ('Strength'),
 				'D skills'		=> array ('General', 'Agility', 'Passing'),
 				'qty'			=> 1,
				'cost'			=> 140000,
				'icon'			=> 'troll1an'
			)
		)
	),	
	
	'Nurgle'	=> array (
		'other'	=> array (
			'RerollCost' => 70000,
			'icon' => RACE_ICONS.'/nurgle.png',
			'race_id' => 15, # (Nurgle)
		),
		'players'	=> array (
			'Rotter'	=> array (
				'ma'        	=> 5,
 				'st'        	=> 3,
 				'ag'        	=> 3,
 				'av'        	=> 8,
 				'Def skills'	=> array ('Decay', "Nurgle's Rot"),
 				'N skills'		=> array ('General', 'Mutation'),
 				'D skills'		=> array ('Agility', 'Strength', 'Passing'),
 				'qty'			=> 16,
				'cost'			=> 40000,
				'icon'			=> 'troll2an'
			),
			'Pestigor'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 3,
 				'ag'        	=> 3,
 				'av'        	=> 8,
 				'Def skills'	=> array ('Horns', "Nurgle's Rot", "Regeneration"),
 				'N skills'		=> array ('General', 'Strength', 'Mutation'),
 				'D skills'		=> array ('Agility', 'Passing'),
 				'qty'			=> 4,
				'cost'			=> 80000,
				'icon'			=> 'troll2an'
			),
			'Nurgle Warrior'=> array (
				'ma'        	=> 4,
 				'st'        	=> 4,
 				'ag'        	=> 2,
 				'av'        	=> 9,
 				'Def skills'	=> array ('Disturbing Presence', "Foul Appearance", "Nurgle's Rot", "Regeneration"),
 				'N skills'		=> array ('General', 'Strength', 'Mutation'),
 				'D skills'		=> array ('Agility', 'Passing'),
 				'qty'			=> 4,
				'cost'			=> 110000,
				'icon'			=> 'troll2an'
			),
			'Beast of Nurgle'	=> array (
				'ma'        	=> 4,
 				'st'        	=> 5,
 				'ag'        	=> 1,
 				'av'        	=> 9,
 				'Def skills'	=> array ('Loner', 'Disturbing Presence', "Foul Appearance", 'Mighty Blow', "Nurgle's Rot", 'Really Stupid', 'Regeneration', 'Tentacles'),
 				'N skills'		=> array ('Strength'),
 				'D skills'		=> array ('General', 'Agility', 'Passing', 'Mutation'),
 				'qty'			=> 1,
				'cost'			=> 140000,
				'icon'			=> 'troll2an'
			)
		)
	),	
		
	'Ogre'	=> array (
		'other'	=> array (
			'RerollCost' => 70000,
			'icon' => RACE_ICONS.'/ogros.png',
			'race_id' => 16, # (Ogre)
		),
		'players'	=> array (
			'Snotling'	=> array (
				'ma'        	=> 5,
 				'st'        	=> 1,
 				'ag'        	=> 3,
 				'av'        	=> 5,
 				'Def skills'	=> array ('Dodge', 'Right Stuff', 'Side Step', 'Stunty', 'Titchy'),
 				'N skills'		=> array ('Agility'),
 				'D skills'		=> array ('General', 'Strength', 'Passing'),
 				'qty'			=> 16,
				'cost'			=> 20000,
				'icon'			=> 'goblin1an'
			),
			'Ogre'	=> array (
				'ma'        	=> 5,
 				'st'        	=> 5,
 				'ag'        	=> 2,
 				'av'        	=> 9,
 				'Def skills'	=> array ('Bone head', 'Mighty Blow', 'Thick Skull', 'Throw Team-Mate'),
 				'N skills'		=> array ('Strength'),
 				'D skills'		=> array ('General','Agility', 'Passing'),
 				'qty'			=> 6,
				'cost'			=> 140000,
				'icon'			=> 'ogre4an'
			)
			
		)
	),	
	
	'Undead'	=> array (
		'other'	=> array (
			'RerollCost' => 70000,
			'icon' => RACE_ICONS.'/undead.png',
			'race_id' => 17, # (Undead)
		),
		'players'	=> array (
			'Skeleton'	=> array (
				'ma'        	=> 5,
 				'st'        	=> 3,
 				'ag'        	=> 2,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Regeneration'),
 				'N skills'		=> array ('General'),
 				'D skills'		=> array ('Agility', 'Strength', 'Passing'),
 				'qty'			=> 16,
				'cost'			=> 30000,
				'icon'			=> 'kmskeleton1an'
			),
			'Zombie'	=> array (
				'ma'        	=> 4,
 				'st'        	=> 3,
 				'ag'        	=> 2,
 				'av'        	=> 8,
 				'Def skills'	=> array ('Regeneration'),
 				'N skills'		=> array ('General'),
 				'D skills'		=> array ('Agility', 'Strength', 'Passing'),
 				'qty'			=> 16,
				'cost'			=> 40000,
				'icon'			=> 'uzombie2an'
			),
			'Ghoul'	=> array (
				'ma'        	=> 7,
 				'st'        	=> 3,
 				'ag'        	=> 3,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Dodge'),
 				'N skills'		=> array ('General', 'Agility'),
 				'D skills'		=> array ('Strength', 'Passing'),
 				'qty'			=> 4,
				'cost'			=> 70000,
				'icon'			=> 'ughoul4an'
			),
			'Wight'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 3,
 				'ag'        	=> 3,
 				'av'        	=> 8,
 				'Def skills'	=> array ('Block', 'Regeneration'),
 				'N skills'		=> array ('General', 'Strength'),
 				'D skills'		=> array ('Agility', 'Passing'),
 				'qty'			=> 2,
				'cost'			=> 90000,
				'icon'			=> 'uwight2an'
			),
			'Mummie'	=> array (
				'ma'        	=> 3,
 				'st'        	=> 5,
 				'ag'        	=> 1,
 				'av'        	=> 9,
 				'Def skills'	=> array ('Mighty Blow', 'Regeneration'),
 				'N skills'		=> array ('Strength'),
 				'D skills'		=> array ('General', 'Agility', 'Passing'),
 				'qty'			=> 2,
				'cost'			=> 110000,
				'icon'			=> 'uwight2an'
			)
		)
	),	
	
		
	'Vampire'	=> array (
		'other'	=> array (
			'RerollCost' => 70000,
			'icon' => RACE_ICONS.'/vampire.png',
			'race_id' => 18, # (Vampire)
		),
		'players'	=> array (
			'Thrall'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 3,
 				'ag'        	=> 3,
 				'av'        	=> 7,
 				'Def skills'	=> array (),
 				'N skills'		=> array ('General'),
 				'D skills'		=> array ('Agility', 'Strength', 'Passing'),
 				'qty'			=> 16,
				'cost'			=> 40000,
				'icon'			=> 'vampire2an'
			),
			'Vampire'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 4,
 				'ag'        	=> 4,
 				'av'        	=> 8,
 				'Def skills'	=> array ('Blood Lust', 'Hypnotic Gaze', 'Regeneration'),
 				'N skills'		=> array ('General', 'Agility', 'Strength'),
 				'D skills'		=> array ('Passing'),
 				'qty'			=> 6,
				'cost'			=> 110000,
				'icon'			=> 'vampire1an'
			)
		)
	),
		
		
	'Skaven'	=> array (
		'other'	=> array (
			'RerollCost' => 60000,
			'icon' => RACE_ICONS.'/skaven.png',
			'race_id' => 19, # (Skaven)
		),
		'players'	=> array (
			'Linerat'	=> array (
				'ma'        	=> 7,
 				'st'        	=> 3,
 				'ag'        	=> 3,
 				'av'        	=> 7,
 				'Def skills'	=> array (),
 				'N skills'		=> array ('General'),
 				'D skills'		=> array ('Agility', 'Strength', 'Passing', 'Mutation'),
 				'qty'			=> 16,
				'cost'			=> 50000,
				'icon'			=> 'sklineman1an'
			),
			'Thrower'	=> array (
				'ma'        	=> 7,
 				'st'        	=> 3,
 				'ag'        	=> 3,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Pass', 'Sure Hands'),
 				'N skills'		=> array ('General', 'Passing'),
 				'D skills'		=> array ('Agility', 'Strength', 'Mutation'),
 				'qty'			=> 2,
				'cost'			=> 70000,
				'icon'			=> 'skthrower1an'
			),
			'Gutter Runner'	=> array (
				'ma'        	=> 9,
 				'st'        	=> 2,
 				'ag'        	=> 4,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Dodge'),
 				'N skills'		=> array ('General', 'Agility'),
 				'D skills'		=> array ('Strength', 'Passing', 'Mutation'),
 				'qty'			=> 4,
				'cost'			=> 80000,
				'icon'			=> 'skrunner1an'
			),
			'Blitzer'	=> array (
				'ma'        	=> 7,
 				'st'        	=> 3,
 				'ag'        	=> 3,
 				'av'        	=> 8,
 				'Def skills'	=> array ('Block'),
 				'N skills'		=> array ('General', 'Strength'),
 				'D skills'		=> array ('Agility', 'Passing', 'Mutation'),
 				'qty'			=> 2,
				'cost'			=> 90000,
				'icon'			=> 'skstorm1an'
			),
			'Rat Ogre'	=> array (
				'ma'        	=> 6,
 				'st'        	=> 5,
 				'ag'        	=> 2,
 				'av'        	=> 8,
 				'Def skills'	=> array ('Loner', 'Frenzy', 'Mighty Blow', 'Prehensile Tail', 'Wild Animal'),
 				'N skills'		=> array ('Strength'),
 				'D skills'		=> array ('General','Agility', 'Passing', 'Mutation'),
 				'qty'			=> 1,
				'cost'			=> 160000,
				'icon'			=> 'ratogre1an'
			)
		)
	),
	
	'Wood Elf'	=> array (
		'other'	=> array (
			'RerollCost' => 50000,
			'icon' => RACE_ICONS.'/woodelf.png',
			'race_id' => 20, # (Wood Elf)
		),
		'players'	=> array (
			'Lineman'	=> array (
				'ma'        	=> 7,
 				'st'        	=> 3,
 				'ag'        	=> 4,
 				'av'        	=> 7,
 				'Def skills'	=> array (),
 				'N skills'		=> array ('Agility','General'),
 				'D skills'		=> array ('Strength', 'Passing'),
 				'qty'			=> 16,
				'cost'			=> 70000,
				'icon'			=> 'welineman1an'
			),
			'Catcher'	=> array (
				'ma'        	=> 9,
 				'st'        	=> 2,
 				'ag'        	=> 4,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Catch','Dodge'),
 				'N skills'		=> array ('Agility','General'),
 				'D skills'		=> array ('Strength', 'Passing'),
 				'qty'			=> 4,
				'cost'			=> 90000,
				'icon'			=> 'wecatcher1an'
			),
			'Thrower'	=> array (
				'ma'        	=> 7,
 				'st'        	=> 3,
 				'ag'        	=> 4,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Pass'),
 				'N skills'		=> array ('Agility','General', 'Passing'),
 				'D skills'		=> array ('Strength'),
 				'qty'			=> 2,
				'cost'			=> 90000,
				'icon'			=> 'wethrower1an'
			),
			'Wardancer'	=> array (
				'ma'        	=> 8,
 				'st'        	=> 3,
 				'ag'        	=> 4,
 				'av'        	=> 7,
 				'Def skills'	=> array ('Block', 'Dodge', 'Leap'),
 				'N skills'		=> array ('Agility','General'),
 				'D skills'		=> array ('Strength', 'Passing'),
 				'qty'			=> 2,
				'cost'			=> 120000,
				'icon'			=> 'weblitzer1an'
			),
			'Treeman'	=> array (
				'ma'        	=> 2,
 				'st'        	=> 6,
 				'ag'        	=> 1,
 				'av'        	=> 10,
 				'Def skills'	=> array ('Loner', 'Mighty Blow', 'Stand Firm', 'Strong Arm', 'Take Root', 'Thick Skull', 'Throw Team-Mate'),
 				'N skills'		=> array ('Strength'),
 				'D skills'		=> array ('General','Agility', 'Passing'),
 				'qty'			=> 1,
				'cost'			=> 120000,
				'icon'			=> 'treeman1an'
			)
		)
	)
);

$stars = array(

   /*
       Note: The numbering of star ids must begin at the value specified in ID_STARS_BEGIN from the header file
       Note: Do never change star ids after using stars in OBBLM.
   */

   'Barik Farblast' => array (
       'id'            => -5,
       'ma'            => 6,
       'st'            => 3,
       'ag'            => 3,
       'av'            => 8,
       'Def skills'    => array ('Loner', 'Hail Mary Pass', 'Pass', 'Secret Weapon', 'Strong Arm', 'Sure Hands', 'Thick Skull'),
       'cost'          => 60000,
       'icon'          => 'star',
       'teams'         => array('Dwarf'),
   ),
   'Brick Far\'th (+ Grotty)' => array (
       'id'            => -6,
       'ma'            => 5,
       'st'            => 5,
       'ag'            => 2,
       'av'            => 9,
       'Def skills'    => array ('Loner', 'Bone-head', 'Mighty Blow', 'Nerves of Steel', 'Strong Arm', 'Thick Skull', 'Throw Team-Mate'),
       'cost'          => 290000,
       'icon'          => 'star',
       'teams'         => array('Chaos', 'Nurgle', 'Ogre'),
   ),
   'Grotty (included in Brick Far\'th)' => array (
       'id'            => -7,
       'ma'            => 6,
       'st'            => 2,
       'ag'            => 4,
       'av'            => 7,
       'Def skills'    => array ('Loner', 'Dodge', 'Right Stuff', 'Stunty'),
       'cost'          => 0,
       'icon'          => 'star',
       'teams'         => array('Chaos', 'Nurgle', 'Ogre'),
   ),
   'Bomber Dribblesnot' => array (
       'id'            => -8,
       'ma'            => 6,
       'st'            => 2,
       'ag'            => 3,
       'av'            => 7,
       'Def skills'    => array ('Loner', 'Accurate', 'Bombardier', 'Dodge', 'No Hands', 'Right Stuff', 'Secret Weapon', 'Stunty'),
       'cost'          => 60000,
       'icon'          => 'star',
       'teams'         => array('Goblin', 'Orc'),
   ),
   'Boomer Eziasson' => array (
       'id'            => -9,
       'ma'            => 4,
       'st'            => 3,
       'ag'            => 2,
       'av'            => 9,
       'Def skills'    => array ('Loner', 'Accurate', 'Block', 'Bombardier', 'No Hands', 'Secret Weapon', 'Thick Skull'),
       'cost'          => 60000,
       'icon'          => 'star',
       'teams'         => array('Dwarf', 'Norse'),
   ),
   'Count Luthor Von Drakenborg' => array (
       'id'            => -10,
       'ma'            => 6,
       'st'            => 5,
       'ag'            => 4,
       'av'            => 9,
       'Def skills'    => array ('Loner', 'Block', 'Dodge', 'Hypnotic Gaze', 'Regeneration'),
       'cost'          => 390000,
       'icon'          => 'star',
       'teams'         => array('Necromantic', 'Undead', 'Vampire'),
   ),
   'Deeproot Strongbranch' => array (
       'id'            => -11,
       'ma'            => 2,
       'st'            => 7,
       'ag'            => 1,
       'av'            => 10,
       'Def skills'    => array ('Loner', 'Block', 'Mighty Blow', 'Stand Firm', 'Strong Arm', 'Thick Skull', 'Throw Team-Mate'),
       'cost'          => 250000,
       'icon'          => 'star',
       'teams'         => array('Halfling'),
   ),
   'Eldril Sidewinder' => array (
       'id'            => -12,
       'ma'            => 8,
       'st'            => 3,
       'ag'            => 4,
       'av'            => 7,
       'Def skills'    => array ('Loner', 'Catch', 'Dodge', 'Hypnotic Gaze', 'Nerves of Steel', 'Pass Block'),
       'cost'          => 170000,
       'icon'          => 'star',
       'teams'         => array('Dark Elf', 'Elf', 'High Elf', 'Wood Elf'),
   ),
   'Flint Churnblade' => array (
       'id'            => -13,
       'ma'            => 5,
       'st'            => 3,
       'ag'            => 2,
       'av'            => 8,
       'Def skills'    => array ('Loner', 'Block', 'Chainsaw', 'No Hands', 'Secret Weapon', 'Thick Skull'),
       'cost'          => 100000,
       'icon'          => 'star',
       'teams'         => array('Dwarf'),
   ),
   'Fungus the Loon' => array (
       'id'            => -14,
       'ma'            => 4,
       'st'            => 7,
       'ag'            => 3,
       'av'            => 7,
       'Def skills'    => array ('Loner', 'Ball & Chain', 'Mighty Blow', 'No Hands', 'Secret Weapon', 'Stunty'),
       'cost'          => 80000,
       'icon'          => 'star',
       'teams'         => array('Goblin'),
   ),
   'Grashnak Blackhoof' => array (
       'id'            => -15,
       'ma'            => 6,
       'st'            => 6,
       'ag'            => 2,
       'av'            => 8,
       'Def skills'    => array ('Loner', 'Frenzy', 'Horns', 'Mighty Blow', 'Thick Skull'),
       'cost'          => 310000,
       'icon'          => 'star',
       'teams'         => array('Chaos', 'Chaos Dwarf', 'Nurgle'),
   ),
   'Griff Oberwald' => array (
       'id'            => -16,
       'ma'            => 7,
       'st'            => 4,
       'ag'            => 4,
       'av'            => 8,
       'Def skills'    => array ('Loner', 'Block', 'Dodge', 'Fend', 'Sprint', 'Sure Feet'),
       'cost'          => 320000,
       'icon'          => 'star',
       'teams'         => array('Human'),
   ),
   'Grim Ironjaw' => array (
       'id'            => -17,
       'ma'            => 5,
       'st'            => 4,
       'ag'            => 3,
       'av'            => 8,
       'Def skills'    => array ('Loner', 'Block', 'Dauntless', 'Frenzy', 'Mighty Blow', 'Thick Skull'),
       'cost'          => 220000,
       'icon'          => 'star',
       'teams'         => array('Dwarf'),
   ),
   'Hack Enslash' => array (
       'id'            => -18,
       'ma'            => 6,
       'st'            => 3,
       'ag'            => 2,
       'av'            => 7,
       'Def skills'    => array ('Loner', 'Chainsaw', 'No Hands', 'Regeneration', 'Secret Weapon', 'Side Step'),
       'cost'          => 90000,
       'icon'          => 'star',
       'teams'         => array('Khemri', 'Necromantic', 'Undead'),
   ),
   'Hakflem Skuttlespike' => array (
       'id'            => -19,
       'ma'            => 9,
       'st'            => 3,
       'ag'            => 4,
       'av'            => 7,
       'Def skills'    => array ('Loner', 'Dodge', 'Extra Arms', 'Prehensile Tail', 'Two Heads'),
       'cost'          => 200000,
       'icon'          => 'star',
       'teams'         => array('Skaven'),
   ),
   'Headsplitter' => array (
       'id'            => -20,
       'ma'            => 6,
       'st'            => 6,
       'ag'            => 3,
       'av'            => 8,
       'Def skills'    => array ('Loner', 'Frenzy', 'Mighty Blow', 'Prehensile Tail'),
       'cost'          => 340000,
       'icon'          => 'star',
       'teams'         => array('Skaven'),
   ),
   'Helmut Wulf' => array (
       'id'            => -21,
       'ma'            => 6,
       'st'            => 3,
       'ag'            => 3,
       'av'            => 8,
       'Def skills'    => array ('Loner', 'Chainsaw', 'No Hands', 'Secret Weapon', 'Stand Firm'),
       'cost'          => 80000,
       'icon'          => 'star',
       'teams'         => array('Amazon', 'Human', 'Lizardman', 'Norse', 'Vampire'),
   ),
   'Hemlock' => array (
       'id'            => -22,
       'ma'            => 8,
       'st'            => 2,
       'ag'            => 3,
       'av'            => 7,
       'Def skills'    => array ('Loner', 'Block', 'Dodge', 'Side Step', 'Jump Up', 'Stab', 'Stunty'),
       'cost'          => 170000,
       'icon'          => 'star',
       'teams'         => array('Lizardman'),
   ),
   'Horkon Heartripper' => array (
       'id'            => -23,
       'ma'            => 7,
       'st'            => 3,
       'ag'            => 4,
       'av'            => 7,
       'Def skills'    => array ('Loner', 'Dodge', 'Leap', 'Multiple Block', 'Shadowing', 'Stab'),
       'cost'          => 210000,
       'icon'          => 'star',
       'teams'         => array('Dark Elf'),
   ),
   'Hthark the Unstoppable' => array (
       'id'            => -24,
       'ma'            => 6,
       'st'            => 5,
       'ag'            => 2,
       'av'            => 9,
       'Def skills'    => array ('Loner', 'Block', 'Break Tackle', 'Juggernaut', 'Sprint', 'Sure Feet', 'Thick Skull'),
       'cost'          => 310000,
       'icon'          => 'star',
       'teams'         => array('Chaos Dwarf'),
   ),
   'Hubris Rakarth' => array (
       'id'            => -25,
       'ma'            => 7,
       'st'            => 4,
       'ag'            => 4,
       'av'            => 8,
       'Def skills'    => array ('Loner', 'Block', 'Dirty Player', 'Jump Up', 'Mighty Blow', 'Strip Ball'),
       'cost'          => 260000,
       'icon'          => 'star',
       'teams'         => array('Dark Elf', 'Elf'),
   ),
   'Icepelt Hammerblow' => array (
       'id'            => -26,
       'ma'            => 5,
       'st'            => 6,
       'ag'            => 1,
       'av'            => 8,
       'Def skills'    => array ('Loner', 'Claws', 'Disturbing Presence', 'Frenzy', 'Mighty Blow', 'Regeneration'),
       'cost'          => 330000,
       'icon'          => 'star',
       'teams'         => array('Norse'),
   ),
   'Jordell Freshbreeze' => array (
       'id'            => -27,
       'ma'            => 8,
       'st'            => 3,
       'ag'            => 5,
       'av'            => 7,
       'Def skills'    => array ('Loner', 'Block', 'Diving Catch', 'Dodge', 'Leap', 'Side Step'),
       'cost'          => 230000,
       'icon'          => 'star',
       'teams'         => array('Elf', 'Wood Elf'),
   ),
   'Lord Borak the Despoiler' => array (
       'id'            => -28,
       'ma'            => 5,
       'st'            => 5,
       'ag'            => 3,
       'av'            => 9,
       'Def skills'    => array ('Loner', 'Block', 'Dirty Player', 'Mighty Blow'),
       'cost'          => 270000,
       'icon'          => 'star',
       'teams'         => array('Chaos', 'Nurgle'),
   ),
   'Max Spleenripper' => array (
       'id'            => -29,
       'ma'            => 5,
       'st'            => 4,
       'ag'            => 3,
       'av'            => 8,
       'Def skills'    => array ('Loner', 'Chainsaw', 'No Hands', 'Secret Weapon'),
       'cost'          => 100000,
       'icon'          => 'star',
       'teams'         => array('Chaos', 'Nurgle'),
   ),
   'Mighty Zug' => array (
       'id'            => -30,
       'ma'            => 4,
       'st'            => 5,
       'ag'            => 2,
       'av'            => 9,
       'Def skills'    => array ('Loner', 'Block', 'Mighty Blow'),
       'cost'          => 230000,
       'icon'          => 'star',
       'teams'         => array('Human'),
   ),
   'Morg \'n\' Thorg' => array (
       'id'            => -31,
       'ma'            => 6,
       'st'            => 6,
       'ag'            => 3,
       'av'            => 10,
       'Def skills'    => array ('Loner', 'Block', 'Mighty Blow', 'Thick Skull', 'Throw Team-Mate'),
       'cost'          => 430000,
       'icon'          => 'star',
       'teams'         => array('Amazon', 'Chaos', 'Chaos Dwarf', 'Dark Elf', 'Dwarf', 'Elf', 'Goblin', 'Halfling', 'High Elf', 'Human', 'Lizardman', 'Orc', 'Norse', 'Nurgle', 'Ogre', 'Vampire', 'Skaven', 'Wood Elf'),
   ),
   'Nobbla Blackwart' => array (
       'id'            => -32,
       'ma'            => 6,
       'st'            => 2,
       'ag'            => 3,
       'av'            => 7,
       'Def skills'    => array ('Loner', 'Block', 'Dodge', 'Chainsaw', 'No Hands', 'Secret Weapon', 'Stunty'),
       'cost'          => 100000,
       'icon'          => 'star',
       'teams'         => array('Chaos Dwarf', 'Goblin', 'Ogre'),
   ),
   'Prince Moranion' => array (
       'id'            => -33,
       'ma'            => 7,
       'st'            => 4,
       'ag'            => 4,
       'av'            => 8,
       'Def skills'    => array ('Loner', 'Block', 'Dauntless', 'Tackle', 'Wrestle'),
       'cost'          => 230000,
       'icon'          => 'star',
       'teams'         => array('Elf', 'High Elf'),
   ),
   'Puggy Baconbreath' => array (
       'id'            => -34,
       'ma'            => 5,
       'st'            => 3,
       'ag'            => 3,
       'av'            => 6,
       'Def skills'    => array ('Loner', 'Block', 'Dodge', 'Nerves of Steel', 'Right Stuff', 'Stunty'),
       'cost'          => 140000,
       'icon'          => 'star',
       'teams'         => array('Halfling', 'Human'),
   ),
   'Ramtut III' => array (
       'id'            => -35,
       'ma'            => 5,
       'st'            => 6,
       'ag'            => 1,
       'av'            => 9,
       'Def skills'    => array ('Loner', 'Break Tackle', 'Mighty Blow', 'Regeneration', 'Wrestle'),
       'cost'          => 350000,
       'icon'          => 'star',
       'teams'         => array('Khemri', 'Necromantic', 'Undead'),
   ),
   'Rashnak Backstabber' => array (
       'id'            => -36,
       'ma'            => 7,
       'st'            => 3,
       'ag'            => 3,
       'av'            => 7,
       'Def skills'    => array ('Loner', 'Dodge', 'Side Step', 'Sneaky Git', 'Stab'),
       'cost'          => 200000,
       'icon'          => 'star',
       'teams'         => array('Chaos Dwarf'),
   ),
   'Ripper' => array (
       'id'            => -37,
       'ma'            => 4,
       'st'            => 6,
       'ag'            => 1,
       'av'            => 9,
       'Def skills'    => array ('Loner', 'Grab', 'Mighty Blow', 'Regeneration', 'Throw Team-Mate'),
       'cost'          => 270000,
       'icon'          => 'star',
       'teams'         => array('Chaos', 'Goblin', 'Nurgle', 'Orc'),
   ),
   'Scrappa Sorehead' => array (
       'id'            => -38,
       'ma'            => 7,
       'st'            => 2,
       'ag'            => 3,
       'av'            => 7,
       'Def skills'    => array ('Loner', 'Dirty Player', 'Dodge', 'Leap', 'Right Stuff', 'Secret Weapon', 'Sprint', 'Stunty', 'Sure Feet', 'Very Long Legs'),
       'cost'          => 50000,
       'icon'          => 'star',
       'teams'         => array('Goblin', 'Ogre', 'Orc'),
   ),
   'Setekh' => array (
       'id'            => -39,
       'ma'            => 6,
       'st'            => 4,
       'ag'            => 2,
       'av'            => 8,
       'Def skills'    => array ('Loner', 'Block', 'Break Tackle', 'Juggernaut', 'Regeneration', 'Strip Ball'),
       'cost'          => 220000,
       'icon'          => 'star',
       'teams'         => array('Khemri', 'Necromantic', 'Undead'),
   ),
   'Slibli' => array (
       'id'            => -40,
       'ma'            => 7,
       'st'            => 4,
       'ag'            => 1,
       'av'            => 9,
       'Def skills'    => array ('Loner', 'Block', 'Grab', 'Guard', 'Stand Firm'),
       'cost'          => 250000,
       'icon'          => 'star',
       'teams'         => array('Lizardman'),
   ),
   'Skitter Stab-Stab' => array (
       'id'            => -41,
       'ma'            => 9,
       'st'            => 2,
       'ag'            => 4,
       'av'            => 7,
       'Def skills'    => array ('Loner', 'Dodge', 'Prehensile Tail', 'Shadowing', 'Stab'),
       'cost'          => 160000,
       'icon'          => 'star',
       'teams'         => array('Skaven'),
   ),
   'Ugroth Bolgrot' => array (
       'id'            => -42,
       'ma'            => 5,
       'st'            => 3,
       'ag'            => 3,
       'av'            => 9,
       'Def skills'    => array ('Loner', 'Chainsaw', 'No Hands', 'Secret Weapon'),
       'cost'          => 70000,
       'icon'          => 'star',
       'teams'         => array('Orc'),
   ),
   'Varag Ghoul-Chewer' => array (
       'id'            => -43,
       'ma'            => 6,
       'st'            => 4,
       'ag'            => 3,
       'av'            => 9,
       'Def skills'    => array ('Loner', 'Block', 'Jump Up', 'Mighty Blow', 'Thick Skull'),
       'cost'          => 260000,
       'icon'          => 'star',
       'teams'         => array('Orc'),
   ),
   'Wilhelm Chaney' => array (
       'id'            => -44,
       'ma'            => 8,
       'st'            => 4,
       'ag'            => 3,
       'av'            => 8,
       'Def skills'    => array ('Loner', 'Catch', 'Claws', 'Frenzy', 'Regeneration', 'Wrestle'),
       'cost'          => 240000,
       'icon'          => 'star',
       'teams'         => array('Necromantic', 'Norse', 'Vampire'),
   ),
   'Zara the Slayer' => array (
       'id'            => -45,
       'ma'            => 6,
       'st'            => 4,
       'ag'            => 3,
       'av'            => 8,
       'Def skills'    => array ('Loner', 'Block', 'Dauntless', 'Dodge', 'Jump Up', 'Stab', 'Stakes'),
       'cost'          => 270000,
       'icon'          => 'star',
       'teams'         => array('Amazon', 'Dwarf', 'Human', 'Norse'),
   ),
   'Zzarg Madeye' => array (
       'id'            => -46,
       'ma'            => 4,
       'st'            => 4,
       'ag'            => 3,
       'av'            => 9,
       'Def skills'    => array ('Loner', 'Hail Mary Pass', 'Pass', 'Secret Weapon', 'Strong Arm', 'Sure Hands', 'Tackle', 'Thick Skull'),
       'cost'          => 60000,
       'icon'          => 'star',
       'teams'         => array('Chaos Dwarf'),
   )
);

$sparray = array (	
	'Rookie'	=> array (
		'SPP'	=> 0,
		'SPR'	=> 0	
	),
	'Experienced'	=> array (
		'SPP'	=> 6,
		'SPR'	=> 1
			
	),
	'Veteran'	=> array (
		'SPP'	=> 16,
		'SPR'	=> 2

	),
	'Emerging Star'	=> array (
		'SPP'	=> 31,
		'SPR'	=> 3	

	),
	'Star'	=> array (
		'SPP'	=> 51,
		'SPR'	=> 4	
	),
	'Super Star'	=> array (
		'SPP'	=> 76,
		'SPR'	=> 5	
	),
	'Legend'	=> array (
		'SPP'	=> 176,
		'SPR'	=> 6
	)
);

$skillarray	= array (	
	'General'	=> array (
		'Block',
		'Dauntless',
		'Dirty Player',
		'Fend',
		'Frenzy',
		'Kick',
		'Kick-Off Return',
		'Pass Block',
		'Pro',
		'Shadowing',
		'Strip Ball',
		'Sure Hands',
		'Tackle',
		'Wrestle'	
	),
	'Agility'	=> array (
		'Catch',
		'Diving Catch',
		'Diving Tackle',
		'Dodge',
		'Jump Up',
		'Leap',
		'Side Step',
		'Sneaky Git',
		'Sprint',
		'Sure Feet'
	),
	'Passing'	=> array (
		'Accurate',
		'Dump-Off',
		'Hail Mary Pass',
		'Leader',
		'Nerves of Steel',
		'Pass',
		'Safe Throw',
	),
	'Strength'	=> array (
		'Break Tackle',
		'Grab',
		'Guard',
		'Juggernaut',
		'Mighty Blow',
		'Multiple Block',
		'Piling On',
		'Stand Firm',
		'Strong Arm',
		'Thick Skull'
	),
	'Mutation'	=> array (
		'Big Hand',
		'Claw/Claws',
		'Disturbing Presence',
		'Extra Arms',
		'Foul Appearance',
		'Horns',
		'Prehensile Tail',
		'Tentacles',
		'Two Heads',
		'Very Long Legs'
	),
	'Extraordinay'	=> array (
		'Always Hungry',
		'Ball & Chain',
		'Blood Lust',
		'Bombardier',
		'Bone-Head',
		'Chainsaw',
		'Decay',
		'Fan Favourite',
		'Hypnotic Gaze',
		'Loner',
		'No Hands',
		"Nurgle's Rot",
		'Really Stupid',
		'Regeneration',
		'Right Stuff',
		'Secret Weapon',
		'Stab',
		'Stakes',
		'Stunty',
		'Take Root',
		'Throw Team-Mate',
		'Titchy',
		'Wild Animal'
	),
	'Achieved characteristics'	=> array (
		'+1 MA',
		'+1 ST',
		'+1 AG',
		'+1 AV'
	),
);

$inducements = array (
    'Bloodweiser Babes' => array (
        'cost' => 50000,
        'max'  => 2
    ),
    'Bribes' => array (
        'cost' => 100000,
        'max'  => 3
    ),
    'Extra Training' => array (
        'cost' => 100000,
        'max'  => 4
    ),
    'Halfling Master Chef' => array (
        'cost' => 300000,
        'max'  => 1
    ),
    'Wandering Apothecaries' => array (
        'cost' => 100000,
        'max'  => 2
    ),
    'Igor' => array (
        'cost' => 100000,
        'max'  => 1
    ),
    'Wizard' => array (
        'cost' => 150000,
        'max'  => 1
    )
);

?>
