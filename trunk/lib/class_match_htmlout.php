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

// Fields used in match reports
# Stored name => display name
# Don't touch - order denpendant entries!!!
$T_MOUT_REL = array('nr' => '#', 'name' => 'Name', 'pos' => 'Position',);
$T_MOUT_ACH = array_combine($T_PMD_ACH, array('MVP','Cp','Td','Int','BH','SI','Ki',));
$T_MOUT_IR = array_combine($T_PMD_IR, array('IR1 D1','IR1 D2','IR2 D1','IR2 D2','IR3 D1','IR3 D2',));
$T_MOUT_INJ = array_combine($T_PMD_INJ, array('Inj','Ageing 1','Ageing 2'));

class Match_HTMLOUT extends Match
{

const T_HTML_MATCHES_PER_PAGE = 100;

function recentMatches() {

    global $lng;
    title($lng->getTrn('menu/matches_menu/recent'));
    list($node, $node_id) = HTMLOUT::nodeSelector(array());
    echo '<br>';
    HTMLOUT::recentGames(false,false,$node,$node_id, false,false,array('url' => 'index.php?section=matches&amp;type=recent', 'n' => MAX_RECENT_GAMES));
}

function upcomingMatches() {

    global $lng;
    title($lng->getTrn('menu/matches_menu/upcoming'));
    list($node, $node_id) = HTMLOUT::nodeSelector(array());
    echo '<br>';
    HTMLOUT::upcomingGames(false,false,$node,$node_id, false,false,array('url' => 'index.php?section=matches&amp;type=upcoming', 'n' => MAX_RECENT_GAMES));
}

public static function matchActions($IS_LOCAL_ADMIN) {
    // Admin actions made?
    if (isset($_GET['action']) && $IS_LOCAL_ADMIN) {
		$match = new Match($_GET['mid']);
		switch ($_GET['action'])
		{
			case 'lock':   status($match->setLocked(true)); break;
			case 'unlock': status($match->setLocked(false)); break;
			case 'delete': status($match->delete()); break;
			case 'reset':  status($match->reset()); break;
		}
    }
    else if (isset($_GET['action'])) {
        status(false, 'Sorry, you do not have permission to do that.');
    }
}

public static function tourMatches()
{
    global $lng, $coach;
    global $leagues, $divisions, $tours;

    $trid = $_GET['trid']; # Shortcut for string interpolation.
    if (!isset($trid) || !in_array($trid, array_keys($tours))) { # Not set or not viewable -> deny access.
        fatal('Invalid tournament ID.');
    }
    $IS_LOCAL_ADMIN = (is_object($coach) && $coach->isNodeCommish(T_NODE_TOURNAMENT, (int) $trid));
	self::matchActions($IS_LOCAL_ADMIN);

    $query = "SELECT COUNT(*) FROM matches WHERE f_tour_id = $trid";
    $result = mysql_query($query);
    list($cnt) = mysql_fetch_row($result);
    $pages = ($cnt == 0) ? 1 : ceil($cnt/self::T_HTML_MATCHES_PER_PAGE);
    global $page;
    $page = isset($_GET['page']) ? $_GET['page'] : 1; # Page 1 is default, of course.
    $_url = "?section=matches&amp;type=tourmatches&amp;trid=$trid&amp;";
    title(get_alt_col('tours', 'tour_id', $trid, 'name'));
    echo '<center><table>';
    echo '<tr><td>';
    echo 'Page: '.implode(', ', array_map(create_function('$nr', 'global $page; return ($nr == $page) ? $nr : "<a href=\''.$_url.'page=$nr\'>$nr</a>";'), range(1,$pages)));
    echo '</td></td>';
    echo "<tr><td>    Matches: $cnt</td></td>";
    echo '</table></center>';

    $rnd = 0; # Initial round number must be lower than possible round numbers.
    $cols = 7; # Common columns counter.
    $ROUND_SORT_DIR = (get_alt_col('tours', 'tour_id', $trid, 'type') == TT_RROBIN) ? 'ASC' : 'DESC'; # Sort differently depeding on tour type.
    $query = "SELECT t1.name AS 't1_name', t1.team_id AS 't1_id', t2.name AS 't2_name', t2.team_id AS 't2_id', match_id, date_played, locked, round, team1_score, team2_score
        FROM matches, teams AS t1, teams AS t2 WHERE f_tour_id = $trid AND team1_id = t1.team_id AND team2_id = t2.team_id
        ORDER BY round $ROUND_SORT_DIR, date_played DESC, date_created ASC LIMIT ".(($page-1)*self::T_HTML_MATCHES_PER_PAGE).', '.(($page)*self::T_HTML_MATCHES_PER_PAGE);
    $result = mysql_query($query);
    echo "<table class='tours'>\n";
    while ($m = mysql_fetch_object($result)) {
        if ($m->round != $rnd) {
            $rnd = $m->round;
            $round = '';
            if     ($rnd == RT_FINAL)         $round = $lng->getTrn('matches/tourmatches/roundtypes/final');
            elseif ($rnd == RT_3RD_PLAYOFF)   $round = $lng->getTrn('matches/tourmatches/roundtypes/thirdPlayoff');
            elseif ($rnd == RT_SEMI)          $round = $lng->getTrn('matches/tourmatches/roundtypes/semi');
            elseif ($rnd == RT_QUARTER)       $round = $lng->getTrn('matches/tourmatches/roundtypes/quarter');
            elseif ($rnd == RT_ROUND16)       $round = $lng->getTrn('matches/tourmatches/roundtypes/rnd16');
            else                              $round = $lng->getTrn('matches/tourmatches/roundtypes/rnd').": $rnd";
            echo "<tr><td colspan='$cols' class='seperator'></td></tr>";
            echo "<tr><td colspan='$cols' class='round'><center><b>$round</b></center></td></tr>";
            echo "<tr><td colspan='$cols' class='seperator'></td></tr>";
        }
        ?>
        <tr>
            <td><?php echo !empty($m->date_played) ? textdate($m->date_played, true) : ''; ?></td>
            <td class="match" style="text-align: right;"><?php echo $m->t1_name;?></td>
            <td class="match" style="text-align: center;"><?php echo !empty($m->date_played) ? $m->team1_score : '';?></td>
            <td class="match" style="text-align: center;">-</td>
            <td class="match" style="text-align: center;"><?php echo !empty($m->date_played) ? $m->team2_score : '';?></td>
            <td class="match" style="text-align: left;"><?php echo $m->t2_name;?></td>
            <?php
            // Does the user have edit or view rights?
            $matchURL = "index.php?section=matches&amp;type=tourmatches&amp;trid=$trid&amp;mid=$m->match_id";
            ?>
            <td>
            <?php
            echo "&nbsp;<a href='index.php?section=matches&amp;type=report&amp;mid=$m->match_id'>".$lng->getTrn('common/view')."</a>&nbsp;\n";
            if ($IS_LOCAL_ADMIN) {
				?>
				<script language="JavaScript" type="text/javascript">
					function match_delete() {
						return confirm('<?php echo $lng->getTrn('matches/tourmatches/matchdelete'); ?>');
					}
					function match_reset() {
						return confirm('<?php echo $lng->getTrn('matches/tourmatches/reset_notice'); ?>');
					}
				</script>
				<?php
                echo "<a onclick=\"return match_reset();\" href='$matchURL&amp;action=reset'>".$lng->getTrn('common/reset')."</a>&nbsp;\n";
                echo "<a onclick=\"return match_delete();\" href='$matchURL&amp;action=delete' style='color:".(!empty($m->date_played) ? 'Red' : 'Blue').";'>".$lng->getTrn('common/delete')."</a>&nbsp;\n";
                echo "<a href='$matchURL&amp;action=".(($m->locked) ? 'unlock' : 'lock')."'>" . ($m->locked ? $lng->getTrn('common/unlock') : $lng->getTrn('common/lock')) . "</a>&nbsp;\n";
            }
            ?>
            </td>
        </tr>
        <?php
    }
    echo "</table>\n";
}

public static function tours()
{

    global $rules, $settings, $lng, $coach;

    title($lng->getTrn('menu/matches_menu/tours'));

    $flist = Coach::allowedNodeAccess(Coach::NODE_STRUCT__TREE, is_object($coach) ? $coach->coach_id : false);
    $tourObjs = array();
    $flist_JShides = array();
    $divsToFoldUp = array();

    // Run through the tours to see which nodes should be hidden.
    $ENABLE_LEAG_HIDING = in_array('league', $settings['tourlist_hide_nodes']);
    $ENABLE_DIV_HIDING  = in_array('division', $settings['tourlist_hide_nodes']);
    $ENABLE_TOUR_HIDING = in_array('tournament', $settings['tourlist_hide_nodes']);
    foreach ($flist as $lid => $divs) {
        $HIDE_LEAG = $ENABLE_LEAG_HIDING;
        foreach ($divs as $did => $tours) {
            if ($did == 'desc') continue;
            $HIDE_DIV = $ENABLE_DIV_HIDING;
            $FOLDUP_DIV = $settings['tourlist_foldup_fin_divs'];
            foreach ($tours as $trid => $desc) {
                if ($trid == 'desc') continue;
                $tourObjs[$trid] = new Tour($trid);
                if ($ENABLE_TOUR_HIDING && $tourObjs[$trid]->is_finished) $flist_JShides[] = "trid_$trid";
                $HIDE_DIV   &= $tourObjs[$trid]->is_finished;
                $FOLDUP_DIV &= $tourObjs[$trid]->is_finished;
            }
            if ($HIDE_DIV) $flist_JShides[] = "did_$did";
            if ($FOLDUP_DIV) $divsToFoldUp[] = $did;
            $HIDE_LEAG &= $HIDE_DIV;
        }
        if ($HIDE_LEAG) $flist_JShides[] = "lid_$lid";
    }

    // Print show hidden button.
    ?>
    <script language="JavaScript" type="text/javascript">
        function showFullTourList()
        {
            var hidden=[<?php echo array_strpack("'%s'", $flist_JShides, ',') ?>];
            for (var h in hidden) {
                slideToggleFast(hidden[h]+'_head');
                slideToggleFast(hidden[h]+'_cont');
            }
            return;
        }
    </script>
    <?php
    echo "<a href='javascript:void(0)' onClick='showFullTourList();'>".$lng->getTrn('matches/tours/showhidden')."</a><br>";

    // Print fixture list.
    foreach ($flist as $lid => $divs) {
        # Container
        echo "<div id='lid_${lid}_cont' class='leaugesNCont' style='".((in_array("lid_${lid}", $flist_JShides)) ? "display:none;" : '')."'>";
        # Title
        echo "<div class='leauges'><b><a href='javascript:void(0);' onClick=\"slideToggleFast('lid_$lid');\">[+/-]</a>&nbsp;".$flist[$lid]['desc']['lname']."</b></div>\n";
        # Body
        echo "<div id='lid_$lid'>";
    foreach ($divs as $did => $tours) {
        if ($did == 'desc') continue;
        # Container
        echo "<div id='did_${did}_cont' class='divisionsNCont' style='".((in_array("did_${did}", $flist_JShides)) ? "display:none;" : '')."'>";
        # Title
        echo "<div class='divisions'><b><a href='javascript:void(0);' onClick=\"slideToggleFast('did_$did');\">[+/-]</a>&nbsp;".$flist[$lid][$did]['desc']['dname']."</b></div>";
        # Body
        echo "<div id='did_$did' ".((in_array($did, $divsToFoldUp)) ? 'style="display:none;"' : '').">";
    foreach ($tours as $trid => $desc) {
        if ($trid == 'desc') continue;
        # Container
        echo "<div id='trid_${trid}_cont' class='toursNCont' style='".((in_array("trid_${trid}", $flist_JShides)) ? "display:none;" : '')."'>";
        # Title
        echo "<div class='tours'><a href='index.php?section=matches&amp;type=tourmatches&amp;trid=$trid'>".$flist[$lid][$did][$trid]['desc']['tname']."</a>";
        $tr = $tourObjs[$trid]; # We already have loaded these - reuse them!
        $suffix = '';
        if ($tr->is_finished) { $suffix .= '-&nbsp;&nbsp;<i>'.$lng->getTrn('common/finished').'</i>&nbsp;&nbsp;';}
        if ($tr->locked)      { $suffix .= '-&nbsp;&nbsp;<i>'.$lng->getTrn('common/locked').'</i>&nbsp;&nbsp;';}
        if (!empty($suffix)) { echo '&nbsp;&nbsp;'.$suffix;}
        echo "</div>\n"; # tour title container
        echo "</div>\n"; # tour container
    }
    echo "</div>\n"; # div body container
    echo "</div>\n"; # div container
    }
    echo "</div>\n"; # league body container
    echo "</div>\n"; # league container
    }

}

public static function report() {

    // Is $match_id valid?
    $match_id = $_GET['mid'];
    if (!get_alt_col('matches', 'match_id', $match_id, 'match_id'))
        fatal("Invalid match ID.");

    global $lng, $stars, $rules, $settings, $coach, $racesHasNecromancer, $racesMayRaiseRotters, $DEA, $T_PMD__ENTRY_EXPECTED;
    global $T_MOUT_REL, $T_MOUT_ACH, $T_MOUT_IR, $T_MOUT_INJ;
    global $leagues,$divisions,$tours;

	// Perform actions (delete, lock/unlock and reset). Needs the
    $IS_LOCAL_ADMIN = (is_object($coach) && $coach->isNodeCommish(T_NODE_TOURNAMENT, get_alt_col('matches', 'match_id', $match_id, 'f_tour_id')));
	self::matchActions($IS_LOCAL_ADMIN);

    // Create objects
    $m = new Match($match_id);
    $team1 = new Team($m->team1_id);
    $team2 = new Team($m->team2_id);

    // Determine visitor privileges.
    $lid = $divisions[$tours[$m->f_tour_id]['f_did']]['f_lid'];
    $ALLOW_EDIT = (!$m->locked && is_object($coach) && ($coach->ring == Coach::T_RING_GLOBAL_ADMIN || $leagues[$lid]['ring'] == Coach::T_RING_LOCAL_ADMIN || $coach->isInMatch($m->match_id)));
    $DIS = ($ALLOW_EDIT) ? '' : 'DISABLED';

    // Relay to ES report page?
    if (isset($_GET['es_report'])) { # Don't care what value the GET field has!
        self::report_ES($match_id, !$ALLOW_EDIT);
        return;
    }

    $easyconvert = new array_to_js();
    @$easyconvert->add_array($stars, 'phpStars'); // Load stars array into JavaScript array.
    echo $easyconvert->output_all();

    echo '<script language="JavaScript" type="text/javascript">
    var ID_MERCS = '.ID_MERCS.';
    var ID_STARS_BEGIN = '.ID_STARS_BEGIN.';
    </script>
    ';

    /*****************
     *
     * Submitted form?
     *
     *****************/

    if (isset($_POST['button']) && $ALLOW_EDIT) {

        if (get_magic_quotes_gpc())
            $_POST['summary'] =  stripslashes($_POST['summary']);

        MTS('Report submit STARTED');

        // FIRST, if any raised zombies are kept we need to create their player objects in order have the correct player- vs. match creation & played dates.
        foreach (array(1 => $team1, 2 => $team2) as $id => $t) {
            if (in_array($t->f_race_id, $racesHasNecromancer) && isset($_POST["t${id}zombie"])) {
                $pos_id = $DEA[$t->f_rname]['players']['Zombie']['pos_id'];
                list($exitStatus,$pid) = Player::create(
                    array(
                        'nr' => $t->getFreePlayerNr(),
                        'f_pos_id' => $pos_id,
                        'team_id' => $t->team_id,
                        'name' => 'RAISED ZOMBIE'
                    ),
                    array(
                        'free' => true,
                    ));
                /*
                    Knowing the new zombie's PID we relocate the zombie match data to regular player data - this allows us
                    to use the same loop for submitting the zombie's match data.
                */
                foreach ($T_PMD__ENTRY_EXPECTED as $f) {
                    $postName = "${f}_t${id}zombie";
                    $_POST["${f}_$pid"] = isset($_POST[$postName]) ? $_POST[$postName] : 0;
                    unset($_POST[$postName]);
                }
            }
        }

        // SECONDLY, look for raised rotters too, do same as above with zombies...
        foreach (array(1 => $team1, 2 => $team2) as $id => $t) {
            if (in_array($t->f_race_id, $racesMayRaiseRotters) && isset($_POST["t${id}rotterCnt"]) && ($N = (int) $_POST["t${id}rotterCnt"]) > 0) {
                foreach (range(1,$N) as $n) {
                    $pos_id = $DEA[$t->f_rname]['players']['Rotter']['pos_id'];
                    list($exitStatus,$pid) = Player::create(
                        array(
                            'nr' => $t->getFreePlayerNr(),
                            'f_pos_id' => $pos_id,
                            'team_id' => $t->team_id,
                            'name' => "RAISED ROTTER $n"
                        ),
                        array(
                            'free' => true,
                        ));

                    /*
                        Knowing the new rotter's PID we relocate the rotter match data to regular player data - this allows us
                        to use the same loop for submitting the rotter's match data.
                    */
                    foreach ($T_PMD__ENTRY_EXPECTED as $f) {
                        $postName = "${f}_t${id}rotter$n";
                        $_POST["${f}_$pid"] = isset($_POST[$postName]) ? $_POST[$postName] : 0;
                        unset($_POST[$postName]);
                    }
                }
            }
        }

        // Update general match data
        status($m->update(array(
            'submitter_id'  => (int) $_SESSION['coach_id'],
            'stadium'       => (int) $_POST['stadium'],
            'gate'          => (int) $_POST['gate']*1000,
            'fans'          => (int) $_POST['fans'],
            'ffactor1'      => (int) $_POST['ff1'],
            'ffactor2'      => (int) $_POST['ff2'],
            'income1'       => (int) $_POST['inc1']*1000,
            'income2'       => (int) $_POST['inc2']*1000,
            'team1_score'   => (int) $_POST['result1'],
            'team2_score'   => (int) $_POST['result2'],
            'smp1'          => (int) $_POST['smp1'],
            'smp2'          => (int) $_POST['smp2'],
            'tcas1'         => (int) $_POST['tcas1'],
            'tcas2'         => (int) $_POST['tcas2'],
            'fame1'         => (int) $_POST['fame1'],
            'fame2'         => (int) $_POST['fame2'],
            'tv1'           => (int) $_POST['tv1']*1000,
            'tv2'           => (int) $_POST['tv2']*1000,
        )));
        if (!empty($_POST['summary'])) {
            $m->saveText($_POST['summary']); # Save summery.
        }
        MTS('matches entry submitted');

        // Update match's player data
        foreach (array(1 => $team1, 2 => $team2) as $id => $t) {

            /* Save ordinary players */

            foreach ($t->getPlayers() as $p) {

                if (!self::player_validation($p, $m))
                    continue;

                // We create zero entries for MNG player(s). This is required!
                if ($p->getStatus($m->match_id) == MNG) {
                    $_POST['mvp_' . $p->player_id]      = 0;
                    $_POST['cp_' . $p->player_id]       = 0;
                    $_POST['td_' . $p->player_id]       = 0;
                    $_POST['intcpt_' . $p->player_id]   = 0;
                    $_POST['bh_' . $p->player_id]       = 0;
                    $_POST['si_' . $p->player_id]       = 0;
                    $_POST['ki_' . $p->player_id]       = 0;
                    $_POST['ir1_d1_' . $p->player_id]   = 0;
                    $_POST['ir1_d2_' . $p->player_id]   = 0;
                    $_POST['ir2_d1_' . $p->player_id]   = 0;
                    $_POST['ir2_d2_' . $p->player_id]   = 0;
                    $_POST['ir3_d1_' . $p->player_id]   = 0;
                    $_POST['ir3_d2_' . $p->player_id]   = 0;
                    $_POST['inj_' . $p->player_id]      = NONE;
                    $_POST['agn1_' . $p->player_id]     = NONE;
                    $_POST['agn2_' . $p->player_id]     = NONE;
                }

                $m->entry($p->player_id, array(
                    'mvp'     => $_POST['mvp_' . $p->player_id],
                    'cp'      => $_POST['cp_' . $p->player_id],
                    'td'      => $_POST['td_' . $p->player_id],
                    'intcpt'  => $_POST['intcpt_' . $p->player_id],
                    'bh'      => $_POST['bh_' . $p->player_id],
                    'si'      => $_POST['si_' . $p->player_id],
                    'ki'      => $_POST['ki_' . $p->player_id],
                    'ir1_d1'  => $_POST['ir1_d1_' . $p->player_id],
                    'ir1_d2'  => $_POST['ir1_d2_' . $p->player_id],
                    'ir2_d1'  => $_POST['ir2_d1_' . $p->player_id],
                    'ir2_d2'  => $_POST['ir2_d2_' . $p->player_id],
                    'ir3_d1'  => $_POST['ir3_d1_' . $p->player_id],
                    'ir3_d2'  => $_POST['ir3_d2_' . $p->player_id],
                    'inj'     => $_POST['inj_' . $p->player_id],
                    'agn1'    => $_POST['agn1_' . $p->player_id],
                    'agn2'    => $_POST['agn2_' . $p->player_id],
                ));
            }
            MTS('Saved all REGULAR player entries in match_data for team '.$id);

            /*
                Save stars entries.
            */

            foreach ($stars as $star) {
                $s = new Star($star['id']);
                if (isset($_POST['team_'.$star['id']]) && $_POST['team_'.$star['id']] == $id) {
                    $sid = $s->star_id;

                    $m->entry($sid, array(
                        // Star required input
                        'f_team_id' => $t->team_id,
                        // Regular input
                        'mvp'     => $_POST["mvp_$sid"],
                        'cp'      => $_POST["cp_$sid"],
                        'td'      => $_POST["td_$sid"],
                        'intcpt'  => $_POST["intcpt_$sid"],
                        'bh'      => $_POST["bh_$sid"],
                        'si'      => $_POST["si_$sid"],
                        'ki'      => $_POST["ki_$sid"],
                        'ir1_d1'  => 0,
                        'ir1_d2'  => 0,
                        'ir2_d1'  => 0,
                        'ir2_d2'  => 0,
                        'ir3_d1'  => 0,
                        'ir3_d2'  => 0,
                        'inj'     => NONE,
                        'agn1'    => NONE,
                        'agn2'    => NONE,
                    ));
                }
                else {
                    $s->rmMatchEntry($m->match_id, $t->team_id);
                }
            }
            MTS('Saved all STAR player entries in match_data for team '.$id);

            /*
                Save mercenary entries.
            */

            Mercenary::rmMatchEntries($m->match_id, $t->team_id); // Remove all previously saved mercs in this match.
            for ($i = 0; $i <= 50; $i++)  { # We don't expect over 50 mercs. This is just some large random number.
                $idm = '_'.ID_MERCS.'_'.$i;
                if (isset($_POST["team$idm"]) && $_POST["team$idm"] == $id) {
                    $m->entry(ID_MERCS, array(
                        // Merc required input
                        'f_team_id' => $t->team_id,
                        'nr'        => $i,
                        'skills'    => $_POST["skills$idm"],
                        // Regular input
                        'mvp'     => $_POST["mvp$idm"],
                        'cp'      => $_POST["cp$idm"],
                        'td'      => $_POST["td$idm"],
                        'intcpt'  => $_POST["intcpt$idm"],
                        'bh'      => $_POST["bh$idm"],
                        'si'      => $_POST["si$idm"],
                        'ki'      => $_POST["ki$idm"],
                        'ir1_d1'  => 0,
                        'ir1_d2'  => 0,
                        'ir2_d1'  => 0,
                        'ir2_d2'  => 0,
                        'ir3_d1'  => 0,
                        'ir3_d2'  => 0,
                        'inj'     => NONE,
                        'agn1'    => NONE,
                        'agn2'    => NONE,
                    ));
                }
            }
            MTS('Saved all STAR player entries in match_data for team '.$id);
        }

        $m->finalizeMatchSubmit(); # Required!
        MTS('Report submit ENDED');

        // Refresh objects used to display form.
        $m = new Match($match_id);
        $team1 = new Team($m->team1_id);
        $team2 = new Team($m->team2_id);
    }

    /****************
     *
     * Generate form
     *
     ****************/

    title("$m->team1_name - $m->team2_name");
    $CP = 8; // Colspan.

    ?>
    <table>
    <tr><td></td><td style='text-align: right;'><i><?php echo $lng->getTrn('common/home');?></i></td><td>&mdash;</td><td style='text-align: left;'><i><?php echo $lng->getTrn('common/away');?></i></td></tr>
    <tr><td><b><?php echo $lng->getTrn('common/teams');?></b>:</td><td style='text-align: right;'><?php echo "$m->team1_name</td><td> &mdash; </td><td style='text-align: left;'>$m->team2_name";?></td></tr>
    <tr><td><b><?php echo $lng->getTrn('common/coaches');?></b>:</td><td style='text-align: right;'><?php echo "$m->coach1_name</td><td> &mdash; </td><td style='text-align: left;'>$m->coach2_name";?></td></tr>
    <tr><td><b><?php echo $lng->getTrn('common/races');?></b>:</td><td style='text-align: right;'><?php echo "$m->race1_name</td><td> &mdash; </td><td style='text-align: left;'>$m->race2_name";?></td></tr>
    <tr><td colspan="4"><hr></td></tr>
    <tr><td><b><?php echo $lng->getTrn('common/league');?></b>:</td><td colspan="3"><?php       echo get_parent_name(T_NODE_MATCH, $m->match_id, T_NODE_LEAGUE);?></td></tr>
    <tr><td><b><?php echo $lng->getTrn('common/division');?></b>:</td><td colspan="3"><?php     echo get_parent_name(T_NODE_MATCH, $m->match_id, T_NODE_DIVISION);?></td></tr>
    <tr><td><b><?php echo $lng->getTrn('common/tournament');?></b>:</td><td colspan="3"><?php   echo get_parent_name(T_NODE_MATCH, $m->match_id, T_NODE_TOURNAMENT);?></td></tr>
    <tr><td><b><?php echo $lng->getTrn('common/dateplayed');?></b>:</td><td colspan="3"><?php   echo ($m->is_played) ? textdate($m->date_played) : '<i>'.$lng->getTrn('matches/report/notplayed').'</i>';?></td></tr>
    <?php
    if (Module::isRegistered('UPLOAD_BOTOCS')) {
        echo "<tr><td><b>Replay</b>:</td><td colspan='3'><a href='handler.php?type=leegmgr&amp;replay=$m->match_id'>View replay</a></td></tr>";
    }
	if ($IS_LOCAL_ADMIN) {
		?>
		<script language="JavaScript" type="text/javascript">
			function match_delete() {
				return confirm('<?php echo $lng->getTrn('matches/tourmatches/matchdelete'); ?>');
			}
			function match_reset() {
				return confirm('<?php echo $lng->getTrn('matches/tourmatches/reset_notice'); ?>');
			}
		</script>
	    <?php
		$matchURL = "index.php?section=matches&type=report&amp;mid=$m->match_id";
		$deleteURL = "index.php?section=matches&amp;type=tourmatches&amp;trid=$m->f_tour_id&amp;mid=$m->match_id";

		echo "<tr><td><b>Admin:</b></td><td colspan='3'>";
		echo "<a onclick=\"return match_reset();\" href='$matchURL&amp;action=reset'>".$lng->getTrn('common/reset')."</a>&nbsp;\n";
		echo "<a onclick=\"return match_delete();\" href='$deleteURL&amp;action=delete' style='color:".(!empty($m->date_played) ? 'Red' : 'Blue').";'>".$lng->getTrn('common/delete')."</a>&nbsp;\n";
		echo "<a href='$matchURL&amp;action=".(($m->locked) ? 'unlock' : 'lock')."'>" . ($m->locked ? $lng->getTrn('common/unlock') : $lng->getTrn('common/lock')) . "</a>&nbsp;\n";
		echo "</td></tr>";
	}
?>
    </table>
    <br>
    <?php echo "<b><a TARGET='_blank' href='".DOC_URL_GUIDE."'>".$lng->getTrn('common/needhelp')."</a></b><br><br>"; ?>
    <form method="POST" enctype="multipart/form-data">
        <table class="common">
            <tr class='commonhead'><td colspan="<?php echo $CP;?>"><b><?php echo $lng->getTrn('matches/report/info');?></b></td></tr>
            <tr><td class='seperator' colspan='<?php echo $CP;?>'></td></tr>
            <tr><td colspan='<?php echo $CP;?>'>
                <b><?php echo $lng->getTrn('matches/report/stadium');?></b>&nbsp;
                <select name="stadium" <?php echo $DIS;?>>
                    <?php
                    $stad = ($m->stadium) ? $m->stadium : $m->team1_id;
                    foreach (array($team1, $team2) as $_t) {
                        echo "<option value='$_t->team_id'".(($stad == $_t->team_id) ? 'SELECTED' : '').">$_t->name</option>\n";
                    }
                    ?>
                </select>
            </td></tr>
            <tr><td colspan='<?php echo $CP;?>'>
                <b><?php echo $lng->getTrn('common/gate');?></b>&nbsp;
                <input type="text" name="gate" value="<?php echo $m->gate ? $m->gate/1000 : 0;?>" size="4" maxlength="4" <?php echo $DIS;?>>k
            </td></tr>
            <tr><td colspan='<?php echo $CP;?>'>
                <b><?php echo $lng->getTrn('matches/report/fans');?></b>&nbsp;
                <input type="text" name="fans" value="<?php echo $m->fans;?>" size="7" maxlength="12" <?php echo $DIS;?>>
            </td></tr>
            <?php
            if (!$settings['hide_ES_extensions']) {
                ?>
                <tr><td colspan='<?php echo $CP;?>'>
                    <b>E</b>xtra player <b>S</b>tats (ES) <a href="index.php?section=matches&amp;type=report&amp;mid=<?php echo $m->match_id?>&amp;es_report=1">report page here</a>
                </td></tr>
                <?php
            }
            ?>
            <tr><td class="seperator" colspan='<?php echo $CP;?>'></td></tr>
            <tr class='commonhead'>
                <td><b><?php echo $lng->getTrn('common/teams');?></b></td>
                <td><b><?php echo $lng->getTrn('common/score');?></b></td>
                <td><b>&Delta; <?php echo $lng->getTrn('matches/report/treas');?></b></td>
                <td><b><?php echo $lng->getTrn('matches/report/ff');?></b></td>
                <td><b><?php echo $lng->getTrn('matches/report/smp');?></b></td>
                <td><b><?php echo $lng->getTrn('matches/report/tcas');?></b></td>
                <td><b><?php echo $lng->getTrn('matches/report/fame');?></b></td>
                <td><b><?php echo $lng->getTrn('matches/report/tv');?></b></td>
            </tr>

            <tr><td class='seperator' colspan='<?php echo $CP;?>'></td></tr>
            <?php
            foreach (array(1,2) as $N) {
                echo "<tr>\n";
                echo "<td>".${"team$N"}->name."</td>\n";
                echo "<td><input type='text' name='result$N' value='".((int) $m->{"team${N}_score"})."' size='1' maxlength='2' $DIS></td>\n";
                echo "<td><input type='text' name='inc$N' value='".(((int) $m->{"income$N"})/1000)."' size='4' maxlength='4' $DIS>k</td>\n";
                echo "<td>";
                foreach (array('1' => 'green', '0' => 'blue', '-1' => 'red') as $Nff => $color) {
                    echo "<input $DIS type='radio' name='ff$N' value='$Nff' ".(($m->{"ffactor$N"} == (int) $Nff) ? 'CHECKED' : '')."><font color='$color'><b>$Nff</b></font>";
                }
                echo "</td>\n";
                echo "<td><input type='text' name='smp$N' value='".($m->{"smp$N"})."' size='1' maxlength='2' $DIS>".$lng->getTrn('matches/report/pts')."</td>\n";
                echo "<td><input type='text' name='tcas$N' value='".($m->{"tcas$N"})."' size='1' maxlength='2' $DIS></td>\n";
                echo "<td><input type='text' name='fame$N' value='".($m->{"fame$N"})."' size='1' maxlength='2' $DIS></td>\n";
                echo "<td><input type='text' name='tv$N' value='".($m->is_played ? $m->{"tv$N"}/1000 : ${"team$N"}->value/1000)."' size='4' maxlength='10' $DIS>k</td>\n";
                echo "</tr>\n";
            }
            ?>
        </table>

        <?php
        $playerFields = array_merge($T_MOUT_REL, $T_MOUT_ACH, $T_MOUT_IR, $T_MOUT_INJ);
        $CPP = count($playerFields);
        foreach (array(1 => $team1, 2 => $team2) as $id => $t) {
            ?>
            <table class='common'>
            <tr><td class='seperator' colspan='<?php echo $CPP;?>'></td></tr>
            <tr class='commonhead'><td colspan='<?php echo $CPP;?>'>
                <b><a href="<?php echo urlcompile(T_URL_PROFILE,T_OBJ_TEAM,$t->team_id,false,false);?>"><?php echo $t->name;?></a> <?php echo $lng->getTrn('matches/report/report');?></b>
            </td></tr>
            <tr><td class='seperator' colspan='<?php echo $CPP;?>'></td></tr>
            <?php
            echo "<tr>\n";
            foreach (array_values($playerFields) as $f) {
                echo "<td><i>$f</i></td>\n";
            }
            echo "</tr>\n";

            foreach ($t->getPlayers() as $p) {

                if (!self::player_validation($p, $m))
                    continue;

                // Fetch player data from match
                $status = $p->getStatus($m->match_id);
                $mdat   = $m->getPlayerEntry($p->player_id);

                // Print player row
                if ($p->is_journeyman) {$bgcolor = COLOR_HTML_JOURNEY;}
                elseif ($status == MNG) {$bgcolor = COLOR_HTML_MNG;}
                else {$bgcolor = false;}
                self::_print_player_row($p->player_id, $p->name, $p->nr, $p->position.(($status == MNG) ? '&nbsp;[MNG]' : ''),$bgcolor, $mdat, $DIS || ($status == MNG));
            }
            echo "</table>\n";

            // Add raised zombies
            global $racesHasNecromancer;
            if (in_array($t->f_race_id, $racesHasNecromancer)) {
                echo "<hr style='width:200px;float:left;'><br>
                <b>Raised zombie?:</b> <input type='checkbox' name='t${id}zombie' value='1' onclick='slideToggleFast(\"t${id}zombie\");'><br>\n";
                echo "<div id='t${id}zombie' style='display:none;'>\n";
                echo "<table class='common'>\n";
                self::_print_player_row("t${id}zombie", 'Raised zombie', '&mdash;', 'Zombie', false, array(), $DIS);
                echo "</table>\n";
                echo "</div>\n";
            }
            // Add raised rotters
            global $racesMayRaiseRotters;
            if (in_array($t->f_race_id, $racesMayRaiseRotters)) {
                $maxRotters = 6; # Note there is no real limit for raised rotters.
                echo "<hr style='width:200px;float:left;'><br>
                <b>Raised rotters?:</b>
                <select name='t${id}rotterCnt' onChange='var i = this.options[this.selectedIndex].value; var j=1; for (j=1; j<=$maxRotters; j++) {if (j<=i) {slideDownFast(\"t${id}rotter\"+j);} else {slideUpFast(\"t${id}rotter\"+j);}}' >";
                foreach (range(0,$maxRotters) as $n) {echo "<option value='$n'>$n</option>";}
                echo "</select>\n";
                foreach (range(0,$maxRotters) as $n) {
                    echo "<div id='t${id}rotter$n' style='display:none;'><table class='common'>\n";
                    self::_print_player_row("t${id}rotter$n", "Raised Rotter #$n", '&mdash;', 'Rotter', false, array(), $DIS);
                    echo "</table></div>\n";
                }
            }
            ?>

            <table style='border-spacing: 0px 10px;'>
                <tr><td align="left" valign="top">
                    <b>Star Players</b>:
                    <input type='button' id="addStarsBtn_<?php echo $id;?>" value="<?php echo $lng->getTrn('common/add');?>"
                    onClick="stars = document.getElementById('stars_<?php echo $id;?>'); addStarMerc(<?php echo $id;?>, stars.options[stars.selectedIndex].value);" <?php echo $DIS; ?>>
                    <select id="stars_<?php echo $id;?>" <?php echo $DIS; ?>>
                        <?php
                        foreach ($stars as $s => $d) {
                            echo "<option ".((in_array($t->f_race_id, $d['races'])) ? 'style="background-color: '.COLOR_HTML_READY.';"' : '')." value='$d[id]'>$s</option>\n";
                        }
                        ?>
                    </select>
                </td></tr>
                <tr><td align="left" valign="top">
                    <b>Mercenaries</b>: <input type='button' id="addMercsBtn_<?php echo $id;?>" value="<?php echo $lng->getTrn('common/add');?>" onClick="addStarMerc(<?php echo "$id, ".ID_MERCS;?>);" <?php echo $DIS; ?>>
                </td></tr>
            </table>

            <table class='common' id='<?php echo "starsmercs_$id";?>'>
            </table>
            <?php
        }
        ?>
        <table class='common'>
            <tr><td class='seperator' colspan='13'></td></tr>
            <tr class='commonhead'><td colspan='13'><b><?php echo $lng->getTrn('matches/report/summary');?></b></td></tr>
            <tr><td colspan='13'><textarea name='summary' rows='10' cols='100' <?php echo $DIS . ">" . $m->getText(); ?></textarea></td></tr>
        </table>
        <br><center><input type="submit" name='button' value="<?php echo $lng->getTrn('common/save');?>" <?php echo $DIS; ?>></center>
    </form>
    <br><br>
    <?php

    /*
        Now, we call javascript routine(s) to fill out stars and mercs rows, if such entries exist in database.
    */

    $i = 0; // Counter. Used to pass PHP-data to Javascript.
    foreach (array(1 => $team1->team_id, 2 => $team2->team_id) as $id => $t) {
        foreach (Star::getStars(STATS_TEAM, $t, STATS_MATCH, $m->match_id) as $s) {
            echo "<script language='JavaScript' type='text/javascript'>\n";
            echo "var mdat$i = [];\n";
            $mdat = $s->getStats(T_NODE_MATCH,$m->match_id);
            foreach (array_keys($T_MOUT_ACH) as $f) {
                echo "mdat${i}['$f'] = ".$mdat[$f].";\n";
            }
            echo "existingStarMerc($id, $s->star_id, mdat$i);\n";
            echo "</script>\n";
            $i++;
        }

        foreach (Mercenary::getMercsHiredByTeam($t, $m->match_id) as $merc) {
            echo "<script language='JavaScript' type='text/javascript'>\n";
            echo "var mdat$i = [];\n";
            foreach (array_merge(array_keys($T_MOUT_ACH), array('skills')) as $f) {
                echo "mdat${i}['$f'] = ".$merc->$f.";\n";
            }
            echo "existingStarMerc($id, ".ID_MERCS.", mdat$i);\n";
            echo "</script>\n";
            $i++;
        }
    }
}


protected static function _print_player_row($FS, $name, $nr, $pos, $bgcolor, $mdat, $DISABLE) {

    global $T_MOUT_REL, $T_MOUT_ACH, $T_MOUT_IR, $T_MOUT_INJ;

    $DIS = ($DISABLE) ? 'DISABLED' : '';
    echo "<tr".(($bgcolor) ? " style='background-color: $bgcolor;'" : '').">\n";
    echo "<td>$nr</td>\n";
    echo "<td>$name</td>\n";
    echo "<td>$pos</td>\n";
    // MVP
    echo "<td><select $DIS name='mvp_$FS'>";
    foreach (range(0,2) as $n) {echo "<option value='$n' ".((isset($mdat['mvp']) && $mdat['mvp'] == $n) ? 'SELECTED' : '').">$n</option>";}
    echo "</select>\n";
    // Rest of ACH.
    foreach (array_diff(array_keys($T_MOUT_ACH), array('mvp')) as $f) {
        echo "<td><input $DIS type='text' onChange='numError(this);' size='1' maxlength='2' name='${f}_$FS' value='".(isset($mdat[$f]) ? $mdat[$f] : 0)."'></td>\n";
    }
    foreach (array_keys($T_MOUT_IR) as $irl) {
        echo "<td><select name='${irl}_$FS' $DIS>";
        foreach (range(0,6) as $N) {
            echo "<option value='$N' ".((isset($mdat[$irl]) && $mdat[$irl] == $N) ? 'SELECTED' : '').">$N</option>";
        }
        echo "</select></td>\n";
    }
    global $T_INJS;
    $T_INJS_AGN = array_diff_key($T_INJS, array(MNG => null, DEAD => null));
    foreach (array_combine(array_keys($T_MOUT_INJ), array($T_INJS, $T_INJS_AGN, $T_INJS_AGN)) as $f => $opts) {
        echo "<td><select name='${f}_$FS' $DIS>";
        foreach ($opts as $status => $name) {
            echo "<option value='$status' ".((isset($mdat[$f]) && $mdat[$f] == $status) ? 'SELECTED' : '').">$name</option>";
        }
        echo "</select></td>\n";
    }
    echo "</tr>\n";
}

public static function report_ES($mid, $DIS)
{
    global $lng, $ES_fields;
    $ES_grps = array();
    foreach ($ES_fields as $f) {
        if (!in_array($f['group'], $ES_grps)) {
            $ES_grps[] = $f['group'];
        }
    }
    $players = self::report_ES_loadPlayers($mid);

    // Update entries if requested.
    if (!$DIS && isset($_POST['ES_submitted'])) {
        $query = "SELECT tour_id AS 'trid', did, f_lid AS 'lid' FROM matches, tours, divisions WHERE match_id = $mid AND f_tour_id = tour_id AND f_did = did";
        $result = mysql_query($query);
        $NR = mysql_fetch_assoc($result); # Node Relations.
        $m = new Match($mid);
        global $p; # Dirty trick to make $p accessible within create_function() below.
        $status = true;
        foreach ($players as $teamPlayers) {
        foreach ($teamPlayers as $p) {
            $status &= Match::ESentry(
                array(
                    'f_pid' => $p['pid'], 'f_tid' => $p['f_tid'], 'f_cid' => $p['f_cid'], 'f_rid' => $p['f_rid'],
                    'f_mid' => $mid, 'f_trid' => $NR['trid'], 'f_did' => $NR['did'], 'f_lid' => $NR['lid']
                ),
                array_combine(array_keys($ES_fields), array_map(create_function('$f', 'global $p; return (int) $_POST["${f}_$p[pid]"];'), array_keys($ES_fields)))
            );
        }
        }
        status($status);
        $players = self::report_ES_loadPlayers($mid); # Reload!
    }

    // Create form
    title('ES submission');
    echo "<center><a href='index.php?section=matches&amp;type=report&amp;mid=$mid'>".$lng->getTrn('common/back')."</a></center>\n";
    HTMLOUT::helpBox('<b>Field explanations</b><br><table>'.implode("\n", array_map(create_function('$f,$def', 'return "<tr><td>$f</td><td>$def[desc]</td></tr>";'), array_keys($ES_fields), array_values($ES_fields))).'</table>', '<b>'.$lng->getTrn('common/needhelp').'</b>');
    echo "<form method='POST'>\n";
    foreach ($players as $teamPlayers) {
        echo "<br>\n";
        echo "<table style='font-size: small;'>\n";
        $COLSPAN = count($teamPlayers)+1; # +1 for field desc.
        $tid = $teamPlayers[0]['f_tid'];
        echo "<tr><td colspan='$COLSPAN'><b><a name='thead$tid'>".get_alt_col('teams', 'team_id', $tid, 'name')."</a></b></td></tr>";
        echo "<tr><td colspan='$COLSPAN'>Player number references:</td></tr>";
        echo implode('', array_map(create_function('$p', 'return "<tr><td colspan=\''.$COLSPAN.'\'>#$p[nr] $p[name]</td></tr>";'), $teamPlayers));
        echo "<tr><td colspan='$COLSPAN'>GOTO anchor ".implode(', ', array_map(create_function('$anc', 'return "<a href=\'#'.$tid.'$anc\'>$anc</a>";'), $ES_grps))."</td></tr>";
        $grp = null;
        foreach ($ES_fields as $f => $def) {
            if ($def['group'] != $grp) {
                $grp = $def['group'];
                echo "<tr><td colspan='$COLSPAN'>&nbsp;</td></tr>";
                echo "<tr style='font-style: italic;'><td><a name='$tid$grp'>$grp</a>&nbsp;|&nbsp;<a href='#thead$tid'>GOTO team head</a></td>".implode('', array_map(create_function('$p', 'return "<td>#$p[nr]</td>";'), $teamPlayers))."</tr>";
                echo "<tr><td colspan='$COLSPAN'><hr></td></tr>";
            }
            echo "<tr><td>$f</td>".implode('', array_map(
                create_function('$p', 'return "<td><input '.(($DIS) ? 'DISABLED' : '').' size=\'2\' maxlength=\'4\' name=\''.$f.'_$p[pid]\' value=\'".(($p[\''.$f.'\']) ? (int) $p[\''.$f.'\'] : 0)."\'></td>";'), $teamPlayers
            ))."</tr>\n";
        }
        echo "</table>\n";
    }
    echo "<br><br><input type='submit' name='submit' value='".$lng->getTrn('common/submit')."'>\n";
    echo "<input type='hidden' name='ES_submitted' value='1'>\n";
    echo "</form>\n";
}

protected static function report_ES_loadPlayers($mid)
{
    global $ES_fields;
    $query = "SELECT
            players.player_id AS 'pid', players.owned_by_team_id AS 'f_tid', players.f_cid AS 'f_cid', players.f_rid AS 'f_rid',
            players.name AS 'name', players.nr AS 'nr',
            ".implode(',', array_keys($ES_fields))."
        FROM matches, match_data, players LEFT JOIN match_data_es ON (match_data_es.f_mid = $mid AND players.player_id = match_data_es.f_pid)
        WHERE
            matches.match_id = $mid AND matches.match_id = match_data.f_match_id AND match_data.f_player_id = players.player_id AND (owned_by_team_id = team1_id OR owned_by_team_id = team2_id)
        ORDER BY f_tid ASC, nr ASC";
#    echo $query;
    $result = mysql_query($query);
    $players = array();
    while ($p = mysql_fetch_assoc($result)) {
        $players[$p['f_tid']][] = $p;
    }
    return $players;
}

public static function userSched() {
    global $lng, $coach, $settings, $leagues,$divisions,$tours;
    if (!is_object($coach)){
        status(false, "You must be logged in to schedule games");
        return;
    }

    if (isset($_POST['creategame'])) {
        list($exitStatus, $mid) = Match::create(array(
            'team1_id'  => (int) $_POST['own_team'],
            'team2_id'  => get_alt_col('teams', 'name', $_POST['opposing_team_autocomplete'], 'team_id'),
            'round'     => (int) $_POST['round'],
            'f_tour_id' => (int) $_POST['tour_id'],
        ));

        status(!$exitStatus, $exitStatus ? Match::$T_CREATE_ERROR_MSGS[$exitStatus] : null);
        if (!$exitStatus) {
            echo "<a href='index.php?section=matches&amp;type=report&amp;mid=$mid'>Click here to open match report.</a>";
        }
    }

    title($lng->getTrn('menu/matches_menu/usersched'));
    list($sel_lid, $HTML_LeagueSelector) = HTMLOUT::simpleLeagueSelector();
    $LOCK_FORMS = false;
    ?>
    <div class='boxCommon'>
        <h3 class='boxTitle<?php echo T_HTMLBOX_MATCH;?>'><?php echo $lng->getTrn('menu/matches_menu/usersched');?></h3>
        <div class='boxBody'>
            <?php echo $HTML_LeagueSelector; ?>
            <form method="POST">
                <?php echo $lng->getTrn('common/tournament'); ?>
                <select name='tour_id' id='tour_id'>
                    <?php
                    $TOURS_CNT = 0;
                    foreach ($tours as $trid => $tr) {
                        if ($divisions[$tr['f_did']]['f_lid'] != $sel_lid) {
                            continue;
                        }
                        if ($tr['type'] == TT_FFA) {
                            $TOURS_CNT++;
                            if (in_array($trid, $settings['coach_schedule_tours'])) {
                                echo "<option value='$trid'>".$divisions[$tr['f_did']]['dname'].": $tr[tname]</option>\n";
                            }
                        }
                    }
                    ?>
                </select>
                <br>
                <?php
                echo $lng->getTrn('matches/tourmatches/roundtypes/rnd').'&nbsp;';
                echo '<select name="round" id="round">';
                global $T_ROUNDS;
                foreach ($T_ROUNDS as $r => $d) {
                    echo "<option value='$r'>".$lng->getTrn($d)."</option>\n";
                }
                ?>
                </select>
                <br>
                Your team
                <select name='own_team' id='own_team'>
                    <?php
                    $TEAMS_CNT = 0;
                    //Sort according to name
                    foreach ($coach->getTeams() as $t) {
                        if (!$t->rdy || $t->is_retired || $t->f_lid != $sel_lid)
                            continue;
                        echo "<option value='$t->team_id'>$t->name</option>\n";
                        $TEAMS_CNT++;
                    }
                    ?>
                </select>
                <br>
                Opposing team
                <input type="text" id='opposing_team_autoselect' name="opposing_team_autocomplete" size="30" maxlength="50">
                <script>
                    $(document).ready(function(){
                        var options, b;

                        options = {
                            minChars:2,
                                serviceUrl:'handler.php?type=autocomplete&obj=<?php echo T_OBJ_TEAM;?>',
                        };
                        b = $('#opposing_team_autoselect').autocomplete(options);
                    });
                </script>
                <br><br>
                <?php
                $LOCK_FORMS = !($TOURS_CNT && $TEAMS_CNT) || empty($settings['coach_schedule_tours']);
                echo '<input type="submit" name="creategame" value="Schedule match" '.(($LOCK_FORMS) ? 'DISABLED' : '').'>';
                echo "<br>\n";
                echo "<br><span style='display:none;font-weight:bold;' id='scheddis'>- Scheduling of matches by coaches in disabled for the selected league.</span>\n";
                echo "<span style='display:none;font-weight:bold;' id='noteams'>- You do not have any teams which can be scheduled in the selected league.</span>\n";
                echo "<span style='display:none;font-weight:bold;' id='notours'>- No Free-For-All tournaments exist in the selected league.</span>\n";
                echo "<script>\n";
                if ($LOCK_FORMS) {
                    ?>
                    document.getElementById('tour_id').disabled = 1;
                    document.getElementById('round').disabled = 1;
                    document.getElementById('own_team').disabled = 1;
                    <?php
                }
                if (empty($settings['coach_schedule_tours'])) {?> slideDown('scheddis'); <?php }
                if ($TOURS_CNT == 0) {?> slideDown('notours'); <?php }
                if ($TEAMS_CNT == 0) {?> slideDown('noteams'); <?php }
                echo "</script>\n";
                ?>
            </form>
        </div>
    </div>
    <?php
}

}

?>
