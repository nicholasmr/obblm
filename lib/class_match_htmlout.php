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
/*************************
 *
 *  RECENT MATCHES
 *
 *************************/

function recentMatches() {

    global $lng;
    title($lng->getTrn('global/secLinks/recent'));
    list($node, $node_id) = HTMLOUT::nodeSelector(false,false,false,'');
    echo '<br>';
    HTMLOUT::recentGames(false,false,$node,$node_id, false,false,array('url' => 'index.php?section=recent', 'n' => MAX_RECENT_GAMES));
}

/*************************
 *
 *  UPCOMMING MATCHES
 *
 *************************/

function upcommingMatches() {

    global $lng;
    title($lng->getTrn('global/secLinks/upcomming'));
    list($node, $node_id) = HTMLOUT::nodeSelector(false,false,false,'');
    echo '<br>';
    HTMLOUT::upcommingGames(false,false,$node,$node_id, false,false,array('url' => 'index.php?section=upcomming', 'n' => MAX_RECENT_GAMES));
}

/*************************
 *
 *  FIXTURE LIST
 *
 *************************/

function sec_fixturelist() {

    global $rules, $settings, $coach, $lng;
    $KEEP_TOUR_OPEN = false;

    // Admin actions made?
    if (is_object($coach) && $coach->admin) {
        if (isset($_GET['lock']) && ($state = 1) || isset($_GET['unlock']) && ($state = 2)) {
            $match = new Match(($state == 1) ? $_GET['lock'] : $_GET['unlock']);
            $KEEP_TOUR_OPEN = $match->f_tour_id;
            status($match->setLocked(($state == 1) ? true : false));
        }
        elseif (isset($_GET['mdel']) && !preg_match('/[^0-9]$/', $_GET['mdel'])) {
            $match = new Match($_GET['mdel']);
            $KEEP_TOUR_OPEN = $match->f_tour_id;
            status($match->delete());
        }
        elseif (isset($_GET['reset']) && !preg_match('/[^0-9]$/', $_GET['reset'])) {
            $match = new Match($_GET['reset']);
            $KEEP_TOUR_OPEN = $match->f_tour_id;
            status($match->reset());
        }
    }

    // Was a match chosen for edit/view?
    if (isset($_GET['match_id']) && !preg_match("/[^0-9]/", $_GET['match_id'])) {
        match_form($_GET['match_id']);
        return;
    }

    // Show tournament standings?
    if (isset($_GET['tour_id']) && !preg_match("/[^0-9]/", $_GET['tour_id'])) {
        if (!is_object($tour = new Tour($_GET['tour_id'])) || empty($tour->date_created))
            fatal('Sorry. Invalid tournament ID specified.');

        /* Table printing */

        switch ($tour->type)
        {
            case TT_RROBIN: $type = 'Round-Robin'; break;
            case TT_FFA:    $type = 'FFA tournament'; break;
        }

        title($tour->name);
        echo "<center><a href='index.php?section=fixturelist'>[".$lng->getTrn('global/misc/back')."]</a></center><br>\n";

        echo "<b>".$lng->getTrn('secs/fixtures/stn/type')."</b>: $type<br>\n";
        echo "<b>".$lng->getTrn('secs/fixtures/stn/rs')."</b>: $tour->rs = ".$tour->getRSSortRule(true)
            .((is_object($coach) && $coach->admin) ? "&nbsp;&nbsp;&nbsp;<a href='index.php?section=admin&amp;subsec=chtr'>[".$lng->getTrn('global/misc/change')."]</a>" : '').
            "<br><br>\n";

        HTMLOUT::standings(STATS_TEAM, STATS_TOUR, $tour->tour_id, array('url' => "index.php?section=fixturelist&amp;tour_id=$tour->tour_id", 'hidemenu' => true));
        if (Module::isRegistered('Prize')) {
            Module::run('Prize', array('printList', $tour->tour_id, false));
        }
        return;
    }

    // Division standings?
    if (isset($_GET['did']) && !preg_match("/[^0-9]/", $_GET['did'])) {
        return;
    }
    // League standings?
    if (isset($_GET['lid']) && !preg_match("/[^0-9]/", $_GET['lid'])) {
        title(get_alt_col('leagues', 'lid', $_GET['lid'], 'name'));
        HTMLOUT::standings(STATS_TEAM, STATS_LEAGUE, (int) $_GET['lid'], array('url' => "index.php?section=fixturelist&amp;lid=$_GET[lid]", 'hidemenu' => true));
        return;
    }

    /*
        Show fixture list.
    */

    title($lng->getTrn('global/secLinks/fixtures'));

    if (Module::isRegistered('UPLOAD_BOTOCS') && $settings['leegmgr_enabled']) {
		?>
		<div style="background-color:#C8C8C8; border: solid 2px; border-color: #C0C0C0; width:40%; padding: 10px;">
		<b>BOTOCS match report upload</b>:
		<a href='handler.php?type=leegmgr'>Click here</a>
		</div>
		<br>
		<?php
    }

    if (isset($settings['cyanide_enabled']) && $settings['cyanide_enabled']) {
		?>
		<div style="background-color:#C8C8C8; border: solid 2px; border-color: #C0C0C0; width:40%; padding: 10px;">
		<b>Cyanide match report upload</b>:
		<a href='handler.php?type=cyanide_match_import'>Click here</a>
		</div>
		<br>
		<?php
    }

    $flist = array( # The fixture list
// $flist MODEL:
#        'league1' => array(
#            'l_obj' => $league_obj
#            'division1' => array(
#                'd_obj' => $division_obj
#                'tour1' => array(
#                    'tour_obj' => $tour_obj,
#                    'round1' => array('match1_id' => $match1_obj, 'match2_id' => $match2_obj),
#                    'round2' => array('match3_id' => $match3_obj, 'match4_id' => $match4_obj)
#                )
#            )
#        )
    );

    // Generate fixture list.
    $leagues = League::getLeagues();
    objsort($leagues, array('+date'));
    foreach ($leagues as $l) {
        $divisions = $l->getDivisions();
        objsort($divisions, array('+did'));
        foreach ($divisions as $d) {
            $tours = $d->getTours();
            objsort($tours, array('+date_created'));
            foreach ($tours as $t) {
                $flist[$l->name][$d->name][$t->name] = array(); # Prevent ksort() errors for empty tournaments.
                foreach ($t->getMatches() as $m) {
                    $flist[$l->name][$d->name][$t->name][$m->round][$m->match_id] = $m; # Copy match object.
                }
                // Sort rounds
                ksort($flist[$l->name][$d->name][$t->name]); # Sort rounds.
                foreach ($flist[$l->name][$d->name][$t->name] as $round => $matches) {
                    if (is_object($flist[$l->name][$d->name][$t->name][$round]))
                        continue;
                    else
                        ksort($flist[$l->name][$d->name][$t->name][$round]); # Sort matches in round by match_id.
                }
                // Objects.
                $flist[$l->name][$d->name][$t->name]['tour_obj'] = $t; # Copy tour object.
                $flist[$l->name][$d->name]['d_obj'] = $d; # Copy tour object.
                $flist[$l->name]['l_obj'] = $l; # Copy tour object.
            }
        }
    }

    // Print fixture list.
    echo "<table style='width:100%;'>\n";
    foreach ($flist as $l => $divs) {
        echo "<tr class='leauges'><td><b>
        <a href='javascript:void(0);' onClick=\"obj=document.getElementById('lid_".$divs['l_obj']->lid."'); if (obj.style.display != 'none'){obj.style.display='none'}else{obj.style.display='block'};\"><b>[+/-]</b></a>&nbsp;
        $l
        </b></td></tr>";
        echo "<tr><td><div id='lid_".$divs['l_obj']->lid."'>";
    foreach ($divs as $d => $flist) {
        if (is_object($flist)) continue;
        echo "<table class='fixtures' style='width:100%;'>\n";
        echo "<tr class='divisions'><td><b>
        <a href='javascript:void(0);' onClick=\"obj=document.getElementById('did_".$flist['d_obj']->did."'); if (obj.style.display != 'none'){obj.style.display='none'}else{obj.style.display='block'};\"><b>[+/-]</b></a>&nbsp;
        $d
        </b></td></tr>";
        echo "<tr><td><div id='did_".$flist['d_obj']->did."'>";
    foreach ($flist as $tour => $rounds) {

        if (is_object($rounds)) continue;

        // Skip tournaments which have no rounds/matches
//        if ($flist[$tour]['tour_obj']->is_empty)
//            continue;

        $t = $flist[$tour]['tour_obj'];

        ?>
        <table style='width:100%;'>
            <tr class='dark'>
                <td>
                    <a href='javascript:void(0);' onClick="obj=document.getElementById('trid_<?php echo $t->tour_id;?>'); if (obj.style.display != 'none'){obj.style.display='none'}else{obj.style.display='block'};"><b>[+/-]</b></a>&nbsp;
                    <?php
                    echo "<b>$tour</b>";
                    $suffix = '';
                    if ($t->is_finished) { $suffix .= '-&nbsp;&nbsp;<i>'.$lng->getTrn('secs/fixtures/fin').'</i>&nbsp;&nbsp;';}
                    if ($t->locked)      { $suffix .= '-&nbsp;&nbsp;<i>'.$lng->getTrn('secs/fixtures/locked').'</i>&nbsp;&nbsp;';}
                    if (!empty($suffix)) { echo '&nbsp;&nbsp;'.$suffix;}
                    ?>
                </td>
                <td style='width:25%; text-align:right;'>
                    <a href='index.php?section=fixturelist&amp;tour_id=<?php echo $t->tour_id;?>'><b><?php echo $lng->getTrn('secs/fixtures/standings');?></b></a>&nbsp;
                </td>
            </tr>
            <tr>
                <td colspan='2'>
                    <div id='trid_<?php echo $t->tour_id;?>'>
                        <table class='fixtures'>
        <?php
        foreach ($rounds as $round => $matches) {

            // Skip the tour object in fixturelist data structure.
            if (is_object($matches))
                continue;

            // Determine what to write in "round" field.
            $org_round = $round; # Copy for later use.
            if     ($round == RT_FINAL)         $round = $lng->getTrn('secs/fixtures/mtypes/final');
            elseif ($round == RT_3RD_PLAYOFF)   $round = $lng->getTrn('secs/fixtures/mtypes/thirdPlayoff');
            elseif ($round == RT_SEMI)          $round = $lng->getTrn('secs/fixtures/mtypes/semi');
            elseif ($round == RT_QUARTER)       $round = $lng->getTrn('secs/fixtures/mtypes/quarter');
            elseif ($round == RT_ROUND16)       $round = $lng->getTrn('secs/fixtures/mtypes/rnd16');
            else                                $round = $lng->getTrn('secs/fixtures/mtypes/rnd').": $round";

            ?>
            <tr><td colspan='7' class="seperator"></td></tr>
            <tr>
                <td width="100"></td>
                <td class="light" width="250"><?php echo $round; ?></td>
                <td class="white" width="25"></td>
                <td class="white" width="50"></td>
                <td class="white" width="25"></td>
                <td class="white" width="250"></td>
                <td width="260"></td>
            </tr>
            <?php

            foreach ($matches as $match_id => $match) { # $match is an object.
                // Some K.O. matches have competitors with team IDs = 0, which are stand-by placeholders (undecided).
                ?>
                <tr>
                <td></td>
                <td class="lightest" style="text-align: right;"><?php echo ($match->team1_id > 0) ? $match->team1_name : '<i>'.$lng->getTrn('secs/fixtures/undecided').'</i>';?></td>
                <td class="lightest" style="text-align: center;"><?php echo ($match->is_played) ? $match->team1_score : '';?></td>
                <td class="lightest" style="text-align: center;">-</td>
                <td class="lightest" style="text-align: center;"><?php echo ($match->is_played) ? $match->team2_score : '';?></td>
                <td class="lightest" style="text-align: left;"><?php echo ($match->team2_id > 0) ? $match->team2_name : '<i>'.$lng->getTrn('secs/fixtures/undecided').'</i>';?></td>
                <?php
                // Does the user have edit or view rights?
                ?>
                <td class="white">
                    &nbsp;
                    <a href="?section=fixturelist&amp;match_id=<?php echo $match->match_id; ?>">
                    <?php
                    if (is_object($coach)) {
                        echo (($coach->isInMatch($match->match_id) || $coach->admin) ? $lng->getTrn('secs/fixtures/edit') : $lng->getTrn('secs/fixtures/view')) . "</a>&nbsp;\n";
                        if ($coach->admin) {
                            echo "<a onclick=\"if(!confirm('".$lng->getTrn('secs/fixtures/reset_notice')."')){return false;}\" href='?section=fixturelist&amp;reset=$match->match_id'>".$lng->getTrn('secs/fixtures/reset')."</a>&nbsp;\n";
                            echo "<a onclick=\"if(!confirm('".$lng->getTrn('secs/fixtures/mdel')."')){return false;}\" href='?section=fixturelist&amp;mdel=$match->match_id' style='color:".(($match->is_played) ? 'Red' : 'Blue').";'>".$lng->getTrn('secs/fixtures/del')."</a>&nbsp;\n";
                            echo "<a href='?section=fixturelist&amp;".(($match->locked) ? 'unlock' : 'lock')."=$match->match_id'>" . ($match->locked ? $lng->getTrn('secs/fixtures/unlock') : $lng->getTrn('secs/fixtures/lock')) . "</a>&nbsp;\n";
                        }
                    }
                    else {
                        echo $lng->getTrn('secs/fixtures/view')."</a>\n";
                    }
                    ?>
                </td>
                </tr>
                <?php
            }
        }

        if ($t->is_finished && isset($t->winner)) { # If tournament is finished.
            echo "<tr><td colspan='7' class='seperator'></td></tr>";
            $team = new Team($t->winner);
            echo "<tr>  <td colspan='1'></td>
                        <td colspan='1' class='light'><i>".$lng->getTrn('secs/fixtures/winner').":</i> $team->name </td>
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
        if (!$settings['force_tour_foldout'] && count($flist) > 1 && !($KEEP_TOUR_OPEN == $t->tour_id)) {
            ?>
            <script language="JavaScript" type="text/javascript">
                document.getElementById('trid_'+<?php echo $t->tour_id;?>).style.display = 'none';
            </script>
            <?php
        }
    }
    echo "</div></td></tr></table>\n";
    }
    echo "</div></td></tr><tr><td class='seperator'></td></tr>\n";
    }
    echo "</table>\n";
}

function match_form($match_id) {

    // Is $match_id valid?
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
            
                if (!player_validation($p, $m))
                    continue;
                
                // Set zero entry for MNG player(s).
                if ($p->getStatus($m->match_id) == MNG) {
                    $_POST['mvp_' . $p->player_id]      = 0;
                    $_POST['cp_' . $p->player_id]       = 0;
                    $_POST['td_' . $p->player_id]       = 0;
                    $_POST['intcpt_' . $p->player_id]   = 0;
                    $_POST['bh_' . $p->player_id]       = 0;
                    $_POST['si_' . $p->player_id]       = 0;
                    $_POST['ki_' . $p->player_id]       = 0;
                    $_POST['inj_' . $p->player_id]      = NONE;
                    $_POST['agn1_' . $p->player_id]     = NONE;
                    $_POST['agn2_' . $p->player_id]     = NONE;
                }
                
                $m->entry(array(
                    'player_id' => $p->player_id,
                    'team_id'   => $t->team_id,
                    /* 
                        Regarding MVP: We must check for isset() since checkboxes are not sent at all when not checked! 
                        We must also test for truth since the MNG-status exception above defines the MNG status, and thereby passing isset() here!
                    */
                    'mvp'     => (isset($_POST['mvp_' . $p->player_id]) && $_POST['mvp_' . $p->player_id]) ? 1 : 0,
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

    title((($m->team1_id) ? $m->team1_name : '<i>'.$lng->getTrn('secs/fixtures/undecided').'</i>') . " - " . (($m->team2_id) ? $m->team2_name : '<i>'.$lng->getTrn('secs/fixtures/undecided').'</i>'));
    $CP = 8; // Colspan.

    $did = get_alt_col('tours', 'tour_id', $m->f_tour_id, 'f_did'); // For below match relations (league, division etc.) table.

    if ( Module::isRegistered('UPLOAD_BOTOCS') )  #&& isset($settings['leegmgr_enabled']) && $settings['leegmgr_enabled']
    {
        Print "<center><a href='http://".$_SERVER["SERVER_NAME"]."/handler.php?type=leegmgr&replay=".$m->match_id."'>view replay</a></center>";
    }

    ?>
    <table>
    <tr><td><b>League</b>:</td><td><?php echo get_alt_col('leagues', 'lid', get_alt_col('divisions', 'did', $did, 'f_lid'), 'name');?></td></tr>
    <tr><td><b>Division</b>:</td><td><?php echo get_alt_col('divisions', 'did', $did, 'name');?></td></tr>
    <tr><td><b>Tournament</b>:</td><td><?php echo get_alt_col('tours', 'tour_id', $m->f_tour_id, 'name');?></td></tr>
    <tr><td><b>Date played</b>:</td><td><?php echo ($m->is_played) ? textdate($m->date_played) : '<i>Not yet played</i>';?></td></tr>
    </table>
    <br>
    <?php HTMLOUT::helpBox($lng->getTrn('secs/fixtures/report/help'), $lng->getTrn('secs/fixtures/report/clickforhelp')); ?>
    <form method="POST" enctype="multipart/form-data">
        <table class="match_form">
            <tr>
                <td colspan="<?php echo $CP;?>" class="dark"><b><?php echo $lng->getTrn('secs/fixtures/report/info');?></b></td>
            </tr>
            <tr><td class='seperator' colspan='<?php echo $CP;?>'></td></tr>
            <tr>
                <td colspan='<?php echo $CP;?>'>
                    <b><?php echo $lng->getTrn('secs/fixtures/report/stad');?></b>&nbsp;
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
                    <b><?php echo $lng->getTrn('secs/fixtures/report/gate');?></b>&nbsp;
                    <input type="text" name="gate" value="<?php echo $m->gate ? $m->gate/1000 : 0;?>" size="4" maxlength="4" <?php echo $DIS;?>>k
                </td>
            </tr>
            <tr>
                <td colspan='<?php echo $CP;?>'>
                    <b><?php echo $lng->getTrn('secs/fixtures/report/fans');?></b>&nbsp;
                    <input type="text" name="fans" value="<?php echo $m->fans;?>" size="7" maxlength="12" <?php echo $DIS;?>>
                </td>
            </tr>
            
            <tr><td class="seperator" colspan='<?php echo $CP;?>'></td></tr>

            <tr>
                <td class="dark"><b><?php echo $lng->getTrn('secs/fixtures/report/teams');?></b></td>
                <td class="dark"><b><?php echo $lng->getTrn('secs/fixtures/report/score');?></b></td>
                <td class="dark"><b>&Delta; <?php echo $lng->getTrn('secs/fixtures/report/treas');?></b></td>
                <td class="dark"><b><?php echo $lng->getTrn('secs/fixtures/report/ff');?></b></td>
                <td class="dark"><b><?php echo $lng->getTrn('secs/fixtures/report/smp');?></b></td>
                <td class="dark"><b><?php echo $lng->getTrn('secs/fixtures/report/tcas');?></b></td>
                <td class="dark"><b><?php echo $lng->getTrn('secs/fixtures/report/fame');?></b></td>
                <td class="dark"><b><?php echo $lng->getTrn('secs/fixtures/report/tv');?></b></td>
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
                    <input type="text" name="smp1" value="<?php echo $m->smp1;?>" size="1" maxlength="2" <?php echo $DIS;?>> <?php echo $lng->getTrn('secs/fixtures/report/pts');?>
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
            <table class='match_form'>
            <tr><td class='seperator' colspan='13'></td></tr>
            <tr><td colspan='13' class='dark'>
                <b><a href="index.php?section=coachcorner&amp;team_id=<?php echo $t->team_id;?>"><?php echo $t->name;?></a> <?php echo $lng->getTrn('secs/fixtures/report/report');?></b>
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

                if (!player_validation($p, $m))
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
            <?php
            if ($rules['enable_stars_mercs']) {
                ?>
                <table style='border-spacing: 10px;'>
                    <tr>
                        <td align="left" valign="top">
                            <b>Star Players</b>: 
                            <input type='button' id="addStarsBtn_<?php echo $id;?>" value="<?php echo $lng->getTrn('secs/fixtures/report/add');?>" 
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
                            <b>Mercenaries</b>: <input type='button' id="addMercsBtn_<?php echo $id;?>" value="<?php echo $lng->getTrn('secs/fixtures/report/add');?>" onClick="addStarMerc(<?php echo "$id, ".ID_MERCS;?>);" <?php echo $DIS; ?>>
                        </td>
                    </tr>
                </table>
                
                <table class='match_form' id='<?php echo "starsmercs_$id";?>'>
                </table>
                <?php
            }
        }
        ?>
        <table class='match_form'>
            <tr>
                <td class='seperator' colspan='13'></td>
            </tr>
            <tr>
                <td colspan='13' class='dark'><b><?php echo $lng->getTrn('secs/fixtures/report/summary');?></b></td>
            </tr>
            <tr>
                <td colspan='13'><textarea name='summary' rows='10' cols='100' <?php echo $DIS . ">" . $m->comment; ?></textarea></td>
            </tr>
        </table>
        <br>
        <center>
            <input type="submit" name='button' value="<?php echo $lng->getTrn('secs/fixtures/report/save');?>" <?php echo $DIS; ?>>
        </center>
    </form>
    <br><br>
    <?php
    $CDIS = (!is_object($coach)) ? 'DISABLED' : '';
    ?>
    <table class="match_form">
        <tr>
            <td colspan='13' class='dark'><b><a href="javascript:void(0)" onclick="obj=document.getElementById('msmrc'); if (obj.style.display != 'none'){obj.style.display='none'}else{obj.style.display='block'};">[+/-]</a> <?php echo $lng->getTrn('secs/fixtures/report/msmrc');?></b></td>
        </tr>
        <tr>
            <td class='seperator'></td>
        </tr>
        <tr>
            <td>
                <div id="msmrc">
                    <?php echo $lng->getTrn('secs/fixtures/report/existCmt');?>: <?php if (!$m->hasComments()) echo '<i>'.$lng->getTrn('secs/fixtures/report/none').'</i>';?><br><br>
                    <?php
                    foreach ($m->getComments() as $c) {
                        echo "Posted $c->date by <b>$c->sname</b> 
                            <form method='POST' name='cmt$c->cid' style='display:inline; margin:0px;'>
                            <input type='hidden' name='type' value='cmtdel'>
                            <input type='hidden' name='cid' value='$c->cid'>
                            <a href='javascript:void(0);' onClick='document.cmt$c->cid.submit();'>[".$lng->getTrn('secs/fixtures/report/delete')."]</a>
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
                <?php echo $lng->getTrn('secs/fixtures/report/newCmt');?>:<br>
                <textarea name="msmrc" rows='5' cols='100' <?php echo $CDIS;?>><?php echo $lng->getTrn('secs/fixtures/report/writeNewCmt');?></textarea>
                <br>
                <input type="submit" value="<?php echo $lng->getTrn('secs/fixtures/report/postCmt');?>" name="new_msmrc" <?php echo $CDIS;?>>
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

function player_validation($p, $m) {

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
