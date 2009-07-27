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

class Comparison
{

public static function main()
{
global $raceididx;
title('Comparison');

/* Get all IDs and names for players, teams, coaches and races. */
$players = $teams = $coaches = array();
// Players
$query = "SELECT player_id, players.name AS 'name', teams.name AS 'team_name' FROM players, teams WHERE owned_by_team_id = team_id ORDER BY teams.name ASC, players.name ASC";
$result = mysql_query($query);
if (is_resource($result) && mysql_num_rows($result) > 0) {
    while ($r = mysql_fetch_assoc($result)) {
        $players[$r['player_id']] = array('name' => $r['name'], 'team_name' => $r['team_name']);
    }
}
// Teams
$query = "SELECT team_id, name FROM teams ORDER BY name ASC";
$result = mysql_query($query);
if (is_resource($result) && mysql_num_rows($result) > 0) {
    while ($r = mysql_fetch_assoc($result)) {
        $teams[$r['team_id']] = $r['name'];
    }
}
//Coaches
$query = "SELECT coach_id, name FROM coaches ORDER BY name ASC";
$result = mysql_query($query);
if (is_resource($result) && mysql_num_rows($result) > 0) {
    while ($r = mysql_fetch_assoc($result)) {
        $coaches[$r['coach_id']] = $r['name'];
    }
}

?>
<form method="POST">    
<table width="100%">
    <tr>
        <td><b>Show stats by...</b></td>
        <td><b>...for games against...</b></td>
        <td><b>...in</b></td>
    </tr>
    <tr>
        <td>
            <select name="type_by" onChange="
                selConst = Number(this.options[this.selectedIndex].value); 
                comparison_disableall('by'); 
                switch(selConst) 
                {
                    case <?php echo STATS_PLAYER;?>: document.getElementById('player_by').style.display = 'block'; break;
                    case <?php echo STATS_TEAM;?>:   document.getElementById('team_by').style.display = 'block'; break;
                    case <?php echo STATS_COACH;?>:  document.getElementById('coach_by').style.display = 'block'; break;
                    case <?php echo STATS_RACE;?>:   document.getElementById('race_by').style.display = 'block'; break;
                }
            ">
                <?php
                foreach (array(STATS_TEAM => 'Team', STATS_PLAYER => 'Player', STATS_COACH => 'Coach', STATS_RACE => 'Race') as $const => $name) {
                    echo "<option value='$const'>$name</option>\n";
                }
                ?>
            </select>
        </td>
        <td>
            <select name="type_ag" onChange="
                selConst = Number(this.options[this.selectedIndex].value); 
                comparison_disableall('ag'); 
                switch(selConst) 
                {
                    case <?php echo STATS_PLAYER;?>: document.getElementById('player_ag').style.display = 'block'; break;
                    case <?php echo STATS_TEAM;?>:   document.getElementById('team_ag').style.display = 'block'; break;
                    case <?php echo STATS_COACH;?>:  document.getElementById('coach_ag').style.display = 'block'; break;
                    case <?php echo STATS_RACE;?>:   document.getElementById('race_ag').style.display = 'block'; break;
                }
            ">
                <?php
                foreach (array(STATS_TEAM => 'Team', STATS_PLAYER => 'Player', STATS_COACH => 'Coach', STATS_RACE => 'Race') as $const => $name) {
                    echo "<option value='$const'>$name</option>\n";
                }
                ?>
            </select>
        </td>
        <td>
            <select name="type_in" onChange="
                selConst = Number(this.options[this.selectedIndex].value); 
                comparison_disableall('in'); 
                switch(selConst) 
                {
                    case <?php echo STATS_TOUR;?>:      document.getElementById('tour_in').style.display = 'block'; break;
                    case <?php echo STATS_DIVISION;?>:  document.getElementById('division_in').style.display = 'block'; break;
                    case <?php echo STATS_LEAGUE;?>:    document.getElementById('league_in').style.display = 'block'; break;
                }
            ">
                <?php
                foreach (array(STATS_LEAGUE => 'League', STATS_DIVISION => 'Division', STATS_TOUR => 'Tournament') as $const => $name) {
                    echo "<option value='$const'>$name</option>\n";
                }
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <td>
            <select style='display:none;' name="player_by" id="player_by">
                <?php
                foreach ($players as $id => $arr) {
                    if (strlen($arr['team_name']) > 20) {$arr['team_name']  = substr($arr['team_name'],0,17).'...'; }
                    if (strlen($arr['name']) > 20)      {$arr['name']       = substr($arr['name'],0,17).'...';}
                    echo "<option value='$id'>$arr[team_name]: $arr[name]</option>\n";
                }
                ?>
            </select>
            <select style='display:none;' name="team_by" id="team_by">
                <?php
                foreach ($teams as $id => $name) {
                    echo "<option value='$id'>$name</option>\n";
                }
                ?>
            </select>
            <select style='display:none;' name="coach_by" id="coach_by">
                <?php
                foreach ($coaches as $id => $name) {
                    echo "<option value='$id'>$name</option>\n";
                }
                ?>
            </select>
            <select style='display:none;' name="race_by" id="race_by">
                <?php
                foreach ($raceididx as $id => $name) {
                    echo "<option value='$id'>$name</option>\n";
                }
                ?>
            </select>
        </td>
        <td>
            <select style='display:none;' name="player_ag" id="player_ag">
                <?php
                foreach ($players as $id => $arr) {
                    if (strlen($arr['team_name']) > 20) {$arr['team_name']  = substr($arr['team_name'],0,17).'...'; }
                    if (strlen($arr['name']) > 20)      {$arr['name']       = substr($arr['name'],0,17).'...';}
                    echo "<option value='$id'>$arr[team_name]: $arr[name]</option>\n";
                }
                ?>
            </select>
            <select style='display:none;' name="team_ag" id="team_ag">
                <?php
                foreach ($teams as $id => $name) {
                    echo "<option value='$id'>$name</option>\n";
                }
                ?>
            </select>
            <select style='display:none;' name="coach_ag" id="coach_ag">
                <?php
                foreach ($coaches as $id => $name) {
                    echo "<option value='$id'>$name</option>\n";
                }
                ?>
            </select>
            <select style='display:none;' name="race_ag" id="race_ag">
                <?php
                foreach ($raceididx as $id => $name) {
                    echo "<option value='$id'>$name</option>\n";
                }
                ?>
            </select>
        </td>
        <td>
            <select style='display:none;' name="tour_in" id="tour_in">
                <?php
                foreach (Tour::getTours() as $t) {
                    echo "<option value='$t->tour_id'>$t->name</option>\n";
                }
                ?>
            </select>
            <select style='display:none;' name="division_in" id="division_in">
                <?php
                foreach (Division::getDivisions() as $d) {
                    echo "<option value='$d->did'>$d->name</option>\n";
                }
                ?>
            </select>
            <select style='display:none;' name="league_in" id="league_in">
                <?php
                echo "<option value='0'>-All-</option>\n";
                foreach (League::getLeagues() as $l) {
                    echo "<option value='$l->lid'>$l->name</option>\n";
                }
                ?>
            </select>
        </td>
    </tr>
</table>
<br>
<input type="submit" name="submit" value="Show stats">
</form>
<!-- defaults -->
<script language="JavaScript" type="text/javascript">
    document.getElementById('team_by').style.display = 'block';
    document.getElementById('team_ag').style.display = 'block';
    document.getElementById('league_in').style.display = 'block';
    
    function comparison_disableall(suffix)
    {
        if (suffix == 'in') {
            document.getElementById('tour_in').style.display = 'none';
            document.getElementById('division_in').style.display = 'none';
            document.getElementById('league_in').style.display = 'none';
        }
        else {
            document.getElementById('player_'+suffix).style.display = 'none';
            document.getElementById('team_'+suffix).style.display = 'none';
            document.getElementById('coach_'+suffix).style.display = 'none';
            document.getElementById('race_'+suffix).style.display = 'none';
        }
        return true;
    }
</script>

<?php

if (isset($_POST['submit'])) {
    echo "<br><hr><br>";
    
    switch ($obj = $_POST['type_by'])
    {
        case STATS_PLAYER:  $o = new Player($obj_id = $_POST['player_by']); break;
        case STATS_TEAM:    $o = new Team($obj_id = $_POST['team_by']); break;
        case STATS_COACH:   $o = new Coach($obj_id = $_POST['coach_by']); break;
        case STATS_RACE:    $o = new Race($obj_id = $_POST['race_by']); break;
    }
    switch ($opp_obj = $_POST['type_ag'])
    {
        case STATS_PLAYER:  $opp_o = new Player($opp_obj_id = $_POST['player_ag']); break;
        case STATS_TEAM:    $opp_o = new Team($opp_obj_id = $_POST['team_ag']); break;
        case STATS_COACH:   $opp_o = new Coach($opp_obj_id = $_POST['coach_ag']); break;
        case STATS_RACE:    $opp_o = new Race($opp_obj_id = $_POST['race_ag']); break;
    }
    switch ($node = $_POST['type_in'])
    {
        case STATS_TOUR:        $node_id = $_POST['tour_in']; break;
        case STATS_DIVISION:    $node_id = $_POST['division_in']; break;
        case STATS_LEAGUE:      $node_id = $_POST['league_in']; break;
        default:                $node_id = false; // All-time.
    }        
    
    echo "Stats for <b>$o->name</b> in matches against <b>$opp_o->name</b><br><br>\n";
    
    $stats   = Stats::getAllStats($obj, $obj_id, $node, $node_id, $opp_obj, $opp_obj_id);

    /* 
        Do some printing...
    */
    
    // General stats
    
    $fields = array(
        'won'               => array('desc' => 'W'), 
        'lost'              => array('desc' => 'L'), 
        'draw'              => array('desc' => 'D'), 
        'played'            => array('desc' => 'GP'), 
        'win_percentage'    => array('desc' => 'WIN%'), 
        'row_won'           => array('desc' => 'SW'), 
        'row_lost'          => array('desc' => 'SL'), 
        'row_draw'          => array('desc' => 'SD'), 
        'score_team'        => array('desc' => 'GF'),
        'score_opponent'    => array('desc' => 'GA'),
        'won_tours'         => array('desc' => 'WT'), 
        'td'                => array('desc' => 'Td'), 
        'cp'                => array('desc' => 'Cp'), 
        'intcpt'            => array('desc' => 'Int'), 
        'cas'               => array('desc' => 'Cas'), 
        'bh'                => array('desc' => 'BH'), 
        'si'                => array('desc' => 'Si'), 
        'ki'                => array('desc' => 'Ki'), 
    );
    $objs = array((object) $stats);
    HTMLOUT::sort_table(
        'Achievements', 
        "index.php?section=comparison", 
        $objs, 
        $fields, 
        array('+won'), 
        array(),
        array('doNr' => false)
    );
    echo "<br>";
    
    // Games played
    HTMLOUT::recentGames($obj, $obj_id, $node, $node_id, $opp_obj, $opp_obj_id, array('url' => "index.php?section=comparison", 'n' => false, 'GET_SS' => 'gp'));
}
}
} 
?>
