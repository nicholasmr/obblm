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

class Coach_HTMLOUT extends Coach
{

public static function profile($cid)
{
    global $lng, $coach;
    
    $c = new self($cid);
    title($c->name);
    
    echo "<center>";
    echo "<a href='".urlcompile(T_URL_STANDINGS,T_OBJ_COACH,false,false,false)."'>[".$lng->getTrn('common/back')."]</a>";
    if (Module::isRegistered('SGraph')) {
        echo "&nbsp; | &nbsp;<a href='handler.php?type=graph&amp;gtype=".SG_T_COACH."&amp;id=$c->coach_id''>[Vis. stats]</a>\n";
    }
    echo "</center><br>\n";

    ?>
    <table class='common'>
        <tr class='commonhead'>
            <td><b><?php echo $lng->getTrn('common/picof');?> <?php echo $c->name;?></b></td>
            <td><b><?php echo $lng->getTrn('common/about');?></b></td>
            <?php
            if (is_object($coach)) {
                ?><td><b><?php echo $lng->getTrn('profile/coach/contact');?></b></td><?php
            }
            ?>
        </tr>
        <tr>
            <td>
                <?php
                ImageSubSys::makeBox(IMGTYPE_COACH, $c->coach_id, false, false);
                ?>
            </td>
            <td valign='top'>
                <?php
                $txt = $c->getText();
                if (empty($txt)) {
                    $txt = $lng->getTrn('common/nobody');
                }
                echo '<p>'.fmtprint($txt)."</p>\n";
                ?>
            </td>
            <?php
            if (is_object($coach)) {
                ?>
                <td valign='top'>
                    <table>
                        <tr>
                            <td><b>Name</b></td>
                            <td><?php echo empty($c->realname) ? '<i>'.$lng->getTrn('common/none').'</i>' : $c->realname;?></td>
                        </tr>
                        <tr>
                            <td><b>Phone</b></td>
                            <td><?php echo empty($c->phone) ? '<i>'.$lng->getTrn('common/none').'</i>' : $c->phone?></td>
                        </tr>
                        <tr>
                            <td><b>Mail</b></td>
                            <td><?php echo empty($c->mail) ? '<i>'.$lng->getTrn('common/none').'</i>' : $c->mail?></td>
                        </tr>
                    </table>
                </td>
                <?php
            }
            ?>
        </tr>
    </table>
    <?php
    $url = urlcompile(T_URL_PROFILE,T_OBJ_COACH,$c->coach_id,false,false);
    HTMLOUT::standings(STATS_TEAM,false,false,array('url' => $url, 'teams_from' => STATS_COACH, 'teams_from_id' => $c->coach_id));
    echo '<br>';
    HTMLOUT::recentGames(STATS_COACH, $c->coach_id, false, false, false, false, array('url' => $url, 'n' => MAX_RECENT_GAMES, 'GET_SS' => 'gp'));
}

public static function standings()
{
    global $lng;
    title($lng->getTrn('menu/statistics_menu/coach_stn'));
    HTMLOUT::standings(STATS_COACH, false, false, array('url' => urlcompile(T_URL_STANDINGS,T_OBJ_COACH,false,false,false)));
}

public static function coachCorner($cid) {

    global $lng, $settings, $coach, $raceididx;

    /*
     *  Main actions:
     *
     *  1.  New team        (team_id = "new")
     *  2.  Existing team.  (team_id = digit)
     *  3.  Change coach settings (passwd & mail)
     *
     */

    /* 
        New team page 
    */
    if (isset($_GET['team_id']) && $_GET['team_id'] == 'new') {
        // Form posted? -> Create team.
        if (isset($_POST['new_team']) && !empty($_POST['name'])) {
            if (get_magic_quotes_gpc()) {
                $_POST['name'] = stripslashes($_POST['name']);
            }
            status(Team::create(array('name' => $_POST['name'], 'coach_id' => $coach->coach_id, 'race' => $_POST['race'], 'f_lid' => isset($_POST['f_lid']) ? $_POST['f_lid'] : 0)));
            # Go back to coach corner main page again.
            unset($_GET['team_id']);
            self::coachCorner($cid);
            return;
        }

        // Show new team form.
        title($lng->getTrn('cc/new_team/title'));
        echo "<center><a href='index.php?section=coachcorner'>".$lng->getTrn('common/back')."</a></center>";
        ?>
        <form method="POST">
        <b><?php echo $lng->getTrn('cc/new_team/name');?>:</b> <br>
        <input type="text" name="name" size="20" maxlength="50">
        <br><br>
        <b><?php echo $lng->getTrn('cc/new_team/race');?>:</b> <br>
        <select name="race">
            <?php
            foreach ($raceididx as $rid => $rname)
                echo "<option value='$rid'>$rname</option>\n";
            ?>
        </select>
        <br><br>
        <?php
        if ($settings['relate_team_to_league']) {
            $leagues = League::getLeagues();
            ?>
            <b><?php echo $lng->getTrn('cc/new_team/league');?>:</b> <br>
            <select name="f_lid" <?php if (empty($leagues)){echo "DISABLED";}?>>
            <?php
            foreach ($leagues as $l)
                echo "<option value='$l->lid'>$l->name</option>\n";
            ?>
            </select>
            <br><br>
            <?php
        }
        ?>
        <input type="submit" name="new_team" value="<?php echo $lng->getTrn('common/create');?>" <?php if ($settings['relate_team_to_league'] && empty($leagues)){echo "DISABLED";}?>>
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
                case 'chpasswd':    status(Coach::checkPasswd($coach->coach_id, $_POST['old_passwd']) && $coach->setPasswd($_POST['new_passwd'])); break;
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

        title($lng->getTrn('menu/cc'));

        HTMLOUT::helpBox(
            "<b>Your teams</b>
            <p>
                This is a list of the teams you coach. Only the teams selected by the league commissioner play in the created leagues. 
                This means you are free to make a testing team for yourself. 
                Teams are deletable as long as they are not scheduled to play any matches or have already played.
            </p>",
            $lng->getTrn('common/needhelp'), 'width:400px;'
        );
        echo "<br>\n";

        echo "<a href='".urlcompile(T_URL_PROFILE,T_OBJ_COACH,$coach->coach_id,false,false)."'>".$lng->getTrn('cc/main/your_stats')."</a><br><br>\n";

        // Generate teams list.
        Team_HTMLOUT::dispTeamList(STATS_COACH, $coach->coach_id);

        // New team and change coach settings.
        ?>
        <a href="?section=coachcorner&amp;team_id=new"><img style="border:none;width:80px;" alt="new team" src="<?php echo RACE_ICONS;?>/new.png"></a>
        <?php echo $lng->getTrn('cc/main/start_new');?>
        <br><br>
        <table class="common"><tr class="commonhead"><td><b><?php echo $lng->getTrn('cc/main/your_info');?></b></td></tr></table>
        <table class="common" style="border-spacing:5px; padding:20px;">
                <tr>
                    <form method="POST">
                    <td><?php echo $lng->getTrn('cc/main/chpasswd');?>:</td>
                    <td><?php echo $lng->getTrn('cc/main/old');?>:<input type='password' name='old_passwd' size="20" maxlength="50"></td>
                    <td><?php echo $lng->getTrn('cc/main/new');?>:<input type='password' name='new_passwd' size="20" maxlength="50"></td>
                    <td><input type="submit" name="button" value="<?php echo $lng->getTrn('cc/main/chpasswd');?>"></td>
                    <input type='hidden' name='type' value='chpasswd'>
                    </form>
                </tr>
                <tr>
                    <form method="POST">
                    <td><?php echo $lng->getTrn('cc/main/chphone');?>:</td>
                    <td><?php echo $lng->getTrn('cc/main/old');?>:<input type='text' name='old_phone' readonly value="<?php echo $coach->phone; ?>" size="20" maxlength="129"></td>
                    <td><?php echo $lng->getTrn('cc/main/new');?>:<input type='text' name='new_phone' size="20" maxlength="25"></td>
                    <td><input type="submit" name="button" value="<?php echo $lng->getTrn('cc/main/chphone');?>"></td>
                    <input type='hidden' name='type' value='chphone'>
                    </form>
                </tr>
                <tr>
                    <form method="POST">
                    <td><?php echo $lng->getTrn('cc/main/chmail');?>:</td>
                    <td><?php echo $lng->getTrn('cc/main/old');?>:<input type='text' name='old_email' readonly value="<?php echo $coach->mail; ?>" size="20" maxlength="129"></td>
                    <td><?php echo $lng->getTrn('cc/main/new');?>:<input type='text' name='new_email' size="20" maxlength="129"></td>
                    <td><input type="submit" name="button" value="<?php echo $lng->getTrn('cc/main/chmail');?>"></td>
                    <input type='hidden' name='type' value='chmail'>
                    </form>
                </tr>
                <tr>
                    <form method="POST">
                    <td><?php echo $lng->getTrn('cc/main/chlogin');?>:</td>
                    <td><?php echo $lng->getTrn('cc/main/old');?>:<input type='text' name='old_name' readonly value="<?php echo $coach->name; ?>" size="20" maxlength="50"></td>
                    <td><?php echo $lng->getTrn('cc/main/new');?>:<input type='text' name='new_name' size="20" maxlength="50"></td>
                    <td><input type="submit" name="button" value="<?php echo $lng->getTrn('cc/main/chlogin');?>"></td>
                    <input type='hidden' name='type' value='chlogin'>
                    </form>
                </tr>
                <tr>
                    <form method="POST">
                    <td><?php echo $lng->getTrn('cc/main/chname');?>:</td>
                    <td><?php echo $lng->getTrn('cc/main/old');?>:<input type='text' name='old_realname' readonly value="<?php echo $coach->realname; ?>" size="20" maxlength="50"></td>
                    <td><?php echo $lng->getTrn('cc/main/new');?>:<input type='text' name='new_realname' size="20" maxlength="50"></td>
                    <td><input type="submit" name="button" value="<?php echo $lng->getTrn('cc/main/chname');?>"></td>
                    <input type='hidden' name='type' value='chname'>
                    </form>
                </tr>
                <tr>
                    <form method="POST">
                    <td><?php echo $lng->getTrn('cc/main/chtheme');?>:</td>
                    <td><?php echo $lng->getTrn('cc/main/current');?>: <?php echo $coach->settings['theme'];?></td>
                    <td>
                        <?php echo $lng->getTrn('cc/main/new');?>:
                        <select name='new_theme'>
                            <?php
                            foreach (array(1 => 'Classic', 2 => 'Clean') as $theme => $desc) {
                                echo "<option value='$theme'>$theme: $desc</option>\n";
                            }
                            ?>
                        </select>
                    </td>
                    <td><input type="submit" name="button" value="<?php echo $lng->getTrn('cc/main/chtheme');?>"></td>
                    <input type='hidden' name='type' value='chtheme'>
                    </form>
                </tr>
        </table>

        <table class='common'>
            <tr class='commonhead'>
                <td><b><?php echo $lng->getTrn('cc/main/photo');?></b></td>
                <td><b><?php echo $lng->getTrn('common/about');?></b></td>
            </tr>
            <tr>
                <td>
                    <?php
                    ImageSubSys::makeBox(IMGTYPE_COACH, $coach->coach_id, true, false);
                    ?>
                </td>
                <td valign='top'>
                    <?php
                    $txt = $coach->getText();
                    if (empty($txt)) {
                        $txt = $lng->getTrn('common/nobody');
                    }
                    ?>
                    <form method='POST'>
                        <textarea name='coachtext' rows='15' cols='70'><?php echo $txt;?></textarea>
                        <br><br>
                        <input type="hidden" name="type" value="coachtext">
                        <input type="submit" name='Save' value="<?php echo $lng->getTrn('common/save');?>">
                    </form>
                </td>
            </tr>
        </table>
        <?php
    }
}
}
?>
