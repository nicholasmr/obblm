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

class Wanted implements ModuleInterface
{
/***************
 * Properties 
 ***************/

public $wanted_id   = 0;
public $pid         = 0;
public $date        = '';
public $why         = '';
public $bounty      = '';

/***************
 * Methods 
 ***************/    

function __construct($wanted_id) 
{
    $result = mysql_query("SELECT * FROM wanted WHERE wanted_id = $wanted_id");
    if ($result && mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_assoc($result)) {
            foreach ($row as $key => $val) {
                $this->$key = $val;
            }
        }
    }
}

public function edit($why, $bounty) 
{
    if (mysql_query("UPDATE wanted SET 
                    why = '".mysql_real_escape_string($why)."', 
                    bounty = '".mysql_real_escape_string($bounty)."' 
                    WHERE wanted_id = $this->wanted_id")) {
        $this->why = $why;
        $this->bounty = $bounty;
        return true;
    }
    else
        return false;
}

public function delete()
{
    return (mysql_query("DELETE FROM wanted WHERE wanted_id = $this->wanted_id"));
}

/***************
 * Statics
 ***************/

public static function getWanted($n = false)
{
    $w = array();

    $result = mysql_query("SELECT wanted_id, pid FROM wanted ORDER BY date DESC" . (($n) ? " LIMIT $n" : ''));
    if ($result && mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_assoc($result)) {
            array_push($w, array('wanted' => new Wanted($row['wanted_id']), 'player' => new Player($row['pid'])));
        }
    }
    
    return $w;
}

