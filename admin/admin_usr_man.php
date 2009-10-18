<?php
if (isset($_POST['type'])) {

    switch ($_POST['type']) {

        case 'mk_coach':
            if (get_magic_quotes_gpc()) {
                $_POST['new_name'] = stripslashes($_POST['new_name']);
                $_POST['new_realname'] = stripslashes($_POST['new_realname']);
                $_POST['new_mail'] = stripslashes($_POST['new_mail']);
                $_POST['new_phone'] = stripslashes($_POST['new_phone']);
                $_POST['new_passwd'] = stripslashes($_POST['new_passwd']);
            }
            status(Coach::create(array(
                'name'     => $_POST['new_name'],
                'realname' => $_POST['new_realname'],
                'passwd'   => $_POST['new_passwd'],
                'mail'     => $_POST['new_mail'],
                'phone'    => $_POST['new_phone'],
                'ring'     => $_POST['new_ring'],
            )));
            break;

        case 'ch_ring':
            $coach = new Coach($_POST['chring_coachid']);
            if (!is_object($coach)) {
                status(false);
                return;
            }
            else {
                status($coach->setRing($_POST['chring_ring']));
            }
            break;

        case 'ch_passwd':
            $coach = new Coach($_POST['chpass_coachid']);
            status($coach->setPasswd($_POST['ch_passwd']));
            break;
            
        case 'ch_comlid':
            $coach = new Coach($_POST['chcomlid_coachid']);
            status($coach->setCommissionerLid($_POST['chcomlid_lid']));
            break;
    }

    // Reload coaches objects.
    $coaches = Coach::getCoaches();
}

// Interface related correction to make long coach names not break visual layout.
foreach ($coaches as $c) {
    if (strlen($c->name) > 28)
        $c->name = substr($c->name, 0, 24) . '...';
    if (strlen($c->mail) > 28)
        $c->mail = substr($c->mail, 0, 24) . '...';
}

title($lng->getTrn('menu/admin_menu/usr_man'));
objsort($coaches, array('+name'));
$com_coaches = array_filter($coaches, create_function('$c', 'return ($c->ring == '.RING_COM.');'));
$rings = array(
    RING_SYS    => 'Ring '.RING_SYS.': Site admin',
    RING_COM    => 'Ring '.RING_COM.': League commissioner',
    RING_COACH  => 'Ring '.RING_COACH.': Regular coach'
);

