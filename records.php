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
                <b>Player:</b><br>
                <select name="player_id">
                    <?php
                    $players = Player::getPlayers();
                    objsort($players, array('+team_name', '+name', '+nr'));
                    foreach ($players as $p) {
                        echo "<option ".(($player_id && $player_id == $p->player_id) ? 'SELECTED' : '')." value='$p->player_id'>$p->team_name's $p->name</option>\n";
                    }
                    ?>
                </select>
                <br><br>
                First write a title.<br>
                This should be a short description of what the player has achieved, for example in which area the achievement has been made in.<br>
                <b>Title:</b><br>
                <input type="text" name="title" size="60" maxlength="100" value="<?php echo $title;?>">
                <br><br>
                Now write about the player's achievement in detail.<br>
                If the player has received an bonus (extra) skill, you could also write about that.<br>
                <b>General description:</b><br>
                <textarea name="about" rows="15" cols="100"><?php echo $about;?></textarea>
                <br><br>
                <input type="submit" value="Submit" name="Submit">
                </form>
                <?php                
        
                return;
                break;

        }
    }
    
    /* Print the hall of fame */
    
    ?>
    Welcome to the hall of fame.<br>
    Here league commissioners may exercise their power by giving publicity to players of noteworthy achievements.<br><br>
    <?php
    if ($ALLOW_EDIT) {
        echo "<a href='index.php?section=records&amp;subsec=hof&amp;action=new'>New entry</a><br>\n";
    }
    
    $HOF = HOF::getHOF();
    
    foreach ($HOF as $x) {
        $h = $x['hof'];
        $p = $x['player'];
    
        ?>    
        <div class="recBox">
            <div class="boxTitle2"><?php echo "<a href='index.php?section=coachcorner&amp;player_id=$p->player_id'>$p->name</a> from <a href='index.php?section=coachcorner&amp;team_id=$p->owned_by_team_id'>$p->team_name</a>: $h->title";?></div>
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
                        Posted <?php echo $h->date;?>
                        </td>
                        <td colspan="2" align="right">
                        <?php
                        if ($ALLOW_EDIT) {
                            ?> 
                            <a href="index.php?section=records&amp;subsec=hof&amp;action=edit&amp;hof_id=<?php echo $h->hof_id;?>">Edit</a>
                            &nbsp;
                            <a href="index.php?section=records&amp;subsec=hof&amp;action=delete&amp;hof_id=<?php echo $h->hof_id;?>">Delete</a> 
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
                <b>Player:</b><br>
                <select name="player_id">
                    <?php
                    $players = Player::getPlayers();
                    objsort($players, array('+team_name', '+name', '+nr'));
                    foreach ($players as $p) {
                        if ($p->is_dead || $p->is_sold) {
                            continue;
                        }
                        echo "<option ".(($player_id && $player_id == $p->player_id) ? 'SELECTED' : '')." value='$p->player_id'>$p->team_name's $p->name</option>\n";
                    }
                    ?>
                </select>
                <br><br>
                What is the bounty for this player's head?<br> 
                This could be anything from raw gold to a free bonus skill for one of the players on the killing team.<br>
                <b>Bounty:</b><br>
                <input type="text" name="bounty" size="60" maxlength="100" value="<?php echo $bounty;?>">
                <br><br>
                Now, why is the player wanted?<br>
                <b>Wanted for:</b><br>
                <textarea name="why" rows="15" cols="100"><?php echo $why;?></textarea>
                <br><br>
                <input type="submit" value="Submit" name="Submit">
                </form>
                <br>
                <i>Please note:</i> Once the player has been killed it is up to a leagues commissioner (admin) to give the appropriate award.
                <?php                
        
                return;
                break;

        }
    }
    
    /* Print the wanted players */
    ?>
    Welcome to the wall of wanted players.<br>
    Watch out for these player, a bounty is out for each of their heads!<br><br>
    <?php
    if ($ALLOW_EDIT) {
        echo "<a href='index.php?section=records&amp;subsec=wanted&amp;action=new'>New wanted player</a><br>\n";
    }
    
    $wanted = Wanted::getWanted();
    
    foreach ($wanted as $x) {
        $w = $x['wanted'];
        $p = $x['player'];
    
        ?>    
        <div class="recBox">
            <div class="boxTitle2">Wanted: <?php echo "<a href='index.php?section=coachcorner&amp;player_id=$p->player_id'>$p->name</a>";?></div>
            <div class="boxBody">
                <table class="recBoxTable">
                    <tr>
                        <td colspan="2" align="left" valign="top">
                            <b>Bounty:</b><br>
                            <?php echo $w->bounty;?>
                            <br>
                        </td>
                    </tr>
                    <tr>
                        <td align="left" valign="top">
                        <br>
                        <b>Wanted for:</b><br>
                        <?php 
                        echo $w->why;
                        if ($p->is_dead) {
                            echo "<br><br><font color='red'><b>HAS BEEN KILLED</b></font>\n";
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
                        Posted <?php echo $w->date;?>
                        </td>
                        <td align="right">
                        <?php
                        if ($ALLOW_EDIT) {
                            ?> 
                            <a href="index.php?section=records&amp;subsec=wanted&amp;action=edit&amp;wanted_id=<?php echo $w->wanted_id;?>">Edit</a>
                            &nbsp;
                            <a href="index.php?section=records&amp;subsec=wanted&amp;action=delete&amp;wanted_id=<?php echo $w->wanted_id;?>">Delete</a> 
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
    echo "Which matches are worth remembering in terms of most TDs, killed and so on?<br><br>\n";
    
    $memmatches = Stats::getMemMatches();

    foreach ($memmatches as $d => $matches) {
        ?>
        <div class="recBox" style="width:70%;">
            <?php
            $title = 'Error: No field.';
            switch ($d)
            {
                case 'td':
                    $title = "Most touchdowns";
                    break;

                case 'cp':
                    $title = "Most completions";
                    break;
                    
                case 'intcpt':
                    $title = "Most interceptions";
                    break;
                    
                case 'ki':
                    $title = "Most killed";
                    break;
                    
                case 'bh+ki+si':
                    $title = "Most casualties";
                    break;
                    
                case 'svic':
                    $title = 'Largest score-difference';
                    break;
                    
                case 'inc':
                    $title = 'Largest team income';
                    break;
                    
                case 'gate':
                    $title = 'Largest gate';
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
                        <a href="index.php?section=fixturelist&amp;match_id=<?php echo $m->match_id;?>">View</a> 
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
                    ?><tr><td align="center"><br><br>More than <?php echo MAX_MEM_MATCHES;?> matches has the same record or no record exists at all.<br><br></td></tr><?php
                }
                ?>
                </table>
            </div>
        </div>
        <?php
    }
}

?>
