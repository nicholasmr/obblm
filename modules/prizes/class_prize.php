<?php

/*
 *  Copyright (c) Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2009-2011. All Rights Reserved.
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

class Prize implements ModuleInterface
{

/***************
 * Properties 
 ***************/

// MySQL stored information    
public $prize_id = 0;
public $team_id  = 0;
public $tour_id  = 0;
public $type     = 0; // Is equal to a PRIZE_* constant.
public $date     = '';
public $title    = '';
public $txt      = '';

/***************
 * Methods 
 ***************/
    
function __construct($prid) 
{
    $result = mysql_query("SELECT * FROM prizes WHERE prize_id = $prid");
    if ($result && mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_assoc($result)) {
            foreach ($row as $key => $val) {
                $this->$key = $val;
            }
        }
    }
    
    return true;
}

public function delete()
{
    return (mysql_query("DELETE FROM prizes WHERE prize_id = $this->prize_id"));
}

public function edit($type, $tid, $trid, $title, $txt)
{
    if (mysql_query("UPDATE prizes SET 
                    title = '".mysql_real_escape_string($title)."', 
                    txt = '".mysql_real_escape_string($txt)."',
                    team_id = $tid,
                    tour_id = $trid,
                    type = $type 
                    WHERE prize_id = $this->prize_id")) {
        $this->txt   = $txt;
        $this->title = $title;
        $this->team_id  = $tid;
        $this->tour_id = $trid;
        $this->type  = $type;
        return true;
    }
    else
        return false;
}

/***************
 * Statics
 ***************/

public static function getTypes() 
{
    return array(PRIZE_1ST => 'First place', PRIZE_2ND => 'Second place', PRIZE_3RD => 'Third place', PRIZE_LETHAL => 'Most lethal', PRIZE_FAIR => 'Fair play');
}

public static function getPrizes($type, $id, $N = false)
{
    $prizes = array();
    if (!$type && !$id) { # Special case
        $type = 'ALL';
    }
    $_IS_OBJ = in_array($type, array(T_OBJ_COACH, T_OBJ_TEAM));
    $_LIMIT = ($N && is_numeric($N)) ? " LIMIT $N" : '';
    $_ORDER_BY__NODE = ' ORDER BY tours.date_created DESC, prizes.type ASC';
    $_ORDER_BY__OBJ = ' ORDER BY prizes.date DESC, prizes.type ASC';
    switch ($type) {
        case T_NODE_LEAGUE:
            if (!isset($_FROMWHERE)) { 
                $_FROMWHERE = "FROM prizes,tours,divisions WHERE prizes.tour_id = tours.tour_id AND tours.f_did = divisions.did AND divisions.f_lid = $id";
            }
            # Fall through
        case T_NODE_DIVISION:
            if (!isset($_FROMWHERE)) { 
                $_FROMWHERE = "FROM prizes,tours WHERE prizes.tour_id = tours.tour_id AND tours.f_did = $id";
            }
            # Fall through
        case T_NODE_TOURNAMENT:
            if (!isset($_FROMWHERE)) { 
                $_FROMWHERE = "FROM prizes,tours WHERE prizes.tour_id = tours.tour_id AND tours.tour_id = $id";
            }
            # Fall through
        case 'ALL':
            if (!isset($_FROMWHERE)) { 
                $_FROMWHERE = "FROM prizes,tours WHERE prizes.tour_id = tours.tour_id";
            }
            $query = "SELECT prizes.prize_id AS 'prize_id', tours.tour_id AS 'tour_id' ".$_FROMWHERE.$_ORDER_BY__NODE.$_LIMIT;
            break;
            
        case T_OBJ_COACH:
            $query = "SELECT prize_id FROM prizes, teams WHERE prizes.team_id =  teams.team_id AND owned_by_coach_id = $id".$_ORDER_BY__OBJ.$_LIMIT;
            break;
        case T_OBJ_TEAM:
            $query = "SELECT prize_id FROM prizes WHERE team_id = $id".$_ORDER_BY__OBJ.$_LIMIT;
            break;
            
        default:
            return array();
    }

    $result = mysql_query($query);
    if ($result && mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_assoc($result)) {
            $pr = new Prize($row['prize_id']);
            if ($_IS_OBJ) {
                $prizes[] = $pr;
            }
            else {
                $prizes[$row['tour_id']][] = $pr;
            }
        }
    }
    return $prizes;
}

