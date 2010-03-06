<?php

/*
 *  Copyright (c) Niels Orsleff Justesen <njustesen@gmail.com> and Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2007-2010. All Rights Reserved.
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
    
    $_URL_forgotpass = "index.php?section=login&amp;forgotpass=1";
    if (isset($_GET['forgotpass'])) {
        if (!isset($_POST['_retry'])) {
            title($lng->getTrn('login/forgotpass'));
        }
        if (isset($_GET['cid']) && isset($_GET['activation_code'])) {
            $c = new Coach($_GET['cid']);
            status($new_passwd = $c->confirmActivation($_GET['activation_code']));
            echo "<br><br>";
            echo $lng->getTrn('login/temppasswd')." <b>$new_passwd</b><br>\n";
            echo '<a href="'.urlcompile(T_URL_PROFILE,T_OBJ_COACH,$_GET['cid'],false,false).'&amp;subsec=profile">'.$lng->getTrn('login/setnewpasswd').'.</a>';
        }
        else if (isset($_POST['coach_AC']) && isset($_POST['email'])) {
            $cid = get_alt_col('coaches', 'name', $_POST['coach_AC'], 'coach_id');
            $c = new Coach($cid);
            $correct_user = ($c->mail == $_POST['email']);
            status($correct_user, $correct_user ? '' : $lng->getTrn('login/mismatch'));
            if ($correct_user) {
                $c->requestPasswdReset();
                echo "<br><br>";
                echo $lng->getTrn('login/resetpasswdmail').'.';
            }
            else {
                // Return to same page.
                unset($_POST['coach']);
                unset($_POST['email']);
                $_POST['_retry'] = true;
                sec_login();
            }
        }
        else {
            ?>
            <br>
            <form method="POST" action="<?php echo $_URL_forgotpass;?>">
                <b><?php echo $lng->getTrn('login/loginname');?></b><br>
                <input type="text" name="coach_AC" size="20" maxlength="50">
                <br><br>
                <b>Email</b><br>
                <input type="text" name="email" size="20" maxlength="50">
                <br><br>
                <input type="submit" name="reqAC" value="<?php echo $lng->getTrn('common/submit');?>">
            </form>
            <?php 
        }       
    }
    else {
        title($lng->getTrn('menu/login'));
        ?>
        <div class='login'>
        <form method="POST" action="index.php">
            <b><?php echo $lng->getTrn('login/loginname');?></b>
            <input type="text" name="coach" size="20" maxlength="50">
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
        echo "<a href='$_URL_forgotpass'>".$lng->getTrn('login/forgotpass').'</a><br>';
        if (Module::isRegistered('Registration') && $settings['allow_registration']) {
            echo "<br><a href='handler.php?type=registration'>Register</a>";
        }    
    }
}

/*************************
 *
 *  MAIN
 *
 *************************/

