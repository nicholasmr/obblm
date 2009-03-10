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
                ?>
                <form method="POST">
                <b><?php echo $lng->getTrn('secs/records/player');?>:</b><br>
                <select name="player_id">
                    <?php
                    $players = Player::getPlayers();
                    objsort($players, array('+team_name', '+name', '+nr'));
                    foreach ($players as $p) {
                        echo "<option ".(($player_id && $player_id == $p->player_id) ? 'SELECTED' : '')." value='$p->player_id'>$p->team_name: $p->name</option>\n";
                    }
                    ?>
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
                ?>
                <form method="POST">
                <b><?php echo $lng->getTrn('secs/records/player');?>:</b><br>
                <select name="player_id">
                    <?php
                    $players = Player::getPlayers();
                    objsort($players, array('+team_name', '+name', '+nr'));
                    foreach ($players as $p) {
                        if ($p->is_dead || $p->is_sold) {
                            continue;
                        }
                        echo "<option ".(($player_id && $player_id == $p->player_id) ? 'SELECTED' : '')." value='$p->player_id'>$p->team_name: $p->name</option>\n";
                    }
                    ?>
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
        <div class="recBox" style="width:70%;">
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
                    
                default:
                    $matches = array(); // Make it an empty array.
            } 
            ?>
            <div class="boxTitle2"><?php echo $title; ?></div>
            <div class="boxBody">
                <table class="recBoxTable">
                <?php
                if (!empty($matches)) {
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
                                echo "<h2>$s[1] &nbsp;-&nbsp; $s[2]</h2>";
                                break;
                                
                            case 'svic':
                                echo "<h2>$m->team1_score &nbsp;-&nbsp; $m->team2_score</h2>";
                                break;
                                
                            case 'inc':
                                $a = $m->income1/1000;
                                $b = $m->income2/1000;
                                echo '<h2>'.(($a > $b) ? "${a}k" : "${b}k").'</h2>';
                                break;
                                
                            case 'gate':
                                echo '<h2>'.($m->gate/1000).'k</h2>';
                                break;
                                
                            case 'mfans':
                                echo '<h2>'.$m->fans.'</h2>';
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
                        <i><?php echo get_alt_col('tours', 'tour_id', $m->f_tour_id, 'name');?>, <?php echo $m->date_played;?></i>, 
                        <a href="index.php?section=fixturelist&amp;match_id=<?php echo $m->match_id;?>"><?php echo $lng->getTrn('secs/records/memma/view');?></a> 
                        </small>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3"><hr></td>
                    </tr>
                    <?php
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

function prizes($type)
{

switch ($type) 
{
case PRIZE_PLAYER:
    
break;
case PRIZE_TEAM:
    
break;
case PRIZE_COACH:
    
break;
}

return true;
}

?>
