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

public function coachPage()
{
    $c = $this;

    title("Coach $c->name");
    echo "<center>";
    echo "<a href='index.php?section=coaches'>[".$lng->getTrn('global/misc/back')."]</a>";
    if (Module::isRegistered('SGraph')) {
        echo "&nbsp; | &nbsp;<a href='handler.php?type=graph&amp;gtype=".SG_T_COACH."&amp;id=$c->coach_id''>[Vis. stats]</a>\n";
    }
    echo "</center><br>\n";

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
                ImageSubSys::makeBox(IMGTYPE_COACH, $c->coach_id, false, false);
                ?>
            </td>
            <td valign='top'>
                <?php
                $txt = $c->getText();
                if (empty($txt)) {
                    $txt = $lng->getTrn('secs/coaches/nowrite')." $c->name.";
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
}

public static function standings()
{
    title($lng->getTrn('global/secLinks/coaches'));
    HTMLOUT::standings(STATS_COACH, false, false, array('url' => 'index.php?section=coaches'));
}

function coachCorner() {

    global $lng, $settings;

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
        if (isset($_POST['new_team']) && !empty($_POST['name'])) {

            if (get_magic_quotes_gpc())
                $_POST['name'] = stripslashes($_POST['name']);

            status(Team::create(array('name' => $_POST['name'], 'coach_id' => $coach->coach_id, 'race' => $_POST['race'], 'f_lid' => isset($_POST['f_lid']) ? $_POST['f_lid'] : 0)));

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
                <?php
                if ($settings['relate_team_to_league']) {
                    $leagues = League::getLeagues();
                    ?>
                    <b><?php echo $lng->getTrn('secs/cc/new_team/league');?>:</b> <br>
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
                <input type="submit" name="new_team" value="<?php echo $lng->getTrn('secs/cc/new_team/button');?>" <?php if ($settings['relate_team_to_league'] && empty($leagues)){echo "DISABLED";}?>>
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

        title($lng->getTrn('global/secLinks/coachcorner'));

        HTMLOUT::helpBox(
            "<b>Your teams</b>
            <p>
                This is a list of the teams you coach. Only the teams selected by the league commissioner play in the created leagues. 
                This means you are free to make a testing team for yourself. 
                Teams are deletable as long as they are not scheduled to play any matches or have already played.
            </p>",
            'Need help?', 'width:400px;'
        );
        echo "<br>\n";
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
                    ImageSubSys::makeBox(IMGTYPE_COACH, $coach->coach_id, true, false);
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

}

?>
