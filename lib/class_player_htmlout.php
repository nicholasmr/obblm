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

class Player_HTMLOUT extends Player
{

public function playerPage() 
{
    global $lng, $coach;
    $team = new Team($this->owned_by_team_id);

    /* Argument(s) passed to generating functions. */
    $ALLOW_EDIT = (is_object($coach) && ($team->owned_by_coach_id == $coach->coach_id || $coach->admin) && !$team->is_retired);
    
    /* Player pages consist of the output of these generating functions. */
    $this->_handleActions($ALLOW_EDIT); # Handles any actions/request sent.
    $this->_head($team);
    $this->_about($ALLOW_EDIT);
    $this->_achievements();
    $this->_matchBest();
    $this->_recentGames();
}

private function _handleActions($ALLOW_EDIT)
{
    $p = $this; // Copy. Used instead of $this for readability.

    if (!$ALLOW_EDIT || !isset($_POST['type'])) {
        return false;
    }
    
    switch ($_POST['type'])
    {
        case 'pic': 
            status($p->savePic(false));
            break;
            
        case 'playertext': 
            if (get_magic_quotes_gpc()) {
                $_POST['playertext'] = stripslashes($_POST['playertext']);
            }
            status($p->saveText($_POST['playertext']));                
            break;
    }
}

private function _head($team)
{
    global $lng;
    $p = $this; // Copy. Used instead of $this for readability.

    title($p->name);
    $players = $team->getPlayers();
    $i = $next = $prev = 0;
    $end = end(array_keys($players));
    foreach ($players as $player) {
        if ($player->player_id == $p->player_id) {
            if ($i == 0) {
                $prev = $end;
                $next = 1;
            }
            elseif ($i == $end) {
                $prev = $end - 1;
                $next = 0;
            }
            else {
                $prev = $i-1;
                $next = $i+1;
            }
        }
        $i++;
    }
    if (count($players) > 1) {
        echo "<center><a href='index.php?section=coachcorner&amp;player_id=".$players[$prev]->player_id."'>[".$lng->getTrn('secs/playerprofile/prev')."]</a> &nbsp;|&nbsp; <a href='index.php?section=coachcorner&amp;player_id=".$players[$next]->player_id."'>[".$lng->getTrn('secs/playerprofile/next')."]</a></center><br>";
    }
}

private function _about($ALLOW_EDIT)
{
    global $lng;
    $p = $this; // Copy. Used instead of $this for readability.
    
    $p->skills = $p->getSkillsStr(true);
    $p->injs = $p->getInjsStr(true);

    ?>
    <div class="row">
        <div class="pboxShort">
            <div class="boxTitle2"><?php echo $lng->getTrn('secs/playerprofile/about');?></div>
            <div class="boxBody">
                <table class="pbox">
                    <tr>
                        <td><b><?php echo $lng->getTrn('secs/playerprofile/name');?></b></td>
                        <td><?php echo "$p->name (#$p->nr)"; ?></td>
                    </tr>
                    <tr>
                        <td><b><?php echo $lng->getTrn('secs/playerprofile/pos');?></b></td>
                        <td><?php echo $p->position; ?></td>
                    </tr>
                    <tr>
                        <td><b><?php echo $lng->getTrn('secs/playerprofile/team');?></b></td>
                        <td><a href="index.php?section=coachcorner&amp;team_id=<?php echo $p->owned_by_team_id; ?>"><?php echo $p->team_name; ?></a></td>
                    </tr>
                    <tr>
                        <td><b><?php echo $lng->getTrn('secs/playerprofile/bought');?></b></td>
                        <td><?php echo $p->date_bought; ?></td>
                    </tr>
                    <tr>
                        <td><b><?php echo $lng->getTrn('secs/playerprofile/status');?></b></td>
                        <td>
                        <?php 
                            if ($p->is_dead) {
                                $p->getDateDied();
                                echo "<b><font color='red'>DEAD</font></b> ($p->date_died)";
                            }
                            elseif ($p->is_sold) {
                                echo "<b>SOLD</b> ($p->date_sold)";
                            }
                            else {
                                echo (($status = Player::theDoctor($p->getStatus(-1))) == 'none') ? '<b><font color="green">Ready</font></b>' : "<b><font color='blue'>$status</font></b>"; 
                            }
                        ?>
                        </td>
                    </tr>
                    <tr>
                        <td><b>Value</b></td>
                        <td><?php echo $p->value/1000 .'k' ?></td>
                    </tr>
                    <tr>
                        <td><b>SPP/extra</b></td>
                        <td><?php echo "$p->spp/$p->extra_spp" ?></td>
                    </tr>
                    <?php
                    if (Module::isRegistered('Wanted')) {
                        ?>
                        <tr>
                            <td><b>Wanted</b></td>
                            <td><?php echo (Module::run('Wanted', array('isWanted', $p->player_id))) ? '<b><font color="red">Yes</font></b>' : 'No';?></td>
                        </tr>
                        <?php
                    }
                    if (Module::isRegistered('HOF')) {
                        ?>
                        <tr>
                            <td><b>In HoF</b></td>
                            <td><?php echo (Module::run('HOF', array('isInHOF', $p->player_id))) ? '<b><font color="green">Yes</font></b>' : 'No';?></td>
                        </tr>
                        <?php
                    }
                    ?>
                    <tr>
                        <td><b>Won</b></td>
                        <td><?php echo "$p->won ($p->row_won streaks)"; ?></td>
                    </tr>
                    <tr>
                        <td><b>Lost</b></td>
                        <td><?php echo "$p->lost ($p->row_lost streaks)"; ?></td>
                    </tr>
                    <tr>
                        <td><b>Draw</b></td>
                        <td><?php echo "$p->draw ($p->row_draw streaks)"; ?></td>
                    </tr>
                    <?php
                    if (Module::isRegistered('SGraph')) {
                        ?>
                        <tr>
                            <td><b>Vis. stats</b></td>
                            <td><?php echo "<a href='handler.php?type=graph&amp;gtype=".SG_T_PLAYER."&amp;id=$p->player_id''><b>View</b></a>\n";; ?></td>
                        </tr>
                        <?php                    
                    }
                    ?>
                    <tr>
                        <td colspan="2"><hr></td>
                    </tr> 
                    <tr>
                        <td><b>Ma</b></td>
                        <td><?php echo $p->ma; ?></td>
                    </tr>
                    <tr>
                        <td><b>St</b></td>
                        <td><?php echo $p->st; ?></td>
                    </tr>
                    <tr>
                        <td><b>Ag</b></td>
                        <td><?php echo $p->ag; ?></td>
                    </tr>
                    <tr>
                        <td><b>Av</b></td>
                        <td><?php echo $p->av; ?></td>
                    </tr>
                    <tr>
                        <td><b>Skills</b></td>
                        <td><?php echo (empty($p->skills)) ? '<i>None</i>' : $p->skills; ?></td>
                    </tr>
                    <tr>
                        <td><b>Injuries</b></td>
                        <td><?php echo (empty($p->injs)) ? '<i>None</i>' : $p->injs; ?></td>
                    </tr>
                    <tr>
                        <td><b>Cp</b></td>
                        <td><?php echo $p->cp; ?></td>
                    </tr>
                    <tr>
                        <td><b>Td</b></td>
                        <td><?php echo $p->td; ?></td>
                    </tr>
                    <tr>
                        <td><b>Int</b></td>
                        <td><?php echo $p->intcpt; ?></td>
                    </tr>
                    <tr>
                        <td><b>BH/SI/Ki</b></td>
                        <td><?php echo "$p->bh/$p->si/$p->ki"; ?></td>
                    </tr>
                    <tr>
                        <td><b>Cas</b></td>
                        <td><?php echo $p->cas; ?></td>
                    </tr>
                    <tr>
                        <td><b>MVP</b></td>
                        <td><?php echo $p->mvp; ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="pboxShort">
            <div class="boxTitle2"><?php echo $lng->getTrn('secs/playerprofile/profile');?></div>
            <div class="boxBody">
                <i><?php echo $lng->getTrn('secs/playerprofile/pic');?></i><hr>
                <?php
                ImageSubSys::makeBox(IMGTYPE_PLAYER, $p->player_id, $ALLOW_EDIT, false);
                ?>
                <br><br>
                <i><?php echo $lng->getTrn('secs/playerprofile/pic');?></i><hr>
                <?php
                $txt = $p->getText(); 
                if (empty($txt)) {
                    $txt = $lng->getTrn('secs/playerprofile/nowrite').' '.$p->name; 
                }
                if ($ALLOW_EDIT) {
                    ?>
                    <form method="POST" enctype="multipart/form-data">
                        <textarea name='playertext' rows='8' cols='45'><?php echo $txt;?></textarea>
                        <br><br>
                        <input type="hidden" name="type" value="playertext">
                        <input type="submit" name='Save' value='<?php echo $lng->getTrn('secs/playerprofile/save');?>'>
                    </form>
                    <?php
                }
                else {
                    echo '<p>'.fmtprint($txt).'</p>';
                }
                ?>
            </div>
        </div>
    </div>
    <?php
}

private function _achievements()
{
    global $lng;
    $p = $this; // Copy. Used instead of $this for readability.
    
    ?>
    <div class="row">
        <div class="pboxLong">
            <div class="boxTitle3"><a href='javascript:void(0);' onClick="obj=document.getElementById('ach'); if (obj.style.display != 'none'){obj.style.display='none'}else{obj.style.display='block'};"><b>[+/-]</b></a> &nbsp;<?php echo $lng->getTrn('secs/playerprofile/ach');?></div>
            <div class="boxBody" id="ach">
                <table class="pbox">
                    <tr>
                        <td><b>Type</b></td>
                        <td><b>Tournament</b></td>
                        <td><b>Opponent</b></td>
                        <td><b>MVP</b></td>
                        <td><b>Cp</b></td>
                        <td><b>Td</b></td>
                        <td><b>Int</b></td>
                        <td><b>Cas</b></td>
                        <td><b>Score</b></td>
                        <td><b>Result</b></td>
                        <td><b>Match</b></td>
                    </tr>
                    <?php
                    foreach (array('intcpt' => 'Interceptions', 'cp' => 'Completions', 'td' => 'Touchdowns', 'mvp' => 'MVP awards', 'bh+ki+si' => 'Cas') as $s => $desc) {
                        $been_there = false;
                        foreach ($p->getAchEntries($s) as $entry) {
                            if (!$been_there)
                                echo "<tr><td colspan='11'><hr></td></tr>";
                            ?>
                            <tr>
                                <?php
                                $m = $entry['match_obj'];
                                if ($been_there) {
                                    echo '<td></td>'; 
                                }
                                else {
                                    echo "<td><i>$desc: " . (($desc == 'Cas') ? $p->cas : $p->$s) . "</i></td>";
                                    $been_there = true;
                                }
                                ?>
                                <td><?php echo get_alt_col('tours', 'tour_id', $m->f_tour_id, 'name'); ?></td>
                                <td><?php echo ($p->owned_by_team_id == $m->team1_id) ? $m->team2_name : $m->team1_name; ?></td>
                                <td><?php echo $entry['mvp']; ?></td>
                                <td><?php echo $entry['cp']; ?></td>
                                <td><?php echo $entry['td']; ?></td>
                                <td><?php echo $entry['intcpt']; ?></td>
                                <td><?php echo $entry['bh']+$entry['si']+$entry['ki']; ?></td>
                                <td><?php echo $m->team1_score .' - '. $m->team2_score; ?></td>
                                <td><?php echo matchresult_icon((($m->is_draw) ? 'D' : (($m->winner == $p->owned_by_team_id) ? 'W' : 'L'))); ?></td>
                                <td><a href='javascript:void(0)' onClick="window.open('index.php?section=fixturelist&amp;match_id=<?php echo $m->match_id;?>');">[view]</a></td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </table>
            </div>
        </div>
    </div>
    <?php
    
    ?>
    <!-- Default open/close values for box -->
    <script language="JavaScript" type="text/javascript">
        document.getElementById('ach').style.display    = 'none';
    </script>
    <?php
}

private function _matchBest()
{
    global $lng;
    $p = $this; // Copy. Used instead of $this for readability.
  
    ?>   
    <div class="row">
        <div class="pboxLong">
            <div class="boxTitle3"><a href='javascript:void(0);' onClick="obj=document.getElementById('mbest'); if (obj.style.display != 'none'){obj.style.display='none'}else{obj.style.display='block'};"><b>[+/-]</b></a> &nbsp;<?php echo $lng->getTrn('secs/playerprofile/best');?></div>
            <div class="boxBody" id="mbest">
                <table class="pbox">
                    <tr>
                        <td><b>Type</b></td>
                        <td><b>Tournament</b></td>
                        <td><b>Opponent</b></td>
                        <td><b>Td</b></td>
                        <td><b>Ki</b></td>
                        <td><b>Score</b></td>
                        <td><b>Result</b></td>
                        <td><b>Match</b></td>
                    </tr>
                    <?php
                    foreach (array('td' => 'scorer', 'ki' => 'killer') as $s => $desc) {
                        $been_there = false;
                        $matches = $p->getMatchMost($s);
                        foreach ($matches as $entry) {
                            if (!$been_there)
                                echo "<tr><td colspan='8'><hr></td></tr>";
                            ?>
                            <tr>
                                <?php
                                $m = $entry['match_obj'];
                                if ($been_there) {
                                    echo '<td></td>'; 
                                }
                                else {
                                    echo "<td><i>Top $desc: " . count($matches) . " times</i></td>";
                                    $been_there = true;
                                }
                                ?>
                                <td><?php echo get_alt_col('tours', 'tour_id', $m->f_tour_id, 'name'); ?></td>
                                <td><?php echo ($p->owned_by_team_id == $m->team1_id) ? $m->team2_name : $m->team1_name; ?></td>
                                <td><?php echo $entry['td']; ?></td>
                                <td><?php echo $entry['ki']; ?></td>
                                <td><?php echo $m->team1_score .' - '. $m->team2_score; ?></td>
                                <td><?php echo matchresult_icon((($m->is_draw) ? 'D' : (($m->winner == $p->owned_by_team_id) ? 'W' : 'L'))); ?></td>
                                <td><a href='javascript:void(0)' onClick="window.open('index.php?section=fixturelist&amp;match_id=<?php echo $m->match_id;?>');">[view]</a></td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </table>
            </div>
        </div>
    </div>
    <?php  
    
    ?>
    <!-- Default open/close values for box -->
    <script language="JavaScript" type="text/javascript">
        document.getElementById('mbest').style.display  = 'none';
    </script>
    <?php
}

private function _recentGames()
{
    global $lng;
    $p = $this; // Copy. Used instead of $this for readability.

    ?>
    <div class="row">
        <div class="pboxLong">
            <div class="boxTitle3"><a href='javascript:void(0);' onClick="obj=document.getElementById('played'); if (obj.style.display != 'none'){obj.style.display='none'}else{obj.style.display='block'};"><b>[+/-]</b></a> &nbsp;<?php echo $lng->getTrn('secs/playerprofile/playedmatches');?></div>
            <div class="boxBody" id="played">
                <?php
                HTMLOUT::recentGames(STATS_PLAYER, $p->player_id, false, false, false, false, array('n' => MAX_RECENT_GAMES, 'url' => "index.php?section=coachcorner&player_id=$p->player_id"));
                ?>
            </div>
        </div>
    </div>
    <?php
    
    ?>
    <!-- Default open/close values for box -->
    <script language="JavaScript" type="text/javascript">
        document.getElementById('played').style.display = 'block';
    </script>
    <?php
}

}

?>