public static function getPrizesString($obj, $id) 
{
    global $lng;
    // ONLY FOR T_OBJ_*
    $prizes = Prize::getPrizes($obj, $id);
    $str = array();
    $ptypes = Prize::getTypes();
    foreach ($ptypes as $idx => $type) {
        $cnt = count(array_filter($prizes, create_function('$p', 'return ($p->type == '.$idx.');')));
        if ($cnt > 0)
            $str[] = $cnt.'x'.$ptypes[$idx];
    }
    return empty($str) ? $lng->getTrn('common/none') : implode(', ', $str);
}

public static function create($type, $tid, $trid, $title, $txt)
{
    if (!in_array($type, array_keys(Prize::getTypes())))
        return false;

    // Delete if already exists for type and tour.
    $query = "SELECT prize_id FROM prizes WHERE tour_id = $trid AND type = $type";
    $result = mysql_query($query);
    if ($result && mysql_num_rows($result) > 0) {
        $row = mysql_fetch_assoc($result);
        $pr = new Prize($row['prize_id']);
        $pr->delete();
    }

    // Create new.
    $query = "
            INSERT INTO prizes 
            (date, type, team_id, tour_id, title, txt) 
            VALUES 
            (NOW(), $type, $tid, $trid, '".mysql_real_escape_string($title)."', '".mysql_real_escape_string($txt)."')
            ";
    $result = mysql_query($query);
    $query = "SELECT MAX(prize_id) AS 'prize_id' FROM prizes;";
    $result = mysql_query($query);
    $row = mysql_fetch_assoc($result);  
    
    return true;
}

/***************
 * Interface
 ***************/

public static function getModuleAttributes()
{
    return array(
        'author'     => 'Nicholas Mossor Rathmann',
        'moduleName' => 'Prizes',
        'date'       => '2009',
        'setCanvas'  => false,
    );
}

public static function getModuleTables()
{
    return array(
        # Table 1 name => column definitions
        'prizes' => array(
            # Column name => definition
            'prize_id' => 'MEDIUMINT UNSIGNED  NOT NULL PRIMARY KEY AUTO_INCREMENT',
            'team_id'  => 'MEDIUMINT UNSIGNED  NOT NULL DEFAULT 0',
            'tour_id'  => 'MEDIUMINT UNSIGNED  NOT NULL DEFAULT 0',
            'type'     => 'TINYINT UNSIGNED    NOT NULL DEFAULT 0',
            'date'     => 'DATETIME',
            'title'    => 'VARCHAR(100)',
            'txt'      => 'TEXT',
        ),
    );
}

public static function getModuleUpgradeSQL()
{
    return array();
}

public static function triggerHandler($type, $argv){}

public static function main($argv)
{
    /*
        First argument is func name in old Prize class, the rest are arguments for that func.
    */
    $func = array_shift($argv);
    return call_user_func_array(array(__CLASS__, $func), $argv);
}

/***************
 * main() related.
 ***************/

