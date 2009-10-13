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

public function racePage() 
{
    $race = $this;
    $roster = $race->getRoster();
    title($race->race);
    ?>
    <center><img src="<?php echo $roster['other']['icon'];?>" alt="Race icon"></center>
    <ul><li>Re-roll cost: <?php echo $roster['other']['RerollCost']/1000;?>k</li></ul><br>
    <?php
    $players = array();
    foreach ($roster['players'] as $player => $d) {
        $p = (object) array_merge(array('position' => $player), $d);
        $p->skills = implode(', ', $p->{'Def skills'});
        foreach (array('N', 'D') as $s) {
            array_walk($p->{"$s skills"}, create_function('&$val', '$val = substr($val,0,1);'));
            $p->$s = implode('', $p->{"$s skills"});
        }
        $players[] = $p;
    }
    $fields = array(
        'position'  => array('desc' => 'Position'),
        'ma'        => array('desc' => 'Ma'),
        'st'        => array('desc' => 'St'),
        'ag'        => array('desc' => 'Ag'),
        'av'        => array('desc' => 'Av'),
        'skills'    => array('desc' => 'Skills', 'nosort' => true),
        'N'         => array('desc' => 'Normal', 'nosort' => true),
        'D'         => array('desc' => 'Double', 'nosort' => true),
        'cost'      => array('desc' => 'Price', 'kilo' => true, 'suffix' => 'k'),
        'qty'       => array('desc' => 'Max.'),
    );
    HTMLOUT::sort_table(
        $race->race.' '.$lng->getTrn('secs/races/players'),
        "index.php?section=races&amp;race=$race->race",
        $players,
        $fields,
        sort_rule('race_page'),
        (isset($_GET['sortpl'])) ? array((($_GET['dirpl'] == 'a') ? '+' : '-') . $_GET['sortpl']) : array(),
        array('GETsuffix' => 'pl')
    );

    // Teams of the chosen race.
    HTMLOUT::standings(STATS_TEAM,false,false,array('url' => "index.php?section=races&amp;race=$race->race_id", 'teams_from' => STATS_RACE, 'teams_from_id' => $race->race_id));
    echo '<br>';
    HTMLOUT::recentGames(STATS_RACE, $race->race_id, false, false, false, false, array('url' => "index.php?section=races&amp;race=$race->race_id", 'n' => MAX_RECENT_GAMES, 'GET_SS' => 'gp'));
}

public static function standings()
{
    title($lng->getTrn('global/secLinks/races'));
    HTMLOUT::standings(STATS_RACE,false,false,array('url' => 'index.php?section=races'));
}

}

?>
