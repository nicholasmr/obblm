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

/***************************************************
 *  ------------
 *  PLEASE NOTE:
 *  ------------
 *
 *  Chunks of the section code are so large, that they have been divided into other files.
 *  These files are:
 *
 *      - matches.php   For handling match reports.
 *      - records.php   For handling the records section.
 *      - admin.php     For handling the admin section.
 *
 ***************************************************/

/*************************
 *
 *  Login
 *
 *************************/

function sec_login() {

    global $lng, $settings;
    title($lng->getTrn('global/secLinks/login'));
    ?>
    <div style='padding-top: 20px; text-align: center;'>
    <form method="POST" action="index.php">
        <b><?php echo $lng->getTrn('secs/login/coach');?></b>
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
        <b><?php echo $lng->getTrn('secs/login/passwd');?></b>
        <input type="password" name="passwd" size="20" maxlength="50">
        <div style='display: none;'><input type='text' name='hackForHittingEnterToLogin' size='1'></div>
        <br><br>
        <input type="submit" name="login" value="Login">
    </form>
    </div>
    <?php
    if (Module::isRegistered('registration') && $settings['allow_registration']) {
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
    $n = $settings['entries_messageboard'];

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
            case 'msgdel': $msg = new Message($_POST['msg_id']); status($msg->delete()); break;
            case 'msgnew': status(Message::create(array('f_coach_id' => $coach->coach_id, 'title' => $_POST['title'], 'msg' => $_POST['txt']))); break;
            case 'msgedit':
                $msg = new Message($_POST['msg_id']);
                status($msg->edit($_POST['title'], $_POST['txt']));
                break;
        }
    }

    /*
        Generate main board.

        Left column is the message board, consisting of both commissioner messages and game summaries/results.
        To generate this table we create a general array holding the content of both.
    */

    $msgs    = Message::getMessages($n);
    $reports = Match::getReports($n);
    $tnews   = TNews::getNews(false, $n);
    $board   = array();

    // First we add all commissioner messages to the board structure.
    foreach ($msgs as $m) {
        $o = (object) array();
        // Specific fields:
        $o->msg_id    = $m->msg_id;
        $o->author_id = $m->f_coach_id;
        // General fields:
        $o->type      = 'msg';
        $o->author    = get_alt_col('coaches', 'coach_id', $m->f_coach_id, 'name');
        $o->title     = $m->title;
        $o->message   = $m->message;
        $o->date      = $m->date_posted;
        array_push($board, $o);
    }

    // Now we add all game summaries.
    foreach ($reports as $r) {
        $o = (object) array();
        $m = new Match($r->match_id);
        // Specific fields:
        $o->date_mod  = $r->date_modified;
        $o->match_id  = $r->match_id;
        $o->comments  = $r->getComments();
        // General fields:
        $o->type      = 'match';
        $o->author    = get_alt_col('coaches', 'coach_id', $r->submitter_id, 'name');
        $o->title     = "Match: $r->team1_name $r->team1_score&mdash;$r->team2_score $r->team2_name";
        $o->message   = $r->comment;
        $o->date      = $r->date_played;
        array_push($board, $o);
    }

    // And finally team news.
    if ($settings['fp_team_news']) {
        foreach ($tnews as $t) {
            $o = (object) array();
            // Specific fields:
                # none
            // General fields:
            $o->type      = 'tnews';
            $o->author    = get_alt_col('teams', 'team_id', $t->f_id, 'name');
            $o->title     = "Team news: $o->author";
            $o->message   = $t->txt;
            $o->date      = $t->date;
            array_push($board, $o);
        }
    }

    // Last touch on the board.
    if (!empty($board)) {
        objsort($board, array('-date'));
        if ($n) {
            $board = array_slice($board, 0, $n);
        }
    }

    /*
        Right column optionally (depending on settings.php) contains standings, latest game results, touchdown and casualties stats.
        We will now generate the stats, so that they are ready to be printed in correct order.
    */

    $standings  = array();
    $matches    = array();
    $touchdowns = array();

    // Standings
        // First tournament specific standings:
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
        // Now overall standings:
    $teams = Team::getTeams();
    objsort($teams, sort_rule('team'));
    array_push($standings, array('name' => $lng->getTrn('global/misc/alltime'), 'rs' => 0, 'wpoints' => false, 'teams' => array_slice($teams, 0, $settings['entries_standings'])));

    // Latest matches
    $matches = Match::getMatches($settings['entries_latest']);

    // Touchdowns
    $touchdowns = Stats::getLeaders(STATS_PLAYER, $settings['entries_touchdown'], array('-td'), true);
    // Casualties
    $casualties = Stats::getLeaders(STATS_PLAYER, $settings['entries_casualties'], array('-bh+ki+si'), true);
    // Completions
    $completions = Stats::getLeaders(STATS_PLAYER, $settings['entries_completions'], array('-cp'), true);

    /*****
     *
     * Now we are ready to generate the HTML code.
     *
     *****/

    ?>
    <div class="main_title"><?php echo $settings['site_name']; ?></div>
    <div class='main_lcol'>
        <div class="main_lcolLinks">
            <?php
            echo "<div class='mail_welcome'>\n";
            readfile('WELCOME');
            echo "</div>\n";
            // New message link
            if (is_object($coach) && $coach->ring <= RING_COM)
                echo "<a href='javascript:void(0);' onClick=\"document.getElementById('msgnew').style.display='block';\">".$lng->getTrn('secs/home/new')."</a>&nbsp;\n";

            // RSS
            echo "<a href='handler.php?type=rss'>RSS</a>\n";
            ?>

            <div style="display:none; clear:both;" id="msgnew">
                <br><br>
                <form method="POST">
                    <textarea name="title" rows="1" cols="50"><?php echo $lng->getTrn('secs/home/title');?></textarea><br><br>
                    <textarea name="txt" rows="15" cols="50"><?php echo $lng->getTrn('secs/home/msg');?></textarea><br><br>
                    <input type="hidden" name="type" value="msgnew">
                    <input type="submit" value="<?php echo $lng->getTrn('secs/home/submit');?>">
                </form>
            </div>
        </div>

        <?php
        $j = 1;
        foreach ($board as $e) {
            echo "<div class='main_lcolBox'>\n";
                switch ($e->type)
                {
                    case 'tnews':
                        $i = 1; break;
                    case 'msg':
                        $i = 3; break;
                    case 'match':
                        $i = 2; break;
                }

                echo "<h3 class='boxTitle$i'>$e->title</h3>\n";

                echo "<div class='boxBody'>\n";

                    $isLong = (strlen($e->message) > 300 && $e->type != 'msg');
                    echo "<div id='short$j'>";
                        echo substr($e->message, 0, 300)." ...&nbsp;<a href='javascript:void(0)'
                            onclick=\"document.getElementById('long$j').style.display='block'; document.getElementById('short$j').style.display='none';\"
                            >[".$lng->getTrn('secs/home/more')."]</a>\n";
                    echo "</div>\n";
                    echo "<div id='long$j'>";
                        echo $e->message;
                    echo "</div>\n";
                    echo "<script language='JavaScript' type='text/javascript'>
                        ".(($isLong)
                            ? "document.getElementById('long$j').style.display = 'none'; document.getElementById('short$j').style.display = 'block';"
                            : "document.getElementById('long$j').style.display = 'block'; document.getElementById('short$j').style.display = 'none';"
                        )."
                    </script>\n";
                    echo "<br><hr>\n";

                    echo "<table class='boxTable'>\n";
                        echo "<tr>\n";
                            if ($e->type == 'match') {
                                echo "<td align='left' width='100%'>".$lng->getTrn('secs/home/posted')." ".textdate($e->date)." " . (isset($e->date_mod) ? "(".$lng->getTrn('secs/home/lastedit')." ".textdate($e->date_mod).") " : '') .$lng->getTrn('secs/home/by')." $e->author</td>\n";
                                echo "<td align='right'><a href='index.php?section=fixturelist&amp;match_id=$e->match_id'>".$lng->getTrn('secs/home/show')."</a></td>\n";
                                if (!empty($e->comments)) {
                                    echo "<td align='right'><a href='javascript:void(0)' onclick=\"obj=document.getElementById('comment$e->match_id'); if (obj.style.display != 'none'){obj.style.display='none'}else{obj.style.display='block'};\">".$lng->getTrn('secs/home/comments')."</a></td>\n";
                                }
                            }
                            elseif ($e->type == 'msg') {
                                echo "<td align='left' width='100%'>".$lng->getTrn('secs/home/posted')." ".textdate($e->date)." ".$lng->getTrn('secs/home/by')." $e->author</td>\n";
                                if (is_object($coach) && ($coach->admin || $coach->coach_id == $e->author_id)) { // Only admins may delete messages, or if it's a commissioner's own message.
                                    echo "<td align='right'><a href='javascript:void(0);' onClick=\"document.getElementById('msgedit$e->msg_id').style.display='block';\">".$lng->getTrn('secs/home/edit')."</a></td>\n";
                                    echo "<td align='right'>
                                        <form method='POST' name='msgdel$e->msg_id' style='display:inline; margin:0px;'>
                                            <input type='hidden' name='type' value='msgdel'>
                                            <input type='hidden' name='msg_id' value='$e->msg_id'>
                                            <a href='javascript:void(0);' onClick='document.msgdel$e->msg_id.submit();'>".$lng->getTrn('secs/home/del')."</a>
                                        </form>
                                        </td>";
                                }
                            }
                            elseif ($e->type == 'tnews') {
                                echo "<td align='left' width='100%'>".$lng->getTrn('secs/home/posted')." ".textdate($e->date)."</td>\n";
                            }
                        ?>
                        </tr>
                    </table>
                    <?php
                    if ($e->type == 'match' && !empty($e->comments)) {
                        echo "<div id='comment$e->match_id'>\n";
                        echo "<hr>\n";
                        foreach ($e->comments as $c) {
                            echo "<br>Posted ".textdate($c->date)." by $c->sname:<br>\n";
                            echo $c->txt."<br>\n";
                        }
                        echo "</div>";
                        echo "<script language='JavaScript' type='text/javascript'>
                            document.getElementById('comment$e->match_id').style.display = 'none';
                        </script>\n";
                    }
                    elseif ($e->type == 'msg') {
                        echo "<div style='display:none;' id='msgedit$e->msg_id'>\n";
                        echo "<hr><br>\n";
                        echo '<form method="POST">
                            <textarea name="title" rows="1" cols="50">'.$e->title.'</textarea><br><br>
                            <textarea name="txt" rows="15" cols="50">'.$e->message.'</textarea><br><br>
                            <input type="hidden" name="type" value="msgedit">
                            <input type="hidden" name="msg_id" value="'.$e->msg_id.'">
                            <input type="submit" value="'.$lng->getTrn('secs/home/submit').'">
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

    <div class='main_rcol'>

        <?php
        if ($settings['entries_standings'] != 0) {
            foreach ($standings as $sta) {
                ?>
                <div class='main_rcolBox'>
                    <h3 class='boxTitle1'><?php echo $sta['name'];?> <?php echo $lng->getTrn('global/misc/stn');?></h3>
                    <div class='boxBody'>
                        <table class="boxTable" style='width:100%;'>
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
                                echo "<td>".(($settings['fp_links']) ? "<a href='index.php?section=coachcorner&amp;team_id=$t->team_id'>$t->name</a>" : $t->name)."</td>\n";
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
            <div class="main_rcolBox">
                <h3 class='boxTitle1'><?php echo $lng->getTrn('secs/home/recent');?></h3>
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
                            echo "<td><a href='index.php?section=fixturelist&amp;match_id=$m->match_id'>Show</a></td>";
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
                <h3 class='boxTitle1'><?php echo $lng->getTrn('secs/home/cas');?></h3>
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
                            echo "<td>".(($settings['fp_links']) ? "<a href='index.php?section=coachcorner&amp;player_id=$p->player_id'>$p->name</a>" : $p->name)."</td>\n";
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
                <h3 class='boxTitle1'><?php echo $lng->getTrn('secs/home/td');?></h3>
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
                            echo "<td>".(($settings['fp_links']) ? "<a href='index.php?section=coachcorner&amp;player_id=$p->player_id'>$p->name</a>" : $p->name)."</td>\n";
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
                <h3 class='boxTitle1'><?php echo $lng->getTrn('secs/home/cp');?></h3>
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
                            echo "<td>".(($settings['fp_links']) ? "<a href='index.php?section=coachcorner&amp;player_id=$p->player_id'>$p->name</a>" : $p->name)."</td>\n";
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
    <div class="main_cpy">
        <a href="http://nicholasmr.dk/index.php?sec=obblm">OBBLM official website</a>
        <br><br>
        This web site is completely unofficial and in no way endorsed by Games Workshop Limited.
        <br>
        Bloodquest, Blood Bowl, the Blood Bowl logo, The Blood Bowl Spike Device, Chaos, the Chaos device, the Chaos logo, Games Workshop, Games Workshop logo, Nurgle, the Nurgle device, Skaven, Tomb Kings, and all associated marks, names, races, race insignia, characters, vehicles, locations, units, illustrations and images from the Blood Bowl game, the Warhammer world are either (R), TM and/or (C) Games Workshop Ltd 2000-2006, variably registered in the UK and other countries around the world. Used without permission. No challenge to their status intended. All Rights Reserved to their respective owners.
    </div>
    <?php
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

        // Prizes
        $trObjWithPrizes = Prize::getPrizesByTour($tour->tour_id, false);

        if (!empty($trObjWithPrizes[0]->prizes)) {
            // For the below cut and paste to work.
            $t = $trObjWithPrizes[0];
            $ALLOW_EDIT = false;
            /* COPY FROM RECORDS SECTION !!! */
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
        }

        return;
    }

    // Division standings?
    if (isset($_GET['did']) && !preg_match("/[^0-9]/", $_GET['did'])) {
        title(get_alt_col('divisions', 'did', $_GET['did'], 'name'));
        HTMLOUT::standings(STATS_TEAM, STATS_DIVISION, (int) $_GET['did'], array('url' => "index.php?section=fixturelist&amp;did=$_GET[did]", 'hidemenu' => true));
        return;
    }
    // League standings?
    if (isset($_GET['lid']) && !preg_match("/[^0-9]/", $_GET['lid'])) {
        title(get_alt_col('leagues', 'lid', $_GET['lid'], 'name'));
        HTMLOUT::standings(STATS_TEAM, STATS_LEAGUE, (int) $_GET['lid'], array('url' => "index.php?section=fixturelist&amp;lid=$_GET[lid]", 'hidemenu' => true));
        return;
    }

    // Tournament description?
    if (isset($_GET['tour_id2']) && !preg_match("/[^0-9]/", $_GET['tour_id2'])) {
        if (!is_object($t = new Tour($_GET['tour_id2'])) || empty($t->date_created))
            fatal('Sorry. Invalid tournament ID specified.');

        // New description sent?
        if (isset($_POST['desc']) && !empty($_POST['desc']) && is_object($coach) && $coach->admin) {
            $txt = new TourDesc($t->tour_id);
            status($txt->save($_POST['desc']));
            unset($txt);
        }

        title("$t->name ".$lng->getTrn('secs/fixtures/tDesc/desc'));

        if (is_object($txt = new TourDesc($t->tour_id)) && empty($txt->txt))
            $txt->txt = $lng->getTrn('secs/fixtures/tDesc/noTourDesc');

        $DIS = (is_object($coach) && $coach->admin) ? '' : 'DISABLED';

        ?>
        <center>
            <form method="POST">
                <textarea name="desc" rows="20" cols="100" ><?php echo $txt->txt;?></textarea>
                <br><br>
                <input <?php echo $DIS;?> type="submit" value="Submit">
            </from>
        </center>
        <?php

        return;
    }

    /*
        Show fixture list.
    */

    title($lng->getTrn('global/secLinks/fixtures'));

    if ($settings['leegmgr_enabled']) {
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
                    <a href='index.php?section=fixturelist&amp;tour_id2=<?php echo $t->tour_id;?>'><b><?php echo $lng->getTrn('secs/fixtures/desc');?></b></a>&nbsp;
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
 *  STANDINGS
 *
 *************************/

function sec_standings() {

    global $lng, $settings;

    title($lng->getTrn('global/secLinks/standings'));
    echo $lng->getTrn('global/sortTbl/simul')."<br><br>\n";

    $teams = HTMLOUT::standings(STATS_TEAM,false,false,array('url' => 'index.php?section=standings', 'hidemenu' => true, 'return_objects' => true));

    if ($settings['hide_retired']) {$teams = array_filter($teams, create_function('$t', 'return !$t->is_retired;'));}
    $fields = array(
        'name'         => array('desc' => 'Team', 'href' => array('link' => 'index.php?section=coachcorner', 'field' => 'team_id', 'value' => 'team_id')),
        'race'         => array('desc' => 'Race', 'href' => array('link' => 'index.php?section=races', 'field' => 'race', 'value' => 'f_race_id')),
        'coach_name'   => array('desc' => 'Coach', 'href' => array('link' => 'index.php?section=coaches', 'field' => 'coach_id', 'value' => 'owned_by_coach_id')),
        'fan_factor'   => array('desc' => 'FF'),
        'rerolls'      => array('desc' => 'RR'),
        'ass_coaches'  => array('desc' => 'Ass. coaches'),
        'cheerleaders' => array('desc' => 'Cheerleaders'),
        'treasury'     => array('desc' => 'Treasury', 'kilo' => true, 'suffix' => 'k'),
        'value'        => array('desc' => 'TV', 'kilo' => true, 'suffix' => 'k'),
    );

    HTMLOUT::sort_table(
        $lng->getTrn('secs/standings/tblTitle2'),
        'index.php?section=standings',
        $teams,
        $fields,
        sort_rule('team'),
        (isset($_GET['sort'])) ? array((($_GET['dir'] == 'a') ? '+' : '-') . $_GET['sort']) : array()
    );

}

/*************************
 *
 *  TEAMS
 *
 *************************/

function sec_teams() {

    /* Generates browsable list over all teams */

    global $lng;
    title($lng->getTrn('global/secLinks/teams'));
    HTMLOUT::dispTeamList(false, false);
}

/*************************
 *
 *  PLAYERS
 *
 *************************/

function sec_players() {

    global $lng;
    title($lng->getTrn('global/secLinks/players'));
    HTMLOUT::standings(STATS_PLAYER,false,false,array('url' => 'index.php?section=players'));
    ?>
    <?php echo $lng->getTrn('secs/players/colors');?>:
    <ul>
        <li style='width: 4em;background-color:<?php echo COLOR_HTML_DEAD;?>;'>Dead</li>
        <li style='width: 4em;background-color:<?php echo COLOR_HTML_SOLD;?>;'>Sold</li>
    </ul>
    <?php
}

/*************************
 *
 *  COACHES
 *
 *************************/

function sec_coaches() {

    global $lng, $coach;

    /*
        Specific coach page requested?
    */
    if (isset($_GET['coach_id']) && !preg_match("/[^0-9]/", $_GET['coach_id']) &&
        is_object($c = new Coach($_GET['coach_id']))) {

        title("Coach $c->name");
        echo "<center><a href='index.php?section=coaches'>[".$lng->getTrn('global/misc/back')."]</a>&nbsp; |
            &nbsp;<a href='handler.php?type=graph&amp;gtype=".SG_T_COACH."&amp;id=$c->coach_id''>[Vis. stats]</a></center><br>\n";

        ?>
        <table class='picAndText'>
            <tr>
                <td class='light'><b><?php echo $lng->getTrn('secs/coaches/pic');?> <?php echo $c->name; ?></b></td>
                <td class='light'><b><?php echo $lng->getTrn('secs/coaches/about');?></b></td>
                <?php
                if (is_object($coach)) {
                    ?><td class='light'><b><?php echo $lng->getTrn('secs/coaches/contact');?></b></td><?php
                }
                ?>
            </tr>
            <tr>
                <td>
                    <?php
                    Image::makeBox(IMGTYPE_COACH, $c->coach_id, false, false);
                    ?>
                </td>
                <td valign='top'>
                    <?php
                    $txt = $c->getText();
                    if (empty($txt)) {
                        $txt = $lng->getTrn('secs/coaches/nowrite')." $c->name.";
                    }
                    echo '<p>'.$txt."</p>\n";
                    ?>
                </td>
                <?php
                if (is_object($coach)) {
                    ?>
                    <td valign='top'>
                        <table>
                            <tr>
                                <td><b>Name</b></td>
                                <td><?php echo empty($c->realname) ? '<i>None</i>' : $c->realname;?></td>
                            </tr>
                            <tr>
                                <td><b>Phone</b></td>
                                <td><?php echo empty($c->phone) ? '<i>None</i>' : $c->phone?></td>
                            </tr>
                            <tr>
                                <td><b>Mail</b></td>
                                <td><?php echo empty($c->mail) ? '<i>None</i>' : $c->mail?></td>
                            </tr>
                        </table>
                    </td>
                    <?php
                }
                ?>
            </tr>
        </table>
        <?php

        HTMLOUT::standings(STATS_TEAM,false,false,array('url' => "index.php?section=coaches&amp;coach_id=$c->coach_id", 'teams_from' => STATS_COACH, 'teams_from_id' => $c->coach_id));
        echo '<br>';
        HTMLOUT::recentGames(STATS_COACH, $c->coach_id, false, false, false, false, array('url' => "index.php?section=coaches&amp;coach_id=$c->coach_id", 'n' => MAX_RECENT_GAMES, 'GET_SS' => 'gp'));

        return;
    }

    /*
        Show coaches table.
    */

    title($lng->getTrn('global/secLinks/coaches'));
    HTMLOUT::standings(STATS_COACH, false, false, array('url' => 'index.php?section=coaches'));
}

/*************************
 *
 *  RACES
 *
 *************************/

function sec_races() {

    global $lng;

    /*
        This function can do two things:
            Either, it can show race statistics
            Or, it can show team data from LRB5.
    */

    /*
        Show specific race stats
    */
    if (isset($_GET['race']) && is_object($race = new Race($_GET['race'])) && ($roster = $race->getRoster())) { // Last eval makes sure $roster is not empty array.
        title($race->race);
        ?>
        <center><img src="<?php echo $roster['other']['icon'];?>" alt="Race icon"></center>
        <ul><li>Re-roll cost: <?php echo $roster['other']['RerollCost']/1000;?>k</li></ul><br>
        <?php
        $players = array();
        foreach ($roster['players'] as $player => $d) {
            $p = (object) array_merge(array('position' => $player), $d);
            $p->skills = implode(', ', $p->{'Def skills'});
            foreach (array('N', 'D') as $s) {
                array_walk($p->{"$s skills"}, create_function('&$val', '$val = substr($val,0,1);'));
                $p->$s = implode('', $p->{"$s skills"});
            }
            $players[] = $p;
        }
        $fields = array(
            'position'  => array('desc' => 'Position'),
            'ma'        => array('desc' => 'Ma'),
            'st'        => array('desc' => 'St'),
            'ag'        => array('desc' => 'Ag'),
            'av'        => array('desc' => 'Av'),
            'skills'    => array('desc' => 'Skills', 'nosort' => true),
            'N'         => array('desc' => 'Normal', 'nosort' => true),
            'D'         => array('desc' => 'Double', 'nosort' => true),
            'cost'      => array('desc' => 'Price', 'kilo' => true, 'suffix' => 'k'),
            'qty'       => array('desc' => 'Max.'),
        );
        HTMLOUT::sort_table(
            $race->race.' '.$lng->getTrn('secs/races/players'),
            "index.php?section=races&amp;race=$race->race",
            $players,
            $fields,
            sort_rule('race_page'),
            (isset($_GET['sortpl'])) ? array((($_GET['dirpl'] == 'a') ? '+' : '-') . $_GET['sortpl']) : array(),
            array('GETsuffix' => 'pl')
        );

        // Teams of the chosen race.
        HTMLOUT::standings(STATS_TEAM,false,false,array('url' => "index.php?section=races&amp;race=$race->race_id", 'teams_from' => STATS_RACE, 'teams_from_id' => $race->race_id));
        echo '<br>';
        HTMLOUT::recentGames(STATS_RACE, $race->race_id, false, false, false, false, array('url' => "index.php?section=races&amp;race=$race->race_id", 'n' => MAX_RECENT_GAMES, 'GET_SS' => 'gp'));

        // Don't also print race stats.
        return;
    }

    /*
        Show all races' stats
    */

    title($lng->getTrn('global/secLinks/races'));
    HTMLOUT::standings(STATS_RACE,false,false,array('url' => 'index.php?section=races'));
}

/*************************
 *
 *  STAR PLAYERS
 *
 *************************/

function sec_stars() {

    global $stars, $lng;

    // Specific star hire history
    if (isset($_GET['sid'])) {
        $s = new Star($_GET['sid']);
        title($lng->getTrn('secs/stars/hh').' '.$s->name);
        echo '<center><a href="index.php?section=stars">['.$lng->getTrn('global/misc/back').']</a></center><br><br>';
        HTMLOUT::starHireHistory(false, false, false, false, $_GET['sid'], array('url' => "index.php?section=stars&amp;sid=$_GET[sid]"));
        return;
    }

    // All stars
    title($lng->getTrn('global/secLinks/stars'));
    echo $lng->getTrn('global/sortTbl/simul')."<br>\n";
    echo $lng->getTrn('global/sortTbl/spp')."<br><br>\n";
    HTMLOUT::standings(STATS_STAR, false, false, array('url' => 'index.php?section=stars'));
    $stars = Star::getStars(false,false,false,false);
    foreach ($stars as $s) {
        $s->skills = '<small>'.implode(', ', $s->skills).'</small>';
        $s->teams = '<small>'.implode(', ', $s->teams).'</small>';
        $s->name = preg_replace('/\s/', '&nbsp;', $s->name);
    }
    $fields = array(
        'name'   => array('desc' => 'Star', 'href' => array('link' => 'index.php?section=stars', 'field' => 'sid', 'value' => 'star_id')),
        'cost'   => array('desc' => 'Price', 'kilo' => true, 'suffix' => 'k'),
        'ma'     => array('desc' => 'Ma'),
        'st'     => array('desc' => 'St'),
        'ag'     => array('desc' => 'Ag'),
        'av'     => array('desc' => 'Av'),
        'teams'  => array('desc' => 'Teams', 'nosort' => true),
        'skills' => array('desc' => 'Skills', 'nosort' => true),
    );
    HTMLOUT::sort_table(
        '<a name="s2">'.$lng->getTrn('secs/stars/tblTitle2').'</a>',
        'index.php?section=stars',
        $stars,
        $fields,
        sort_rule('star'),
        (isset($_GET['sort'])) ? array((($_GET['dir'] == 'a') ? '+' : '-') . $_GET['sort']) : array(),
        array('anchor' => 's2')
    );
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
        'hof'    => $lng->getTrn('secs/records/d_hof'),
        'wanted' => $lng->getTrn('secs/records/d_wanted'),
        'prize'  => $lng->getTrn('secs/records/d_prizes'),
    );

    // This section's routines are placed in the records.php file.
    if (isset($_GET['subsec'])) {
        switch ($_GET['subsec'])
        {
            case 'hof':    Module::run('hof',    array('makeList', array($ALLOW_EDIT))); break;
            case 'wanted': Module::run('wanted', array('makeList', array($ALLOW_EDIT))); break;
            case 'prize':  title($subsecs[$_GET['subsec']]); prizes($ALLOW_EDIT); break;
        }
        return;
    }

    title($lng->getTrn('global/secLinks/records'));
    echo $lng->getTrn('secs/records/menu')."<br><br>\n";
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
                    $img = new Image(IMGTYPE_PLAYER, $p->player_id);
                    $pic = $img->getPath();
                    echo "<div style='float:left; padding:10px;'>$p->name (#$p->nr)<br><a href='index.php?section=coachcorner&amp;player_id=$p->player_id'><img HEIGHT=150 src='$pic' alt='pic'></a></div>";
                }
                break;

            case 'stad':
                echo "<b>".$lng->getTrn('secs/gallery/stads')."</b><br><hr><br>\n";
                $teams = Team::getTeams();
                foreach ($teams as $t) {
                    $img = new Image(IMGTYPE_TEAMSTADIUM, $t->team_id);
                    $pic = $img->getPath();
                    echo "<div style='float:left; padding:10px;'>$t->name<br><a href='$pic'><img HEIGHT=150 src='$pic' alt='pic'></a></div>";
                }
                break;

            case 'coach':
                echo "<b>".$lng->getTrn('secs/gallery/coaches')."</b><br><hr><br>\n";
                $coaches = Coach::getCoaches();
                foreach ($coaches as $c) {
                    $img = new Image(IMGTYPE_COACH, $c->coach_id);
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
            list($author,$date) = Module::getInfo($modname);
            $mods[] = "<i>$modname</i> ($author, $date)";
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
 *
 *  GUEST BOOK
 *
 *************************/

function sec_guest() {

    global $coach, $lng;

    /*
        Show guest book
    */

    if (isset($_POST['msg']) && !empty($_POST['msg'])) {
        status(GuestBook::create($_POST['msg']));
    }

    if (isset($_GET['delete'])) {
        $g = new GuestBook($_GET['delete']);
        $g->delete();
        unset($g);
    }

    title($lng->getTrn('global/secLinks/gb'));
    ?>
    <div style="text-align: center;">
    <b>        <?php echo $lng->getTrn('secs/gb/new');?>: </b>
    <form method="POST">
        <textarea name="msg" rows="5" cols="50"></textarea>
        <br>
        <?php echo $lng->getTrn('secs/gb/note');?>
        <br><br>
        <input type="submit" value="Submit">
    </form>
    </div>
    <br>
    <?php

    foreach (GuestBook::getBook() as $g) {
        echo "<div class='gb'>\n";
            echo "<div class='boxTitle1'>".$lng->getTrn('secs/gb/posted')." $g->date</div>\n";
            echo "<div class='boxBody'>\n";
                echo "$g->txt";
                if (is_object($coach) && $coach->admin) {
                    ?>
                    <br><br><hr>
                    <table class='boxTable' style="width: 100%">
                    <tr>
                    <td style="text-align: right;">
                    <?php
                    echo "<a href='index.php?section=guest&amp;delete=$g->gb_id'>".$lng->getTrn('secs/gb/del')."</a>\n";
                    ?>
                    </td>
                    </tr>
                    </table>
                    <?php
                }
                ?>
            </div>
        </div>
        <?php
    }
}

/*************************
 *
 *  COACH CORNER
 *
 *************************/

function sec_coachcorner() {

    global $lng;

    /*
        Before displaying coach corner we check if visitor wants a specific team's page or a player page.
    */

    if (isset($_GET['player_id']) && $_GET['player_id'] < 0) {
        status(false, 'Sorry. Player rosters do not exist for star players and mercenaries.');
        return;
    }

    // If player ID is set then show player page. This MUST be checked for before checking if a team's page is wanted, else access will be denied.
    if (isset($_GET['player_id']) && !preg_match("/[^0-9]/", $_GET['player_id'])) {
        if (!get_alt_col('players', 'player_id', $_GET['player_id'], 'player_id')) {
            fatal("Invalid player ID.");
        }
        $player = new Player_HTMLOUT($_GET['player_id']);
        $player->playerPage();
        return;
    }

    // If team ID is set then show team page.
    if (isset($_GET['team_id']) && !preg_match("/[^0-9]/", $_GET['team_id']) && $_GET['team_id'] != 'new') {
        if (!get_alt_col('teams', 'team_id', $_GET['team_id'], 'team_id')) {
                fatal("Invalid team ID.");
        }
        $team = new Team_HTMLOUT($_GET['team_id']);
        $team->teamPage();
        return;
    }

    // Quit if not logged in.
    if (!isset($_SESSION['logged_in'])) {
        fatal('You must log in to access coach corner.');
        return;
    }

    /*
     *  Main actions:
     *
     *  1.  New team        (team_id = "new")
     *  2.  Existing team.  (team_id = digit)
     *  3.  Change coach settings (passwd & mail)
     *
     */

    $coach = new Coach($_SESSION['coach_id']);

    /* New team page */

    if (isset($_GET['team_id']) && $_GET['team_id'] == 'new') {

        // Form posted? -> Create team.
        if (isset($_POST['new_team']) && !empty($_POST['name']) && !empty($_POST['race'])) {

            if (get_magic_quotes_gpc())
                $_POST['name'] = stripslashes($_POST['name']);

            status(Team::create(array('name' => $_POST['name'], 'coach_id' => $coach->coach_id, 'race' => $_POST['race'])));

            // Go back to coach corner main page again.
            unset($_GET['team_id']);
            sec_coachcorner();
            return;
        }

        // Show new team form.
        title($lng->getTrn('secs/cc/new_team/title'));
        ?>
        <form method="POST">
        <table class="text">
            <tr>
            <td valign="top">
                <b><?php echo $lng->getTrn('secs/cc/new_team/name');?>:</b> <br>
                <input type="text" name="name" size="20" maxlength="50">
                <br><br>
                <b><?php echo $lng->getTrn('secs/cc/new_team/race');?>:</b> <br>
                <select name="race">
                    <?php
                    foreach (Race::getRaces(false) as $r)
                        echo "<option value='$r'>$r</option>\n";
                    ?>
                </select>
                <br><br>
                <input type="submit" name="new_team" value="<?php echo $lng->getTrn('secs/cc/new_team/button');?>">
            </td>
            </tr>
        </table>
        </form>
        <?php
    }

    /* Show coach corner main page. */

    else {

        // Was new password/email request made?
        if (isset($_POST['type'])) {

            if (get_magic_quotes_gpc()) {
                $_POST['new_passwd']   = isset($_POST['new_passwd'])     ? stripslashes($_POST['new_passwd']) : '';
                $_POST['new_phone']    = isset($_POST['new_phone'])      ? stripslashes($_POST['new_phone']) : '';
                $_POST['new_email']    = isset($_POST['new_email'])      ? stripslashes($_POST['new_email']) : '';
                $_POST['new_name']     = isset($_POST['new_name'])       ? stripslashes($_POST['new_name']) : '';
                $_POST['new_realname'] = isset($_POST['new_realname'])   ? stripslashes($_POST['new_realname']) : '';
            }

            switch ($_POST['type'])
            {
                case 'chpasswd':    status(Coach::login($coach->name, $_POST['old_passwd'], false) && $coach->setPasswd($_POST['new_passwd'])); break;
                case 'chphone':     status($coach->setPhone($_POST['new_phone'])); break;
                case 'chmail':      status($coach->setMail($_POST['new_email'])); break;
                case 'chlogin':     status($coach->setName($_POST['new_name'])); break;
                case 'chname':      status($coach->setRealName($_POST['new_realname'])); break;
                case 'chtheme':     status($coach->setSetting('theme', (int) $_POST['new_theme'])); break;

                case 'pic':         status($coach->savePic(false)); break;
                case 'coachtext':
                    if (get_magic_quotes_gpc()) {
                        $_POST['coachtext'] = stripslashes($_POST['coachtext']);
                    }
                    status($coach->saveText($_POST['coachtext']));
                    break;
            }
        }

        title($lng->getTrn('global/secLinks/coachcorner'));

        // Coach stats.
        ?>
        <table class="text">
            <tr>
                <td class="light">
                    <b><?php echo $lng->getTrn('secs/cc/main/cstats');?></b>
                </td>
            </tr>
        </table>

        <table class="text" style="padding:10px; border-spacing:5px;">
            <tr>
                <td width="150px"></td>
                <td></td>
            </tr>
            <tr>
                <td><?php echo $lng->getTrn('secs/cc/main/games_played');?>:</td>
                <td><?php echo $coach->played; ?></td>
            </tr>
            <tr>
                <td><?php echo $lng->getTrn('secs/cc/main/win_ptc');?>:</td>
                <td><?php echo sprintf("%1.1f", $coach->played == 0 ? 0 : $coach->won/$coach->played * 100) . '%'; ?></td>
            </tr>
            <tr>
                <td><?php echo $lng->getTrn('secs/cc/main/tours_won');?>:</td>
                <td>
                <?php
                $won_tours = array();
                foreach ($coach->getWonTours() as $tour) {
                    array_push($won_tours, $tour->name);
                }
                echo empty($won_tours) ? '<i>None</i>' : implode(', ', $won_tours);
                ?>
                </td>
            </tr>
        </table>
        <?php


        // Generate teams list.

        HTMLOUT::dispTeamList(STATS_COACH, $coach->coach_id);

        // New team and change coach settings.

        ?>
        <table class="teams" style="width:270px;">
            <tr>
                <td style="text-align:center;">
                    <a href="?section=coachcorner&amp;team_id=new"><img style="border: none;" alt="new team" src="<?php echo RACE_ICONS;?>/new.png"></a>
                </td>
            </tr>
            <tr>
                <td class="light" style="text-align:center;">
                    <?php echo $lng->getTrn('secs/cc/main/start_new');?>
                </td>
            </tr>
        </table>

        <table class="text">
            <tr>
                <td class="light">
                    <b><?php echo $lng->getTrn('secs/cc/main/your_info');?></b>
                </td>
            </tr>
        </table>

        <table class="text" style="border-spacing:5px; padding:20px;">
                <tr>
                    <form method="POST">
                    <td><?php echo $lng->getTrn('secs/cc/main/chpasswd');?>:</td>
                    <td><?php echo $lng->getTrn('secs/cc/main/old');?>:<input type='password' name='old_passwd' size="20" maxlength="50"></td>
                    <td><?php echo $lng->getTrn('secs/cc/main/new');?>:<input type='password' name='new_passwd' size="20" maxlength="50"></td>
                    <td><input type="submit" name="button" value="<?php echo $lng->getTrn('secs/cc/main/chpasswd');?>"></td>
                    <input type='hidden' name='type' value='chpasswd'>
                    </form>
                </tr>
                <tr>
                    <form method="POST">
                    <td><?php echo $lng->getTrn('secs/cc/main/chphone');?>:</td>
                    <td><?php echo $lng->getTrn('secs/cc/main/old');?>:<input type='text' name='old_phone' readonly value="<?php echo $coach->phone; ?>" size="20" maxlength="129"></td>
                    <td><?php echo $lng->getTrn('secs/cc/main/new');?>:<input type='text' name='new_phone' size="20" maxlength="25"></td>
                    <td><input type="submit" name="button" value="<?php echo $lng->getTrn('secs/cc/main/chphone');?>"></td>
                    <input type='hidden' name='type' value='chphone'>
                    </form>
                </tr>
                <tr>
                    <form method="POST">
                    <td><?php echo $lng->getTrn('secs/cc/main/chmail');?>:</td>
                    <td><?php echo $lng->getTrn('secs/cc/main/old');?>:<input type='text' name='old_email' readonly value="<?php echo $coach->mail; ?>" size="20" maxlength="129"></td>
                    <td><?php echo $lng->getTrn('secs/cc/main/new');?>:<input type='text' name='new_email' size="20" maxlength="129"></td>
                    <td><input type="submit" name="button" value="<?php echo $lng->getTrn('secs/cc/main/chmail');?>"></td>
                    <input type='hidden' name='type' value='chmail'>
                    </form>
                </tr>
                <tr>
                    <form method="POST">
                    <td><?php echo $lng->getTrn('secs/cc/main/chlogin');?>:</td>
                    <td><?php echo $lng->getTrn('secs/cc/main/old');?>:<input type='text' name='old_name' readonly value="<?php echo $coach->name; ?>" size="20" maxlength="50"></td>
                    <td><?php echo $lng->getTrn('secs/cc/main/new');?>:<input type='text' name='new_name' size="20" maxlength="50"></td>
                    <td><input type="submit" name="button" value="<?php echo $lng->getTrn('secs/cc/main/chlogin');?>"></td>
                    <input type='hidden' name='type' value='chlogin'>
                    </form>
                </tr>
                <tr>
                    <form method="POST">
                    <td><?php echo $lng->getTrn('secs/cc/main/chname');?>:</td>
                    <td><?php echo $lng->getTrn('secs/cc/main/old');?>:<input type='text' name='old_realname' readonly value="<?php echo $coach->realname; ?>" size="20" maxlength="50"></td>
                    <td><?php echo $lng->getTrn('secs/cc/main/new');?>:<input type='text' name='new_realname' size="20" maxlength="50"></td>
                    <td><input type="submit" name="button" value="<?php echo $lng->getTrn('secs/cc/main/chname');?>"></td>
                    <input type='hidden' name='type' value='chname'>
                    </form>
                </tr>
                <tr>
                    <form method="POST">
                    <td><?php echo $lng->getTrn('secs/cc/main/chtheme');?>:</td>
                    <td><?php echo $lng->getTrn('secs/cc/main/current');?>: <?php echo $coach->settings['theme'];?></td>
                    <td>
                        <?php echo $lng->getTrn('secs/cc/main/new');?>:
                        <select name='new_theme'>
                            <?php
                            foreach (array(1 => 'Classic', 2 => 'Clean') as $theme => $desc) {
                                echo "<option value='$theme'>$theme: $desc</option>\n";
                            }
                            ?>
                        </select>
                    </td>
                    <td><input type="submit" name="button" value="<?php echo $lng->getTrn('secs/cc/main/chtheme');?>"></td>
                    <input type='hidden' name='type' value='chtheme'>
                    </form>
                </tr>
        </table>

        <table class='picAndText'>
            <tr>
                <td class='light'><b><?php echo $lng->getTrn('secs/cc/main/photo');?></b></td>
                <td class='light'><b><?php echo $lng->getTrn('secs/cc/main/about');?></b></td>
            </tr>
            <tr>
                <td>
                    <?php
                    Image::makeBox(IMGTYPE_COACH, $coach->coach_id, true, false);
                    ?>
                </td>
                <td valign='top'>
                    <?php
                    $txt = $coach->getText();
                    if (empty($txt)) {
                        $txt = $lng->getTrn('secs/cc/main/nowrite');
                    }
                    ?>
                    <form method='POST'>
                        <textarea name='coachtext' rows='15' cols='70'><?php echo $txt;?></textarea>
                        <br><br>
                        <input type="hidden" name="type" value="coachtext">
                        <input type="submit" name='Save' value="<?php echo $lng->getTrn('secs/cc/main/save');?>">
                    </form>
                </td>
            </tr>
        </table>
        <?php
    }

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
