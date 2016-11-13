<?php

/*
 *  Copyright (c) Daniel Straalman <email is protected> 2009-2012. All Rights Reserved.
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

require_once('lib/game_data_lrb6x.php');

// Changes to present teams/positionals from LRB5b to LRB6.

$DEA['Necromantic']['players']['Flesh Golem']['cost'] = 110000;
$DEA['Necromantic']['players']['Necromantic Werewolf'] = $DEA['Necromantic']['players']['Werewolf']; unset($DEA['Necromantic']['players']['Werewolf']);
$DEA['Norse']['players']['Norse Werewolf'] = $DEA['Norse']['players']['Ulfwerener']; unset($DEA['Norse']['players']['Ulfwerener']);
$DEA['Norse']['players']['Yhetee'] = $DEA['Norse']['players']['Snow Troll']; unset($DEA['Norse']['players']['Snow Troll']);
$DEA['Norse']['players']['Catcher'] = $DEA['Norse']['players']['Runner']; unset($DEA['Norse']['players']['Runner']);
$DEA['Norse']['players']['Blitzer'] = $DEA['Norse']['players']['Berserker']; unset($DEA['Norse']['players']['Berserker']);

// Changes in star players from LRB5b to LRB6.

$stars['Bertha Bigfist']['cost']                      = 290000;
$stars['Crazy Igor']['cost']                          = 120000;
$stars['Dolfar Longstride']['cost']                   = 150000;
$stars['Fezglitch']['cost']                           = 100000;
$stars['Glart Smashrip Jr.']['cost']                  = 210000;
$stars['Morg \'n\' Thorg']['cost']                    = 430000;
$stars['Zzharg Madeye']['cost']                       = 90000;
$stars['Deeproot Strongbranch']['cost']               = 300000;
$stars['Eldril Sidewinder']['cost']                   = 200000;
$stars['Ramtut III']['cost']                          = 380000;

// Changes to inducements

$inducements['Halfling Master Chef']['reduced_cost'] = 100000; # Old LRB5 value.

//Amazonas spanish
$DEA['Amazonas']= $DEA['Amazon']; unset($DEA['Amazon']);
$DEA['Amazonas']['players']['Linea']= $DEA['Amazonas']['players']['Linewoman']; unset($DEA['Amazonas']['players']['Linewoman']);
$DEA['Amazonas']['players']['Lanzadora']= $DEA['Amazonas']['players']['Thrower']; unset($DEA['Amazonas']['players']['Thrower']);
$DEA['Amazonas']['players']['Receptora']= $DEA['Amazonas']['players']['Catcher']; unset($DEA['Amazonas']['players']['Catcher']);

//Altos Elfos spanish
$DEA['Altos Elfos']= $DEA['High Elf']; unset($DEA['High Elf']);
$DEA['Altos Elfos']['players']['Linea']= $DEA['Altos Elfos']['players']['Lineman']; unset($DEA['Altos Elfos']['players']['Lineman']);
$DEA['Altos Elfos']['players']['Lanzador']= $DEA['Altos Elfos']['players']['Thrower']; unset($DEA['Altos Elfos']['players']['Thrower']);
$DEA['Altos Elfos']['players']['Receptor']= $DEA['Altos Elfos']['players']['Catcher']; unset($DEA['Altos Elfos']['players']['Catcher']);

//Caos spanish
$DEA['Caos']= $DEA['Chaos']; unset($DEA['Chaos']);
$DEA['Caos']['players']['Hombre Bestia']= $DEA['Caos']['players']['Beastman']; unset($DEA['Caos']['players']['Beastman']);
$DEA['Caos']['players']['Guerrero del Caos']= $DEA['Caos']['players']['Chaos Warrior']; unset($DEA['Caos']['players']['Chaos Warrior']);
$DEA['Caos']['players']['Minotauro']= $DEA['Caos']['players']['Minotaur']; unset($DEA['Caos']['players']['Minotaur']);

//Elfos Pro spanish
$DEA['Elfos']= $DEA['Elf']; unset($DEA['Elf']);
$DEA['Elfos']['players']['Linea']= $DEA['Elfos']['players']['Lineman']; unset($DEA['Elfos']['players']['Lineman']);
$DEA['Elfos']['players']['Lanzador']= $DEA['Elfos']['players']['Thrower']; unset($DEA['Elfos']['players']['Thrower']);
$DEA['Elfos']['players']['Receptor']= $DEA['Elfos']['players']['Catcher']; unset($DEA['Elfos']['players']['Catcher']);

//Elfos Oscuros spanish
$DEA['Elfos Oscuros']= $DEA['Dark Elf']; unset($DEA['Dark Elf']);
$DEA['Elfos Oscuros']['players']['Linea']= $DEA['Elfos Oscuros']['players']['Lineman']; unset($DEA['Elfos Oscuros']['players']['Lineman']);
$DEA['Elfos Oscuros']['players']['Corredor']= $DEA['Elfos Oscuros']['players']['Runner']; unset($DEA['Elfos Oscuros']['players']['Runner']);
$DEA['Elfos Oscuros']['players']['Asesino']= $DEA['Elfos Oscuros']['players']['Assassin']; unset($DEA['Elfos Oscuros']['players']['Assassin']);
$DEA['Elfos Oscuros']['players']['Bruja Elfa']= $DEA['Elfos Oscuros']['players']['Witch Elf']; unset($DEA['Elfos Oscuros']['players']['Witch Elf']);

//Elfos Silvanos spanish
$DEA['Elfos Silvanos']= $DEA['Wood Elf']; unset($DEA['Wood Elf']);
$DEA['Elfos Silvanos']['players']['Linea']= $DEA['Elfos Silvanos']['players']['Lineman']; unset($DEA['Elfos Silvanos']['players']['Lineman']);
$DEA['Elfos Silvanos']['players']['Receptor']= $DEA['Elfos Silvanos']['players']['Catcher']; unset($DEA['Elfos Silvanos']['players']['Catcher']);
$DEA['Elfos Silvanos']['players']['Lanzador']= $DEA['Elfos Silvanos']['players']['Thrower']; unset($DEA['Elfos Silvanos']['players']['Thrower']);
$DEA['Elfos Silvanos']['players']['Bailarin Guerrero']= $DEA['Elfos Silvanos']['players']['Wardancer']; unset($DEA['Elfos Silvanos']['players']['Wardancer']);
$DEA['Elfos Silvanos']['players']['Hombre Arbol']= $DEA['Elfos Silvanos']['players']['Treeman']; unset($DEA['Elfos Silvanos']['players']['Treeman']);

//Enanos spanish
$DEA['Enanos']= $DEA['Dwarf']; unset($DEA['Dwarf']);
$DEA['Enanos']['players']['Defensa']= $DEA['Enanos']['players']['Blocker']; unset($DEA['Enanos']['players']['Blocker']);
$DEA['Enanos']['players']['Corredor']= $DEA['Enanos']['players']['Runner']; unset($DEA['Enanos']['players']['Runner']);
$DEA['Enanos']['players']['Matatrolls']= $DEA['Enanos']['players']['Troll Slayer']; unset($DEA['Enanos']['players']['Troll Slayer']);
$DEA['Enanos']['players']['Apisonadora']= $DEA['Enanos']['players']['Deathroller']; unset($DEA['Enanos']['players']['Deathroller']);

//Enanos del Caos spanish
$DEA['Enanos del Caos']= $DEA['Chaos Dwarf']; unset($DEA['Chaos Dwarf']);
$DEA['Enanos del Caos']['players']['Defensa Enano del Caos']= $DEA['Enanos del Caos']['players']['Chaos Dwarf Blocker']; unset($DEA['Enanos del Caos']['players']['Chaos Dwarf Blocker']);
$DEA['Enanos del Caos']['players']['Centauro Toro']= $DEA['Enanos del Caos']['players']['Bull Centaur']; unset($DEA['Enanos del Caos']['players']['Bull Centaur']);
$DEA['Enanos del Caos']['players']['Minotauro']= $DEA['Enanos del Caos']['players']['Minotaur']; unset($DEA['Enanos del Caos']['players']['Minotaur']);

//Goblins spanish
$DEA['Goblins']= $DEA['Goblin']; unset($DEA['Goblin']);
$DEA['Goblins']['players']['Bombardero']= $DEA['Goblins']['players']['Bombardier']; unset($DEA['Goblins']['players']['Bombardier']);
$DEA['Goblins']['players']['Chiflado']= $DEA['Goblins']['players']['Looney']; unset($DEA['Goblins']['players']['Looney']);
$DEA['Goblins']['players']['Fanatico']= $DEA['Goblins']['players']['Fanatic']; unset($DEA['Goblins']['players']['Fanatic']);
$DEA['Goblins']['players']['Pogo']= $DEA['Goblins']['players']['Pogoer']; unset($DEA['Goblins']['players']['Pogoer']);

//Halflings spanish
$DEA['Halflings']= $DEA['Halfling']; unset($DEA['Halfling']);
$DEA['Halflings']['players']['Hombre Arbol']= $DEA['Halflings']['players']['Treeman']; unset($DEA['Halflings']['players']['Treeman']);

//Hombres Lagarto spanish
$DEA['Hombres Lagarto']= $DEA['Lizardman']; unset($DEA['Lizardman']);
$DEA['Hombres Lagarto']['players']['Eslizon']= $DEA['Hombres Lagarto']['players']['Skink']; unset($DEA['Hombres Lagarto']['players']['Skink']);
$DEA['Hombres Lagarto']['players']['Saurio']= $DEA['Hombres Lagarto']['players']['Saurus']; unset($DEA['Hombres Lagarto']['players']['Saurus']);

//Humanos spanish
$DEA['Humanos']= $DEA['Human']; unset($DEA['Human']);
$DEA['Humanos']['players']['Linea']= $DEA['Humanos']['players']['Lineman']; unset($DEA['Humanos']['players']['Lineman']);
$DEA['Humanos']['players']['Receptor']= $DEA['Humanos']['players']['Catcher']; unset($DEA['Humanos']['players']['Catcher']);
$DEA['Humanos']['players']['Lanzador']= $DEA['Humanos']['players']['Thrower']; unset($DEA['Humanos']['players']['Thrower']);
$DEA['Humanos']['players']['Ogro']= $DEA['Humanos']['players']['Ogre']; unset($DEA['Humanos']['players']['Ogre']);

//Khemri spanish
$DEA['Khemri']['players']['Esqueleto']= $DEA['Khemri']['players']['Skeleton']; unset($DEA['Khemri']['players']['Skeleton']);
$DEA['Khemri']['players']['Guardian de la Tumba']= $DEA['Khemri']['players']['Tomb Guardian']; unset($DEA['Khemri']['players']['Tomb Guardian']);

//Nigromantes spanish
$DEA['Nigromantes']= $DEA['Necromantic']; unset($DEA['Necromantic']);
$DEA['Nigromantes']['players']['Zombi']= $DEA['Nigromantes']['players']['Zombie']; unset($DEA['Nigromantes']['players']['Zombie']);
$DEA['Nigromantes']['players']['Necrofago']= $DEA['Nigromantes']['players']['Ghoul']; unset($DEA['Nigromantes']['players']['Ghoul']);
$DEA['Nigromantes']['players']['Tumulario']= $DEA['Nigromantes']['players']['Wight']; unset($DEA['Nigromantes']['players']['Wight']);
$DEA['Nigromantes']['players']['Hombre Lobo']= $DEA['Nigromantes']['players']['Necromantic Werewolf']; unset($DEA['Nigromantes']['players']['Necromantic Werewolf']);
$DEA['Nigromantes']['players']['Golem de Carne']= $DEA['Nigromantes']['players']['Flesh Golem']; unset($DEA['Nigromantes']['players']['Flesh Golem']);

//No Muertos spanish
$DEA['No Muertos']= $DEA['Undead']; unset($DEA['Undead']);
$DEA['No Muertos']['players']['Zombi']= $DEA['No Muertos']['players']['Zombie']; unset($DEA['No Muertos']['players']['Zombie']);
$DEA['No Muertos']['players']['Esqueleto']= $DEA['No Muertos']['players']['Skeleton']; unset($DEA['No Muertos']['players']['Skeleton']);
$DEA['No Muertos']['players']['Tumulario']= $DEA['No Muertos']['players']['Wight']; unset($DEA['No Muertos']['players']['Wight']);
$DEA['No Muertos']['players']['Necrofago']= $DEA['No Muertos']['players']['Ghoul']; unset($DEA['No Muertos']['players']['Ghoul']);
$DEA['No Muertos']['players']['Momia']= $DEA['No Muertos']['players']['Mummy']; unset($DEA['No Muertos']['players']['Mummy']);

//Nordicos spanish
$DEA['Nordicos']= $DEA['Norse']; unset($DEA['Norse']);
$DEA['Nordicos']['players']['Linea']= $DEA['Nordicos']['players']['Lineman']; unset($DEA['Nordicos']['players']['Lineman']);
$DEA['Nordicos']['players']['Lanzador']= $DEA['Nordicos']['players']['Thrower']; unset($DEA['Nordicos']['players']['Thrower']);
$DEA['Nordicos']['players']['Corredor']= $DEA['Nordicos']['players']['Catcher']; unset($DEA['Nordicos']['players']['Catcher']);
$DEA['Nordicos']['players']['Berserker']= $DEA['Nordicos']['players']['Blitzer']; unset($DEA['Nordicos']['players']['Blitzer']);
$DEA['Nordicos']['players']['Hombre Lobo Nordico']= $DEA['Nordicos']['players']['Norse Werewolf']; unset($DEA['Nordicos']['players']['Norse Werewolf']);
$DEA['Nordicos']['players']['Yehti']= $DEA['Nordicos']['players']['Yhetee']; unset($DEA['Nordicos']['players']['Yhetee']);

//Nurgle spanish
$DEA['Nurgle']['players']['Putrefacto']= $DEA['Nurgle']['players']['Rotter']; unset($DEA['Nurgle']['players']['Rotter']);
$DEA['Nurgle']['players']['Guerrero de Nurgle']= $DEA['Nurgle']['players']['Nurgle Warrior']; unset($DEA['Nurgle']['players']['Nurgle Warrior']);
$DEA['Nurgle']['players']['Bestia de Nurgle']= $DEA['Nurgle']['players']['Beast of Nurgle']; unset($DEA['Nurgle']['players']['Beast of Nurgle']);

//Ogros spanish
$DEA['Ogros']= $DEA['Ogre']; unset($DEA['Ogre']);
$DEA['Ogros']['players']['Ogro']= $DEA['Ogros']['players']['Ogre']; unset($DEA['Ogros']['players']['Ogre']);

//Orcos spanish
$DEA['Orcos']= $DEA['Orc']; unset($DEA['Orc']);
$DEA['Orcos']['players']['Linea']= $DEA['Orcos']['players']['Lineman']; unset($DEA['Orcos']['players']['Lineman']);
$DEA['Orcos']['players']['Lanzador']= $DEA['Orcos']['players']['Thrower']; unset($DEA['Orcos']['players']['Thrower']);
$DEA['Orcos']['players']['Defensa Orco Negro']= $DEA['Orcos']['players']['Black Orc Blocker']; unset($DEA['Orcos']['players']['Black Orc Blocker']);

//Skavens spanish
$DEA['Skavens']= $DEA['Skaven']; unset($DEA['Skaven']);
$DEA['Skavens']['players']['Linea']= $DEA['Skavens']['players']['Lineman']; unset($DEA['Skavens']['players']['Lineman']);
$DEA['Skavens']['players']['Lanzador']= $DEA['Skavens']['players']['Thrower']; unset($DEA['Skavens']['players']['Thrower']);
$DEA['Skavens']['players']['Corredor de Alcantarillas']= $DEA['Skavens']['players']['Gutter Runner']; unset($DEA['Skavens']['players']['Gutter Runner']);
$DEA['Skavens']['players']['Rata Ogro']= $DEA['Skavens']['players']['Rat Ogre']; unset($DEA['Skavens']['players']['Rat Ogre']);

//Vampiros spanish
$DEA['Vampiros']= $DEA['Vampire']; unset($DEA['Vampire']);
$DEA['Vampiros']['players']['Siervo']= $DEA['Vampiros']['players']['Thrall']; unset($DEA['Vampiros']['players']['Thrall']);
$DEA['Vampiros']['players']['Vampiro']= $DEA['Vampiros']['players']['Vampire']; unset($DEA['Vampiros']['players']['Vampire']);

//Slann spanish
$DEA['Slann']['players']['Linea']= $DEA['Slann']['players']['Lineman']; unset($DEA['Slann']['players']['Lineman']);
$DEA['Slann']['players']['Receptor']= $DEA['Slann']['players']['Catcher']; unset($DEA['Slann']['players']['Catcher']);

//Pacto del Caos spanish
$DEA['Pacto del Caos']= $DEA['Chaos Pact']; unset($DEA['Chaos Pact']);
$DEA['Pacto del Caos']['players']['Barbaro']= $DEA['Pacto del Caos']['players']['Marauder']; unset($DEA['Pacto del Caos']['players']['Marauder']);
$DEA['Pacto del Caos']['players']['Goblin Renegado']= $DEA['Pacto del Caos']['players']['Goblin Renegade']; unset($DEA['Pacto del Caos']['players']['Goblin Renegade']);
$DEA['Pacto del Caos']['players']['Skaven Renegado']= $DEA['Pacto del Caos']['players']['Skaven Renegade']; unset($DEA['Pacto del Caos']['players']['Skaven Renegade']);
$DEA['Pacto del Caos']['players']['Elfo Oscuro Renegado']= $DEA['Pacto del Caos']['players']['Dark Elf Renegade']; unset($DEA['Pacto del Caos']['players']['Dark Elf Renegade']);
$DEA['Pacto del Caos']['players']['Troll del Caos']= $DEA['Pacto del Caos']['players']['Chaos Troll']; unset($DEA['Pacto del Caos']['players']['Chaos Troll']);
$DEA['Pacto del Caos']['players']['Ogro del Caos']= $DEA['Pacto del Caos']['players']['Chaos Ogre']; unset($DEA['Pacto del Caos']['players']['Chaos Ogre']);
$DEA['Pacto del Caos']['players']['Minotauro']= $DEA['Pacto del Caos']['players']['Minotaur']; unset($DEA['Pacto del Caos']['players']['Minotaur']);

//Submundo spanish
$DEA['Submundo']= $DEA['Underworld']; unset($DEA['Underworld']);
$DEA['Submundo']['players']['Goblin del Submundo']= $DEA['Submundo']['players']['Underworld Goblin']; unset($DEA['Submundo']['players']['Underworld Goblin']);
$DEA['Submundo']['players']['Linea Skaven']= $DEA['Submundo']['players']['Skaven Lineman']; unset($DEA['Submundo']['players']['Skaven Lineman']);
$DEA['Submundo']['players']['Lanzador Skaven']= $DEA['Submundo']['players']['Skaven Thrower']; unset($DEA['Submundo']['players']['Skaven Thrower']);
$DEA['Submundo']['players']['Blitzer Skaven']= $DEA['Submundo']['players']['Skaven Blitzer']; unset($DEA['Submundo']['players']['Skaven Blitzer']);
$DEA['Submundo']['players']['Troll de Piedra Bruja']= $DEA['Submundo']['players']['Warpstone Troll']; unset($DEA['Submundo']['players']['Warpstone Troll']);

//spanish inducements 
$inducements['Chicas Bloodweiser']=$inducements['Bloodweiser Babes']; unset($inducements['Bloodweiser Babes']);
$inducements['Sobornos']=$inducements['Bribes']; unset($inducements['Bribes']);
$inducements['Entrenamiento Adicional']=$inducements['Extra Training']; unset($inducements['Extra Training']);
$inducements['Gran Chef Halfling']=$inducements['Halfling Master Chef']; unset($inducements['Halfling Master Chef']); 
$inducements['Medicos Ambulantes']=$inducements['Wandering Apothecaries']; unset($inducements['Wandering Apothecaries']); 
$inducements['Hechicero']=$inducements['Wizard']; unset($inducements['Wizard']); 