public static function create($player_id, $why, $bounty)
{
        return (mysql_query("
                INSERT INTO wanted 
                (pid, why, bounty, date) 
                VALUES 
                ($player_id, '".mysql_real_escape_string($why)."', '".mysql_real_escape_string($bounty)."', NOW())
                "));
}

/***************
 * Interface
 ***************/

public static function main($argv) 
{
    // func may be "isWanted" or "makeList".
    $func = array_shift($argv);
    return call_user_func_array(array(__CLASS__, $func), $argv);
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
    return array(
        'wanted' => array(
            'wanted_id' => 'MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
            'pid'    => 'MEDIUMINT UNSIGNED',
            'date'   => 'DATETIME',
            'why'    => 'TEXT',
            'bounty' => 'TEXT',
        )
    );
}

public static function getModuleUpgradeSQL()
{
    return array(
        '075-080' => array(
            'CREATE TABLE IF NOT EXISTS wanted
            (
                    wanted_id   MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                    pid         MEDIUMINT UNSIGNED,
                    date        DATETIME,
                    why         TEXT,
                    bounty      TEXT
            )',
            'INSERT INTO wanted (pid, date, why, bounty) SELECT f_id, date, txt, txt2 FROM texts WHERE type = 6 ORDER BY date ASC',
            'DELETE FROM texts WHERE type = 6',
        ),
    );
}

public static function triggerHandler($type, $argv){}

/***************
 * main() related.
 ***************/

public static  function isWanted($pid) 
{
    $query = "SELECT pid FROM wanted WHERE pid = $pid";
    return (($result = mysql_query($query)) && mysql_num_rows($result) > 0);
}

public static function makeList($ALLOW_EDIT)
{
    
    global $lng, $coach, $settings;
    HTMLOUT::frame_begin(is_object($coach) ? $coach->settings['theme'] : $settings['stylesheet']); # Make page frame, banner and menu.
    title($lng->getTrn('name', __CLASS__));
    
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
                    $player_id = $w->pid;
                    $why = $w->why;
                    $bounty = $w->bounty;
                }
                else {
                    fatal('Sorry. You did not specify which wanted-id you wish to edit.');
                }
                
                // Fall-through to "new" !!!

            case 'new':
                ?>
                <form method="POST">
                <b><?php echo $lng->getTrn('player', __CLASS__);?>:</b><br>
                <i><?php echo $lng->getTrn('sort_hint', __CLASS__);?></i><br>
                <select name="player_id" id="players">
                    <?php
                    $query = "SELECT player_id, players.name AS 'name', teams.name AS 'team_name' FROM players, teams WHERE owned_by_team_id = team_id ORDER by team_name ASC, name ASC";
                    $result = mysql_query($query);
                    while ($row = mysql_fetch_assoc($result)) {
                        echo "<option value='$row[player_id]' ".(($player_id == $row['player_id']) ? 'SELECTED' : '').">$row[team_name]: $row[name] </option>\n";
                    }
                    ?>
                </select>                
                <br><br>
                <?php echo $lng->getTrn('title', __CLASS__);?><br>
                <b><?php echo $lng->getTrn('g_title', __CLASS__);?>:</b><br>
                <input type="text" name="bounty" size="60" maxlength="100" value="<?php echo $bounty;?>">
                <br><br>
                <?php echo $lng->getTrn('about', __CLASS__);?><br>
                <b><?php echo $lng->getTrn('g_about', __CLASS__);?>:</b><br>
                <textarea name="why" rows="15" cols="100"><?php echo $why;?></textarea>
                <br><br>
                <input type="submit" value="<?php echo $lng->getTrn('submit', __CLASS__);?>" name="Submit">
                </form>

                <br>
                <?php
                echo $lng->getTrn('note', __CLASS__);
        
                return;
                break;

        }
    }

    /* Print the wanted players */
    echo $lng->getTrn('desc', __CLASS__)."<br><br>\n";
    if ($ALLOW_EDIT) {
        echo "<a href='handler.php?type=wanted&amp;action=new'>".$lng->getTrn('new', __CLASS__)."</a><br>\n";
    }
    
    $wanted = Wanted::getWanted();
    
    foreach ($wanted as $x) {
        $w = $x['wanted'];
        $p = $x['player'];
    
        ?>    
        <div style="clear: both; width: 70%; border: 1px solid #545454; margin: 20px auto 20px auto;">
            <div class="boxTitle<?php echo T_HTMLBOX_INFO;?>"><?php echo $lng->getTrn('wanted', __CLASS__).": <a href='".urlcompile(T_URL_PROFILE,T_OBJ_PLAYER,$p->player_id,false,false)."'>$p->name</a>";?></div>
            <div class="boxBody">
                <table class="common">
                    <tr>
                        <td colspan="2" align="left" valign="top">
                            <b><?php echo $lng->getTrn('g_title', __CLASS__);?>:</b><br>
                            <?php echo $w->bounty;?>
                            <br>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" valign="top">
                        <br>
                        <b><?php echo $lng->getTrn('g_about', __CLASS__);?>:</b><br>
                        <?php 
                        echo $w->why;
                        if ($p->is_dead) {
                            echo "<br><br><font color='red'><b>".$lng->getTrn('killed', __CLASS__)."</b></font>\n";
                        }
                        ?>
                        </td>
                        <td align="right" style="width: 30%;">
                            <img border='0px' height='100' width='100' alt='player picture' src="<?php $img = new ImageSubSys(T_OBJ_PLAYER, $p->player_id); echo $img->getPath();?>">
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><hr></td>
                    </tr>
                    <tr>
                        <td align="left">
                        <?php echo $lng->getTrn('posted', __CLASS__).' '. $w->date;?>
                        </td>
                        <td align="right">
                        <?php
                        if ($ALLOW_EDIT) {
                            ?> 
                            <a href="handler.php?type=wanted&amp;action=edit&amp;wanted_id=<?php echo $w->wanted_id;?>"><?php echo $lng->getTrn('edit', __CLASS__);?></a>
                            &nbsp;
                            <a href="handler.php?type=wanted&amp;action=delete&amp;wanted_id=<?php echo $w->wanted_id;?>"><?php echo $lng->getTrn('del', __CLASS__);?></a> 
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
    HTMLOUT::frame_end();
}
}

?>
