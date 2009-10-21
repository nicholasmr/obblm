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

class Match_HTMLOUT extends Match
{

function recentMatches() {

    global $lng;
    title($lng->getTrn('menu/matches_menu/recent'));
    list($node, $node_id) = HTMLOUT::nodeSelector(false,false,false,'');
    echo '<br>';
    HTMLOUT::recentGames(false,false,$node,$node_id, false,false,array('url' => 'index.php?section=matches&amp;type=recent', 'n' => MAX_RECENT_GAMES));
}

function upcommingMatches() {

    global $lng;
    title($lng->getTrn('menu/matches_menu/upcomming'));
    list($node, $node_id) = HTMLOUT::nodeSelector(false,false,false,'');
    echo '<br>';
    HTMLOUT::upcommingGames(false,false,$node,$node_id, false,false,array('url' => 'index.php?section=matches&amp;type=upcomming', 'n' => MAX_RECENT_GAMES));
}

public static function tourMatches() 
{
    global $lng, $coach;
    
    // Admin actions made?
    if (isset($_GET['action']) && is_object($coach) && $coach->admin) {
        $match = new Match($_GET['mid']);
        switch ($_GET['action'])
        {
            case 'lock':   status($match->setLocked(true)); break;
            case 'unlock': status($match->setLocked(false)); break;
            case 'delete': status($match->delete()); break;
            case 'reset':  status($match->reset()); break;            
        }
    }
    
    $tr = new Tour($_GET['trid']);
    title($tr->name);
    $matches = array();
    foreach ($tr->getMatches() as $m) {
        $matches[$m->round][] = $m;
    }
    ksort($matches);

    foreach ($matches as $round => $matches) {
        echo "<table class='tours'>\n";
        // Determine what to write in "round" field.
        $org_round = $round; # Copy for later use.
        if     ($round == RT_FINAL)         $round = $lng->getTrn('matches/tourmatches/roundtypes/final');
        elseif ($round == RT_3RD_PLAYOFF)   $round = $lng->getTrn('matches/tourmatches/roundtypes/thirdPlayoff');
        elseif ($round == RT_SEMI)          $round = $lng->getTrn('matches/tourmatches/roundtypes/semi');
        elseif ($round == RT_QUARTER)       $round = $lng->getTrn('matches/tourmatches/roundtypes/quarter');
        elseif ($round == RT_ROUND16)       $round = $lng->getTrn('matches/tourmatches/roundtypes/rnd16');
        else                                $round = $lng->getTrn('matches/tourmatches/roundtypes/rnd').": $round";

        ?>
        <tr><td colspan='7' class="seperator"></td></tr>
        <tr>
            <td width="100"></td>
            <td class="round" width="250"><?php echo $round; ?></td>
            <td width="25"></td>
            <td width="50"></td>
            <td width="25"></td>
            <td width="250"></td>
            <td width="260"></td>
        </tr>
        <?php
        foreach ($matches as $m) {
            ?>
            <tr>
            <td></td>
            <td class="match" style="text-align: right;"><?php echo $m->team1_name;?></td>
            <td class="match" style="text-align: center;"><?php echo ($m->is_played) ? $m->team1_score : '';?></td>
            <td class="match" style="text-align: center;">-</td>
            <td class="match" style="text-align: center;"><?php echo ($m->is_played) ? $m->team2_score : '';?></td>
            <td class="match" style="text-align: left;"><?php echo $m->team2_name;?></td>
            <?php
            // Does the user have edit or view rights?
            $matchURL = "index.php?section=matches&amp;type=tourmatches&amp;trid=$tr->tour_id&amp;mid=$m->match_id";
            ?>
            <td>
                &nbsp;
                <a href="index.php?section=matches&amp;type=report&amp;mid=<?php echo $m->match_id;?>">
                <?php
                if (is_object($coach)) {
                    echo (($coach->isInMatch($m->match_id) || $coach->admin) ? $lng->getTrn('common/edit') : $lng->getTrn('common/view')) . "</a>&nbsp;\n";
                    if ($coach->admin) {
                        echo "<a onclick=\"if(!confirm('".$lng->getTrn('matches/tourmatches/reset_notice')."')){return false;}\" href='$matchURL&amp;action=reset'>".$lng->getTrn('common/reset')."</a>&nbsp;\n";
                        echo "<a onclick=\"if(!confirm('".$lng->getTrn('matches/tourmatches/matchdelete')."')){return false;}\" href='$matchURL&amp;action=delete' style='color:".(($m->is_played) ? 'Red' : 'Blue').";'>".$lng->getTrn('common/delete')."</a>&nbsp;\n";
                        echo "<a href='$matchURL&amp;action=".(($m->locked) ? 'unlock' : 'lock')."'>" . ($m->locked ? $lng->getTrn('common/unlock') : $lng->getTrn('common/lock')) . "</a>&nbsp;\n";
                    }
                }
                else {
                    echo $lng->getTrn('common/view')."</a>\n";
                }
                ?>
            </td>
            </tr>
            <?php
        }
    }

    if ($tr->is_finished && isset($tr->winner)) { # If tournament is finished.
        echo "<tr><td colspan='7' class='seperator'></td></tr>";
        $team = new Team($tr->winner);
        echo "<tr>  <td colspan='1'></td>
                    <td colspan='1' class='match'><i>".$lng->getTrn('matches/tourmatches/winner').":</i> $team->name </td>
                    <td colspan='5'></td>
                </tr>\n";
    }
    ?>
    <tr><td colspan='7' class='seperator'></td></tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>
    <?php
}

public static function tours() 
{

    global $rules, $settings, $lng;

    title($lng->getTrn('menu/matches_menu/tours'));

    $query = "SELECT lid,did,tour_id,locked,
        tours.name AS 'tours.name',divisions.name AS 'divisions.name',leagues.name AS 'leagues.name'
        FROM tours,divisions,leagues WHERE tours.f_did = divisions.did AND divisions.f_lid = leagues.lid
        ORDER BY leagues.lid ASC, divisions.did ASC, tours.tour_id ASC";
    $result = mysql_query($query);
    $flist = array();
    while ($row = mysql_fetch_object($result)) {
        $flist[$row->lid][$row->did][$row->tour_id] = $row;
        $flist[$row->lid]['info']            = array('name' => $row->{'leagues.name'});
        $flist[$row->lid][$row->did]['info'] = array('name' => $row->{'divisions.name'});
    }

    // Print fixture list.
    echo "<table class='tours'>\n";
    foreach ($flist as $lid => $divs) {
        echo "<tr class='leauges'><td><b>
        <a href='javascript:void(0);' onClick=\"slideToggleFast('lid_$lid');\"><b>[+/-]</b></a>&nbsp;
        ".$flist[$lid]['info']['name']."
        </b></td></tr>";
        echo "<tr><td><div id='lid_$lid'>";
    foreach ($divs as $did => $tours) {
        if ($did == 'info') continue;
        echo "<table class='tours'>\n";
        echo "<tr class='divisions'><td><b>
        <a href='javascript:void(0);' onClick=\"slideToggleFast('did_$did');\"><b>[+/-]</b></a>&nbsp;
        ".$flist[$lid][$did]['info']['name']."
        </b></td></tr>";
        echo "<tr><td><div id='did_$did'>";
    foreach ($tours as $trid => $mergedObj) {
        if ($trid == 'info') continue;
        ?>
        <table class='tours'>
            <tr class='tours'>
                <td>
                    &nbsp;&nbsp;<a href='index.php?section=matches&amp;type=tourmatches&amp;trid=<?php echo $trid;?>'><b><?php echo $mergedObj->{'tours.name'};?></b></a>
                    <?php
                    $tr = new Tour($trid);
                    $suffix = '';
                    if ($tr->is_finished) { $suffix .= '-&nbsp;&nbsp;<i>'.$lng->getTrn('common/finished').'</i>&nbsp;&nbsp;';}
                    if ($tr->locked)      { $suffix .= '-&nbsp;&nbsp;<i>'.$lng->getTrn('common/locked').'</i>&nbsp;&nbsp;';}
                    if (!empty($suffix)) { echo '&nbsp;&nbsp;'.$suffix;}
                    ?>
                </td>
            </tr>
        </table>
        <?php
    }
    echo "</div></td></tr></table>\n";
    }
    echo "</div></td></tr>\n";
    }
    echo "</table>\n";
}

function report() {

    // Is $match_id valid?
    $match_id = $_GET['mid'];
    if (!get_alt_col('matches', 'match_id', $match_id, 'match_id'))
        fatal("Invalid match ID.");
    
    global $stars;
    global $rules;
    global $lng;
    
    $easyconvert = new array_to_js();
    @$easyconvert->add_array($stars, 'phpStars'); // Load stars array into JavaScript array.
    echo $easyconvert->output_all();

    echo '<script language="JavaScript" type="text/javascript">
    var ID_MERCS = '.ID_MERCS.';
    var ID_STARS_BEGIN = '.ID_STARS_BEGIN.';    
    </script>
    ';
   
    // Create objects
    $coach = (isset($_SESSION['logged_in'])) ? new Coach($_SESSION['coach_id']) : null;
    $m = new Match($match_id);
    $team1 = new Team($m->team1_id);
    $team2 = new Team($m->team2_id);
    
    // Determine visitor privileges.
    $ALLOW_EDIT = false;

    if (!$m->locked && is_object($coach) && ($coach->admin || $coach->isInMatch($m->match_id)))
        $ALLOW_EDIT = true;
    
    $DIS = ($ALLOW_EDIT) ? '' : 'DISABLED';

    /*****************
     *
     * Submitted form?
     *
     *****************/
     
    if (isset($_POST['button']) && $ALLOW_EDIT) {
    
        if (get_magic_quotes_gpc())
            $_POST['summary'] =  stripslashes($_POST['summary']);
        
        // Update general match data
        status($m->update(array(
            'submitter_id'  => $_SESSION['coach_id'],
            'stadium'       => $_POST['stadium'],
            'gate'          => (int) ($_POST['gate'] * 1000),
            'fans'          => (int) $_POST['fans'],
            'ffactor1'      => $_POST['ff_1'],
            'ffactor2'      => $_POST['ff_2'],
            'income1'       => $_POST['inc_1'] ? $_POST['inc_1'] * 1000 : 0,
            'income2'       => $_POST['inc_2'] ? $_POST['inc_2'] * 1000 : 0,
            'team1_score'   => $_POST['result1'] ? $_POST['result1'] : 0,
            'team2_score'   => $_POST['result2'] ? $_POST['result2'] : 0,
            'smp1'          => (int) $_POST['smp1'],
            'smp2'          => (int) $_POST['smp2'],
            'tcas1'         => (int) $_POST['tcas1'],
            'tcas2'         => (int) $_POST['tcas2'],
            'fame1'         => (int) $_POST['fame1'],
            'fame2'         => (int) $_POST['fame2'],
            'tv1'           => (int) $_POST['tv1']*1000,
            'tv2'           => (int) $_POST['tv2']*1000,
            'comment'       => $_POST['summary'] ? $_POST['summary'] : '',
        )));

        // Update match's player data
        foreach (array(1 => $team1, 2 => $team2) as $id => $t) {
        
            /* Save ordinary players */
        
            foreach ($t->getPlayers() as $p) {
            
                if (!self::player_validation($p, $m))
                    continue;
                
                $m->entry(array(
                    'player_id' => $p->player_id,
                    'team_id'   => $t->team_id,
                    // Regarding MVP: We must check for isset() since checkboxes are not sent at all when not checked! 
                    'mvp'     => (isset($_POST['mvp_' . $p->player_id])) ? 1 : 0,
                    'cp'      => $_POST['cp_' . $p->player_id],
                    'td'      => $_POST['td_' . $p->player_id],
                    'intcpt'  => $_POST['intcpt_' . $p->player_id],
                    'bh'      => $_POST['bh_' . $p->player_id],
                    'si'      => $_POST['si_' . $p->player_id],
                    'ki'      => $_POST['ki_' . $p->player_id],
                    'inj'     => $_POST['inj_' . $p->player_id],
                    'agn1'    => $_POST['agn1_' . $p->player_id],
                    'agn2'    => $_POST['agn2_' . $p->player_id],
                ));
            }
            
            /* 
                Save stars entries. 
            */

            foreach ($stars as $star) {
                $s = new Star($star['id']);
                if (isset($_POST['team_'.$star['id']]) && $_POST['team_'.$star['id']] == $id) {
                    $sid = $s->star_id;

                    $m->entry(array(
                        'player_id' => $sid,
                        'team_id'   => $t->team_id,
                        
                        'mvp'     => (isset($_POST["mvp_$sid"]) && $_POST["mvp_$sid"]) ? 1 : 0,
                        'cp'      => $_POST["cp_$sid"],
                        'td'      => $_POST["td_$sid"],
                        'intcpt'  => $_POST["intcpt_$sid"],
                        'bh'      => $_POST["bh_$sid"],
                        'si'      => $_POST["si_$sid"],
                        'ki'      => $_POST["ki_$sid"],
                    ));
                }
                else {
                    $s->rmMatchEntry($m->match_id, $t->team_id);
                }
            }
            
            /* 
                Save mercenary entries. 
            */
            
            Mercenary::rmMatchEntries($m->match_id, $t->team_id); // Remove all previously saved mercs in this match.
            for ($i = 0; $i <= 50; $i++)  { # We don't expect over 50 mercs. This is just some large random number.
                $idm = '_'.ID_MERCS.'_'.$i;
                if (isset($_POST["team$idm"]) && $_POST["team$idm"] == $id) {
                    $m->entry(array(
                        'player_id' => ID_MERCS,
                        'team_id'   => $t->team_id,
                        'nr'        => $i,
                        'skills'    => $_POST["skills$idm"],                    
                        
                        'mvp'     => (isset($_POST["mvp$idm"]) && $_POST["mvp$idm"]) ? 1 : 0,
                        'cp'      => $_POST["cp$idm"],
                        'td'      => $_POST["td$idm"],
                        'intcpt'  => $_POST["intcpt$idm"],
                        'bh'      => $_POST["bh$idm"],
                        'si'      => $_POST["si$idm"],
                        'ki'      => $_POST["ki$idm"],
                    ));
                }
            }
        }

        // Refresh objects used to display form.
        $m = new Match($match_id);
        $team1 = new Team($m->team1_id);
        $team2 = new Team($m->team2_id);
    }
    
    // Match comment made?
    if (isset($_POST['msmrc']) && is_object($coach)) {
        status($m->newComment($coach->coach_id, $_POST['msmrc']));
    }
    
    // Match comment delete?
    if (isset($_POST['type']) && $_POST['type'] == 'cmtdel' && is_object($coach)) {
        status($m->deleteComment($_POST['cid']));
    }

    /****************
     *
     * Generate form 
     *
     ****************/

    title("$m->team1_name - $m->team2_name");
    $CP = 8; // Colspan.

    if (Module::isRegistered('UPLOAD_BOTOCS')) {
        Print "<center><a href='http://".$_SERVER["SERVER_NAME"]."/handler.php?type=leegmgr&replay=".$m->match_id."'>view replay</a></center>";
    }

    ?>
    <table>
    <tr><td><b><?php echo $lng->getTrn('common/league');?></b>:</td><td><?php       echo get_parent_name(T_NODE_MATCH, $m->match_id, T_NODE_LEAGUE);?></td></tr>
    <tr><td><b><?php echo $lng->getTrn('common/division');?></b>:</td><td><?php     echo get_parent_name(T_NODE_MATCH, $m->match_id, T_NODE_DIVISION);?></td></tr>
    <tr><td><b><?php echo $lng->getTrn('common/tournament');?></b>:</td><td><?php   echo get_parent_name(T_NODE_MATCH, $m->match_id, T_NODE_TOURNAMENT);?></td></tr>
    <tr><td><b><?php echo $lng->getTrn('common/dateplayed');?></b>:</td><td><?php   echo ($m->is_played) ? textdate($m->date_played) : '<i>'.$lng->getTrn('matches/report/notplayed').'</i>';?></td></tr>
    </table>
    <br>
    <?php HTMLOUT::helpBox($lng->getTrn('matches/report/help'), $lng->getTrn('common/needhelp')); ?>
    <form method="POST" enctype="multipart/form-data">
        <table class="common">
            <tr class='commonhead'>
                <td colspan="<?php echo $CP;?>"><b><?php echo $lng->getTrn('matches/report/info');?></b></td>
            </tr>
            <tr><td class='seperator' colspan='<?php echo $CP;?>'></td></tr>
            <tr>
                <td colspan='<?php echo $CP;?>'>
                    <b><?php echo $lng->getTrn('matches/report/stadium');?></b>&nbsp;
                    <select name="stadium" <?php echo $DIS;?>>
                        <?php
                        $teams = Team::getTeams();
                        objsort($teams, array('+name'));
                        $stad = ($m->stadium) ? $m->stadium : $m->team1_id;
                        foreach ($teams as $_t) {
                            echo "<option value='$_t->team_id' " . (($stad == $_t->team_id) ? 'SELECTED' : '' ) . " ".(($_t->team_id == $m->team1_id || $_t->team_id == $m->team2_id) ? "style='background-color: ".COLOR_HTML_READY.";'" : '').">$_t->name</option>\n";
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan='<?php echo $CP;?>'>
                    <b><?php echo $lng->getTrn('matches/report/gate');?></b>&nbsp;
                    <input type="text" name="gate" value="<?php echo $m->gate ? $m->gate/1000 : 0;?>" size="4" maxlength="4" <?php echo $DIS;?>>k
                </td>
            </tr>
            <tr>
                <td colspan='<?php echo $CP;?>'>
                    <b><?php echo $lng->getTrn('matches/report/fans');?></b>&nbsp;
                    <input type="text" name="fans" value="<?php echo $m->fans;?>" size="7" maxlength="12" <?php echo $DIS;?>>
                </td>
            </tr>
            
            <tr><td class="seperator" colspan='<?php echo $CP;?>'></td></tr>

            <tr class='commonhead'>
                <td><b><?php echo $lng->getTrn('matches/report/teams');?></b></td>
                <td><b><?php echo $lng->getTrn('matches/report/score');?></b></td>
                <td><b>&Delta; <?php echo $lng->getTrn('matches/report/treas');?></b></td>
                <td><b><?php echo $lng->getTrn('matches/report/ff');?></b></td>
                <td><b><?php echo $lng->getTrn('matches/report/smp');?></b></td>
                <td><b><?php echo $lng->getTrn('matches/report/tcas');?></b></td>
                <td><b><?php echo $lng->getTrn('matches/report/fame');?></b></td>
                <td><b><?php echo $lng->getTrn('matches/report/tv');?></b></td>
            </tr>
            
            <tr><td class='seperator' colspan='<?php echo $CP;?>'></td></tr>

            <tr>
                <td><?php echo $m->team1_name;?></td>
                <td>
                    <input type="text" name="result1" value="<?php echo $m->team1_score ? $m->team1_score : 0;?>" size="1" maxlength="2" <?php echo $DIS;?>>
                </td>
                <td>
                    <input type='text' name='inc_1' value="<?php echo ((int) $m->income1)/1000;?>" size='4' maxlength='4' <?php echo $DIS;?>>k
                </td>
                <td>
                    <input <?php echo $DIS;?> type='radio' name='ff_1' value='1'  <?php echo ($m->ffactor1 == 1)  ? 'CHECKED' : '';?>><font color='green'><b>+1</b></font>
                    <input <?php echo $DIS;?> type='radio' name='ff_1' value='0'  <?php echo ($m->ffactor1 == 0)  ? 'CHECKED' : '';?>><font color='blue'><b>+0</b></font>
                    <input <?php echo $DIS;?> type='radio' name='ff_1' value='-1' <?php echo ($m->ffactor1 == -1) ? 'CHECKED' : '';?>><font color='red'><b>-1</b></font>
                </td>
                <td>
                    <input type="text" name="smp1" value="<?php echo $m->smp1;?>" size="1" maxlength="2" <?php echo $DIS;?>> <?php echo $lng->getTrn('matches/report/pts');?>
                </td>
                <td>
                    <input type="text" name="tcas1" value="<?php echo $m->tcas1;?>" size="1" maxlength="2" <?php echo $DIS;?>>
                </td>
                <td>
                    <input type="text" name="fame1" value="<?php echo $m->fame1;?>" size="1" maxlength="2" <?php echo $DIS;?>>
                </td>
                <td>
                    <input type="text" name="tv1" value="<?php echo ($m->is_played) ? $m->tv1/1000 : $team1->value/1000;?>" size="4" maxlength="10" <?php echo $DIS;?>>k
                </td>
            </tr>
            <tr>
                <td><?php echo $m->team2_name;?></td>
                <td>
                    <input type="text" name="result2" value="<?php echo $m->team2_score ? $m->team2_score : 0;?>" size="1" maxlength="2" <?php echo $DIS;?>>
                </td>
                <td>
                    <input type='text' name='inc_2' value="<?php echo ((int) $m->income2)/1000;?>" size='4' maxlength='4' <?php echo $DIS;?>>k
                </td>
                <td>
                    <input <?php echo $DIS;?> type='radio' name='ff_2' value='1'  <?php echo ($m->ffactor2 == 1)  ? 'CHECKED' : '';?>><font color='green'><b>+1</b></font>
                    <input <?php echo $DIS;?> type='radio' name='ff_2' value='0'  <?php echo ($m->ffactor2 == 0)  ? 'CHECKED' : '';?>><font color='blue'><b>+0</b></font>
                    <input <?php echo $DIS;?> type='radio' name='ff_2' value='-1' <?php echo ($m->ffactor2 == -1) ? 'CHECKED' : '';?>><font color='red'><b>-1</b></font>
                </td>
                <td>
                    <input type="text" name="smp2" value="<?php echo $m->smp2;?>" size="1" maxlength="2" <?php echo $DIS;?>> points
                </td>
                <td>
                    <input type="text" name="tcas2" value="<?php echo $m->tcas2;?>" size="1" maxlength="2" <?php echo $DIS;?>>
                </td>
                <td>
                    <input type="text" name="fame2" value="<?php echo $m->fame2;?>" size="1" maxlength="2" <?php echo $DIS;?>>
                </td>
                <td>
                    <input type="text" name="tv2" value="<?php echo ($m->is_played) ? $m->tv2/1000 : $team2->value/1000;?>" size="4" maxlength="10" <?php echo $DIS;?>>k
                </td>
            </tr>
            
        </table>

        <?php
        foreach (array(1 => $team1, 2 => $team2) as $id => $t) {

            ?>
            <table class='common'>
            <tr><td class='seperator' colspan='13'></td></tr>
            <tr class='commonhead'><td colspan='13'>
                <b><a href="<?php echo urlcompile(T_URL_PROFILE,T_OBJ_TEAM,$t->team_id,false,false);?>"><?php echo $t->name;?></a> <?php echo $lng->getTrn('matches/report/report');?></b>
            </td></tr>
            <tr><td class='seperator' colspan='13'></td></tr>

            <tr>
                <td><i>Nr</i></td>
                <td><i>Name</i></td>
                <td><i>Position</i></td>
                <td><i>MVP</i></td>
                <td><i>Cp</i></td>
                <td><i>TD</i></td>
                <td><i>Int</i></td>
                <td><i>BH</i></td>
                <td><i>SI</i></td>
                <td><i>Ki</i></td>
                <td><i>Inj</i></td>
                <td><i>Ageing</i></td>
                <td><i>Ageing</i></td>
            </tr>
            <?php
            
            foreach ($t->getPlayers() as $p) {

                if (!self::player_validation($p, $m))
                    continue;
            
                // Fetch player data from match
                $status = $p->getStatus($m->match_id);
                $mdat   = $p->getMatchData($m->match_id);

                // Print player row
                echo "<tr ";
                    if ($p->is_journeyman)    {echo 'style="background-color: '.COLOR_HTML_JOURNEY.'"';}
                    elseif ($status == MNG) {echo 'style="background-color: '.COLOR_HTML_MNG.'"';}
                echo " >\n";
                
                echo "<td>$p->nr</td>\n";
                echo "<td>$p->name</td>\n";
                echo "<td>$p->position" . ($status == MNG ? '&nbsp;[MNG]' : '') . "</td>\n";
                echo "<td><input type='checkbox' " . ($mdat['mvp'] ? 'CHECKED ' : '') . (($DIS || ($status == MNG)) ? 'DISABLED' : '') . " name='mvp_$p->player_id'></td>\n";
                foreach (array('cp', 'td', 'intcpt', 'bh', 'si', 'ki') as $field) {
                    echo "<td><input ". (($DIS || ($status == MNG)) ? 'DISABLED' : '') . " type='text' onChange='numError(this);' size='1' maxlength='2' name='" . $field . "_$p->player_id' value='" . $mdat[$field] . "'></td>\n";
                }
                
                ?>
                <td>
                    <select name="inj_<?php echo $p->player_id;?>" <?php echo $DIS || $status == MNG ? 'DISABLED' : ''; ?>>
                        <?php
                        echo "<option value='" . NONE . "' " .  ($mdat['inj'] == NONE ? 'SELECTED' : '') . ">None</option>\n";
                        echo "<option value='" . MNG . "' " .   ($mdat['inj'] == MNG ?  'SELECTED' : '') . ">MNG</option>\n";
                        echo "<option value='" . NI . "' " .    ($mdat['inj'] == NI ?   'SELECTED' : '') . ">Ni</option>\n";
                        echo "<option value='" . MA . "' " .    ($mdat['inj'] == MA ?   'SELECTED' : '') . ">Ma</option>\n";
                        echo "<option value='" . AV . "' " .    ($mdat['inj'] == AV ?   'SELECTED' : '') . ">Av</option>\n";
                        echo "<option value='" . AG . "' " .    ($mdat['inj'] == AG ?   'SELECTED' : '') . ">Ag</option>\n";
                        echo "<option value='" . ST . "' " .    ($mdat['inj'] == ST ?   'SELECTED' : '') . ">St</option>\n";
                        echo "<option value='" . DEAD . "' " .  ($mdat['inj'] == DEAD ? 'SELECTED' : '') . ">Dead!</option>\n";                                
                        ?>
                    </select>
                </td>
                <td>
                    <select name="agn1_<?php echo $p->player_id;?>" <?php echo $DIS || $status == MNG ? 'DISABLED' : ''; ?>>
                        <?php
                        echo "<option value='" . NONE . "' " .  ($mdat['agn1'] == NONE ? 'SELECTED' : '') . ">None</option>\n";
                        echo "<option value='" . NI . "' " .    ($mdat['agn1'] == NI ? 'SELECTED' : '') . ">Ni</option>\n";
                        echo "<option value='" . MA . "' " .    ($mdat['agn1'] == MA ? 'SELECTED' : '') . ">Ma</option>\n";
                        echo "<option value='" . AV . "' " .    ($mdat['agn1'] == AV ? 'SELECTED' : '') . ">Av</option>\n";
                        echo "<option value='" . AG . "' " .    ($mdat['agn1'] == AG ? 'SELECTED' : '') . ">Ag</option>\n";
                        echo "<option value='" . ST . "' " .    ($mdat['agn1'] == ST ? 'SELECTED' : '') . ">St</option>\n";
                        ?>
                    </select>
                </td>
                <td>
                    <select name="agn2_<?php echo $p->player_id;?>" <?php echo $DIS || $status == MNG ? 'DISABLED' : ''; ?>>
                        <?php
                        echo "<option value='" . NONE . "' " .  ($mdat['agn2'] == NONE ? 'SELECTED' : '') . ">None</option>\n";
                        echo "<option value='" . NI . "' " .    ($mdat['agn2'] == NI ? 'SELECTED' : '') . ">Ni</option>\n";
                        echo "<option value='" . MA . "' " .    ($mdat['agn2'] == MA ? 'SELECTED' : '') . ">Ma</option>\n";
                        echo "<option value='" . AV . "' " .    ($mdat['agn2'] == AV ? 'SELECTED' : '') . ">Av</option>\n";
                        echo "<option value='" . AG . "' " .    ($mdat['agn2'] == AG ? 'SELECTED' : '') . ">Ag</option>\n";
                        echo "<option value='" . ST . "' " .    ($mdat['agn2'] == ST ? 'SELECTED' : '') . ">St</option>\n";
                        ?>
                    </select>
                </td>
                </tr>
                <?php
            }
            ?>
            </table>

            <table style='border-spacing: 10px;'>
                <tr>
                    <td align="left" valign="top">
                        <b>Star Players</b>: 
                        <input type='button' id="addStarsBtn_<?php echo $id;?>" value="<?php echo $lng->getTrn('common/add');?>" 
                        onClick="stars = document.getElementById('stars_<?php echo $id;?>'); addStarMerc(<?php echo $id;?>, stars.options[stars.selectedIndex].value);" <?php echo $DIS; ?>>
                        <select id="stars_<?php echo $id;?>" <?php echo $DIS; ?>>
                            <?php
                            foreach ($stars as $s => $d) {
                                echo "<option ".((in_array($t->race, $d['teams'])) ? 'style="background-color: '.COLOR_HTML_READY.';"' : '')." value='$d[id]'>$s</option>\n";
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td align="left" valign="top">
                        <b>Mercenaries</b>: <input type='button' id="addMercsBtn_<?php echo $id;?>" value="<?php echo $lng->getTrn('common/add');?>" onClick="addStarMerc(<?php echo "$id, ".ID_MERCS;?>);" <?php echo $DIS; ?>>
                    </td>
                </tr>
            </table>
            
            <table class='common' id='<?php echo "starsmercs_$id";?>'>
            </table>
            <?php
        }
        ?>
        <table class='common'>
            <tr>
                <td class='seperator' colspan='13'></td>
            </tr>
            <tr class='commonhead'>
                <td colspan='13'><b><?php echo $lng->getTrn('matches/report/summary');?></b></td>
            </tr>
            <tr>
                <td colspan='13'><textarea name='summary' rows='10' cols='100' <?php echo $DIS . ">" . $m->comment; ?></textarea></td>
            </tr>
        </table>
        <br>
        <center>
            <input type="submit" name='button' value="<?php echo $lng->getTrn('common/save');?>" <?php echo $DIS; ?>>
        </center>
    </form>
    <br><br>
    <?php
    $CDIS = (!is_object($coach)) ? 'DISABLED' : '';
    ?>
    <table class="common">
        <tr class='commonhead'>
            <td colspan='13'><b><a href="javascript:void(0)" onclick="obj=document.getElementById('msmrc'); if (obj.style.display != 'none'){obj.style.display='none'}else{obj.style.display='block'};">[+/-]</a> <?php echo $lng->getTrn('matches/report/comments');?></b></td>
        </tr>
        <tr>
            <td class='seperator'></td>
        </tr>
        <tr>
            <td>
                <div id="msmrc">
                    <?php echo $lng->getTrn('matches/report/existCmt');?>: <?php if (!$m->hasComments()) echo '<i>'.$lng->getTrn('common/none').'</i>';?><br><br>
                    <?php
                    foreach ($m->getComments() as $c) {
                        echo "Posted $c->date by <b>$c->sname</b> 
                            <form method='POST' name='cmt$c->cid' style='display:inline; margin:0px;'>
                            <input type='hidden' name='type' value='cmtdel'>
                            <input type='hidden' name='cid' value='$c->cid'>
                            <a href='javascript:void(0);' onClick='document.cmt$c->cid.submit();'>".$lng->getTrn('common/delete')."</a>
                            </form>
                            :<br>".$c->txt."<br><br>\n";
                    }
                    ?>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <form method="POST">
                <?php echo $lng->getTrn('matches/report/newCmt');?>:<br>
                <textarea name="msmrc" rows='5' cols='100' <?php echo $CDIS;?>><?php echo $lng->getTrn('common/nobody');?></textarea>
                <br>
                <input type="submit" value="<?php echo $lng->getTrn('common/submit');?>" name="new_msmrc" <?php echo $CDIS;?>>
                </form>
            </td>
        </tr>
    </table>
    <script language='JavaScript' type='text/javascript'>
        document.getElementById('msmrc').style.display = 'none';
    </script>
    <?php
    
    /* 
        Now, we call javascript routine(s) to fill out stars and mercs rows, if such entries exist in database. 
    */
    
    $i = 0; // Counter. Used to pass PHP-data to Javascript.
    foreach (array(1 => $team1->team_id, 2 => $team2->team_id) as $id => $t) {
        foreach (Star::getStars(STATS_TEAM, $t, STATS_MATCH, $m->match_id) as $s) {
            $s->setStats(false,false, STATS_MATCH, $m->match_id); // Set the star's stats fields to the saved values in the database for this match.
            echo "<script language='JavaScript' type='text/javascript'>\n";
            echo "var mdat$i = [];\n";
            foreach (array('mvp', 'cp', 'td', 'intcpt', 'bh', 'ki', 'si') as $f) {
                echo "mdat${i}['$f'] = ".$s->$f.";\n";
            }
            echo "existingStarMerc($id, $s->star_id, mdat$i);\n";
            echo "</script>\n";
            $i++;
        }
        
        foreach (Mercenary::getMercsHiredByTeam($t, $m->match_id) as $merc) {
            echo "<script language='JavaScript' type='text/javascript'>\n";
            echo "var mdat$i = [];\n";
            foreach (array('mvp', 'cp', 'td', 'intcpt', 'bh', 'ki', 'si', 'skills') as $f) {
                echo "mdat${i}['$f'] = ".$merc->$f.";\n";
            }
            echo "existingStarMerc($id, ".ID_MERCS.", mdat$i);\n";
            echo "</script>\n";
            $i++;
        }
    }
}

private static function player_validation($p, $m) {

    if (!is_object($p) || !is_object($m))
        return false;
        
    // Existing match?                    
    if ($m->is_played) {

        // Skip if player is bought after match was played.
        if ($p->date_bought > $m->date_played)
            return false;
    
        // If sold before this match was played.
        if ($p->is_sold && $p->date_sold < $m->date_played)
            return false;
        
        // Player died in a earlier match.
        if ($p->getStatus($m->match_id) == DEAD)
            return false;
    }
    // New match?
    else {
    
        if ($p->is_dead || $p->is_sold)
            return false;
    }
    
    return true;
}


}

?>
