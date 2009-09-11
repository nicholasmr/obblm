<?php
if (isset($_POST['button'])) {

    switch ($_POST['button']) {

        case 'Create coach':
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

        case 'Change privileges':
            $coach = new Coach($_POST['chring_coachid']);
            if (!is_object($coach)) {
                status(false);
                return;
            }
            else {
                status($coach->setRing($_POST['chring_ring']));
            }
            break;

        case 'Change password':
            $coach = new Coach($_POST['chpass_coachid']);
            status($coach->setPasswd($_POST['ch_passwd']));
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

title($lng->getTrn('secs/admin/um'));
objsort($coaches, array('+name'));
$rings = array(
    RING_SYS    => 'Ring '.RING_SYS.': Site admin',
    RING_COM    => 'Ring '.RING_COM.': League commissioner',
    RING_COACH  => 'Ring '.RING_COACH.': Regular coach'
);

?>
<form method="POST" action="?section=admin&amp;subsec=usrman">

    <b>OBBLM access levels:</b>
    <ul>
        <li><?php echo $lng->getTrn('secs/admin/access/r2');?></li>
        <li><?php echo $lng->getTrn('secs/admin/access/r1');?></li>
        <li><?php echo $lng->getTrn('secs/admin/access/r0');?></li>
    </ul>

    <div class="adminBox">
        <div class="boxTitle3">
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
            <br><br>
            <input type="submit" name="button" value="Create coach">
        </div>
    </div>

    <div class="adminBox">
        <div class="boxTitle3">
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
            <input type="submit" name="button" value="Change privileges">
        </div>
    </div>

    <div class="adminBox">
        <div class="boxTitle3">
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
            <input type="submit" name="button" value="Change password">
        </div>
    </div>

    <div class="adminBox" style="clear: both;">
        <div class="boxTitle4">
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
                        <td><b>Coach ID</b></td>
                        <td><b>Access level</b></td>
                    </tr>
                <?php
                foreach ($coaches as $coach) {
                    echo "<tr>\n";
                    echo "<td>$coach->realname</td>\n";
                    echo "<td>$coach->name</td>\n";
                    echo "<td>".((empty($coach->phone)) ? '<i>None</i>' : $coach->phone)."</td>\n";
                    echo "<td>".((empty($coach->mail)) ? '<i>None</i>' : "<a href='mailto:$coach->mail'>$coach->mail</a>")."</td>\n";
                    echo "<td>$coach->coach_id</td>\n";
                    echo "<td>" . $rings[$coach->ring] . " </td>\n";
                    echo "</tr>\n";
                }
                echo "</table>\n";
            }
            ?>
        </div>
    </div>

</form>
<?php
