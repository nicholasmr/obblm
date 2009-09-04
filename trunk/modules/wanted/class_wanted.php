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

class Wanted extends _Text implements ModuleInterface
{
/***************
 * Properties 
 ***************/

public $wanted_id   = 0;
public $player_id   = 0;
public $why         = '';
public $bounty      = '';

/***************
 * Methods 
 ***************/    

function __construct($wanted_id) 
{
    parent::__construct($wanted_id);
    
    $this->wanted_id    = $this->txt_id;        
    $this->player_id    = $this->f_id;
    $this->bounty       = $this->txt2;
    $this->why          = $this->txt;
    
    unset($this->txt2);
    unset($this->txt);
}

public function edit($why, $bounty) 
{
    return parent::edit($why, $bounty, false, false);
}

/***************
 * Statics
 ***************/

public static function getWanted($n = false)
{
    $w = array();

    $result = mysql_query("SELECT txt_id, f_id FROM texts WHERE type = ".T_TEXT_WANTED." ORDER BY date DESC" . (($n) ? " LIMIT $n" : ''));
    if ($result && mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_assoc($result)) {
            array_push($w, array('wanted' => new Wanted($row['txt_id']), 'player' => new Player($row['f_id'])));
        }
    }
    
    return $w;
}

public static function create($player_id, $why, $bounty)
{
    return parent::create($player_id, T_TEXT_WANTED, $why, $bounty);
}

/***************
 * Interface
 ***************/

public static function main($argv) 
{
    // func may be "isWanted" or "makeList".
    $func = array_shift($argv);
    return call_user_func_array("Wanted::$func", $argv);
}

public static function getModuleAttributes()
{
    return array(
        'author'     => 'Nicholas Mossor Rathmann',
        'moduleName' => 'Wanted',
        'date'       => '2008',
        'setCanvas'  => false,
    );
}

public static function getModuleTables()
{
    return array();
}

/***************
 * main() related.
 ***************/

public static  function isWanted($pid) 
{
    $query = "SELECT f_id FROM texts WHERE f_id = $pid AND type = ".T_TEXT_WANTED;
    return (($result = mysql_query($query)) && mysql_num_rows($result) > 0);
}

