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

class HOF implements ModuleInterface
{

/***************
 * Properties 
 ***************/

public $hof_id      = 0;
public $pid         = 0;
public $date        = '';
public $title       = '';
public $about       = '';

/***************
 * Methods 
 ***************/    

function __construct($hof_id) 
{
    $result = mysql_query("SELECT * FROM hof WHERE hof_id = $hof_id");
    if ($result && mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_assoc($result)) {
            foreach ($row as $key => $val) {
                $this->$key = $val;
            }
        }
    }
}

public function edit($title, $about) 
{
    if (mysql_query("UPDATE hof SET 
                    title = '".mysql_real_escape_string($title)."', 
                    about = '".mysql_real_escape_string($about)."' 
                    WHERE hof_id = $this->hof_id")) {
        $this->title = $title;
        $this->about = $about;
        return true;
    }
    else
        return false;
}

public function delete()
{
    return (mysql_query("DELETE FROM hof WHERE hof_id = $this->hof_id"));
}

/***************
 * Statics
 ***************/

public static function getHOF($n = false)
{
    $HOF = array();

    $result = mysql_query("SELECT hof_id, pid FROM hof ORDER BY date DESC" . (($n) ? " LIMIT $n" : ''));
    if ($result && mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_assoc($result)) {
            array_push($HOF, array('hof' => new HOF($row['hof_id']), 'player' => new Player($row['pid'])));
        }
    }
    
    return $HOF;
}

