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
 *      - teams.php     For handling team pages.
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

    global $lng;

    ?>
    <div style='padding-top:40px; text-align: center;'>
    <form method="POST" action="index.php">
        <b><?php echo $lng->getTrn('secs/login/coach');?></b>
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
        &nbsp;&nbsp;
        <b><?php echo $lng->getTrn('secs/login/passwd');?></b>
        <input type="password" name="passwd" size="20" maxlength="50"> 
        <br><br>
        <input type="submit" name="login" value="Login">
        <br><br>
        <?php echo $lng->getTrn('secs/login/note');?>
    </form>
    </div>
    <?php
}

/*************************
 *
 *  MAIN
 *
 *************************/

function sec_main() {

    global $settings, $rules, $coach, $lng;
    $n = (!isset($_GET['view']) || $_GET['view'] == 'normal') ? $settings['entries_messageboard'] : false; // false == show everything, else show $n board entries.

    /*
        Left column is the message board, consisting of both commissioner messages and game summaries/results.
        To generate this table we create a general array holding the content of both.
    */

    $msgs = Message::getMessages($n);
    $reports = Match::getReports($n);    
    $board = array();
    
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
        $pics = $m->getPics();
        // Specific fields:
        $o->date_mod  = $r->date_modified;
        $o->match_id  = $r->match_id;
        $o->hasPics   = !empty($pics);
        $o->comments  = $r->getComments();
        // General fields:
        $o->type      = 'match';
        $o->author    = get_alt_col('coaches', 'coach_id', $r->submitter_id, 'name');
        $o->title     = "Match: $r->team1_name $r->team1_score - $r->team2_score $r->team2_name";
        $o->message   = $r->comment;
        $o->date      = $r->date_played;
        array_push($board, $o);
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
            if ($t->begun && !$t->is_finished) {
                $teams = $t->getTeams();
                foreach ($teams as $team) {
                    $team->setStats($t->tour_id);
                }
                objsort($teams, $t->getRSSortRule());
                array_push($standings, array('name' => $t->name, 'rs' => $t->rs, 'wpoints' => $t->isRSWithPoints(), 'teams' => array_slice($teams, 0, $settings['entries_standings'])));
            }
        }
    }
        // Now overall standings:
    $teams = Team::getTeams();
    objsort($teams, sort_rule('team'));
    array_push($standings, array('name' => 'Overall', 'rs' => 0, 'wpoints' => false, 'teams' => array_slice($teams, 0, $settings['entries_standings'])));

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
    <div class="main_title"><?php echo $settings['league_name']; ?></div>
    <div class='main_lcol'>
        <div class="main_lcolLinks">
            <?php
            echo "<div class='mail_welcome'>\n";
            readfile('WELCOME');
            echo "</div>\n";
            // New message link
            if (is_object($coach) && $coach->ring <= RING_COM)
                echo "<a href='javascript:void(0)' onclick=\"window.open('handler.php?type=msg&amp;action=new', 'handler_msg', 'width=550,height=450');\">".$lng->getTrn('secs/home/new')."</a>\n";

            // View mode
            if (!empty($board)) { # Only show when messages exist.
                if (isset($_GET['view']) && $_GET['view'] == 'all')
                    echo "<a href='index.php?section=main&amp;view=normal'>".$lng->getTrn('secs/home/normal')."</a>\n";
                else
                    echo "<a href='index.php?section=main&amp;view=all'>".$lng->getTrn('secs/home/showall')."</a>\n";
            }
            // RSS
            echo "<a href='handler.php?type=rss'>RSS</a>\n";
            ?>
        </div>
    
        <?php
        $j = 1;
        foreach ($board as $e) {
            echo "<div class='main_lcolBox'>\n";
                if     ($e->type == 'match') $i = 2;
                elseif ($e->type == 'msg')   $i = 3;
                echo "<div class='boxTitle$i'>$e->title</div>\n";
                
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
                                echo "<td align='left' width='100%'>".$lng->getTrn('secs/home/posted')." $e->date " . (isset($e->date_mod) ? "(".$lng->getTrn('secs/home/lastedit')." $e->date_mod) " : '') .$lng->getTrn('secs/home/by')." $e->author</td>\n";
                                echo "<td align='right'><a href='index.php?section=fixturelist&amp;match_id=$e->match_id'>".$lng->getTrn('secs/home/show')."</a></td>\n";
                                if (!empty($e->comments)) {
                                    echo "<td align='right'><a href='javascript:void(0)' onclick=\"obj=document.getElementById('comment$e->match_id'); if (obj.style.display != 'none'){obj.style.display='none'}else{obj.style.display='block'};\">".$lng->getTrn('secs/home/comments')."</a></td>\n";
                                }
                                if ($e->hasPics) {
                                    echo "<td align='right'><a href='handler.php?type=mg&amp;mid=$e->match_id'>".$lng->getTrn('secs/home/photos')."</a></td>\n";
                                }
                            }
                            elseif ($e->type == 'msg') {
                                echo "<td align='left' width='100%'>".$lng->getTrn('secs/home/posted')." $e->date ".$lng->getTrn('secs/home/by')." $e->author</td>\n";
                                if (is_object($coach) && ($coach->admin || $coach->coach_id == $e->author_id)) { // Only admins may delete messages, or if it's a commissioner's own message.
                                    echo "<td align='right'><a href='javascript:void(0)' onclick=\"window.open('handler.php?type=msg&amp;action=edit&amp;msg_id=$e->msg_id', 'handler_msg', 'width=550,height=450');\">".$lng->getTrn('secs/home/edit')."</a></td>\n";
                                    echo "<td align='right'><a href='javascript:void(0)' onclick=\"window.open('handler.php?type=msg&amp;action=delete&amp;msg_id=$e->msg_id', 'handler_msg', 'width=250,height=250');\">".$lng->getTrn('secs/home/del')."</a></td>\n";
                                }
                            }
                        ?>
                        </tr>
                    </table>
                    <?php
                    if ($e->type == 'match' && !empty($e->comments)) {
                        echo "<div id='comment$e->match_id'>\n";
                        echo "<hr>\n";
                        foreach ($e->comments as $c) {
                            echo "<br>Posted $c->date by $c->sname:<br>\n";
                            echo $c->txt."<br>\n";
                        }
                        echo "</div>";
                        echo "<script language='JavaScript' type='text/javascript'>
                            document.getElementById('comment$e->match_id').style.display = 'none';
                        </script>\n";
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
                    <div class='boxTitle1'><?php echo $sta['name'];?> <?php echo $lng->getTrn('global/misc/stn');?></div>
                    <div class='boxBody'>
                        <table class="boxTable" style='width:100%;'>
                            <tr>
                                <td><b>Team</b></td>
                                <td><b>W</b></td>
                                <td><b>L</b></td>
                                <td><b>D</b></td>
                                <?php
                                if ($sta['wpoints']) {
                                    echo "<td><b>Points</b></td>\n";
                                    if ($sta['rs'] == 3) {
                                        echo "<td><b>TD+Cas</b></td>\n";
                                    }
                                    else {
                                        echo "<td><b>Cas</b></td>\n";                                            
                                    }
                                }
                                else {
                                    echo "<td><b>Cas</b></td>\n";
                                    echo "<td><b>Score</b></td>\n";                                
                                }
                                ?>
                            </tr>
                            <?php
                            foreach ($sta['teams'] as $t) {
                                echo "<tr>\n";
                                echo "<td>$t->name</td>\n";
                                echo "<td>$t->won</td>\n";
                                echo "<td>$t->lost</td>\n";
                                echo "<td>$t->draw</td>\n";
                                if ($sta['wpoints']) {
                                    echo '<td>'.((is_float($t->points)) ? sprintf('%1.2f', $t->points) : $t->points)."</td>\n";
                                    if ($sta['rs'] == 3) {
                                        echo "<td>$t->tdcas</td>\n";
                                    }
                                    else {
                                        echo "<td>$t->cas</td>\n";                                            
                                    }
                                }
                                else {
                                    echo "<td>$t->cas</td>\n";
                                    echo "<td>$t->score_team - $t->score_opponent</td>\n";
                                }
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
                <div class='boxTitle1'><?php echo $lng->getTrn('secs/home/recent');?></div>
                <div class='boxBody'>
                    <table class="boxTable">
                        <tr>
                            <td style="text-align: right;" width="50%"><b>Home</b></td>
                            <td> </td>
                            <td><b>-</b></td>
                            <td> </td>
                            <td style="text-align: left;" width="50%"><b>Guest</b></td>

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
                            echo "<td>" . $m->$home_score . "</td>\n";
                            echo "<td>-</td>\n";
                            echo "<td>" . $m->$guest_score . "</td>\n";
                            echo "<td style='text-align: left;'>" . $m->$guest_name . "</td>\n";
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
                <div class='boxTitle1'><?php echo $lng->getTrn('secs/home/cas');?></div>
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
                            echo "<td>$p->name</td>\n";
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
                <div class='boxTitle1'><?php echo $lng->getTrn('secs/home/td');?></div>
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
                            echo "<td>$p->name</td>\n";
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
                <div class='boxTitle1'><?php echo $lng->getTrn('secs/home/cp');?></div>
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
                            echo "<td>$p->name</td>\n";
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

    global $rules, $coach, $lng;
    $KEEP_TOUR_OPEN = false;

    // Admin actions made?
    if (is_object($coach) && $coach->admin) {
        if (isset($_GET['tlock']) && !preg_match('/[^0-9]$/', $_GET['tlock'])) {
            $match = new Match($_GET['tlock']);
            status($match->toggleLock());
        }
        elseif (isset($_GET['mdel']) && !preg_match('/[^0-9]$/', $_GET['mdel'])) {
            $match = new Match($_GET['mdel']);
            $KEEP_TOUR_OPEN = $match->f_tour_id;
            status($match->delete());
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
            case TT_NOFINAL:  $type = 'Round-Robin without final'; break;
            case TT_FINAL:    $type = 'Round-Robin with final'; break;
            case TT_SEMI:     $type = 'Round-Robin with final and semi-finals'; break;
            case TT_KNOCKOUT: $type = 'Knock-Out tournament'; break;
            case TT_SINGLE:   $type = 'FFA tournament'; break;
        }

        title($tour->name);
        echo "<center><a href='index.php?section=fixturelist'>[".$lng->getTrn('global/misc/back')."]</a></center><br>\n";
        
        echo "<b>".$lng->getTrn('secs/fixtures/stn/type')."</b>: $type<br>\n";
        echo "<b>".$lng->getTrn('secs/fixtures/stn/rs')."</b>: $tour->rs = ".$tour->getRSSortRule(true)
            .((is_object($coach) && $coach->admin) ? "&nbsp;&nbsp;&nbsp;<a href='index.php?section=admin&amp;subsec=chtr'>[".$lng->getTrn('global/misc/change')."]</a>" : '').
            "<br><br>\n";

        $teams = $tour->getStandings();
        $ELORanks = ELO::getRanks($tour->tour_id);
        foreach ($teams as $t) {
            $t->setStreaks($tour->tour_id);
            $t->elo         = $ELORanks[$t->team_id];
            $t->APM_ki      = ($t->played == 0) ? 0 : $t->ki/$t->played;
            $t->APM_cas     = ($t->played == 0) ? 0 : $t->cas/$t->played;
        }

        $fields = array(
            'name'           => array('desc' => 'Team', 'href' => array('link' => 'index.php?section=coachcorner', 'field' => 'team_id', 'value' => 'team_id')),
            'points'         => array('desc' => 'PTS'), 
            'smp'            => array('desc' => 'SMP'), 
            'won'            => array('desc' => 'W'), 
            'lost'           => array('desc' => 'L'), 
            'draw'           => array('desc' => 'D'), 
            'played'         => array('desc' => 'GP'), 
            'win_percentage' => array('desc' => 'WIN%'), 
            'row_won'        => array('desc' => 'SW'), 
            'row_lost'       => array('desc' => 'SL'), 
            'row_draw'       => array('desc' => 'SD'), 
            'elo'            => array('desc' => 'ELO'), 
            'score_team'     => array('desc' => 'GF'),
            'score_opponent' => array('desc' => 'GA'),
            'APM_ki'         => array('desc' => 'APM Ki'),
            'APM_cas'        => array('desc' => 'APM Cas'),
            'tcas'           => array('desc' => 'tcas'), 
            'td'             => array('desc' => 'Td'), 
            'cp'             => array('desc' => 'Cp'), 
            'intcpt'         => array('desc' => 'Int'), 
            'cas'            => array('desc' => 'Cas'), 
            'bh'             => array('desc' => 'BH'), 
            'si'             => array('desc' => 'Si'), 
            'ki'             => array('desc' => 'Ki'), 
        );

        if (!$tour->isRSWithPoints())
            unset($fields['points']);
        
        sort_table(
            "$tour->name ".$lng->getTrn('global/misc/stn'), 
            "index.php?section=fixturelist&amp;tour_id=$tour->tour_id", 
            $teams, 
            $fields, 
            $tour->getRSSortRule(), 
            (isset($_GET['sort'])) ? array((($_GET['dir'] == 'a') ? '+' : '-') . $_GET['sort']) : array()
        );
        
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
    
    echo $lng->getTrn('secs/fixtures/links')."<br><br>\n";

    
    $flist = array( # The fixture list
// $flist MODEL:
#            'tour1' => array(
#                'tour_obj' => $tour_obj,
#                'round1' => array('match1_id' => $match1_obj, 'match2_id' => $match2_obj),
#                'round2' => array('match3_id' => $match3_obj, 'match4_id' => $match4_obj) 
#            )
    );
    
    // Generate fixture list.
    foreach (Tour::getTours() as $t) {
        foreach ($t->getMatches() as $m) {
            $flist[$t->name][$m->round][$m->match_id] = $m; # Copy match object.
        }
        $flist[$t->name]['tour_obj'] = $t; # Copy tour object.
    }

    // Sort fixture list data structure.
    foreach ($flist as $tour => $rounds) {
        ksort($flist[$tour]); # Sort rounds.
        foreach ($rounds as $round => $matches) {
            if (is_object($flist[$tour][$round]))
                continue;
            else
                ksort($flist[$tour][$round]); # Sort matches in round by match_id.
        }
    }

    // Print fixture list.
    foreach ($flist as $tour => $rounds) {

        // Skip tournaments which have no rounds/matches
        if ($flist[$tour]['tour_obj']->empty)
            continue;

        $t = $flist[$tour]['tour_obj'];

        ?>
        <table class='fixtures' style='width:100%;'>
            <tr class='dark'>
                <td style='width:15%;'>
                    <a href='javascript:void(0);' onClick="obj=document.getElementById('trid_<?php echo $t->tour_id;?>'); if (obj.style.display != 'none'){obj.style.display='none'}else{obj.style.display='block'};"><b>[+/-]</b></a>
                    <a title='Standings' href='index.php?section=fixturelist&amp;tour_id=<?php echo $t->tour_id;?>'><b>[S]</b></a>
                    <a title='Description' href='index.php?section=fixturelist&amp;tour_id2=<?php echo $t->tour_id;?>'><b>[D]</b></a>
                    <?php
                    if ($t->type == TT_KNOCKOUT) {
                        echo "<a title='Tournament bracket' href='javascript:void(0)' onclick=\"window.open('handler.php?type=gdbracket&amp;tour_id=" . $t->tour_id . "', 'handler_tour', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=800,height=400'); return false;\"><b>[B]</b></a>\n";
                    }
                    ?>
                </td>
                <td style='width:85%;'>
                    &nbsp;&nbsp;&nbsp;<?php echo "<b>$tour</b>".(($t->is_finished) ? '&nbsp;&nbsp;<i>- '.$lng->getTrn('secs/fixtures/fin').'</i>' : '');?>
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
            elseif ($round == RT_PLAYIN)        $round = $lng->getTrn('secs/fixtures/mtypes/playin');
            elseif ($round == RT_FIRST && $t->type == TT_KNOCKOUT) $round = $lng->getTrn('secs/fixtures/mtypes/firstrnd');
            else                                $round = $lng->getTrn('secs/fixtures/mtypes/rnd').": $round";
                
            ?>
            <tr><td colspan='7' class="seperator"></td></tr>
            <tr>
                <td width="180"></td>
                <td class="light" width="250"><?php echo $round; ?></td>
                <td class="white" width="25"></td>
                <td class="white" width="50"></td>
                <td class="white" width="25"></td>
                <td class="white" width="250"></td>
                <td width="180"></td>
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
                        echo (($coach->isInMatch($match->match_id) || $coach->admin) ? $lng->getTrn('secs/fixtures/edit') : $lng->getTrn('secs/fixtures/view')) . "</a>&nbsp;&nbsp;\n";
                        if ($coach->admin) {
                            if ($t->type != TT_KNOCKOUT) {
                                echo "<a onclick=\"if(!confirm('".$lng->getTrn('secs/fixtures/mdel')."')){return false;}\" href='?section=fixturelist&amp;mdel=$match->match_id' style='color:".(($match->is_played) ? 'Red' : 'Blue').";'>".$lng->getTrn('secs/fixtures/del')."</a>&nbsp;&nbsp;\n";
                            }
                            echo "<a href='?section=fixturelist&amp;tlock=$match->match_id'>" . ($match->locked ? $lng->getTrn('secs/fixtures/unlock') : $lng->getTrn('secs/fixtures/lock')) . "</a>&nbsp;&nbsp;\n";
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
        if (count($flist) > 1 && !($KEEP_TOUR_OPEN == $t->tour_id)) {
            ?>
            <script language="JavaScript" type="text/javascript">
                document.getElementById('trid_'+<?php echo $t->tour_id;?>).style.display = 'none';
            </script>
            <?php
        }
    }
}

/*************************
 *
 *  STANDINGS
 *
 *************************/

function sec_standings() {

    global $lng;
    $PMS = (isset($_GET['pms']) && $_GET['pms']); // Per match stats?
    
    title($lng->getTrn('global/secLinks/standings'));

    echo $lng->getTrn('global/sortTbl/simul')."<br><br>\n";
    echo '<a href="index.php?section=standings&amp;pms='.(($PMS) ? 0 : 1).'"><b>['.$lng->getTrn('global/misc/'.(($PMS) ? 'oas' : 'pms'))."]</b></a><br><br>\n";

    $ELORanks = ELO::getRanks(false);
    $teams = Team::getTeams();
    foreach ($teams as $t) {
        $t->setExtraStats();
        $t->setStreaks(false);
        $t->elo = $ELORanks[$t->team_id] + $t->elo_0;
        
        if ($PMS) {
            foreach (array('score_team', 'score_opponent', 'tcas', 'td', 'cp', 'intcpt', 'cas', 'bh', 'si', 'ki') as $f) {
                $t->$f /= ($t->played == 0) ? 1 : $t->played;
            }
        }
    }
    
    $fields = array(
        'name'              => array('desc' => 'Team', 'href' => array('link' => 'index.php?section=coachcorner', 'field' => 'team_id', 'value' => 'team_id')), 
        'won'               => array('desc' => 'W'), 
        'lost'              => array('desc' => 'L'), 
        'draw'              => array('desc' => 'D'), 
        'played'            => array('desc' => 'GP'), 
        'win_percentage'    => array('desc' => 'WIN%'), 
        'row_won'           => array('desc' => 'SW'), 
        'row_lost'          => array('desc' => 'SL'), 
        'row_draw'          => array('desc' => 'SD'), 
        'elo'               => array('desc' => 'ELO'), 
        'score_team'        => array('desc' => 'GF'),
        'score_opponent'    => array('desc' => 'GA'),
        'won_tours'         => array('desc' => 'WT'), 
        'tcas'              => array('desc' => 'tcas'),         
        'td'                => array('desc' => 'Td'), 
        'cp'                => array('desc' => 'Cp'), 
        'intcpt'            => array('desc' => 'Int'), 
        'cas'               => array('desc' => 'Cas'), 
        'bh'                => array('desc' => 'BH'), 
        'si'                => array('desc' => 'Si'), 
        'ki'                => array('desc' => 'Ki'), 
    );
    
    sort_table(
        $lng->getTrn('secs/standings/tblTitle'), 
        'index.php?section=standings'.(($PMS)? '&amp;pms=1' : ''), 
        $teams, 
        $fields, 
        sort_rule('team'), 
        (isset($_GET['sort'])) ? array((($_GET['dir'] == 'a') ? '+' : '-') . $_GET['sort']) : array()
    );
    
    $fields = array(
        'name'         => array('desc' => 'Team', 'href' => array('link' => 'index.php?section=coachcorner', 'field' => 'team_id', 'value' => 'team_id')), 
        'race'         => array('desc' => 'Race', 'href' => array('link' => 'index.php?section=races', 'field' => 'race', 'value' => 'race')), 
        'coach_name'   => array('desc' => 'Coach', 'href' => array('link' => 'index.php?section=coaches', 'field' => 'coach_id', 'value' => 'owned_by_coach_id')), 
        'fan_factor'   => array('desc' => 'FF'), 
        'rerolls'      => array('desc' => 'RR'), 
        'ass_coaches'  => array('desc' => 'Ass. coaches'), 
        'cheerleaders' => array('desc' => 'Cheerleaders'), 
        'treasury'     => array('desc' => 'Treasury', 'kilo' => true, 'suffix' => 'k'), 
        'value'        => array('desc' => 'TV', 'kilo' => true, 'suffix' => 'k'), 
    );
    
    sort_table(
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
    disp_teams();
}

/*************************
 *
 *  PLAYERS
 *
 *************************/

function sec_players() {

    global $settings, $lng;
    
    title($lng->getTrn('global/secLinks/players'));

    // Generate tournament selection form
    if (isset($_POST['tour_id'])) {
        $_SESSION['trid_players'] = $_POST['tour_id'];
    }
    $tour_id = isset($_SESSION['trid_players']) ? $_SESSION['trid_players'] : false;
    $TOUR = (bool) $tour_id;
    
    ?>
    
    <form method="POST">
    <select name="tour_id" onChange='this.form.submit();'>
    <optgroup label="<?php echo $lng->getTrn('global/misc/all');?>">
        <option value="0">All tournaments</option>
    </optgroup>
    <optgroup label="<?php echo $lng->getTrn('global/misc/specific');?>">
        <?php
        foreach (Tour::getTours() as $t) {
            if (!$t->empty) # Only if tournament contains matches.
                echo "<option value='$t->tour_id' " . ($tour_id && $tour_id == $t->tour_id ? 'SELECTED' : '') . " >$t->name</option>";
        }
        ?>
    </optgroup>
    </select>
    </form>
    <br>
    <?php
    
    $PMS = (isset($_GET['pms']) && $_GET['pms']); // Per match stats?
    echo '<a href="index.php?section=players&amp;pms='.(($PMS) ? 0 : 1).'"><b>['.$lng->getTrn('global/misc/'.(($PMS) ? 'oas' : 'pms'))."]</b></a><br><br>\n";
    
    // Print table.
    $players = Player::getPlayers();
    foreach ($players as $p) {
        $p->setExtraStats($tour_id);
        $p->setStreaks($tour_id);
        if     ($p->is_sold) $p->HTMLbcolor = COLOR_HTML_SOLD;
        elseif ($p->is_dead) $p->HTMLbcolor = COLOR_HTML_DEAD; 
        if ($tour_id) {
            $p->setStats($tour_id);
            $p->value = '-';
        }
        
        if ($PMS) {
            foreach (array('td', 'cp', 'intcpt', 'cas', 'bh', 'si', 'ki', 'mvp') as $f) {
                $p->$f /= ($p->played == 0) ? 1 : $p->played;
            }
        }
    }
    
    $fields = array(
        'name'              => array('desc' => 'Player', 'href' => array('link' => 'index.php?section=coachcorner', 'field' => 'player_id', 'value' => 'player_id')), 
        'team_name'         => array('desc' => 'Team', 'href' => array('link' => 'index.php?section=coachcorner', 'field' => 'team_id', 'value' => 'owned_by_team_id')), 
        'won'               => array('desc' => 'W'), 
        'lost'              => array('desc' => 'L'), 
        'draw'              => array('desc' => 'D'), 
        'played'            => array('desc' => 'GP'), 
        'win_percentage'    => array('desc' => 'WIN%'), 
        'row_won'           => array('desc' => 'SW'), 
        'row_lost'          => array('desc' => 'SL'), 
        'row_draw'          => array('desc' => 'SD'), 
        'td'                => array('desc' => 'Td'), 
        'cp'                => array('desc' => 'Cp'), 
        'intcpt'            => array('desc' => 'Int'), 
        'cas'               => array('desc' => 'Cas'), 
        'bh'                => array('desc' => 'BH'), 
        'si'                => array('desc' => 'Si'), 
        'ki'                => array('desc' => 'Ki'), 
        'mvp'               => array('desc' => 'MVP'), 
        'spp'               => array('desc' => 'SPP'),
        'value'             => array('desc' => 'Value', 'nosort' => $TOUR, 'kilo' => !$TOUR, 'suffix' => (!$TOUR) ? 'k' : ''), 
    );
    
    sort_table(
        $lng->getTrn('secs/players/tblTitle'), 
        'index.php?section=players'.(($PMS)? '&amp;pms=1' : ''), 
        $players, 
        $fields, 
        sort_rule('player_overall'), 
        (isset($_GET['sort'])) ? array((($_GET['dir'] == 'a') ? '+' : '-') . $_GET['sort']) : array(),
        array('limit' => $settings['entries_players'], 'color' => true)
    );

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

    global $lng;

    /* 
        Specific coach page requested? 
    */
    if (isset($_GET['coach_id']) && !preg_match("/[^0-9]/", $_GET['coach_id']) &&
        is_object($c = new Coach($_GET['coach_id'])) && $c->setExtraStats()) {
        
        title("Coach $c->name");
        echo "<center><a href='index.php?section=coaches'>[".$lng->getTrn('global/misc/back')."]</a></center><br>\n";
        
        ?>
        <table class='picAndText'>
            <tr>
                <td class='light'><b><?php echo $lng->getTrn('secs/coaches/pic');?> <?php echo $c->name; ?></b></td>
                <td class='light'><b><?php echo $lng->getTrn('secs/coaches/about');?></b></td>
            </tr>
            <tr>
                <td>
                    <?php
                    pic_box($c->getPic(), false);
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
            </tr>
        </table>
        <?php

        /* Coach's teams */
        foreach ($c->teams as $t) {
            $t->setExtraStats();
            $t->setStreaks(false);
        }
        
        $fields = array(
            'name'              => array('desc' => 'Team', 'href' => array('link' => 'index.php?section=coachcorner', 'field' => 'team_id', 'value' => 'team_id')),
            'race'              => array('desc' => 'Race', 'href' => array('link' => 'index.php?section=races', 'field' => 'race', 'value' => 'race')), 
            'won'               => array('desc' => 'W'), 
            'lost'              => array('desc' => 'L'), 
            'draw'              => array('desc' => 'D'), 
            'played'            => array('desc' => 'GP'), 
            'win_percentage'    => array('desc' => 'WIN%'), 
            'row_won'           => array('desc' => 'SW'), 
            'row_lost'          => array('desc' => 'SL'), 
            'row_draw'          => array('desc' => 'SD'), 
            'score_team'        => array('desc' => 'GF'),
            'score_opponent'    => array('desc' => 'GA'),
            'won_tours'         => array('desc' => 'WT'), 
            'td'                => array('desc' => 'Td'), 
            'cp'                => array('desc' => 'Cp'), 
            'intcpt'            => array('desc' => 'Int'), 
            'cas'               => array('desc' => 'Cas'), 
            'bh'                => array('desc' => 'BH'), 
            'si'                => array('desc' => 'Si'), 
            'ki'                => array('desc' => 'Ki'), 
            'value'             => array('desc' => 'TV', 'kilo' => true, 'suffix' => 'k'), 
        );
        
        sort_table(
            $lng->getTrn('secs/coaches/teams'), 
            "index.php?section=coaches&amp;coach_id=$c->coach_id", 
            $c->teams, 
            $fields, 
            sort_rule('team'), 
            (isset($_GET['sort'])) ? array((($_GET['dir'] == 'a') ? '+' : '-') . $_GET['sort']) : array()
        );
        
        /* Played games */
        
        $matches = Stats::getPlayedMatches(STATS_COACH, $c->coach_id, MAX_RECENT_GAMES, true);
        foreach ($matches as $m) {
            $me = (get_alt_col('teams', 'team_id', $m->team1_id, 'owned_by_coach_id') == $c->coach_id) ? 1 : 2;
            $op = ($me == 1) ? 2 : 1;
            $m->myteam = $m->{"team${me}_name"};
            $m->opponent = $m->{"team${op}_name"};
            $m->score = $m->team1_score. ' - ' . $m->team2_score;
            $m->result = (($m->is_draw) ? 'D' : (($m->winner == $m->{"team${me}_id"}) ? 'W' : 'L'));
            $m->match = '[view]';
            $m->tour = get_alt_col('tours', 'tour_id', $m->f_tour_id, 'name');
        }
        
        $fields = array(
            'date_played' => array('desc' => 'Date'),
            'tour'     => array('desc' => 'Tournament'),
            'myteam'   => array('desc' => "$c->name's team"), 
            'opponent' => array('desc' => 'Opponent'), 
            'gate'     => array('desc' => 'Gate', 'kilo' => true, 'suffix' => 'k', 'href' => false), 
            'score'    => array('desc' => 'Score', 'nosort' => true), 
            'result'   => array('desc' => 'Result', 'nosort' => true), 
            'match'    => array('desc' => 'Match', 'href' => array('link' => 'index.php?section=fixturelist', 'field' => 'match_id', 'value' => 'match_id'), 'nosort' => true), 
        );
        
        sort_table(
            '<a name="played">'.$lng->getTrn('secs/coaches/played').'</a>', 
            "index.php?section=coaches&amp;coach_id=$c->coach_id", 
            $matches, 
            $fields, 
            sort_rule('match'), 
            (isset($_GET['sort2'])) ? array((($_GET['dir2'] == 'a') ? '+' : '-') . $_GET['sort2']) : array(),
            array('GETsuffix' => '2', 'anchor' => 'played')
        );
        
        return;
    }
    
    /*
        Show coaches table.
    */

    // Generate tournament selection form
    if (isset($_POST['tour_id'])) {
        $_SESSION['trid_coaches'] = $_POST['tour_id'];
    }
    $tour_id = isset($_SESSION['trid_coaches']) ? $_SESSION['trid_coaches'] : false;
    $TOUR = (bool) $tour_id;

    title($lng->getTrn('global/secLinks/coaches'));
    ?>
    
    <form method="POST">
    <select name="tour_id" onChange='this.form.submit();'>
    <optgroup label="<?php echo $lng->getTrn('global/misc/all');?>">
        <option value="0">All tournaments</option>
    </optgroup>
    <optgroup label="<?php echo $lng->getTrn('global/misc/specific');?>">
        <?php
        foreach (Tour::getTours() as $t) {
            if (!$t->empty) # Only if tournament contains matches.
                echo "<option value='$t->tour_id' " . ($tour_id && $tour_id == $t->tour_id ? 'SELECTED' : '') . " >$t->name</option>";
        }
        ?>
    </optgroup>
    </select>
    </form>
    <br>
    <?php

    $PMS = (isset($_GET['pms']) && $_GET['pms']); // Per match stats?
    echo '<a href="index.php?section=coaches&amp;pms='.(($PMS) ? 0 : 1).'"><b>['.$lng->getTrn('global/misc/'.(($PMS) ? 'oas' : 'pms'))."]</b></a><br><br>\n";

    $coaches = Coach::getCoaches();
    foreach ($coaches as $c) {
        $c->setStats($tour_id);
        $c->setExtraStats();
        $c->setStreaks($tour_id);
        
        if ($PMS) {
            foreach (array('score_team', 'score_opponent', 'td', 'cp', 'intcpt', 'cas', 'bh', 'si', 'ki') as $f) {
                $c->$f /= ($c->played == 0) ? 1 : $c->played;
            }
        }
    }

    $lnk = 'index.php?section=coaches';
    $fields = array(
        'name'              => array('desc' => 'Coach', 'href' => array('link' => $lnk, 'field' => 'coach_id', 'value' => 'coach_id')),
        'teams_cnt'         => array('desc' => 'Teams'), 
        'won'               => array('desc' => 'W'), 
        'lost'              => array('desc' => 'L'), 
        'draw'              => array('desc' => 'D'), 
        'played'            => array('desc' => 'GP'), 
        'win_percentage'    => array('desc' => 'WIN%'), 
        'row_won'           => array('desc' => 'SW'), 
        'row_lost'          => array('desc' => 'SL'), 
        'row_draw'          => array('desc' => 'SD'), 
        'score_team'        => array('desc' => 'GF'),
        'score_opponent'    => array('desc' => 'GA'),
        'won_tours'         => array('desc' => 'WT'), 
        'td'                => array('desc' => 'Td'), 
        'cp'                => array('desc' => 'Cp'), 
        'intcpt'            => array('desc' => 'Int'), 
        'cas'               => array('desc' => 'Cas'), 
        'bh'                => array('desc' => 'BH'), 
        'si'                => array('desc' => 'Si'), 
        'ki'                => array('desc' => 'Ki'), 
        'avg_team_value'    => array('desc' => 'TV*', 'kilo' => true, 'suffix' => 'k'), 
    );

    if ($tour_id) {
        unset($fields['won_tours']);
        unset($fields['avg_team_value']);
    }
    
    sort_table(
        $lng->getTrn('secs/coaches/tblTitle'), 
        $lnk.(($PMS)? '&amp;pms=1' : ''), 
        $coaches, 
        $fields, 
        sort_rule('coach'), 
        (isset($_GET['sort'])) ? array((($_GET['dir'] == 'a') ? '+' : '-') . $_GET['sort']) : array()
    );
}

/*************************
 *
 *  RACES
 *
 *************************/

function sec_races() {

    global $DEA, $lng;

    /* 
        This function can do two things:
            Either, it can show race statistics
            Or, it can show team data from LRB5.
    */

    // A specific race view was requested:
    if (isset($_GET['race']) && array_key_exists($_GET['race'], $DEA)) {
    
        title($r = $_GET['race']);
        
        ?>
        <center>
        <img src="<?php echo $DEA[$r]['other']['icon'];?>" alt="Race icon">
        </center>
        <ul>
            <li>Re-roll cost: <?php echo $DEA[$r]['other']['RerollCost']/1000;?>k</li>
        </ul>
        <br>

        <?php
        /* Players from chosen race. */
        
        $players = array();
        foreach ($DEA[$r]['players'] as $p => $d) {
            array_push($players, (object) array_merge(array('position' => $p), $d));
        }
        foreach ($players as $p) {
            $p->skills = implode(', ', $p->{'Def skills'});
            foreach (array('N', 'D') as $s) {
                array_walk($p->{"$s skills"}, create_function('&$val', '$val = substr($val,0,1);'));
                $p->$s = implode('', $p->{"$s skills"});
            }
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
        sort_table(
            "$r ".$lng->getTrn('secs/races/players'), 
            "index.php?section=races&amp;race=$r",
            $players, 
            $fields, 
            sort_rule('race_page'), 
            (isset($_GET['sortpl'])) ? array((($_GET['dirpl'] == 'a') ? '+' : '-') . $_GET['sortpl']) : array(),
            array('GETsuffix' => 'pl')
        );
        
        /* Teams of the chosen race. */
        $teams = Team::getTeams($r);
        foreach ($teams as $t) {
            $t->setExtraStats();
            $t->setStreaks(false);
        }
        
        $fields = array(
            'name'              => array('desc' => 'Team', 'href' => array('link' => 'index.php?section=coachcorner', 'field' => 'team_id', 'value' => 'team_id')), 
            'coach_name'        => array('desc' => 'Coach', 'href' => array('link' => 'index.php?section=coaches', 'field' => 'coach_id', 'value' => 'owned_by_coach_id')), 
            'won'               => array('desc' => 'W'), 
            'lost'              => array('desc' => 'L'), 
            'draw'              => array('desc' => 'D'), 
            'played'            => array('desc' => 'GP'), 
            'win_percentage'    => array('desc' => 'WIN%'), 
            'row_won'           => array('desc' => 'SW'), 
            'row_lost'          => array('desc' => 'SL'), 
            'row_draw'          => array('desc' => 'SD'), 
            'score_team'        => array('desc' => 'GF'),
            'score_opponent'    => array('desc' => 'GA'),
            'won_tours'         => array('desc' => 'WT'), 
            'td'                => array('desc' => 'Td'), 
            'cp'                => array('desc' => 'Cp'), 
            'intcpt'            => array('desc' => 'Int'), 
            'cas'               => array('desc' => 'Cas'), 
            'bh'                => array('desc' => 'BH'), 
            'si'                => array('desc' => 'Si'), 
            'ki'                => array('desc' => 'Ki'), 
            'value'             => array('desc' => 'TV', 'kilo' => true, 'suffix' => 'k'), 
        );
        
        sort_table(
            "<a name='teams'>$r ".$lng->getTrn('secs/races/teams')."</a>", 
            "index.php?section=races&amp;race=$r",
            $teams, 
            $fields, 
            sort_rule('team'), 
            (isset($_GET['sortt'])) ? array((($_GET['dirt'] == 'a') ? '+' : '-') . $_GET['sortt']) : array(),
            array('GETsuffix' => 't', 'anchor' => 'teams')
        );
        
        // Return, since we don't wan't continue and print race stats too.
        return; 
    }

    // No specific race view was requested:
    title($lng->getTrn('global/secLinks/races'));    

    $races = array();
    foreach ($DEA as $r => $desc) {
        array_push($races, (object) Team::getRaceStats($r));
    }
    
    $fields = array(
        'race'              => array('desc' => 'Race', 'href' => array('link' => 'index.php?section=races', 'field' => 'race', 'value' => 'race')), 
        'teams'             => array('desc' => 'Teams'), 
        'won'               => array('desc' => 'W'), 
        'lost'              => array('desc' => 'L'), 
        'draw'              => array('desc' => 'D'), 
        'played'            => array('desc' => 'GP'), 
        'win_percentage'    => array('desc' => 'WIN%'), 
        'won_tours'         => array('desc' => 'WT'), 
        'td'                => array('desc' => 'Td*'), 
        'cp'                => array('desc' => 'Cp*'), 
        'intcpt'            => array('desc' => 'Int*'), 
        'cas'               => array('desc' => 'Cas*'), 
        'bh'                => array('desc' => 'BH*'), 
        'si'                => array('desc' => 'Si*'), 
        'ki'                => array('desc' => 'Ki*'), 
        'value'             => array('desc' => 'TV*', 'kilo' => true, 'suffix' => 'k'), 
    );
    
    sort_table(
        $lng->getTrn('secs/races/tblTitle'), 
        'index.php?section=races', 
        $races, 
        $fields, 
        sort_rule('race'), 
        (isset($_GET['sort'])) ? array((($_GET['dir'] == 'a') ? '+' : '-') . $_GET['sort']) : array(),
        array('dashed' => array('condField' => 'teams', 'fieldVal' => 0, 'noDashFields' => array('race')))
    );
}

/*************************
 *
 *  STAR PLAYERS
 *
 *************************/

function sec_stars() {

    global $stars, $lng;

    if (isset($_GET['sid'])) {
        $mdat = array();
        $s = new Star($_GET['sid']);
        title($lng->getTrn('secs/stars/hh')." $s->name");
        echo '<center><a href="index.php?section=stars">['.$lng->getTrn('global/misc/back').']</a></center><br><br>';
        foreach ($s->getHireHistory(false, false, false) as $m) {
            $o = (object) array();
            foreach (array('match_id', 'date_played', 'hiredByName', 'hiredAgainstName') as $k) {
                $o->$k = $m->$k;
            }
            $s->setStats(false, $m->match_id, false);
            foreach (array('td', 'cp', 'intcpt', 'cas', 'bh', 'si', 'ki', 'mvp', 'spp') as $k) {
                $o->$k = $s->$k;
            }
            $o->match = '[view]';
            $o->tour = get_alt_col('tours', 'tour_id', $m->f_tour_id, 'name');
            array_push($mdat, $o);
        }
        $fields = array(
            'date_played'       => array('desc' => 'Hire date'), 
            'tour'              => array('desc' => 'Tournament'),
            'hiredByName'       => array('desc' => 'Hired by'), 
            'hiredAgainstName'  => array('desc' => 'Opponent team'), 
            'match'             => array('desc' => 'Match', 'href' => array('link' => 'index.php?section=fixturelist', 'field' => 'match_id', 'value' => 'match_id'), 'nosort' => true), 
            'cp'     => array('desc' => 'Cp'), 
            'td'     => array('desc' => 'Td'), 
            'intcpt' => array('desc' => 'Int'), 
            'cas'    => array('desc' => 'Cas'), 
            'bh'     => array('desc' => 'BH'), 
            'si'     => array('desc' => 'Si'), 
            'ki'     => array('desc' => 'Ki'), 
            'mvp'    => array('desc' => 'MVP'), 
            'spp'    => array('desc' => 'SPP'),
        );
        sort_table(
            $lng->getTrn('secs/stars/hh')." $s->name", 
            'index.php?section=stars&amp;sid='.$s->star_id, 
            $mdat, 
            $fields, 
            sort_rule('star_HH'), 
            (isset($_GET['sort'])) ? array((($_GET['dir'] == 'a') ? '+' : '-') . $_GET['sort']) : array(),
            array('doNr' => false,)
        );
        return;
    }

    // Generate tournament selection form
    if (isset($_POST['tour_id'])) {
        $_SESSION['trid_stars'] = $_POST['tour_id'];
    }
    $tour_id = isset($_SESSION['trid_stars']) ? $_SESSION['trid_stars'] : false;
    $TOUR = (bool) $tour_id;

    title($lng->getTrn('global/secLinks/stars'));
    
    echo $lng->getTrn('global/sortTbl/simul')."<br>\n";
    echo $lng->getTrn('global/sortTbl/spp')."<br><br>\n";
    ?>
    
    <form method="POST">
    <select name="tour_id" onChange='this.form.submit();'>
    <optgroup label="<?php echo $lng->getTrn('global/misc/all');?>">
        <option value="0">All tournaments</option>
    </optgroup>
    <optgroup label="<?php echo $lng->getTrn('global/misc/specific');?>">
        <?php
        foreach (Tour::getTours() as $t) {
            if (!$t->empty) # Only if tournament contains matches.
                echo "<option value='$t->tour_id' " . ($tour_id && $tour_id == $t->tour_id ? 'SELECTED' : '') . " >$t->name</option>";
        }
        ?>
    </optgroup>
    </select>
    </form>
    <br>
    <?php
    
    $stars = Star::getStars(false, false, false);
    foreach ($stars as $s) {
        $s->setStats(false, false, $tour_id);
        $s->setMatchStats(false, false, $tour_id);
        $s->setStreaks($tour_id);
        $s->skills = '<small>'.implode(', ', $s->skills).'</small>';
        $s->teams = '<small>'.implode(', ', $s->teams).'</small>';
        $s->name = preg_replace('/\s/', '&nbsp;', $s->name);
    }
    
    $fields = array(
        'name'              => array('desc' => 'Star', 'href' => array('link' => 'index.php?section=stars', 'field' => 'sid', 'value' => 'star_id')), 
        'cost'              => array('desc' => 'Price', 'kilo' => true, 'suffix' => 'k'), 
        'ma'                => array('desc' => 'Ma'), 
        'st'                => array('desc' => 'St'), 
        'ag'                => array('desc' => 'Ag'), 
        'av'                => array('desc' => 'Av'), 
        'won'               => array('desc' => 'W'), 
        'lost'              => array('desc' => 'L'), 
        'draw'              => array('desc' => 'D'), 
        'played'            => array('desc' => 'GP'), 
        'win_percentage'    => array('desc' => 'WIN%'), 
        'row_won'           => array('desc' => 'SW'), 
        'row_lost'          => array('desc' => 'SL'), 
        'row_draw'          => array('desc' => 'SD'), 
        'cp'                => array('desc' => 'Cp'), 
        'td'                => array('desc' => 'Td'), 
        'intcpt'            => array('desc' => 'Int'), 
        'cas'               => array('desc' => 'Cas'), 
        'bh'                => array('desc' => 'BH'), 
        'si'                => array('desc' => 'Si'), 
        'ki'                => array('desc' => 'Ki'), 
        'mvp'               => array('desc' => 'MVP'), 
        'spp'               => array('desc' => 'SPP'),
    );
    
    sort_table(
        $lng->getTrn('secs/stars/tblTitle'), 
        'index.php?section=stars', 
        $stars, 
        $fields, 
        sort_rule('star'), 
        (isset($_GET['sort'])) ? array((($_GET['dir'] == 'a') ? '+' : '-') . $_GET['sort']) : array(),
        array()
    );
    
    $fields = array(
        'name'   => array('desc' => 'Star', 'href' => array('link' => 'index.php?section=stars', 'field' => 'sid', 'value' => 'star_id')), 
        'teams'  => array('desc' => 'Teams', 'nosort' => true),
        'skills' => array('desc' => 'Skills', 'nosort' => true), 
    );
    
    sort_table(
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

    global $lng;

    $c = isset($_SESSION['logged_in']) ? new Coach($_SESSION['coach_id']) : null;
    $ALLOW_EDIT = (is_object($c) && $c->admin) ? true : false;
    $subsecs = array(
        'hof'           => $lng->getTrn('secs/records/d_hof'), 
        'wanted'        => $lng->getTrn('secs/records/d_wanted'), 
        'memm'          => $lng->getTrn('secs/records/d_memma'),
        'prize'         => 'Prizes',
    );

    // This section's routines are placed in the records.php file.
    if (isset($_GET['subsec'])) {
        title($subsecs[$_GET['subsec']]);
        switch ($_GET['subsec'])
        {
            case 'hof':
                hof($ALLOW_EDIT);
                break;
                
            case 'wanted':
                wanted($ALLOW_EDIT);
                break;

            case 'memm':
                mem_matches();
                break;
                
            case 'prize':
                prizes($ALLOW_EDIT);
                break;
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

    /* 
        Before displaying coach corner we check if visitor wants a specific team's page or a player page.
    */
    
    if (isset($_GET['player_id']) && $_GET['player_id'] < 0) {
        status(false, 'Sorry. Player rosters do not exist for star players and mercenaries.');
        return;
    }

    // If player ID is set then show player page. This MUST be checked for before checking if a team's page is wanted, else access will be denied.
    if (isset($_GET['player_id']) && !preg_match("/[^0-9]/", $_GET['player_id'])) {
        player_roaster($_GET['player_id']);
        return;
    }

    // If team ID is set then show team page.
    if (isset($_GET['team_id']) && !preg_match("/[^0-9]/", $_GET['team_id']) && $_GET['team_id'] != 'new') {
        team_roaster($_GET['team_id']);
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
        title("Create new team");
        ?>
        <form method="POST">
        <table class="text">
            <tr>
            <td valign="top">
                <b>Team name:</b> <br>
                <input type="text" name="name" size="20" maxlength="50">
                <br><br>
                <b>Race:</b> <br>
                <select name="race">
                    <?php
                    foreach (get_races() as $race => $icon_file)
                        echo "<option value='$race'>$race</option>\n";
                    ?>
                </select>
                <br><br>
                <input type="submit" name="new_team" value="Create team">
            </td>
            </tr>
        </table>
        </form>
        <?php
    }
    
    /* Show coach corner main page. */
    
    else {
    
        // Was new password/email request made?
        if (isset($_POST['button'])) {
        
            if (get_magic_quotes_gpc()) {
                $_POST['new_passwd'] = isset($_POST['new_passwd']) ? stripslashes($_POST['new_passwd']) : '';
                $_POST['new_phone']  = isset($_POST['new_phone'])  ? stripslashes($_POST['new_phone']) : '';
                $_POST['new_email']  = isset($_POST['new_email'])  ? stripslashes($_POST['new_email']) : '';
                $_POST['new_name']   = isset($_POST['new_name'])   ? stripslashes($_POST['new_name']) : '';
            }
        
            switch ($_POST['button']) 
            {
                case 'Change password':     status(login($coach->name, $_POST['old_passwd'], false) && $coach->setPasswd($_POST['new_passwd'])); break;
                case 'Change phone number': status($coach->setPhone($_POST['new_phone'])); break;
                case 'Change email':        status($coach->setMail($_POST['new_email'])); break;
                case 'Change name':         status($coach->setName($_POST['new_name'])); break;
                case 'Change theme':        status($coach->setSetting('theme', (int) $_POST['new_theme'])); break;
            }
        }
        
        if (isset($_POST['type'])) {
            switch ($_POST['type'])
            {
                case 'pic':
                    status(!$coach->savePic('pic'));                    
                    break;
                    
                case 'coachtext':
                    if (get_magic_quotes_gpc()) {
                        $_POST['coachtext'] = stripslashes($_POST['coachtext']);
                    }
                    status($coach->saveText($_POST['coachtext']));
                    break;
            }
        }
        
        title("Coach corner");
        
        // Coach stats.
        ?>
        <table class="text">
            <tr>
                <td class="light">
                    <b>Coach stats</b>
                </td>
            </tr>
        </table>

        <table class="text" style="padding:10px; border-spacing:5px;">
            <tr>
                <td width="150px"></td>
                <td></td>
            </tr>
            <tr>
                <td>Games played:</td>
                <td><?php echo $coach->played; ?></td>
            </tr>
            <tr>
                <td>Win percentage:</td>
                <td><?php echo sprintf("%1.1f", $coach->played == 0 ? 0 : $coach->won/$coach->played * 100) . '%'; ?></td>
            </tr>
            <tr>
                <td>Tournaments won:</td>
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
        
        disp_teams($coach->coach_id);
        
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
                    Start a new team
                </td>
            </tr>
        </table>       

        <table class="text">
            <tr>
                <td class="light">
                    <b>Your account information</b>
                </td>
            </tr>
        </table>

        <form method="POST">
        <table class="text" style="border-spacing:5px; padding:20px;">
                <tr>
                    <td>Change password:</td>
                    <td>Old:<input type='password' name='old_passwd' size="20" maxlength="50"></td>
                    <td>New:<input type='password' name='new_passwd' size="20" maxlength="50"></td>
                    <td><input type="submit" name="button" value="Change password"></td>
                </tr>
                <tr>
                    <td>Change phone number:</td>
                    <td>Old:<input type='text' name='old_phone' readonly value="<?php echo $coach->phone; ?>" size="20" maxlength="129"></td>
                    <td>New:<input type='text' name='new_phone' size="20" maxlength="25"></td>
                    <td><input type="submit" name="button" value="Change phone number"></td>
                </tr>
                <tr>
                    <td>Change email:</td>
                    <td>Old:<input type='text' name='old_email' readonly value="<?php echo $coach->mail; ?>" size="20" maxlength="129"></td>
                    <td>New:<input type='text' name='new_email' size="20" maxlength="129"></td>
                    <td><input type="submit" name="button" value="Change email"></td>
                </tr>
                <tr>
                    <td>Change name:</td>
                    <td>Old:<input type='text' name='old_name' readonly value="<?php echo $coach->name; ?>" size="20" maxlength="50"></td>
                    <td>New:<input type='text' name='new_name' size="20" maxlength="50"></td>
                    <td><input type="submit" name="button" value="Change name"></td>
                </tr>
                <tr>
                    <td>Change OBBLM theme:</td>
                    <td>Current: <?php echo $coach->settings['theme'];?></td>
                    <td>
                        New:
                        <select name='new_theme'>
                            <?php
                            foreach (array(1 => 'Classic', 2 => 'Clean') as $theme => $desc) {
                                echo "<option value='$theme'>$theme: $desc</option>\n";
                            }
                            ?>
                        </select>
                    </td>
                    <td><input type="submit" name="button" value="Change theme"></td>
                </tr>
        </table>
        </form>
        
        <table class='picAndText'>
            <tr>
                <td class='light'><b>Photo of you</b></td>
                <td class='light'><b>About you</b></td>
            </tr>
            <tr>
                <td>
                    <?php
                    pic_box($coach->getPic(), true);
                    ?>
                </td>
                <td valign='top'>
                    <?php
                    $txt = $coach->getText(); 
                    if (empty($txt)) {
                        $txt = "Nothing has yet been written about you."; 
                    }
                    ?>
                    <form method='POST'>
                        <textarea name='coachtext' rows='15' cols='70'><?php echo $txt;?></textarea>
                        <br><br>
                        <input type="hidden" name="type" value="coachtext">
                        <input type="submit" name='Save' value='Save'>
                    </form>
                </td>
            </tr>
        </table>
        <?php
    }
    
}

?>
