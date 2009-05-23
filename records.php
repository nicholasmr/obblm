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

function hof($ALLOW_EDIT) {
    
    global $lng;
    
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
                            <img border='0px' height='100' width='100' alt='player picture' src='<?php echo $p->getPic();?>'>
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

function wanted($ALLOW_EDIT)
{
    
    global $lng;
    
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
                            <img border='0px' height='100' width='100' alt='player picture' src='<?php echo $p->getPic();?>'>
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

function mem_matches()
{

    global $lng;
    
    echo $lng->getTrn('secs/records/memma/desc')."<br><br>\n";
    
    $memmatches = Stats::getMemMatches();

    foreach ($memmatches as $d => $matches) {
        ?>
        <div class="recBox" style="width:60%;">
            <?php
            $title = 'Error: No field.';
            switch ($d)
            {
                case 'td':
                    $title = $lng->getTrn('secs/records/memma/td');
                    break;

                case 'cp':
                    $title = $lng->getTrn('secs/records/memma/cp');
                    break;
                    
                case 'intcpt':
                    $title = $lng->getTrn('secs/records/memma/intcpt');
                    break;
                    
                case 'ki':
                    $title = $lng->getTrn('secs/records/memma/ki');
                    break;
                    
                case 'bh+ki+si':
                    $title = $lng->getTrn('secs/records/memma/cas');
                    break;
                    
                case 'svic':
                    $title = $lng->getTrn('secs/records/memma/svic');
                    break;
                    
                case 'inc':
                    $title = $lng->getTrn('secs/records/memma/inc');
                    break;
                    
                case 'gate':
                    $title = $lng->getTrn('secs/records/memma/gate');
                    break;
                    
                case 'mfans':
                    $title = $lng->getTrn('secs/records/memma/mfans');
                    break;
                    
                case 'tvdiff':
                    $title = $lng->getTrn('secs/records/memma/tvdiff');
                    break;
                    
                default:
                    $matches = array(); // Make it an empty array.
            } 
            ?>
            <div class="boxTitle2"><?php echo $title; ?></div>
            <div class="boxBody">
                <table class="recBoxTable">
                <?php
                if (!empty($matches)) {
                $i = count($matches);
                foreach ($matches as $m) {
                    $t1 = new Team($m->team1_id);
                    $t2 = new Team($m->team2_id);
                    ?>
                    <tr>
                        <td align="left" style="width:40%;">
                            <img border='0px' height='30' width='30' alt='team picture' src='<?php echo $t1->getLogo();?>'>
                            <?php echo $t1->name;?>
                        </td>
                        <td align="center">
                        <?php 
                        switch ($d)
                        {
                            case 'td':
                            case 'cp':
                            case 'intcpt':
                            case 'ki':
                            case 'bh+ki+si':
                                $s = $m->getSummedAch($d);
                                echo "<b>$s[1] &nbsp;-&nbsp; $s[2]</b>";
                                break;
                                
                            case 'svic':
                                echo "<b>$m->team1_score &nbsp;-&nbsp; $m->team2_score</b>";
                                break;
                                
                            case 'inc':
                                $a = $m->income1/1000;
                                $b = $m->income2/1000;
                                echo "<b> ${a}k - ${b}k</b>";
                                break;
                                
                            case 'gate':
                                echo '<b>'.($m->gate/1000).'k</b>';
                                break;
                                
                            case 'mfans':
                                echo '<b>'.$m->fans.'</b>';
                                break;

                            case 'tvdiff':
                                echo "<b>".($m->tv1/1000)."k - ".($m->tv2/1000)."k</b>";
                                break;
                        } 
                        ?>
                        </td>
                        <td align="right" style="width:40%;">
                            <?php echo $t2->name;?>
                            <img border='0px' height='30' width='30' alt='team picture' src='<?php echo $t2->getLogo();?>'>
                        </td>
                    </tr>
                    <tr>
                        <td align="right" colspan="3">
                        <small>
                        <i><?php echo get_alt_col('tours', 'tour_id', $m->f_tour_id, 'name');?>, <?php echo textdate($m->date_played, true);?></i>, 
                        <a href="index.php?section=fixturelist&amp;match_id=<?php echo $m->match_id;?>"><?php echo $lng->getTrn('secs/records/memma/view');?></a> 
                        </small>
                        </td>
                    </tr>
                    <?php
                    if (--$i > 0) {
                        echo '<tr><td colspan="3"><hr></td></tr>';
                    }
                }
                }
                else {
                    ?><tr><td align="center"><br><br><?php echo preg_replace('/\sX\s/', ' '.MAX_MEM_MATCHES.' ', $lng->getTrn('secs/records/memma/filled'));?><br><br></td></tr><?php
                }
                ?>
                </table>
            </div>
        </div>
        <?php
    }
}

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