public static function create($player_id, $title, $about)
{
    return (mysql_query("
            INSERT INTO hof 
            (pid, title, about, date) 
            VALUES 
            ($player_id, '".mysql_real_escape_string($title)."', '".mysql_real_escape_string($about)."', NOW())
            "));
}

/***************
 * Interface
 ***************/

public static function main($argv) 
{
    // func may be "isInHOF" or "makeList".
    $func = array_shift($argv);
    return call_user_func_array(array(__CLASS__, $func), $argv);
}

public static function getModuleAttributes()
{
    return array(
        'author'     => 'Nicholas Mossor Rathmann',
        'moduleName' => 'Hall of fame',
        'date'       => '2008',
        'setCanvas'  => false,
    );
}

public static function getModuleTables()
{
    return array(
        'hof' => array(
            'hof_id' => 'MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
            'pid'    => 'MEDIUMINT UNSIGNED',
            'date'   => 'DATETIME',
            'title'  => 'TEXT',
            'about'  => 'TEXT',
        )
    );
}

public static function getModuleUpgradeSQL()
{
    return array(
        '075-080' => array(
            'CREATE TABLE IF NOT EXISTS hof
            (
                    hof_id  MEDIUMINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
                    pid     MEDIUMINT UNSIGNED,
                    date    DATETIME,
                    title   TEXT,
                    about   TEXT
            )',
            'INSERT INTO hof (pid, date, title, about) SELECT f_id, date, txt2, txt FROM texts WHERE type = 5 ORDER BY date ASC',
            'DELETE FROM texts WHERE type = 5',
        ),
    );
}

public static function triggerHandler($type, $argv){}

/***************
 * main() related.
 ***************/

public static function isInHOF($pid) 
{
    $query = "SELECT pid FROM hof WHERE pid = $pid";
    return (($result = mysql_query($query)) && mysql_num_rows($result) > 0);
}

public static function makeList($ALLOW_EDIT) {
    
    global $lng, $coach, $settings;
    HTMLOUT::frame_begin(is_object($coach) ? $coach->settings['theme'] : $settings['stylesheet']); # Make page frame, banner and menu.
    title($lng->getTrn('name', __CLASS__));
    
    /* A new entry was sent. Add it to system */
    
    if (isset($_POST['player_id']) && $ALLOW_EDIT) {
        if (get_magic_quotes_gpc()) {
            $_POST['title'] = stripslashes($_POST['title']);
            $_POST['about'] = stripslashes($_POST['about']);
        }
        switch ($_GET['action'])
        {
            case 'edit':
                $h = new HOF($_GET['hof_id']);
                status($h->edit($_POST['title'], $_POST['about']));
                break;
            
            case 'new':
                status(HOF::create($_POST['player_id'], $_POST['title'], $_POST['about']));
                break;
        }
    }
    
    /* Was a request for a new entry made? */ 
    
    elseif (isset($_GET['action']) && $ALLOW_EDIT) {
        
        // Default schema values. These are empty unless "edit" is chosen.
        $player_id = false;
        $title = '';
        $about = '';
        
        switch ($_GET['action'])
        {
            case 'delete':
                if (isset($_GET['hof_id']) && is_numeric($_GET['hof_id'])) {
                    $h = new HOF($_GET['hof_id']);
                    status($h->delete());
                    unset($h);
                }
                else {
                    fatal('Sorry. You did not specify which HOF-id you wish to delete.');
                }                
                break;
                
            case 'edit':
                if (isset($_GET['hof_id']) && is_numeric($_GET['hof_id'])) {
                    $h = new HOF($_GET['hof_id']);
                    $player_id = $h->pid;
                    $title = $h->title;
                    $about = $h->about;
                }
                else {
                    fatal('Sorry. You did not specify which HOF-id you wish to edit.');
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
                <input type="text" name="title" size="60" maxlength="100" value="<?php echo $title;?>">
                <br><br>
                <?php echo $lng->getTrn('about', __CLASS__);?><br>
                <b><?php echo $lng->getTrn('g_about', __CLASS__);?>:</b><br>
                <textarea name="about" rows="15" cols="100"><?php echo $about;?></textarea>
                <br><br>
                <input type="submit" value="<?php echo $lng->getTrn('submit', __CLASS__);?>" name="Submit">
                </form>
                <?php                
        
                return;
                break;

        }
    }
    
    /* Print the hall of fame */
    
    echo $lng->getTrn('desc', __CLASS__)."<br><br>\n";
    if ($ALLOW_EDIT) {
        echo "<a href='handler.php?type=hof&amp;action=new'>".$lng->getTrn('new', __CLASS__)."</a><br>\n";
    }
    
    $HOF = HOF::getHOF();
    
    foreach ($HOF as $x) {
        $h = $x['hof'];
        $p = $x['player'];
    
        ?>    
        <div class="boxWide" style="width: 70%; margin: 20px auto 20px auto;">
            <div class="boxTitle<?php echo T_HTMLBOX_INFO;?>"><?php echo "<a href='".urlcompile(T_URL_PROFILE,T_OBJ_PLAYER,$p->player_id,false,false)."'>$p->name</a> ".$lng->getTrn('from', __CLASS__)." <a href='".urlcompile(T_URL_PROFILE,T_OBJ_TEAM,$p->owned_by_team_id,false,false)."'>$p->f_tname</a>: $h->title";?></div>
            <div class="boxBody">
                <table class="common">
                    <tr>
                        <td align="left" valign="top">
                            <?php echo $h->about;?>
                        </td>
                        <td align="right">
                            <img border='0px' height='100' width='100' alt='player picture' src="<?php $img = new ImageSubSys(T_OBJ_PLAYER, $p->player_id); echo $img->getPath();?>">
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><hr></td>
                    </tr>
                    <tr>
                        <td align="left">
                        <?php echo $lng->getTrn('posted', __CLASS__).' '. $h->date;?>
                        </td>
                        <td colspan="2" align="right">
                        <?php
                        if ($ALLOW_EDIT) {
                            ?> 
                            <a href="handler.php?type=hof&amp;action=edit&amp;hof_id=<?php echo $h->hof_id;?>"><?php echo $lng->getTrn('edit', __CLASS__);?></a>
                            &nbsp;
                            <a href="handler.php?type=hof&amp;action=delete&amp;hof_id=<?php echo $h->hof_id;?>"><?php echo $lng->getTrn('del', __CLASS__);?></a> 
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
