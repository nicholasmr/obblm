<?php

if (isset($_POST['type'])) {

    switch ($_POST['type']) {

        case 'mk_coach':
            if (get_magic_quotes_gpc()) {
                $_POST['name']      = stripslashes($_POST['name']);
                $_POST['realname']  = stripslashes($_POST['realname']);
                $_POST['mail']      = stripslashes($_POST['mail']);
                $_POST['phone']     = stripslashes($_POST['phone']);
                $_POST['passwd']    = stripslashes($_POST['passwd']);
            }
            $INPUT_OK = (
                   (int) $_POST['ring'] >= $coach->ring # Don't allow a coach to create a new coach with greater privileges.
                && in_array((int) $_POST['f_lid'], $league_ids) # IF (logged in) coach is associated to a league then disallow creating a new coach in another league than his/her own.
                && in_array($_POST['lang'], Translations::$registeredLanguages)
            );
            status($INPUT_OK && Coach::create(array(
                'name'     => $_POST['name'],
                'realname' => $_POST['realname'],
                'passwd'   => $_POST['passwd'],
                'mail'     => $_POST['mail'],
                'phone'    => $_POST['phone'],
                'ring'     => $_POST['ring'],
                'f_lid'    => $_POST['f_lid'],
                'settings' => array('lang' => $_POST['lang']),
            )));
            break;

        case 'ch_ring':
            $c = new Coach($_POST['cid']);
            status(
                   (int) $_POST['ring'] >= $coach->ring # Don't allow a coach to set greater privileges to another coach than own privileges.
                && in_array((int) $_POST['cid'], $coach_ids)
                && $c->setRing($_POST['ring'])
            );
            break;

        case 'ch_passwd':
            $c = new Coach($_POST['cid']);
            status(
                   in_array((int) $_POST['cid'], $coach_ids)
                && $c->setPasswd($_POST['passwd'])
            );
            break;
            
        case 'ch_f_lid':
            $c = new Coach($_POST['cid']);
            status(
                   in_array((int) $_POST['lid'], $league_ids)
                && in_array((int) $_POST['cid'], $coach_ids)
                && $c->setLid($_POST['lid'])
            );
            break;
    }

    // Reload manage state.
    $coach = new Coach($coach->coach_id); # Re-load in case of we changed our OWN (logged on coach) settings.
    list($GLOBAL_MANAGE, $coaches, $leagues) = _getState();
}

objsort($coaches, array('+name'));
objsort($leagues, array('+name'));

title($lng->getTrn('menu/admin_menu/usr_man'));
$rings = array(
    RING_SYS    => 'Ring '.RING_SYS.': Site admin',
    RING_COM    => 'Ring '.RING_COM.': League commissioner',
    RING_COACH  => 'Ring '.RING_COACH.': Regular coach'
);

?>
<b>OBBLM access levels:</b>
<ul>
    <li>Ring 2: Ordinary coaches: May manage own teams and submit match reports in which own teams play.</li>
    <li>Ring 1: League commissioners: Same as ring 2, but may also schedule matches, view the site log, post messages on the front page board and create new coaches.</li>
    <li>Ring 0: Site administrators: Same as ring 1, but has access to the whole administrators section, and may also manage other teams + submit their match reports.</li>
</ul>

<div class="boxCommon">
    <div class="boxTitle<?php echo T_HTMLBOX_ADMIN;?>">
        Create new coach
    </div>
    <div class="boxBody">
        <form method="POST">
        Coach name (login):<br> <input type="text" name="name" size="20" maxlength="50"><br><br>
        Full name:<br> <input type="text" name="realname" size="20" maxlength="50"><br><br>
        Mail (optional):<br> <input type="text" name="mail" size="20" maxlength="129"><br><br>
        Phone (optional):<br> <input type="text" name="phone" size="20" maxlength="129"><br><br>
        Password:<br> <input type="password" name="passwd" size="20" maxlength="50"><br><br>
        Language<br>
        <select name='lang'>
            <?php
            foreach (Translations::$registeredLanguages as $lang) {
                echo "<option value='$lang'>$lang</option>\n";
            }
            ?>
        </select>
        <br><br>
        Associated league<br>
        <select name='f_lid'>
            <?php
            foreach ($leagues as $l) {
                echo "<option value='$l->lid'>$l->name</option>\n";
            }
            ?>
        </select>
        <br><br>
        Site access level:<br>
        <select name="ring">
            <?php
            foreach ($rings as $r => $desc) {
                if ($r >= $coach->ring) {
                    echo "<option value='$r' ".(($r == RING_COACH) ? 'SELECTED' : '').">$desc</option>\n";
                }
            }
            ?>
        </select>
        <br><br>
        <input type="hidden" name="type" value="mk_coach">
        <input type="submit" name="button" value="Create coach">
        </form>
    </div>
</div>

<div style='float:left;'> <!-- Outer -->
<div class="row"> <!-- Inner row 1 -->

<div class="boxCommon">
    <div class="boxTitle<?php echo T_HTMLBOX_ADMIN;?>">
        Change coach access level
    </div>
    <div class="boxBody">
        <form method="POST">
        Coach:<br>
        <select name="cid">
            <?php
            foreach ($coaches as $c) {
                echo "<option value='$c->coach_id'>$c->name</option>\n";
            }
            ?>
        </select>
        <br><br>
        Site access level:<br>
        <select name="ring">
            <?php
            foreach ($rings as $r => $desc) {
                echo "$r >= $coach->ring $coach->name";
                if ($r >= $coach->ring) {
                    echo "<option value='$r'>$desc</option>\n";
                }
            }
            ?>
        </select>
        <br><br>
        <input type="hidden" name="type" value="ch_ring">
        <input type="submit" name="button" value="Change privileges">
        </form>
    </div>
</div>

<div class="boxCommon">
    <div class="boxTitle<?php echo T_HTMLBOX_ADMIN;?>">
        Change coach password
    </div>
    <div class="boxBody">
        <form method="POST">
        Coach:<br>
        <select name="cid">
            <?php
            foreach ($coaches as $c) {
                echo "<option value='$c->coach_id'>$c->name</option>\n";
            }
            ?>
        </select>
        <br><br>
        Password:<br> <input type="password" name="passwd" size="20" maxlength="50"><br><br>
        <input type="hidden" name="type" value="ch_passwd">
        <input type="submit" name="button" value="Change password">
        </form>
    </div>
</div>

</div> <!-- END row 1 -->
<div class="row"> <!-- Intter row 2 -->

<div class="boxCommon">
    <div class="boxTitle<?php echo T_HTMLBOX_ADMIN;?>">
        Change associated league
    </div>
    <div class="boxBody">
        <form method="POST">
        Coach:<br>
        <select name="cid">
            <?php
            foreach ($coaches as $c) {
                echo "<option value='$c->coach_id'>$c->name</option>\n";
            }
            ?>
        </select>
        <br><br>
        League:<br>
        <select name="lid">
            <?php
            foreach ($leagues as $l) {
                echo "<option value='$l->lid'>$l->name</option>\n";
            }
            ?>
        </select>
        <br><br>
        <input type="hidden" name="type" value="ch_f_lid">
        <input type="submit" name="button" value="Change relation" <?php echo empty($coaches) ? 'DISABLED' : '';?>>
        </form>
    </div>
</div>

</div> <!-- END row 2 -->
</div> <!-- END Outer -->
<?php