?>
<form method="POST" action="?section=admin&amp;subsec=usr_man">

    <b>OBBLM access levels:</b>
    <ul>
        <li>Ring 2: Ordinary coaches: May manage own teams and submit match reports in which own teams play.</li>
        <li>Ring 1: League commissioners: Same as ring 2, but may also schedule matches, view the site log and post messages on the front page board.</li>
        <li>Ring 0: Site administrators: Same as ring 1, but has access to the whole administrators section, and may also manage other teams + submit their match reports.</li>
    </ul>

    <div class="boxCommon">
        <div class="boxTitle<?php echo T_HTMLBOX_ADMIN;?>">
            Create new coach
        </div>
        <div class="boxBody">
            Coach name (login):<br> <input type="text" name="new_name" size="20" maxlength="50"><br><br>
            Full name:<br> <input type="text" name="new_realname" size="20" maxlength="50"><br><br>
            Mail (optional):<br> <input type="text" name="new_mail" size="20" maxlength="129"><br><br>
            Phone (optional):<br> <input type="text" name="new_phone" size="20" maxlength="129"><br><br>
            Password:<br> <input type="password" name="new_passwd" size="20" maxlength="50"><br><br>
            Site access level:<br>
            <select name="new_ring">
                <?php
                foreach ($rings as $r => $desc) {
                    echo "<option value='$r' ".(($r == RING_COACH) ? 'SELECTED' : '').">$desc</option>\n";
                }
                ?>
            </select>
            <br><i>(Commisioners are by default not<br> assigned to any league, you must<br> do this after creating the coach).</i>
            <br><br>
            <input type="hidden" name="type" value="mk_coach">
            <input type="submit" name="button" value="Create coach">
        </div>
    </div>

    <div style='float:left;'> <!-- Outer -->
    <div class="row"> <!-- Inner row 1 -->
    
    <div class="boxCommon">
        <div class="boxTitle<?php echo T_HTMLBOX_ADMIN;?>">
            Change coach access level
        </div>
        <div class="boxBody">
            Coach:<br>
            <select name="chring_coachid">
                <?php
                foreach ($coaches as $coach) {
                    echo "<option value='$coach->coach_id'>$coach->name</option>\n";
                }
                ?>
            </select>
            <br><br>
            Site access level:<br>
            <select name="chring_ring">
                <?php
                foreach ($rings as $r => $desc) {
                    echo "<option value='$r'>$desc</option>\n";
                }
                ?>
            </select>
            <br><br>
            <input type="hidden" name="type" value="ch_ring">
            <input type="submit" name="button" value="Change privileges">
        </div>
    </div>

    <div class="boxCommon">
        <div class="boxTitle<?php echo T_HTMLBOX_ADMIN;?>">
            Change coach password
        </div>
        <div class="boxBody">
            Coach:<br>
            <select name="chpass_coachid">
                <?php
                foreach ($coaches as $coach) {
                    echo "<option value='$coach->coach_id'>$coach->name</option>\n";
                }
                ?>
            </select>
            <br><br>
            Password:<br> <input type="password" name="ch_passwd" size="20" maxlength="50"><br><br>
            <input type="hidden" name="type" value="ch_passwd">
            <input type="submit" name="button" value="Change password">
        </div>
    </div>
    
    </div> <!-- END row 1 -->
    <div class="row"> <!-- Intter row 2 -->
    
    <div class="boxCommon">
        <div class="boxTitle<?php echo T_HTMLBOX_ADMIN;?>">
            Change commissioner's league
        </div>
        <div class="boxBody">
            Coach:<br>
            <select name="chcomlid_coachid">
                <?php
                foreach ($com_coaches as $coach) {
                    echo "<option value='$coach->coach_id'>$coach->name</option>\n";
                }
                ?>
            </select>
            <br><br>
            Set to commision the league:<br>
            <select name="chcomlid_lid">
                <?php
                foreach (League::getLeagues() as $l) {
                    echo "<option value='$l->lid'>$l->name</option>\n";
                }
                echo "<option value='".RING_COM_NOLEAGUE."'>--No league--</option>\n";
                ?>
            </select>
            <br><br>
            <input type="hidden" name="type" value="ch_comlid">
            <input type="submit" name="button" value="Change relation" <?php echo empty($com_coaches) ? 'DISABLED' : '';?>>
        </div>
    </div>
    
    </div> <!-- END row 1 -->
    </div> <!-- END Outer -->

    <div class="boxCommon" style="clear: both;">
        <div class="boxTitle<?php echo T_HTMLBOX_INFO;?>">
            Coaches
        </div>
        <div class="boxBody">
            <?php
            if (!empty($coaches)) {
                ?>
                <table class="boxTable">
                    <tr>
                        <td><b>Name</b></td>
                        <td><b>Nickname</b></td>
                        <td><b>Phone</b></td>
                        <td><b>Mail</b></td>
                        <td><b>Coach&nbsp;ID</b></td>
                        <td><b>Access&nbsp;level</b></td>
                        <td><b>Commish's&nbsp;leauge</b></td>
                    </tr>
                <?php
                foreach ($coaches as $coach) {
                    echo "<tr>\n";
                    echo "<td>$coach->realname</td>\n";
                    echo "<td>$coach->name</td>\n";
                    echo "<td>".((empty($coach->phone)) ? '<i>None</i>' : $coach->phone)."</td>\n";
                    echo "<td>".((empty($coach->mail)) ? '<i>None</i>' : "<a href='mailto:$coach->mail'>$coach->mail</a>")."</td>\n";
                    echo "<td>$coach->coach_id</td>\n";
                    echo "<td>".$rings[$coach->ring]."</td>\n";
                    echo "<td>".(($coach->ring == RING_COM) ? (($coach->com_lid != RING_COM_NOLEAGUE) ? get_alt_col('leagues', 'lid', $coach->com_lid, 'name') :  '<i>None</i>') : '&mdash;')."</td>\n";
                    echo "</tr>\n";
                }
                echo "</table>\n";
            }
            ?>
        </div>
    </div>

</form>
<?php