// Main prizes page.
public static function makeList($ALLOW_EDIT)
{
    
    global $lng, $coach, $settings;
    HTMLOUT::frame_begin(is_object($coach) ? $coach->settings['theme'] : $settings['stylesheet']); # Make page frame, banner and menu.
    title($lng->getTrn('name', 'Prize'));
    
    /* A new entry was sent. Add it to system */
    
    if ($ALLOW_EDIT && isset($_POST['tid']) && isset($_POST['trid'])) {
        if (get_magic_quotes_gpc()) {
            $_POST['title'] = stripslashes($_POST['title']);
            $_POST['txt'] = stripslashes($_POST['txt']);
        }
        switch ($_GET['action'])
        {
            case 'new':
                status(Prize::create($_POST['ptype'], $_POST['tid'], $_POST['trid'], $_POST['title'], $_POST['txt']));
                break;
        }
    }
    
    /* Was a request for a new entry made? */ 
    
    elseif (isset($_GET['action']) && $ALLOW_EDIT) {
        
        switch ($_GET['action'])
        {
            case 'delete':
                if (isset($_GET['prid']) && is_numeric($_GET['prid'])) {
                    $pr = new Prize($_GET['prid']);
                    status($pr->delete());
                    unset($pr);
                }
                else {
                    fatal('Sorry. You did not specify which prize ID you wish to delete.');
                }                
                break;
                
            case 'new':
                ?>
                <form method="POST" enctype="multipart/form-data">
                <b><?php echo $lng->getTrn('tour', __CLASS__);?>:</b><br>
                <select name="trid">
                    <?php
                    $tours = Tour::getTours();
                    objsort($tours, array('+name'));
                    foreach ($tours as $tr) {
                        echo "<option value='$tr->tour_id'>$tr->name</option>\n";
                    }
                    ?>
                </select>
                <br><br>
                <b><?php echo $lng->getTrn('team', __CLASS__);?>:</b><br>
                <select name="tid">
                    <?php
                    $teams = Team::getTeams();
                    objsort($teams, array('+name'));
                    foreach ($teams as $t) {
                        echo "<option value='$t->team_id'>$t->name</option>\n";
                    }
                    ?>
                </select>
                <br><br>
                <b><?php echo $lng->getTrn('kind', __CLASS__);?>:</b><br>
                <select name="ptype">
                    <?php
                    foreach (Prize::getTypes() as $ptype => $desc) {
                        echo "<option value='$ptype'>$desc</option>\n";
                    }
                    ?>
                </select>
                <br><br>
                <?php echo $lng->getTrn('title', __CLASS__);?><br>
                <b><?php echo $lng->getTrn('g_title', __CLASS__);?>:</b><br>
                <input type="text" name="title" size="60" maxlength="100" value="">
                <br><br>
                <?php echo $lng->getTrn('about', __CLASS__);?><br>
                <b><?php echo $lng->getTrn('g_about', __CLASS__);?>:</b><br>
                <textarea name="txt" rows="15" cols="100"></textarea>
                <br><br><br>
                <input type="submit" value="<?php echo $lng->getTrn('submit', __CLASS__);?>" name="Submit" <?php echo (empty($tours) | empty($teams)) ? 'DISABLED' : '';?>>
                </form>
                <br>
                <?php
        
                return;
                break;

        }
    }
    
    /* Print the prizes */
    echo $lng->getTrn('desc', __CLASS__)."<br><br>\n";
    list($sel_node, $sel_node_id) = HTMLOUT::nodeSelector(array());
    if ($ALLOW_EDIT) {
        echo "<br><a href='handler.php?type=prize&amp;action=new'>".$lng->getTrn('new', __CLASS__)."</a><br>\n";
    }
    
    Prize::printList($sel_node, $sel_node_id, $ALLOW_EDIT);
    HTMLOUT::frame_end();
}

// Prints prizes list for a given tour_id or all tours.
public static function printList($node, $node_id, $ALLOW_EDIT)
{
    global $lng;
    $prizes = Prize::getPrizes($node, $node_id);
    $FOLD_UP = false; # (count($prizes) > 20);
    foreach ($prizes as $trid => $tourprizes) {
        $tname = get_alt_col('tours', 'tour_id', $trid, 'name');
        ?>    
        <div class="boxWide" style="width: 70%; margin: 20px auto 20px auto;">
            <div class="boxTitle<?php echo T_HTMLBOX_INFO;?>"><?php echo "$tname prizes";?> <a href='javascript:void(0);' onClick="slideToggleFast('<?php echo 'trpr'.$trid;?>');">[+/-]</a></div>
            <div id="trpr<?php echo $trid;?>">
            <div class="boxBody">
                <table class="common" style='border-spacing: 10px;'>
                    <tr>
                        <td><b>Prize&nbsp;type</b></td>
                        <td align='center'><b>Team</b></td>
                        <td><b>About</b></td>
                    </tr>
                    <?php
                    $ptypes = Prize::getTypes();
                    foreach ($tourprizes as $pr) {
                        echo "<tr><td colspan='4'><hr></td></td>";
                        echo "<tr>\n";
                        $delete = ($ALLOW_EDIT) ? '<a href="handler.php?type=prize&amp;action=delete&amp;prid='.$pr->prize_id.'">'.$lng->getTrn('common/delete').'</a>' : '';
                        echo "<td valign='top'><i>".preg_replace('/\s/', '&nbsp;', $ptypes[$pr->type])."</i>&nbsp;$delete</td>\n";
                        echo "<td valign='top'><b>".preg_replace('/\s/', '&nbsp;', get_alt_col('teams', 'team_id', $pr->team_id, 'name'))."</b></td>\n";
                        echo "<td valign='top'>".$pr->title."<br><br><i>".$pr->txt."</i></td>\n";
                        echo "</tr>\n";
                    }
                    ?>
                </table>
            </div>
            </div>
        </div>
        <?php
        if ($FOLD_UP) {
            ?>
            <script language="JavaScript" type="text/javascript">
                document.getElementById('trpr<?php echo $t->tour_id;?>').style.display = 'none';
            </script>
            <?php
        }
    }
}
}

?>
