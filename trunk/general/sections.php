<?php

/*
 *  Copyright (c) Niels Orsleff Justesen <njustesen@gmail.com> and Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2007-2009. All Rights Reserved.
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

/*************************
 *
 *  Login
 *
 *************************/

function sec_login() {

    global $lng, $settings;
    title($lng->getTrn('login/name'));
    ?>
    <div class='login'>
    <form method="POST" action="index.php">
        <b><?php echo $lng->getTrn('login/loginname');?></b>
        <?php
        if ($settings['login_list']) {
            ?>
            <select name='coach'>
            <?php
            $coaches = Coach::getCoaches();
            objsort($coaches, array('+name'));
            foreach ($coaches as $c) {
                if (!$c->retired)
                    echo "<option value='$c->coach_id'>$c->name</option>";
            }
            ?>
            </select>
            <?php
        }
        else {
            ?>
            <input type="text" name="coach" size="20" maxlength="50">
            <?php
        }
        ?>
        &nbsp;&nbsp;
        <b><?php echo $lng->getTrn('login/passwd');?></b>
        <input type="password" name="passwd" size="20" maxlength="50">
        <div style='display: none;'><input type='text' name='hackForHittingEnterToLogin' size='1'></div>
        <br><br>
        <b><?php echo $lng->getTrn('login/remember');?></b>
        <input type='checkbox' name='remember' value='1'>
        <br><br>
        <input type="submit" name="login" value="<?php echo $lng->getTrn('login/loginbutton');?>">
    </form>
    </div>
    <?php
    if (Module::isRegistered('Registration') && $settings['allow_registration']) {
        echo "<br><a href='handler.php?type=registration'>Register</a>";
    }
}

/*************************
 *
 *  MAIN
 *
 *************************/

