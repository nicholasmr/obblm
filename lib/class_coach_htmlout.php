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

public static function standings()
{
    global $lng;
    title($lng->getTrn('menu/statistics_menu/coach_stn'));
    HTMLOUT::standings(STATS_COACH, false, false, array('url' => urlcompile(T_URL_STANDINGS,T_OBJ_COACH,false,false,false)));
}

public static function profile($cid) {

    global $lng, $coach;
    $c = new self($cid);
    $ALLOW_EDIT = (is_object($coach) && ($c->coach_id == $coach->coach_id || $coach->admin)); # Coach (or admin) visting own profile?

    title($c->name);
    echo "<hr>";
    $c->_sectionNames = array('cc_teams', 'cc_ccprofile', 'cc_stats', 'cc_newteam', 'cc_recentmatches');
    $c->_menu($ALLOW_EDIT);
    $c->_CCprofile($ALLOW_EDIT);
    $c->_stats();
    $c->_newTeam($ALLOW_EDIT); # Must come before _teams() else new teams won't show.
    $c->_teams($ALLOW_EDIT);
    $c->_recentGames();

    // Default folded out sub-section.
    $activeDiv = (isset($_POST['type']) && in_array('cc_'.$_POST['type'],$c->_sectionNames)) ? 'cc_'.$_POST['type'] : 'cc_ccprofile';
    if (isset($_GET['sortgp'])) {$activeDiv = 'cc_recentmatches';}
    if (isset($_GET['sort'])) {$activeDiv = 'cc_teams';}
    ?><script language="JavaScript" type="text/javascript"> foldup('<?php echo $activeDiv;?>'); </script><?php
}

// Small helper routine for _menu().
private function _makeOnClick($divID)
{
    return "onClick=\"foldup('$divID');\"";
}

private function _menu($ALLOW_EDIT)
{
    global $lng, $settings, $rules;
    
    ?>
    <br>
    <ul id="nav" class="dropdown dropdown-horizontal" style="position:static; z-index:0;">
        <li><a href='javascript:void(0)' <?php echo $this->_makeOnClick('cc_teams');?>><?php echo $lng->getTrn('cc/coach_teams');?></a></li>
        <li><a href='javascript:void(0)' <?php echo $this->_makeOnClick('cc_ccprofile');?>><?php echo $lng->getTrn('cc/profile');?></a></li>
        <li><a href='javascript:void(0)' <?php echo $this->_makeOnClick('cc_stats');?>><?php echo $lng->getTrn('common/stats');?></a></li>
        <li><a href='javascript:void(0)' <?php echo $this->_makeOnClick('cc_recentmatches');?>><?php echo $lng->getTrn('common/recentmatches');?></a></li>
        <?php
        if ($ALLOW_EDIT) {
            ?><li><a href='javascript:void(0)' <?php echo $this->_makeOnClick('cc_newteam');?>><?php echo $lng->getTrn('cc/new_team');?></a></li><?php
        }
        if (Module::isRegistered('SGraph')) {
            echo "<li><a href='handler.php?type=graph&amp;gtype=".SG_T_COACH."&amp;id=$this->coach_id'>Vis. stats</a></li>\n";
        }
        ?>
    </ul>
    <br><br>
    
    <script language="JavaScript" type="text/javascript">
        function foldup(execption)
        {
            var fields = [<?php echo implode(',', array_map(create_function('$sn', 'return "\'$sn\'";'), $this->_sectionNames)); ?>];
            for (f in fields) {
                document.getElementById(fields[f]).style.display='none';
            }
            document.getElementById(execption).style.display='block';
        }
    </script>
    <?php
}

private function _newTeam($ALLOW_EDIT)
{
    global $lng, $settings, $raceididx;

    echo "<div id='cc_newteam' style='clear:both;'>\n";
        
    // Form posted? -> Create team.
    if (isset($_POST['type']) && $_POST['type'] == 'newteam' && $ALLOW_EDIT) {
        if (get_magic_quotes_gpc()) {
            $_POST['name'] = stripslashes($_POST['name']);
        }
        status(Team::create(array('name' => $_POST['name'], 'coach_id' => $this->coach_id, 'race' => $_POST['race'], 'f_lid' => isset($_POST['f_lid']) ? $_POST['f_lid'] : 0)));
    }

    // Show new team form.
    echo "<br><br>";
    ?>
    <form method="POST">
    <b><?php echo $lng->getTrn('common/name');?>:</b> <br>
    <input type="text" name="name" size="20" maxlength="50">
    <br><br>
    <b><?php echo $lng->getTrn('common/race');?>:</b> <br>
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
        <b><?php echo $lng->getTrn('cc/league');?>:</b> <br>
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
    <input type='hidden' name='type' value='newteam'>
    <input type="submit" name="new_team" value="<?php echo $lng->getTrn('common/create');?>" <?php if ($settings['relate_team_to_league'] && empty($leagues)){echo "DISABLED";}?>>
    </form>
    <?php
    echo "</div>\n";
}

