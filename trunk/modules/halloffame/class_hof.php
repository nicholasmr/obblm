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

class HOF extends _Text implements ModuleInterface
{
/***************
 * Properties 
 ***************/

public $hof_id      = 0;
public $player_id   = 0;
public $title       = '';
public $about       = '';

/***************
 * Methods 
 ***************/    

function __construct($hof_id) 
{
    parent::__construct($hof_id);
    
    $this->hof_id       = $this->txt_id;        
    $this->player_id    = $this->f_id;
    $this->title        = $this->txt2;
    $this->about        = $this->txt;
    
    unset($this->txt2);
    unset($this->txt);
}

public function edit($title, $about) 
{
    return parent::edit($about, $title, false, false);
}

/***************
 * Statics
 ***************/

public static function getHOF($n = false)
{
    $HOF = array();

    $result = mysql_query("SELECT txt_id, f_id FROM texts WHERE type = ".T_TEXT_HOF." ORDER BY date DESC" . (($n) ? " LIMIT $n" : ''));
    if ($result && mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_assoc($result)) {
            array_push($HOF, array('hof' => new HOF($row['txt_id']), 'player' => new Player($row['f_id'])));
        }
    }
    
    return $HOF;
}

public static function create($player_id, $title, $about)
{
    return parent::create($player_id, T_TEXT_HOF, $about, $title);
}

/***************
 * Interface
 ***************/

public static function main($argv) 
{
    // func may be "isInHOF" or "makeList".
    $func = array_shift($argv);
    return call_user_func_array("HOF::$func", $argv);
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
    return array();
}

/***************
 * main() related.
 ***************/

public static function isInHOF($pid) 
{
    $query = "SELECT f_id FROM texts WHERE f_id = $pid AND type = ".T_TEXT_HOF;
    return (($result = mysql_query($query)) && mysql_num_rows($result) > 0);
}

public static function makeList($ALLOW_EDIT) {
    
    global $lng;
    title($lng->getTrn('secs/records/d_hof'));
    
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
                    $player_id = $h->player_id;
                    $title = $h->title;
                    $about = $h->about;
                }
                else {
                    fatal('Sorry. You did not specify which HOF-id you wish to edit.');
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
                <?php echo $lng->getTrn('secs/records/hof/title');?><br>
                <b><?php echo $lng->getTrn('secs/records/hof/g_title');?>:</b><br>
                <input type="text" name="title" size="60" maxlength="100" value="<?php echo $title;?>">
                <br><br>
                <?php echo $lng->getTrn('secs/records/hof/about');?><br>
                <b><?php echo $lng->getTrn('secs/records/hof/g_about');?>:</b><br>
                <textarea name="about" rows="15" cols="100"><?php echo $about;?></textarea>
                <br><br>
                <input type="submit" value="<?php echo $lng->getTrn('secs/records/submit');?>" name="Submit">
                </form>
                
                <!-- Set player list to be the players from the default selected team. -->
                <script language='JavaScript' type='text/javascript'>
                    tsel = document.getElementById('teams');
                    updateTeamPlayers(tsel.options[tsel.selectedIndex].value, document.getElementById('players'));
                </script>
                <?php                
        
                return;
                break;

        }
    }
    
    /* Print the hall of fame */
    
    echo $lng->getTrn('secs/records/hof/desc')."<br><br>\n";
    if ($ALLOW_EDIT) {
        echo "<a href='index.php?section=records&amp;subsec=hof&amp;action=new'>".$lng->getTrn('secs/records/new')."</a><br>\n";
    }
    
    $HOF = HOF::getHOF();
    
    foreach ($HOF as $x) {
        $h = $x['hof'];
        $p = $x['player'];
    
        ?>    
        <div class="recBox">
            <div class="boxTitle2"><?php echo "<a href='index.php?section=coachcorner&amp;player_id=$p->player_id'>$p->name</a> ".$lng->getTrn('secs/records/from')." <a href='index.php?section=coachcorner&amp;team_id=$p->owned_by_team_id'>$p->team_name</a>: $h->title";?></div>
            <div class="boxBody">
                <table class="recBoxTable">
                    <tr>
                        <td align="left" valign="top">
                            <?php echo $h->about;?>
                        </td>
                        <td align="right">
                            <img border='0px' height='100' width='100' alt='player picture' src='<?php echo NO_PIC;?>'>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><hr></td>
                    </tr>
                    <tr>
                        <td align="left">
                        <?php echo $lng->getTrn('secs/records/posted').' '. $h->date;?>
                        </td>
                        <td colspan="2" align="right">
                        <?php
                        if ($ALLOW_EDIT) {
                            ?> 
                            <a href="index.php?section=records&amp;subsec=hof&amp;action=edit&amp;hof_id=<?php echo $h->hof_id;?>"><?php echo $lng->getTrn('secs/records/edit');?></a>
                            &nbsp;
                            <a href="index.php?section=records&amp;subsec=hof&amp;action=delete&amp;hof_id=<?php echo $h->hof_id;?>"><?php echo $lng->getTrn('secs/records/del');?></a> 
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