function sec_main() {

    global $settings, $rules, $coach, $lng;

    /*
        Was any main board actions made?
    */

    if (isset($_POST['type']) && is_object($coach)) {
        if (get_magic_quotes_gpc()) {
            if (isset($_POST['title'])) $_POST['title'] = stripslashes($_POST['title']);
            if (isset($_POST['txt']))   $_POST['txt']   = stripslashes($_POST['txt']);
        }
        switch ($_POST['type'])
        {
            case 'msgdel':  $msg = new Message($_POST['msg_id']); status($msg->delete()); break;
            case 'msgnew':  status(Message::create(array('f_coach_id' => $coach->coach_id, 'title' => $_POST['title'], 'msg' => $_POST['txt']))); break;
            case 'msgedit': $msg = new Message($_POST['msg_id']); status($msg->edit($_POST['title'], $_POST['txt'])); break;
        }
    }

    /*
        Generate main board.

        Left column is the message board, consisting of both commissioner messages and game summaries/results.
        To generate this table we create a general array holding the content of both.
    */
    
    $board = TextSubSys::getMainBoardMessages($settings['entries_messageboard']);


    /*
        Right column optionally (depending on settings.php) contains standings, latest game results, touchdown and casualties stats.
        We will now generate the stats, so that they are ready to be printed in correct order.
    */

    $matches = Match::getMatches($settings['entries_latest']); // Recent matches
    $touchdowns  = Stats::getLeaders(STATS_PLAYER, $settings['entries_touchdown'], array('-td'), true);          // Touchdowns
    $casualties  = Stats::getLeaders(STATS_PLAYER, $settings['entries_casualties'], array('-bh+ki+si'), true);   // Casualties
    $completions = Stats::getLeaders(STATS_PLAYER, $settings['entries_completions'], array('-cp'), true);        // Completions

    // Standings
    $standings  = array();
    if ($settings['show_active_tours']) {
        $tours = Tour::getTours();
        foreach ($tours as $t) {
            if ($t->is_begun && !$t->is_finished) {
                $teams = $t->getTeams();
                foreach ($teams as $team) {
                    $team->setStats(STATS_TOUR, $t->tour_id);
                }
                objsort($teams, $t->getRSSortRule());
                array_push($standings, array('name' => $t->name, 'rs' => $t->rs, 'wpoints' => $t->isRSWithPoints(), 'teams' => array_slice($teams, 0, $settings['entries_standings'])));
            }
        }
    }

    /*****
     *
     * Now we are ready to generate the HTML code.
     *
     *****/

    ?>
    <div class="main_head"><?php echo $settings['site_name']; ?></div>
    <div class='main_leftColumn'>
        <div class="main_leftColumn_head">
            <?php
            echo "<div class='main_leftColumn_welcome'>\n";
            readfile('WELCOME');
            echo "</div>\n";
            if (is_object($coach) && $coach->ring <= RING_COM) {echo "<a href='javascript:void(0);' onClick=\"slideToggle('msgnew');\">".$lng->getTrn('main/newmsg')."</a>&nbsp;\n";}
            if (Module::isRegistered('RSSfeed')) {echo "<a href='handler.php?type=rss'>RSS</a>\n";}
            ?>
            <div style="display:none; clear:both;" id="msgnew">
                <br><br>
                <form method="POST">
                    <textarea name="title" rows="1" cols="50"><?php echo $lng->getTrn('common/notitle');?></textarea><br><br>
                    <textarea name="txt" rows="15" cols="50"><?php echo $lng->getTrn('common/nobody');?></textarea><br><br>
                    <input type="hidden" name="type" value="msgnew">
                    <input type="submit" value="<?php echo $lng->getTrn('common/submit');?>">
                </form>
            </div>
        </div>

        <?php
        $j = 1;
        foreach ($board as $e) {
            echo "<div class='main_leftColumn_box'>\n";
                echo "<h3 class='boxTitle$e->cssidx'>$e->title</h3>\n";
                echo "<div class='boxBody'>\n";
                    $fmtMsg = fmtprint($e->message); # Basic supported syntax: linebreaks.
                    echo substr($fmtMsg, 0, 300)."<span id='e$j' style='display:none;'>".substr($fmtMsg, 300)."</span><span id='moreLink$j' ".((strlen($fmtMsg) > 300) ? '' : 'style="display:none"')."> ...&nbsp;<a href='javascript:void(0)' onclick=\"fadeOut('moreLink$j');fadeIn('e$j');\">[".$lng->getTrn('main/more')."]</a></span>\n";
                    echo "<br><hr>\n";
                    echo "<table class='boxTable'><tr>\n";
                        switch ($e->type) 
                        {
                            case T_TEXT_MATCH_SUMMARY:
                                echo "<td align='left' width='100%'>".$lng->getTrn('main/posted')." ".textdate($e->date)." " . (isset($e->date_mod) ? "(".$lng->getTrn('main/lastedit')." ".textdate($e->date_mod).") " : '') .$lng->getTrn('main/by')." $e->author</td>\n";
                                echo "<td align='right'><a href='".urlcompile(T_NODE_MATCH, $e->match_id)."'>".$lng->getTrn('common/view')."</a></td>\n";
                                if (!empty($e->comments)) {
                                    echo "<td align='right'><a href='javascript:void(0)' onclick=\"slideToggle('comment$e->match_id');\">".$lng->getTrn('main/comments')."</a></td>\n";
                                }
                                break;
                            case  T_TEXT_MSG:
                                echo "<td align='left' width='100%'>".$lng->getTrn('main/posted')." ".textdate($e->date)." ".$lng->getTrn('main/by')." $e->author</td>\n";
                                if (is_object($coach) && ($coach->admin || $coach->coach_id == $e->author_id)) { // Only admins may delete messages, or if it's a commissioner's own message.
                                    echo "<td align='right'><a href='javascript:void(0);' onClick=\"slideToggle('msgedit$e->msg_id');\">".$lng->getTrn('common/edit')."</a></td>\n";
                                    echo "<td align='right'>";
                                    inlineform(array('type' => 'msgdel', 'msg_id' => $e->msg_id), "msgdel$e->msg_id", $lng->getTrn('common/delete'));
                                    echo "</td>";
                                }
                                break;
                            case T_TEXT_TNEWS:
                                echo "<td align='left' width='100%'>".$lng->getTrn('main/posted')." ".textdate($e->date)."</td>\n";
                                break;
                        }
                        ?>
                    </tr></table>
                    <?php
                    if ($e->type == T_TEXT_MATCH_SUMMARY && !empty($e->comments)) {
                        echo "<div style='display:none;' id='comment$e->match_id'><hr>\n";
                        foreach ($e->comments as $c) { echo '<br>'.$lng->getTrn('main/posted').' '.textdate($c->date).' '.$lng->getTrn('main/by')." $c->sname:<br>\n".$c->txt."<br>\n";}
                        echo "</div>";
                    }
                    elseif ($e->type == T_TEXT_MSG) {
                        echo "<div style='display:none;' id='msgedit$e->msg_id'>\n";
                        echo "<hr><br>\n";
                        echo '<form method="POST">
                            <textarea name="title" rows="1" cols="50">'.$e->title.'</textarea><br><br>
                            <textarea name="txt" rows="15" cols="50">'.$e->message.'</textarea><br><br>
                            <input type="hidden" name="type" value="msgedit">
                            <input type="hidden" name="msg_id" value="'.$e->msg_id.'">
                            <input type="submit" value="'.$lng->getTrn('common/submit').'">
                        </form>';
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
            <?php
            $j++;
        }
        ?>

    </div>

    <div class='main_rightColumn'>

        <?php
        if ($settings['entries_standings'] != 0) {
            foreach ($standings as $sta) {
                ?>
                <div class='main_rightColumn_box'>
                    <h3 class='boxTitle<?php echo T_HTMLBOX_STATS;?>'><?php echo $sta['name'];?></h3>
                    <div class='boxBody'>
                        <table class="boxTable">
                            <tr>
                                <td style='width:100%;'><b>Team</b></td>
                                <?php if ($sta['wpoints']) {echo "<td><b>PTS</b></td>\n";}?>
                                <td><b>Cas</b></td>
                                <td><b>W</b></td>
                                <td><b>L</b></td>
                                <td><b>D</b></td>
                                <td><b>Score</b></td>
                            </tr>
                            <?php
                            foreach ($sta['teams'] as $t) {
                                echo "<tr>\n";
                                echo "<td>".(($settings['fp_links']) ? "<a href='".urlcompile(T_OBJ_TEAM, $t->team_id)."'>$t->name</a>" : $t->name)."</td>\n";
                                if ($sta['wpoints']) {echo '<td>'.((is_float($t->points)) ? sprintf('%1.2f', $t->points) : $t->points)."</td>\n";}
                                echo "<td>$t->cas</td>\n";
                                echo "<td>$t->won</td>\n";
                                echo "<td>$t->lost</td>\n";
                                echo "<td>$t->draw</td>\n";
                                echo "<td><nobr>$t->score_team&mdash;$t->score_opponent<nobr></td>\n";
                                echo "</tr>\n";
                            }
                            ?>
                        </table>
                    </div>
                </div>
                <?php
            }
        }
        if ($settings['entries_latest'] != 0) {
            ?>
            <div class="main_rightColumn_box">
                <h3 class='boxTitle1'><?php echo $lng->getTrn('common/recentgames');?></h3>
                <div class='boxBody'>
                    <table class="boxTable">
                        <tr>
                            <td style="text-align: right;" width="50%"><b>Home</b></td>
                            <td> </td>
                            <td style="text-align: left;" width="50%"><b>Guest</b></td>
                            <td> </td>

                        </tr>
                        <?php
                        foreach ($matches as $m) {

                            $home   = $m->stadium == $m->team1_id ? 'team1' : 'team2';
                            $guest  = $home == 'team1' ? 'team2' : 'team1';

                            $home_name      = $home . '_name';
                            $home_score     = $home . '_score';
                            $guest_name     = $guest . '_name';
                            $guest_score    = $guest . '_score';

                            echo "<tr>\n";
                            echo "<td style='text-align: right;'>" . $m->$home_name . "</td>\n";
                            echo "<td><nobr>" . $m->$home_score . "&mdash;" . $m->$guest_score . "</nobr></td>\n";
                            echo "<td style='text-align: left;'>" . $m->$guest_name . "</td>\n";
                            echo "<td><a href='".urlcompile(T_NODE_MATCH,$m->match_id)."'>Show</a></td>";
                            echo "</tr>";
                        }
                        ?>
                    </table>
                </div>
            </div>
            <?php
        }
        if ($settings['entries_casualties'] != 0) {
            ?>
            <div class="main_rcolBox">
                <h3 class='boxTitle1'><?php echo $lng->getTrn('common/cas');?></h3>
                <div class='boxBody'>
                    <table class="boxTable">
                        <tr>
                            <td width="100%"><b>Name</b></td>
                            <td><b>Amount</b></td>
                            <td><b>Value</b></td>
                        </tr>
                        <?php
                        foreach ($casualties as $p) {
                            echo "<tr>\n";
                            echo "<td>".(($settings['fp_links']) ? "<a href='".urlcompile(T_OBJ_PLAYER,$p->player_id)."'>$p->name</a>" : $p->name)."</td>\n";
                            echo "<td>$p->cas</td>\n";
                            echo "<td>" . $p->value/1000 . "k</td>\n";
                            echo "</tr>";
                        }
                        ?>
                    </table>
                </div>
            </div>
            <?php
        }
        if ($settings['entries_touchdown'] != 0) {
            ?>
            <div class="main_rcolBox">
                <h3 class='boxTitle1'><?php echo $lng->getTrn('common/td');?></h3>
                <div class='boxBody'>
                    <table class="boxTable">
                        <tr>
                            <td width="100%"><b>Name</b></td>
                            <td><b>Amount</b></td>
                            <td><b>Value</b></td>
                        </tr>
                        <?php
                        foreach ($touchdowns as $p) {
                            echo "<tr>\n";
                            echo "<td>".(($settings['fp_links']) ? "<a href='".urlcompile(T_OBJ_PLAYER,$p->player_id)."'>$p->name</a>" : $p->name)."</td>\n";
                            echo "<td>$p->td</td>\n";
                            echo "<td>" . $p->value/1000 . "k</td>\n";
                            echo "</tr>";
                        }
                        ?>
                    </table>
                </div>
            </div>
            <?php
        }
        if ($settings['entries_completions'] != 0) {
            ?>
            <div class="main_rcolBox">
                <h3 class='boxTitle1'><?php echo $lng->getTrn('common/cp');?></h3>
                <div class='boxBody'>
                    <table class="boxTable">
                        <tr>
                            <td width="100%"><b>Name</b></td>
                            <td><b>Amount</b></td>
                            <td><b>Value</b></td>
                        </tr>
                        <?php
                        foreach ($completions as $p) {
                            echo "<tr>\n";
                            echo "<td>".(($settings['fp_links']) ? "<a href='".urlcompile(T_OBJ_PLAYER,$p->player_id)."'>$p->name</a>" : $p->name)."</td>\n";
                            echo "<td>$p->cp</td>\n";
                            echo "<td>" . $p->value/1000 . "k</td>\n";
                            echo "</tr>";
                        }
                        ?>
                    </table>
                </div>
            </div>
            <?php
        }
        ?>

    </div>
    <div class="main_foot">
        <a href="http://nicholasmr.dk/index.php?sec=obblm">OBBLM official website</a>
        <br><br>
        This web site is completely unofficial and in no way endorsed by Games Workshop Limited.
        <br>
        Bloodquest, Blood Bowl, the Blood Bowl logo, The Blood Bowl Spike Device, Chaos, the Chaos device, the Chaos logo, Games Workshop, Games Workshop logo, Nurgle, the Nurgle device, Skaven, Tomb Kings, and all associated marks, names, races, race insignia, characters, vehicles, locations, units, illustrations and images from the Blood Bowl game, the Warhammer world are either (R), TM and/or (C) Games Workshop Ltd 2000-2006, variably registered in the UK and other countries around the world. Used without permission. No challenge to their status intended. All Rights Reserved to their respective owners.
    </div>
    <?php
}

function sec_teamlist() {
    global $lng;
    title($lng->getTrn('global/secLinks/teams'));
    HTMLOUT::dispTeamList(false, false);

}
function sec_matches() {
    switch ($_GET['type'])
    {
        # Save all these subroutines in class_match_htmlout.php
        case 'bracket': # "TOURNAMENTS" section.
        case 'report': # Match reports.
        case 'recent':
        case 'upcomming':
    }    
}

function sec_standings() {
    switch ($_GET['type'])
    {
        case T_OBJ_PLAYER: Player_HTMLOUT::standings(); break;
        case T_OBJ_TEAM:   Team_HTMLOUT::standings();   break;
# For team node specific standings...
#        Team_HTMLOUT::standings($_GET['type'], $_GET['id']);
        case T_OBJ_COACH:  Coach_HTMLOUT::standings();  break;
        case T_OBJ_STAR:   Star_HTMLOUT::standings();   break;
        case T_OBJ_RACE:   Race_HTMLOUT::standings();   break;
    }
}

function sec_profile() {
    switch ($_GET['type'])
    {
        case T_OBJ_PLAYER: $player = new Player_HTMLOUT($_GET['id']); $player->playerPage(); break;
        case T_OBJ_TEAM:   $team = new Team_HTMLOUT($_GET['id']);     $team->teamPage();     break;
        case T_OBJ_COACH:  $coach = new Coach_HTMLOUT($_GET['id']);   $coach->coachPage();   break;
        case T_OBJ_STAR:   $star = new Star_HTMLOUT($_GET['id']);     $star->starPage();     break;
        case T_OBJ_RACE:   $race = new Race_HTMLOUT($_GET['id']);     $race->racePage();     break;
    }
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


/*************************
 *
 *  Records
 *
 *************************/

function sec_records() {

    global $lng, $coach;
    $ALLOW_EDIT = (is_object($coach) && $coach->admin);
    $subsecs = array(
        'hof'    => $lng->getTrn('name', 'HOF'),
        'wanted' => $lng->getTrn('name', 'Wanted'),
        'prize'  => $lng->getTrn('name', 'Prize'),
    );

    // This section's routines are placed in the records.php file.
    if (isset($_GET['subsec'])) {
        switch ($_GET['subsec'])
        {
            case 'hof':    Module::run('HOF',    array('makeList', $ALLOW_EDIT)); break;
            case 'wanted': Module::run('Wanted', array('makeList', $ALLOW_EDIT)); break;
            case 'prize':  Module::run('Prize',  array('makeList', $ALLOW_EDIT)); break;
        }
        return;
    }

    title($lng->getTrn('global/secLinks/records'));
    echo "Please select one of the below pages<br><br>\n";
    foreach ($subsecs as $a => $b) {
        echo "<a href='index.php?section=records&amp;subsec=$a'><b>$b</b></a><br>\n";
    }

}

/*************************
 *
 *  RULES
 *
 *************************/

function sec_rules() {

    global $rules, $lng;
    title($lng->getTrn('global/secLinks/rules'));
    readfile('LEAGUERULES');
    echo "<br><br><hr><br>\n";
    echo $lng->getTrn('secs/rules/intro');
    ?>
    <table>
        <tr>
            <td><i><?php echo $lng->getTrn('secs/rules/rule');?></i></td>
            <td><i><?php echo $lng->getTrn('secs/rules/val');?></i></td>
        </tr>

        <tr>
            <td colspan="2"><hr></td>
        </tr>

        <tr>
            <td><?php echo $lng->getTrn('secs/rules/init_ts');?></td>
            <td><?php echo $rules['initial_treasury']/1000; ?>k</td>
        </tr>

        <tr>
            <td><?php echo $lng->getTrn('secs/rules/max_pl');?></td>
            <td><?php echo $rules['max_team_players']; ?></td>
        </tr>

        <tr>
            <td><?php echo $lng->getTrn('secs/rules/rr_price');?></td>
            <td><?php echo $rules['static_rerolls_prices'] ? $lng->getTrn('secs/rules/yes') : $lng->getTrn('secs/rules/no'); ?></td>
        </tr>

        <tr>
            <td><?php echo $lng->getTrn('secs/rules/refund');?></td>
            <td><?php echo $rules['player_refund'] * 100 . $lng->getTrn('secs/rules/refundptc'); ?></td>
        </tr>

        <tr>
            <td><?php echo $lng->getTrn('secs/rules/max_jm');?></td>
            <td><?php echo $rules['journeymen_limit']; ?></td>
        </tr>

        <tr>
            <td><?php echo $lng->getTrn('secs/rules/enable_post_ff');?></td>
            <td><?php echo $rules['post_game_ff'] ? $lng->getTrn('secs/rules/yes') : $lng->getTrn('secs/rules/no'); ?></td>
        </tr>

        <tr>
            <td><?php echo $lng->getTrn('secs/rules/init_rr');?></td>
            <td><?php echo $rules['initial_rerolls']; ?></td>
        </tr>

        <tr>
            <td><?php echo $lng->getTrn('secs/rules/init_ff');?></td>
            <td><?php echo $rules['initial_fan_factor']; ?></td>
        </tr>

        <tr>
            <td><?php echo $lng->getTrn('secs/rules/init_ac');?></td>
            <td><?php echo $rules['initial_ass_coaches']; ?></td>
        </tr>

        <tr>
            <td><?php echo $lng->getTrn('secs/rules/init_cl');?></td>
            <td><?php echo $rules['initial_cheerleaders']; ?></td>
        </tr>

        <tr>
            <td><?php echo $lng->getTrn('secs/rules/max_rr');?></td>
            <td><?php echo $rules['max_rerolls'] < 0 ? $lng->getTrn('secs/rules/unlimited') : $rules['max_rerolls']; ?></td>
        </tr>

        <tr>
            <td><?php echo $lng->getTrn('secs/rules/max_ff');?></td>
            <td><?php echo $rules['max_fan_factor'] < 0 ? $lng->getTrn('secs/rules/unlimited') : $rules['max_fan_factor']; ?></td>
        </tr>

        <tr>
            <td><?php echo $lng->getTrn('secs/rules/max_ac');?></td>
            <td><?php echo $rules['max_ass_coaches'] < 0 ? $lng->getTrn('secs/rules/unlimited') : $rules['max_ass_coaches']; ?></td>
        </tr>

        <tr>
            <td><?php echo $lng->getTrn('secs/rules/max_cl');?></td>
            <td><?php echo $rules['max_cheerleaders'] < 0 ? $lng->getTrn('secs/rules/unlimited') : $rules['max_cheerleaders']; ?></td>
        </tr>

        <tr>
            <td><?php echo $lng->getTrn('secs/rules/ap_price');?></td>
            <td><?php echo $rules['cost_apothecary']; ?> gp</td>
        </tr>

        <tr>
            <td><?php echo $lng->getTrn('secs/rules/ff_price');?></td>
            <td><?php echo $rules['cost_fan_factor']; ?> gp</td>
        </tr>

        <tr>
            <td><?php echo $lng->getTrn('secs/rules/ac_price');?></td>
            <td><?php echo $rules['cost_ass_coaches']; ?> gp</td>
        </tr>

        <tr>
            <td><?php echo $lng->getTrn('secs/rules/cl_price');?></td>
            <td><?php echo $rules['cost_cheerleaders']; ?> gp</td>
        </tr>

        <tr>
            <td><?php echo $lng->getTrn('secs/rules/enable_starmerc');?></td>
            <td><?php echo $rules['enable_stars_mercs'] ? $lng->getTrn('secs/rules/yes') : $lng->getTrn('secs/rules/no'); ?></td>
        </tr>

        <tr>
            <td><?php echo $lng->getTrn('secs/rules/enable_lrb6');?></td>
            <td><?php echo $rules['enable_lrb6x'] ? $lng->getTrn('secs/rules/yes') : $lng->getTrn('secs/rules/no'); ?></td>
        </tr>
    </table>

    <?php
}

/*************************
 *
 *  GALLERY
 *
 *************************/

function sec_gallery() {

    global $lng;

    title('Gallery');

    if (isset($_POST['type'])) {
        echo "<center><a href='index.php?section=gallery'>[".$lng->getTrn('global/misc/back')."]</a></center>\n";
        switch ($_POST['type'])
        {
            case 'team':
                $t = new Team((int) $_POST['tid']);
                echo "<b>".$lng->getTrn('secs/gallery/playersof')." $t->name</b><br><hr><br>\n";
                $players = $t->getPlayers();
                foreach ($players as $p) {
                    $img = new ImageSubSys(IMGTYPE_PLAYER, $p->player_id);
                    $pic = $img->getPath();
                    echo "<div style='float:left; padding:10px;'>$p->name (#$p->nr)<br><a href='index.php?section=coachcorner&amp;player_id=$p->player_id'><img HEIGHT=150 src='$pic' alt='pic'></a></div>";
                }
                break;

            case 'stad':
                echo "<b>".$lng->getTrn('secs/gallery/stads')."</b><br><hr><br>\n";
                $teams = Team::getTeams();
                foreach ($teams as $t) {
                    $img = new ImageSubSys(IMGTYPE_TEAMSTADIUM, $t->team_id);
                    $pic = $img->getPath();
                    echo "<div style='float:left; padding:10px;'>$t->name<br><a href='$pic'><img HEIGHT=150 src='$pic' alt='pic'></a></div>";
                }
                break;

            case 'coach':
                echo "<b>".$lng->getTrn('secs/gallery/coaches')."</b><br><hr><br>\n";
                $coaches = Coach::getCoaches();
                foreach ($coaches as $c) {
                    $img = new ImageSubSys(IMGTYPE_COACH, $c->coach_id);
                    $pic = $img->getPath();
                    echo "<div style='float:left; padding:10px;'>$c->name<br><a href='$pic'><img HEIGHT=150 src='$pic' alt='pic'></a></div>";
                }
                break;
        }

        return;
    }

    $team_list = "
        <form method='POST' style='display:inline; margin:0px;'><select name='tid' onChange='this.form.submit();'>
        <option value='0'>-".$lng->getTrn('secs/gallery/none')."-</option>".
        implode("\n", array_map(create_function('$o', 'return "<option value=\'$o->team_id\'>$o->name</option>";'), Team::getTeams()))
        ."</select><input type='hidden' name='type' value='team'></form>
    ";
    $stad = "
        <form method='POST' name='stadForm' style='display:inline; margin:0px;'>
        <input type='hidden' name='type' value='stad'>
        <a href='javascript:void(0);' onClick='document.stadForm.submit();'>".$lng->getTrn('secs/gallery/stads')."</a>
        </form>
    ";
    $coaches = "
        <form method='POST' name='coachesForm' style='display:inline; margin:0px;'>
        <input type='hidden' name='type' value='coach'>
        <a href='javascript:void(0);' onClick='document.coachesForm.submit();'>".$lng->getTrn('secs/gallery/coaches')."</a>
        </form>
    ";
    echo $lng->getTrn('secs/gallery/note');
    ?>
    <ul>
    <li><?php echo $lng->getTrn('secs/gallery/players').$team_list?></li>
    <li><?php echo $stad?></li>
    <li><?php echo $coaches?></li>
    </ul>
    <?php
}

/*************************
 *
 *  ABOUT
 *
 *************************/

function sec_about() {

    global $lng;

    title($lng->getTrn('secs/obblm/intro'));

    ?>
    <table class="text">
        <tr>
            <td>
                <?php echo $lng->getTrn('secs/obblm/intro_txt'); ?>
            </td>
        </tr>
    </table>

    <?php
    title($lng->getTrn('secs/obblm/faq'));
    ?>

    <table class="text">
        <tr>
            <td>
                <?php echo $lng->getTrn('secs/obblm/faq_txt'); ?>
            </td>
        </tr>
    </table>

    <?php
    title("About OBBLM");
    global $credits;
    ?>

    <p>
        OBBLM v. <?php echo OBBLM_VERSION; ?><br><br>
        Online Blood Bowl League Manager is an online game management system for Game Workshop's board game Blood Bowl.<br>
        <br>
        The authors of this program are
        <ul>
            <li> <a href="mailto:nicholas.rathmann@gmail.com">Nicholas Mossor Rathmann</a>
            <li> Niels Orsleff Justesen</a>
        </ul>
        With special thanks to <?php $lc = array_pop($credits); echo implode(', ', $credits)." and $lc"; ?>.<br><br>
        Bugs reports and suggestions are welcome.
        <br>
        OBBLM consists of valid HTML 4.01 transitional document type pages.
        <br><br>
        <img src="http://www.w3.org/Icons/valid-html401" alt="Valid HTML 4.01 Transitional" height="31" width="88">

        <br><br>
        <b>Modules loaded:</b><br>
        <?php
        $mods = array();
        foreach (Module::getRegistered() as $modname) {
            list($author,$date,$moduleName) = Module::getInfo($modname);
            $mods[] = "<i>$moduleName</i> ($author, $date)";
        }
        echo implode(', ', $mods);
        ?>
    </p>

    <?php
    title("Disclaimer");
    ?>

    <p>
        By installing and using this software you hereby accept and understand the following disclaimer
        <br><br>
        <b>This web site is completely unofficial and in no way endorsed by Games Workshop Limited.</b>
        <br><br>
        Bloodquest, Blood Bowl, the Blood Bowl logo, The Blood Bowl Spike Device, Chaos, the Chaos device, the Chaos logo, Games Workshop, Games Workshop logo, Nurgle, the Nurgle device, Skaven, Tomb Kings, and all associated marks, names, races, race insignia, characters, vehicles, locations, units, illustrations and images from the Blood Bowl game, the Warhammer world are either ®, TM and/or © Games Workshop Ltd 2000-2006, variably registered in the UK and other countries around the world. Used without permission. No challenge to their status intended. All Rights Reserved to their respective owners.
    </p>

    <?php
    title("License");
    ?>

    <p>
        Copyright (c) Niels Orsleff Justesen and Nicholas Mossor Rathmann 2007-2009. All Rights Reserved.
        <br><br>
        OBBLM is free software; you can redistribute it and/or modify
        it under the terms of the GNU General Public License as published by
        the Free Software Foundation; either version 3 of the License, or
        (at your option) any later version.
        <br><br>
        OBBLM is distributed in the hope that it will be useful,
        but WITHOUT ANY WARRANTY; without even the implied warranty of
        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
        GNU General Public License for more details.
        <br><br>
        You should have received a copy of the GNU General Public License
        along with this program.  If not, see http://www.gnu.org/licenses/.
    </p>
    <?php
}

/*************************
 * *  COACH CORNER
 * *************************/

function sec_coachcorner() {
    Coach_HTMLOUT::coachCorner();
}

/*************************
 *
 *  RECENT MATCHES
 *
 *************************/

function sec_recentmatches() {

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

function sec_upcommingmatches() {

    global $lng;
    title($lng->getTrn('global/secLinks/upcomming'));
    list($node, $node_id) = HTMLOUT::nodeSelector(false,false,false,'');
    echo '<br>';
    HTMLOUT::upcommingGames(false,false,$node,$node_id, false,false,array('url' => 'index.php?section=upcomming', 'n' => MAX_RECENT_GAMES));
}

?>
