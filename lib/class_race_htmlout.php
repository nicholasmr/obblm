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

class Race_HTMLOUT extends Race
{

public static function profile($rid) 
{
    global $lng, $DEA;
    $race = new Race($rid);
    $roster = $DEA[$race->name];
    title($race->name);
    ?>
    <center><img src="<?php echo RACE_ICONS.'/'.$roster['other']['icon'];?>" alt="Race icon"></center>
    <ul>
        <li>ID: <?php echo $roster['other']['race_id'];?></li>
        <li>Re-roll cost: <?php echo $roster['other']['rr_cost']/1000;?>k</li>
    </ul><br>
    <?php
    $players = array();
    foreach ($roster['players'] as $player => $d) {
        $p = (object) array_merge(array('position' => $player), $d);
        $p->skills = implode(', ', skillsTrans($p->def));
        $p->N = implode('',$p->norm);
        $p->D = implode('',$p->doub);
        $players[] = $p;
    }
    $fields = array(
        'position'  => array('desc' => $lng->getTrn('common/pos')),
        'pos_id'    => array('desc' => 'ID'),
        'ma'        => array('desc' => 'MO'),
        'st'        => array('desc' => 'FU'),
        'ag'        => array('desc' => 'AG'),
        'av'        => array('desc' => 'AR'),
        'skills'    => array('desc' => $lng->getTrn('common/skills'), 'nosort' => true),
        'N'         => array('desc' => 'Normal', 'nosort' => true),
        'D'         => array('desc' => 'Double', 'nosort' => true),
        'cost'      => array('desc' => $lng->getTrn('common/price'), 'kilo' => true, 'suffix' => 'k'),
        'qty'       => array('desc' => 'Max. qty'),
    );
    HTMLOUT::sort_table(
        'Roster',
        urlcompile(T_URL_PROFILE,T_OBJ_RACE,$race->race_id,false,false),
        $players,
        $fields,
        sort_rule('race_page'),
        (isset($_GET['sortpl'])) ? array((($_GET['dirpl'] == 'a') ? '+' : '-') . $_GET['sortpl']) : array(),
        array('GETsuffix' => 'pl', 'noHelp' => true, 'doNr' => false)
    );

    // Teams of the chosen race.
    $url = urlcompile(T_URL_PROFILE,T_OBJ_RACE,$race->race_id,false,false);
    HTMLOUT::standings(STATS_TEAM,false,false,array('url' => $url, 'teams_from' => STATS_RACE, 'teams_from_id' => $race->race_id));
    echo '<br>';
    HTMLOUT::recentGames(STATS_RACE, $race->race_id, false, false, false, false, array('url' => $url, 'n' => MAX_RECENT_GAMES, 'GET_SS' => 'gp'));
}

public static function standings()
{  
    global $lng;
    title($lng->getTrn('menu/statistics_menu/race_stn'));
    HTMLOUT::standings(STATS_RACE,false,false,array('url' => urlcompile(T_URL_STANDINGS,T_OBJ_RACE,false,false,false)));
}

}

