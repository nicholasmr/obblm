<?php

/*
 *  Copyright (c) Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2008-2009. All Rights Reserved.
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

function prizes($ALLOW_EDIT)
{
    
    global $lng;
    
    /* A new entry was sent. Add it to system */
    
    if ($ALLOW_EDIT && isset($_POST['tid']) && isset($_POST['trid'])) {
        if (get_magic_quotes_gpc()) {
            $_POST['title'] = stripslashes($_POST['title']);
            $_POST['txt'] = stripslashes($_POST['txt']);
        }
        switch ($_GET['action'])
        {
            case 'new':
                status(Prize::create($_POST['ptype'], $_POST['tid'], $_POST['trid'], $_POST['title'], $_POST['txt'], isset($_FILES['pic']) ? 'pic' : false));
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
                <b><?php echo $lng->getTrn('secs/records/tour');?>:</b><br>
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
                <b><?php echo $lng->getTrn('secs/records/team');?>:</b><br>
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
                <b><?php echo $lng->getTrn('secs/records/prizes/kind');?>:</b><br>
                <select name="ptype">
                    <?php
                    foreach (Prize::getTypes() as $ptype => $desc) {
                        echo "<option value='$ptype'>$desc</option>\n";
                    }
                    ?>
                </select>
                <br><br>
                <?php echo $lng->getTrn('secs/records/prizes/title');?><br>
                <b><?php echo $lng->getTrn('secs/records/prizes/g_title');?>:</b><br>
                <input type="text" name="title" size="60" maxlength="100" value="">
                <br><br>
                <?php echo $lng->getTrn('secs/records/prizes/about');?><br>
                <b><?php echo $lng->getTrn('secs/records/prizes/g_about');?>:</b><br>
                <textarea name="txt" rows="15" cols="100"></textarea>
                <br><br>
                <b><?php echo $lng->getTrn('secs/records/prizes/pic');?>:</b><br>
                <input name="pic" type="file">
                <br><br><br>
                <input type="submit" value="<?php echo $lng->getTrn('secs/records/submit');?>" name="Submit" <?php echo (empty($tours) | empty($teams)) ? 'DISABLED' : '';?>>
                </form>
                <br>
                <?php
        
                return;
                break;

        }
    }
    
    /* Print the prizes */
    echo $lng->getTrn('secs/records/prizes/desc')."<br><br>\n";
    if ($ALLOW_EDIT) {
        echo "<a href='index.php?section=records&amp;subsec=prize&amp;action=new'>".$lng->getTrn('secs/records/new')."</a><br>\n";
    }
    
    $tours = Prize::getPrizesByTour(false, false);
    $PACK = (count($tours) > 1);
    
    foreach ($tours as $t) {
    
        ?>    
        <div class="recBox">
            <div class="boxTitle2"><?php echo "$t->name prizes";?> <a href='javascript:void(0);' onClick="obj=document.getElementById('<?php echo 'trpr'.$t->tour_id;?>'); if (obj.style.display != 'none'){obj.style.display='none'}else{obj.style.display='block'};">[+/-]</a></div>
            <div id="trpr<?php echo $t->tour_id;?>">
            <div class="boxBody">
                <table class="recBoxTable" style='border-spacing: 10px;'>
                    <tr>
                        <td><b>Prize&nbsp;type</b></td>
                        <td align='center'><b>Team</b></td>
                        <td><b>About</b></td>
                        <td><b>Photo</b></td>
                    </tr>
                    <?php
                    $ptypes = Prize::getTypes();
                    foreach ($t->prizes as $idx => $probj) {
                        echo "<tr><td colspan='4'><hr></td></td>";
                        echo "<tr>\n";
                        $delete = ($ALLOW_EDIT) ? '<a href="index.php?section=records&amp;subsec=prize&amp;action=delete&amp;prid='.$probj->prize_id.'">[X]</a>' : '';
                        echo "<td valign='top'><i>".preg_replace('/\s/', '&nbsp;', $ptypes[$idx])."</i>&nbsp;$delete</td>\n";
                        echo "<td valign='top'><b>".preg_replace('/\s/', '&nbsp;', get_alt_col('teams', 'team_id', $probj->team_id, 'name'))."</b></td>\n";
                        echo "<td valign='top'>".$probj->title."<br><br><i>".$probj->txt."</i></td>\n";
                        echo "<td><a href='$probj->pic'><img HEIGHT=70 src='$probj->pic' alt='Photo'></a>
</td>\n";
                        echo "</tr>\n";
                    }
                    ?>
                </table>
            </div>
            </div>
        </div>
        <?php
        if ($PACK) {
            ?>
            <script language="JavaScript" type="text/javascript">
                document.getElementById('trpr<?php echo $t->tour_id;?>').style.display = 'none';
            </script>
            <?php
        }
    }
}

?>