public static function makeList($ALLOW_EDIT)
{
    
    global $lng;
    title($lng->getTrn('secs/records/d_wanted'));
    
    /* A new entry was sent. Add it to system */
    
    if (isset($_POST['player_id']) && $ALLOW_EDIT) {
        if (get_magic_quotes_gpc()) {
            $_POST['bounty'] = stripslashes($_POST['bounty']);
            $_POST['why'] = stripslashes($_POST['why']);
        }
        switch ($_GET['action'])
        {
            case 'edit':
                $w = new Wanted($_GET['wanted_id']);
                status($w->edit($_POST['why'], $_POST['bounty']));
                break;
            
            case 'new':
                status(Wanted::create($_POST['player_id'], $_POST['why'], $_POST['bounty']));
                break;
        }
    }
    
    /* Was a request for a new entry made? */ 
    
    elseif (isset($_GET['action']) && $ALLOW_EDIT) {
        
        // Default schema values. These are empty unless "edit" is chosen.
        $player_id = false;
        $bounty = '';
        $why = '';
        
        switch ($_GET['action'])
        {
            case 'delete':
                if (isset($_GET['wanted_id']) && is_numeric($_GET['wanted_id'])) {
                    $w = new Wanted($_GET['wanted_id']);
                    status($w->delete());
                    unset($w);
                }
                else {
                    fatal('Sorry. You did not specify which wanted-id you wish to delete.');
                }                
                break;
                
            case 'edit':
                if (isset($_GET['wanted_id']) && is_numeric($_GET['wanted_id'])) {
                    $w = new Wanted($_GET['wanted_id']);
                    $player_id = $w->player_id;
                    $why = $w->why;
                    $bounty = $w->bounty;
                }
                else {
                    fatal('Sorry. You did not specify which wanted-id you wish to edit.');
                }
                
                // Fall-through to "new" !!!

            case 'new':
                $teams = Team::getTeams();
                $jsteams = array();
                foreach ($teams as $t) {
                    $players = $t->getPlayers();
                    objsort($players, array('+name'));
                    foreach ($players as $p) {
                        $jsteams[$t->team_id][] = array('pid' => $p->player_id, 'name' => $p->name);
                    }
                }
                $easyconvert = new array_to_js();
                @$easyconvert->add_array($jsteams, 'jsteams'); // Load Game Data array into JavaScript array.
                echo $easyconvert->output_all();
                ?>
                <form method="POST">
                <b><?php echo $lng->getTrn('secs/records/team');?>:</b><br>
                <select name="player_id" id="teams" onChange="updateTeamPlayers(this.options[this.selectedIndex].value, document.getElementById('players'));">
                    <?php
                    objsort($teams, array('+name'));
                    foreach ($teams as $t) {
                        echo "<option value='$t->team_id'>$t->name</option>\n";
                    }
                    ?>
                </select>                
                <br><br>
                <b><?php echo $lng->getTrn('secs/records/player');?>:</b><br>
                <select name="player_id" id="players">
                    <option value='0'>-Empty-</option>
                </select>
                <br><br>
                <?php echo $lng->getTrn('secs/records/wanted/title');?><br>
                <b><?php echo $lng->getTrn('secs/records/wanted/g_title');?>:</b><br>
                <input type="text" name="bounty" size="60" maxlength="100" value="<?php echo $bounty;?>">
                <br><br>
                <?php echo $lng->getTrn('secs/records/wanted/about');?><br>
                <b><?php echo $lng->getTrn('secs/records/wanted/g_about');?>:</b><br>
                <textarea name="why" rows="15" cols="100"><?php echo $why;?></textarea>
                <br><br>
                <input type="submit" value="<?php echo $lng->getTrn('secs/records/submit');?>" name="Submit">
                </form>

                <!-- Set player list to be the players from the default selected team. -->
                <script language='JavaScript' type='text/javascript'>
                    tsel = document.getElementById('teams');
                    updateTeamPlayers(tsel.options[tsel.selectedIndex].value, document.getElementById('players'));
                </script>

                <br>
                <?php
                echo $lng->getTrn('secs/records/wanted/note');
        
                return;
                break;

        }
    }

    /* Print the wanted players */
    echo $lng->getTrn('secs/records/wanted/desc')."<br><br>\n";
    if ($ALLOW_EDIT) {
        echo "<a href='index.php?section=records&amp;subsec=wanted&amp;action=new'>".$lng->getTrn('secs/records/new')."</a><br>\n";
    }
    
    $wanted = Wanted::getWanted();
    
    foreach ($wanted as $x) {
        $w = $x['wanted'];
        $p = $x['player'];
    
        ?>    
        <div class="recBox">
            <div class="boxTitle2"><?php echo $lng->getTrn('secs/records/wanted/wanted').": <a href='index.php?section=coachcorner&amp;player_id=$p->player_id'>$p->name</a>";?></div>
            <div class="boxBody">
                <table class="recBoxTable">
                    <tr>
                        <td colspan="2" align="left" valign="top">
                            <b><?php echo $lng->getTrn('secs/records/wanted/g_title');?>:</b><br>
                            <?php echo $w->bounty;?>
                            <br>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" valign="top">
                        <br>
                        <b><?php echo $lng->getTrn('secs/records/wanted/g_about');?>:</b><br>
                        <?php 
                        echo $w->why;
                        if ($p->is_dead) {
                            echo "<br><br><font color='red'><b>".$lng->getTrn('secs/records/wanted/killed')."</b></font>\n";
                        }
                        ?>
                        </td>
                        <td align="right" style="width: 30%;">
                            <img border='0px' height='100' width='100' alt='player picture' src='<?php echo NO_PIC;?>'>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><hr></td>
                    </tr>
                    <tr>
                        <td align="left">
                        <?php echo $lng->getTrn('secs/records/posted').' '. $w->date;?>
                        </td>
                        <td align="right">
                        <?php
                        if ($ALLOW_EDIT) {
                            ?> 
                            <a href="index.php?section=records&amp;subsec=wanted&amp;action=edit&amp;wanted_id=<?php echo $w->wanted_id;?>"><?php echo $lng->getTrn('secs/records/edit');?></a>
                            &nbsp;
                            <a href="index.php?section=records&amp;subsec=wanted&amp;action=delete&amp;wanted_id=<?php echo $w->wanted_id;?>"><?php echo $lng->getTrn('secs/records/del');?></a> 
                            <?php
                        }
                        ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <?php
    }
}
}

?>