private function _teams($ALLOW_EDIT)
{
    global $lng;
    
    echo "<div id='cc_teams' style='clear:both;'>\n";
    echo "<br>";
    if ($ALLOW_EDIT) {
        HTMLOUT::helpBox(
            "<b>Your teams</b>
            <p>
                This is a list of the teams you coach. Only the teams selected by the league commissioner play in the created leagues. 
                This means you are free to make a testing team for yourself. 
                Teams are deletable as long as they are not scheduled to play any matches or have already played.
            </p>",
            $lng->getTrn('common/needhelp'), 'width:400px;'
        );
    }

    // Generate teams list.
#    Team_HTMLOUT::dispTeamList(T_OBJ_COACH, $this->coach_id);
#    echo "<br>";
    $url = urlcompile(T_URL_PROFILE,T_OBJ_COACH,$this->coach_id,false,false);
    HTMLOUT::standings(T_OBJ_TEAM,false,false,array('url' => $url, 'teams_from' => T_OBJ_COACH, 'teams_from_id' => $this->coach_id));
    echo "</div>";
}

private function _CCprofile($ALLOW_EDIT) 
{
    global $lng, $coach;

    echo "<div id='cc_ccprofile' style='clear:both;'>\n";
    
    // Was new password/email request made?
    if (isset($_POST['type']) && $ALLOW_EDIT) {

        if (get_magic_quotes_gpc()) {
            $_POST['new_passwd']   = isset($_POST['new_passwd'])     ? stripslashes($_POST['new_passwd']) : '';
            $_POST['new_phone']    = isset($_POST['new_phone'])      ? stripslashes($_POST['new_phone']) : '';
            $_POST['new_email']    = isset($_POST['new_email'])      ? stripslashes($_POST['new_email']) : '';
            $_POST['new_name']     = isset($_POST['new_name'])       ? stripslashes($_POST['new_name']) : '';
            $_POST['new_realname'] = isset($_POST['new_realname'])   ? stripslashes($_POST['new_realname']) : '';
        }

        switch ($_POST['type'])
        {
            case 'chpasswd':    status(Coach::checkPasswd($this->coach_id, $_POST['old_passwd']) && $this->setPasswd($_POST['new_passwd'])); break;
            case 'chphone':     status($this->setPhone($_POST['new_phone'])); break;
            case 'chmail':      status($this->setMail($_POST['new_email'])); break;
            case 'chlogin':     status($this->setName($_POST['new_name'])); break;
            case 'chname':      status($this->setRealName($_POST['new_realname'])); break;
            case 'chtheme':     status($this->setSetting('theme', (int) $_POST['new_theme'])); break;

            case 'pic':         status($this->savePic(false)); break;
            case 'coachtext':
                if (get_magic_quotes_gpc()) {
                    $_POST['coachtext'] = stripslashes($_POST['coachtext']);
                }
                status($this->saveText($_POST['coachtext']));
                break;
        }
    }

    // New team and change coach settings.
    echo "<br><br>";
    ?>
    <table class="common"><tr class="commonhead"><td><b><?php echo $lng->getTrn('cc/coach_info');?></b></td></tr></table>
    <br>
    <?php
    echo $lng->getTrn('cc/note_persinfo');
    echo "<br><br>";
    
    if (is_object($coach) && !$ALLOW_EDIT) { # Logged in but not viewing own coach page.
        ?>
        <table>
            <tr>
                <td>Name:</td>
                <td><?php echo empty($c->realname) ? '<i>'.$lng->getTrn('common/none').'</i>' : $c->realname;?></td>
            </tr>
            <tr>
                <td>Phone:</td>
                <td><?php echo empty($c->phone) ? '<i>'.$lng->getTrn('common/none').'</i>' : $c->phone?></td>
            </tr>
            <tr>
                <td>Mail:</td>
                <td><?php echo empty($c->mail) ? '<i>'.$lng->getTrn('common/none').'</i>' : $c->mail?></td>
            </tr>
        </table>
        <br>
        <?php
    }
    if ($ALLOW_EDIT) {
        ?>
        <table class="common" style="border-spacing:5px; padding:20px;">
            <tr>
                <form method="POST">
                <td><?php echo $lng->getTrn('cc/chpasswd');?>:</td>
                <td><?php echo $lng->getTrn('cc/old');?>:<input type='password' name='old_passwd' size="20" maxlength="50"></td>
                <td><?php echo $lng->getTrn('cc/new');?>:<input type='password' name='new_passwd' size="20" maxlength="50"></td>
                <td><input type="submit" name="button" value="<?php echo $lng->getTrn('cc/chpasswd');?>"></td>
                <input type='hidden' name='type' value='chpasswd'>
                </form>
            </tr>
            <tr>
                <form method="POST">
                <td><?php echo $lng->getTrn('cc/chphone');?>:</td>
                <td><?php echo $lng->getTrn('cc/old');?>:<input type='text' name='old_phone' readonly value="<?php echo $this->phone; ?>" size="20" maxlength="129"></td>
                <td><?php echo $lng->getTrn('cc/new');?>:<input type='text' name='new_phone' size="20" maxlength="25"></td>
                <td><input type="submit" name="button" value="<?php echo $lng->getTrn('cc/chphone');?>"></td>
                <input type='hidden' name='type' value='chphone'>
                </form>
            </tr>
            <tr>
                <form method="POST">
                <td><?php echo $lng->getTrn('cc/chmail');?>:</td>
                <td><?php echo $lng->getTrn('cc/old');?>:<input type='text' name='old_email' readonly value="<?php echo $this->mail; ?>" size="20" maxlength="129"></td>
                <td><?php echo $lng->getTrn('cc/new');?>:<input type='text' name='new_email' size="20" maxlength="129"></td>
                <td><input type="submit" name="button" value="<?php echo $lng->getTrn('cc/chmail');?>"></td>
                <input type='hidden' name='type' value='chmail'>
                </form>
            </tr>
            <tr>
                <form method="POST">
                <td><?php echo $lng->getTrn('cc/chlogin');?>:</td>
                <td><?php echo $lng->getTrn('cc/old');?>:<input type='text' name='old_name' readonly value="<?php echo $this->name; ?>" size="20" maxlength="50"></td>
                <td><?php echo $lng->getTrn('cc/new');?>:<input type='text' name='new_name' size="20" maxlength="50"></td>
                <td><input type="submit" name="button" value="<?php echo $lng->getTrn('cc/chlogin');?>"></td>
                <input type='hidden' name='type' value='chlogin'>
                </form>
            </tr>
            <tr>
                <form method="POST">
                <td><?php echo $lng->getTrn('cc/chname');?>:</td>
                <td><?php echo $lng->getTrn('cc/old');?>:<input type='text' name='old_realname' readonly value="<?php echo $this->realname; ?>" size="20" maxlength="50"></td>
                <td><?php echo $lng->getTrn('cc/new');?>:<input type='text' name='new_realname' size="20" maxlength="50"></td>
                <td><input type="submit" name="button" value="<?php echo $lng->getTrn('cc/chname');?>"></td>
                <input type='hidden' name='type' value='chname'>
                </form>
            </tr>
            <tr>
                <form method="POST">
                <td><?php echo $lng->getTrn('cc/chtheme');?>:</td>
                <td><?php echo $lng->getTrn('cc/current');?>: <?php echo $this->settings['theme'];?></td>
                <td>
                    <?php echo $lng->getTrn('cc/new');?>:
                    <select name='new_theme'>
                        <?php
                        foreach (array(1 => 'Classic', 2 => 'Clean') as $theme => $desc) {
                            echo "<option value='$theme'>$theme: $desc</option>\n";
                        }
                        ?>
                    </select>
                </td>
                <td><input type="submit" name="button" value="<?php echo $lng->getTrn('cc/chtheme');?>"></td>
                <input type='hidden' name='type' value='chtheme'>
                </form>
            </tr>
        </table>
        <?php
    }
    ?>

    <table class='common'>
        <tr class='commonhead'>
            <td><b><?php echo $lng->getTrn('cc/photo');?></b></td>
            <td><b><?php echo $lng->getTrn('common/about');?></b></td>
        </tr>
        <tr>
            <td>
                <?php
                ImageSubSys::makeBox(IMGTYPE_COACH, $this->coach_id, $ALLOW_EDIT, false);
                ?>
            </td>
            <td valign='top'>
                <?php
                $txt = $this->getText();
                if (empty($txt)) {
                    $txt = $lng->getTrn('common/nobody');
                }
                if ($ALLOW_EDIT) {
                    ?>
                    <form method='POST'>
                        <textarea name='coachtext' rows='15' cols='70'><?php echo $txt;?></textarea>
                        <br><br>
                        <input type="hidden" name="type" value="coachtext">
                        <input type="submit" name='Save' value="<?php echo $lng->getTrn('common/save');?>">
                    </form>
                    <?php
                }
                else {
                    echo '<p>'.fmtprint($txt)."</p>\n";
                }
                ?>
            </td>
        </tr>
    </table>
    <?php
    echo "</div>\n";
}