function sec_main() {

    global $settings, $rules, $coach, $lng, $leagues;
    
    MTS('Main start');

    list($sel_lid, $HTML_LeagueSelector) = HTMLOUT::simpleLeagueSelector();
    $IS_GLOBAL_ADMIN = (is_object($coach) && $coach->ring == Coach::T_RING_GLOBAL_ADMIN);
    
    /*
     *  Was any main board actions made?
     */

    if (isset($_POST['type']) && is_object($coach) && $coach->isNodeCommish(T_NODE_LEAGUE, $sel_lid)) {
        if (get_magic_quotes_gpc()) {
            if (isset($_POST['title'])) $_POST['title'] = stripslashes($_POST['title']);
            if (isset($_POST['txt']))   $_POST['txt']   = stripslashes($_POST['txt']);
        }
        $msg = isset($_POST['msg_id']) ? new Message((int) $_POST['msg_id']) : null;
        switch ($_POST['type'])
        {
            case 'msgdel': status($msg->delete()); break;
            case 'msgnew':  
                status(Message::create(array(
                    'f_coach_id' => $coach->coach_id, 
                    'f_lid'      => ($IS_GLOBAL_ADMIN && isset($_POST['BC']) && $_POST['BC']) ? Message::T_BROADCAST : $sel_lid, 
                    'title'      => $_POST['title'], 
                    'msg'        => $_POST['txt'])
                )); break;
            case 'msgedit': status($msg->edit($_POST['title'], $_POST['txt'])); break;
        }
    }

    /*
     *  Now we are ready to generate the HTML code.
     */

    ?>
    <div class="main_head"><?php echo $settings['league_name']; ?></div>
    <div class='main_leftColumn'>
        <div class="main_leftColumn_head">
            <?php
            echo "<div class='main_leftColumn_welcome'>\n";
            echo $settings['welcome'];
            echo "</div>\n";
            echo "<div class='main_leftColumn_left'>\n";
            echo $HTML_LeagueSelector;
            echo "</div>\n";
            echo "<div class='main_leftColumn_right'>\n";
            if (is_object($coach) && $coach->isNodeCommish(T_NODE_LEAGUE, $sel_lid)) {
                echo "<a href='javascript:void(0);' onClick=\"slideToggle('msgnew');\">".$lng->getTrn('main/newmsg')."</a>&nbsp;\n";
            }
            if (Module::isRegistered('RSSfeed')) {echo "<a href='handler.php?type=rss'>RSS</a>\n";}
            echo "</div>\n";
            ?>
            <div style="display:none; clear:both;" id="msgnew">
                <br><br>
                <form method="POST">
                    <textarea name="title" rows="1" cols="50"><?php echo $lng->getTrn('common/notitle');?></textarea><br><br>
                    <textarea name="txt" rows="15" cols="50"><?php echo $lng->getTrn('common/nobody');?></textarea><br><br>
                    <?php 
                    if ($IS_GLOBAL_ADMIN) {
                        echo $lng->getTrn('main/broadcast');
                        ?><input type="checkbox" name="BC"><br><br><?php
                    }
                    ?>
                    <input type="hidden" name="type" value="msgnew">
                    <input type="submit" value="<?php echo $lng->getTrn('common/submit');?>">
                </form>
            </div>
        </div>

        <?php
        /*
            Generate main board.

            Left column is the message board, consisting of both commissioner messages and game summaries/results.
            To generate this table we create a general array holding the content of both.
        */
        $j = 1;
        foreach (TextSubSys::getMainBoardMessages($settings['fp_messageboard']['length'], $sel_lid, $settings['fp_messageboard']['show_team_news'], $settings['fp_messageboard']['show_match_summaries']) as $e) {
            echo "<div class='boxWide'>\n";
                echo "<h3 class='boxTitle$e->cssidx'>$e->title</h3>\n";
                echo "<div class='boxBody'>\n";
                    $fmtMsg = fmtprint($e->message); # Basic supported syntax: linebreaks.
                    echo mb_substr($fmtMsg, 0, 300)."<span id='e$j' style='display:none;'>".mb_substr($fmtMsg, 300)."</span><span id='moreLink$j' ".((strlen($fmtMsg) > 300) ? '' : 'style="display:none"')."> ...&nbsp;<a href='javascript:void(0)' onclick=\"fadeOut('moreLink$j');fadeIn('e$j');\">[".$lng->getTrn('main/more')."]</a></span>\n";
                    echo "<br><hr>\n";
                    echo "<table class='boxTable'><tr>\n";
                        switch ($e->type) 
                        {
                            case T_TEXT_MATCH_SUMMARY:
                                echo "<td align='left' width='100%'>".$lng->getTrn('main/posted')." ".textdate($e->date)." " . (isset($e->date_mod) ? "(".$lng->getTrn('main/lastedit')." ".textdate($e->date_mod).") " : '') .$lng->getTrn('main/by')." $e->author</td>\n";
                                echo "<td align='right'><a href='index.php?section=matches&amp;type=report&amp;mid=$e->match_id'>".$lng->getTrn('common/view')."</a></td>\n";
                                break;
                            case  T_TEXT_MSG:
                                echo "<td align='left' width='100%'>".$lng->getTrn('main/posted')." ".textdate($e->date)." ".$lng->getTrn('main/by')." $e->author</td>\n";
                                if (is_object($coach) && ($IS_GLOBAL_ADMIN || $coach->coach_id == $e->author_id)) { // Only admins may delete messages, or if it's a commissioner's own message.
                                    echo "<td align='right'><a href='javascript:void(0);' onClick=\"slideToggle('msgedit$e->msg_id');\">".$lng->getTrn('common/edit')."</a></td>\n";
                                    echo "<td align='right'>";
                                    echo inlineform(array('type' => 'msgdel', 'msg_id' => $e->msg_id), "msgdel$e->msg_id", $lng->getTrn('common/delete'));
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
                    if ($e->type == T_TEXT_MSG) {
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
    <?php
    MTS('Board messages generated');
    
    /*
        The right hand side column optionally (depending on settings) contains standings, latest game results, touchdown and casualties stats.
        We will now generate the stats, so that they are ready to be printed in correct order.
    */
    
    echo "<div class='main_rightColumn'>\n";
    $boxes_ordered = array();
    $boxes_unordered = array_merge($settings['fp_standings'], $settings['fp_leaders'], $settings['fp_latestgames']);
    foreach (range(1, count($boxes_unordered)) as $i) {
        foreach ($boxes_unordered as $box) {
            if ($box['box_ID'] != $i) {
                continue;
            }
            # These fields distinguishes the box types.
            else if (isset($box['fields'])) {$box['dispType'] = 'standings';}
            else if (isset($box['field']))  {$box['dispType'] = 'leaders';}
            else if (isset($box['type']))   {$box['dispType'] = 'latestgames';}
            $boxes_ordered[] = $box;
        }
    }

    // Used in the below standings dispType boxes.
    global $core_tables, $ES_fields;
    $_MV_COLS = array_merge(array_keys($core_tables['mv_teams']), array_keys($ES_fields));
    
    // Let's print those boxes!
    foreach ($boxes_ordered as $box) {
    
    switch ($box['dispType']) {
        
        case 'standings':
            if (!get_alt_col('tours', 'tour_id', $box['id'], 'tour_id')) {
                break;
            }
                
            $tour = new Tour($box['id']);
            $teams = Stats::getRaw(T_OBJ_TEAM, array(T_NODE_TOURNAMENT => $tour->tour_id), $box['length'], $tour->getRSSortRule(), false);
            ?>
            <div class='boxWide'>
                <h3 class='boxTitle<?php echo T_HTMLBOX_STATS;?>'><?php echo $box['title'];?></h3>
                <div class='boxBody'>
                    <table class="boxTable">
                        <?php
                        echo "<tr>\n";
                        foreach ($box['fields'] as $title => $f) {
                            echo "<td><i>$title</i></td>\n";
                        }
                        echo "</tr>\n";
                        foreach ($teams as $t) {
                            echo "<tr>\n";
                            foreach ($box['fields'] as $title => $f) {
                                if (in_array($f, $_MV_COLS)) {
                                    $f = 'mv_'.$f;
                                }
                                echo "<td>";
                                if ($settings['fp_links'] && $f == 'name')
                                    echo "<a href='".urlcompile(T_URL_PROFILE,T_OBJ_TEAM,$t['team_id'],false,false)."'>$t[name]</a>";
                                elseif (is_numeric($t[$f]) && !ctype_digit($t[$f]))
                                    echo sprintf('%1.2f', $t[$f]);
                                else
                                    echo $t[$f];
                                echo "</td>\n";
                            }
                            echo "</tr>\n";
                        }
                        ?>
                    </table>
                </div>
            </div>
            <?php
            MTS('Standings table generated');
            break;
            
        case 'latestgames':
    
            if ($box['length'] <= 0) {
                break;
            }
            ?>
            <div class="boxWide">
                <h3 class='boxTitle<?php echo T_HTMLBOX_MATCH;?>'><?php echo $box['title'];?></h3>
                <div class='boxBody'>
                    <table class="boxTable">
                        <tr>
                            <td style="text-align: right;" width="50%"><i><?php echo $lng->getTrn('common/home');?></i></td><td> </td>
                            <td style="text-align: left;" width="50%"><i><?php echo $lng->getTrn('common/away');?></i></td><td> </td>
                        </tr>
                        <?php
                        switch ($box['type']) {
                            case 'league':     $_type = T_NODE_LEAGUE; break;
                            case 'division':   $_type = T_NODE_DIVISION; break;
                            case 'tournament': $_type = T_NODE_TOURNAMENT; break;
                            default: $_type = T_NODE_LEAGUE;
                        }
                        foreach (Match::getMatches($box['length'], $_type, $box['id'], false) as $m) {
                            $home   = ($m->stadium == $m->team1_id) ? 'team1' : 'team2';
                            $guest  = ($home == 'team1') ? 'team2' : 'team1';
                            echo "<tr valign='top'>\n";
                            echo "<td style='text-align: right;'>" . $m->{"{$home}_name"} . "</td>\n";
                            echo "<td><nobr>" . $m->{"${home}_score"} . "&mdash;" . $m->{"${guest}_score"} . "</nobr></td>\n";
                            echo "<td style='text-align: left;'>" . $m->{"${guest}_name"} . "</td>\n";
                            echo "<td><a href='index.php?section=matches&amp;type=report&amp;mid=$m->match_id'>Show</a></td>";
                            echo "</tr>";
                        }
                        ?>
                    </table>
                </div>
            </div>
            <?php
            MTS('Latest matche table generated');
            break;
    
        case 'leaders':
        
            $f = 'mv_'.$box['field'];
            $players = Stats::getRaw(T_OBJ_PLAYER, array(T_NODE_TOURNAMENT => $box['id']), $box['length'], array('-'.$f), false)
            ?>
            <div class="boxWide">
                <h3 class='boxTitle<?php echo T_HTMLBOX_STATS;?>'><?php echo $box['title'];?></h3>
                <div class='boxBody'>
                    <table class="boxTable">
                        <tr>
                            <td width="100%"><i><?php echo $lng->getTrn('common/name');?></i></td>
                            <td><i>#</i></td>
                            <td><i><?php echo $lng->getTrn('common/value');?></i></td>
                        </tr>
                        <?php
                        foreach ($players as $p) {
                            echo "<tr>\n";
                            echo "<td>".(($settings['fp_links']) ? "<a href='".urlcompile(T_URL_PROFILE,T_OBJ_PLAYER,$p['player_id'],false,false)."'>$p[name]</a>" : $p['name'])."</td>\n";
                            echo "<td>".$p[$f]."</td>\n";
                            echo "<td>".$p['value']/1000 ."k</td>\n";
                            echo "</tr>";
                        }
                        ?>
                    </table>
                </div>
            </div>
            <?php
            MTS('Leaders standings generated');
            break;
    }
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
    title($lng->getTrn('menu/teams'));
    Team_HTMLOUT::dispTeamList();
}

function sec_coachlist() {
    global $lng;
    title($lng->getTrn('menu/coaches'));
    Coach_HTMLOUT::dispTeamList();
}

function sec_matcheshandler() {
    switch ($_GET['type'])
    {
        # Save all these subroutines in class_match_htmlout.php
        case 'tours':       Match_HTMLOUT::tours(); break;
        case 'tourmatches': Match_HTMLOUT::tourMatches(); break;
        case 'report':      Match_HTMLOUT::report(); break;
        case 'recent':      Match_HTMLOUT::recentMatches(); break;
        case 'upcoming':    Match_HTMLOUT::upcomingMatches(); break;
    }    
}

function sec_objhandler() {
    $types = array(T_OBJ_PLAYER => 'Player', T_OBJ_TEAM => 'Team', T_OBJ_COACH => 'Coach', T_OBJ_STAR => 'Star', T_OBJ_RACE => 'Race');
    foreach ($types as $t => $classPrefix) {
        if ($_GET['obj'] == $t) {
            switch ($_GET['type'])
            {
                case T_URL_STANDINGS:
                    call_user_func(
                        array("${classPrefix}_HTMLOUT", 'standings'), 
                        isset($_GET['node'])    ? $_GET['node']    : false, 
                        isset($_GET['node_id']) ? $_GET['node_id'] : false
                    );
                    break;
                case T_URL_PROFILE:
                    if (!call_user_func(array($classPrefix, 'exists'), $_GET['obj_id'])) {
                        fatal("The specified ID does not exist.");
                    }
                    call_user_func(array("${classPrefix}_HTMLOUT", 'profile'), $_GET['obj_id']);
                    break;
            }
        }
    }
}

/*************************
 *
 *  RULES
 *
 *************************/

function sec_rules() {

    global $lng, $settings;
    title($lng->getTrn('menu/rules'));
    list($sel_lid, $HTML_LeagueSelector) = HTMLOUT::simpleLeagueSelector();
    echo $HTML_LeagueSelector;
    echo "<br><br>";
    echo $settings['rules'];
}

/*************************
 *
 *  ABOUT
 *
 *************************/

function sec_about() {

    global $lng, $credits;

    title($lng->getTrn('about/intro'));
    echo '<p>'.$lng->getTrn('about/intro_txt').'</p>';
    
    title($lng->getTrn('about/faq'));
    echo '<p>'.$lng->getTrn('about/faq_txt').'</p>';
    
    title("About OBBLM");
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

    <?php title("Disclaimer");?>
    <p>
        By installing and using this software you hereby accept and understand the following disclaimer
        <br><br>
        <b>This web site is completely unofficial and in no way endorsed by Games Workshop Limited.</b>
        <br><br>
        Bloodquest, Blood Bowl, the Blood Bowl logo, The Blood Bowl Spike Device, Chaos, the Chaos device, the Chaos logo, Games Workshop, Games Workshop logo, Nurgle, the Nurgle device, Skaven, Tomb Kings, 
        and all associated marks, names, races, race insignia, characters, vehicles, locations, units, illustrations and images from the Blood Bowl game, the Warhammer world are either ®, TM and/or © Games Workshop Ltd 2000-2006, 
        variably registered in the UK and other countries around the world. Used without permission. No challenge to their status intended. All Rights Reserved to their respective owners.
    </p>

    <?php title("License");?>
    <p>
        Copyright (c) Niels Orsleff Justesen and Nicholas Mossor Rathmann 2007-2010. All Rights Reserved.
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
?>