private function _stats()
{
    global $lng;
    echo "<div id='cc_stats' style='clear:both;'>\n";
    ?>
    <div class="boxCommon">
        <h3 class='boxTitle1'>General</h3>
        <div class='boxBody'>
            <table class="boxTable">
            <?php
            echo "<tr><td>Played</td><td>$this->mv_played</td></tr>\n";
            echo "<tr><td>WIN%</td><td>".(sprintf("%1.1f", $this->rg_win_pct).'%')."</td></tr>\n";
            echo "<tr><td>ELO</td><td>".(($this->rg_elo) ? sprintf("%1.2f", $this->rg_elo) : '<i>N/A</i>')."</td></tr>\n";
            echo "<tr><td>W/L/D</td><td>$this->mv_won/$this->mv_lost/$this->mv_draw</td></tr>\n";
            echo "<tr><td>W/L/D streaks</td><td>$this->mv_swon/$this->mv_slost/$this->mv_sdraw</td></tr>\n";
            echo "<tr><td>Won tours</td><td>$this->wt_cnt</td></tr>\n";            
            echo "<tr><td colspan='2'><hr></td></tr>";
            $result = mysql_query("
                SELECT 
                    COUNT(*) AS 'teams_total', 
                    SUM(IF(rdy IS TRUE,1,0)) AS 'teams_active', 
                    SUM(IF(retired IS TRUE,1,0)) AS 'teams_retired',
                    AVG(elo) AS 'avg_elo',
                    CAST(AVG(ff) AS SIGNED INT) AS 'avg_ff',
                    CAST(AVG(tv)/1000 AS SIGNED INT) AS 'avg_tv'
                FROM teams WHERE owned_by_coach_id = $this->coach_id");
            $row = mysql_fetch_assoc($result);
            echo "<tr><td>Teams total</td><td>$row[teams_total]</td></tr>\n";
            echo "<tr><td>Teams active</td><td>$row[teams_active]</td></tr>\n";
            echo "<tr><td>Teams retired</td><td>$row[teams_retired]</td></tr>\n";
            echo "<tr><td>Average ELO per team</td><td>".(($row['avg_elo']) ? sprintf("%1.2f", $row['avg_elo']) : '<i>N/A</i>')."</td></tr>\n";
            echo "<tr><td>Average TV per team</td><td>$row[avg_tv]</td></tr>\n";
            echo "<tr><td>Average FF per team</td><td>$row[avg_ff]</td></tr>\n";
            ?>
            </table>
        </div>
    </div>
    <div class="boxCommon">
        <h3 class='boxTitle1'>Achievements</h3>
        <div class='boxBody'>
            <table class="boxTable">
            <?php
            $stats = array(
                # Display name => array(field (int or false) sprintf() precision)
                'CAS' => array('cas', 2),
                'BH'  => array('bh', 2),
                'Ki'  => array('ki', 2),
                'Si'  => array('si', 2),
                'TD'  => array('td', 2),
                'Int' => array('intcpt', 2),
                'Cp'  => array('cp', 2),
                'GF'  => array('gf', 2),
                'GA'  => array('ga', 2),
                'SMP' => array('smp', 2),                
            );
            $thisAVG = clone $this;
            $thisAVG->setStats(false, false, true);
            echo "<tr><td>Ach.</td> <td>Amount</td> <td>Avg. per match</td></tr>\n";
            echo "<tr><td colspan='5'><hr></td></tr>\n";
            foreach ($stats as $name => $d) {
                echo "<tr><td><i>$name</i></td>";
                echo "<td>".($this->{"mv_$d[0]"})."</td>";
                echo "<td>".sprintf("%1.$d[1]f", $thisAVG->{"mv_$d[0]"})."</td>";
                echo "</tr>\n";
            }
            ?>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="boxWide">
            <div class="boxTitle<?php echo T_HTMLBOX_STATS;?>"><a href='javascript:void(0);' onClick="slideToggleFast('ES');"><b>[+/-]</b></a> &nbsp;ES</div>
            <div class="boxBody" id="ES">
                <?php
                HTMLOUT::generateEStable($this);
                ?>
            </div>
        </div>
    </div>
    <?php
    echo "</div>\n";
}

private function _recentGames()
{
    echo "<div id='cc_recentmatches' style='clear:both;'>\n";
    echo "<br>";
    $url = urlcompile(T_URL_PROFILE,T_OBJ_COACH,$this->coach_id,false,false);
    HTMLOUT::recentGames(T_OBJ_COACH, $this->coach_id, false, false, false, false, array('url' => $url, 'n' => MAX_RECENT_GAMES, 'GET_SS' => 'gp'));
    echo "</div>";
}

}
?>
