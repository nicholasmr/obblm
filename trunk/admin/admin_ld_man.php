<?php
if (isset($_POST['type'])) {
    if (get_magic_quotes_gpc()) {
        foreach (array('name', 'location',) as $i) {
            $_POST[$i] = isset($_POST[$i]) ? stripslashes($_POST[$i]) : '';
        }
    }
    if (isset($_POST['lid']) && (!isset($leagues[$_POST['lid']]) || $leagues[$_POST['lid']]['ring'] != Coach::T_RING_LOCAL_ADMIN) || 
        isset($_POST['did']) && (!isset($divisions[$_POST['did']]) || $leagues[$divisions[$_POST['did']]['f_lid']]['ring'] != Coach::T_RING_LOCAL_ADMIN)
       ) {
        status(false, 'You do not have permissions to administrate the chosen division or league');
        $_POST['type'] = 'QUIT';
    }
    else {
        $l = (isset($_POST['lid'])) ? new League($_POST['lid']) : null;
        $d = (isset($_POST['did'])) ? new Division($_POST['did']) : null;    
    }

    switch ($_POST['type'])
    {
        case 'QUIT': break;
        case 'new_league':      status($IS_GLOBAL_ADMIN && League::create($_POST['name'], $_POST['location'], isset($_POST['tie_teams']) && $_POST['tie_teams'])); break;
        case 'new_division':    status(Division::create($_POST['lid'], $_POST['name'])); break;
        case 'mod_league':      status($l->setName($_POST['name']) && $l->setLocation($_POST['location']) && $l->setTeamDivisionTies(isset($_POST['tie_teams']) && $_POST['tie_teams'])); break;
        case 'mod_division':    status($d->setName($_POST['name'])); break;
        case 'del_league':      status($IS_GLOBAL_ADMIN && $l->delete()); break;
        case 'del_division':    status($IS_GLOBAL_ADMIN && $d->delete()); break;
    }
    
    setupGlobalVars(T_SETUP_GLOBAL_VARS__COMMON); # Re-load $leagues, $divisions ...
}

title($lng->getTrn('menu/admin_menu/ld_man'));

?>
<b>Please note:</b> When deleting any of the below data seperation layers (divisions and leagues) a "syncAll()" re-synchronisation should be run afterwards from the <a href='index.php?section=admin&amp;subsec=cpanel'>OBBLM core panel</a>.
<table>
    <tr>
        <td valign='top'>
        <div class="boxCommon">
            <div class="boxTitle<?php echo T_HTMLBOX_ADMIN;?>">Create division</div>
            <div class="boxBody">
            <form method="POST">
            In league:<br>
            <select name='lid'>
                <?php
                foreach ($leagues as $lid => $desc) {
                    if ($desc['ring'] == Coach::T_RING_LOCAL_ADMIN) {
                        echo "<option value='$lid'>$desc[lname]</option>\n";
                    }
                }
                ?>
            </select><br><br>
            Name:<br>
            <input type="text" name="name"><br><br>
            <input type='submit' value='Create' <?php echo empty($leagues) ? ' DISABLED ' : '';?>>
            <input type='hidden' name='type' value='new_division'>
            </form>
            </div>
        </div>
        </td>
        <td valign='top'>
        <div class="boxCommon">
            <div class="boxTitle<?php echo T_HTMLBOX_ADMIN;?>">Modify division</div>
            <div class="boxBody">
            <form method="POST">
            Division:<br>
            <select name='did'>
                <?php
                foreach ($divisions as $did => $desc) {
                    if ($leagues[$desc['f_lid']]['ring'] == Coach::T_RING_LOCAL_ADMIN) {
                        echo "<option value='$did'>".$leagues[$desc['f_lid']]['lname'].": $desc[dname]</option>\n";
                    }
                }
                ?>
            </select><br><br>
            New name:<br>
            <input type="text" name="name"><br><br>
            <input type='submit' value='Modify' <?php echo empty($divisions) ? ' DISABLED ' : '';?>>
            <input type='hidden' name='type' value='mod_division'>
            </form>
            </div>
        </div>
        </td>
        <td valign='top'>
        <div class="boxCommon">
            <div class="boxTitle<?php echo T_HTMLBOX_ADMIN;?>">Delete division</div>
            <div class="boxBody">
            <form method="POST">
            Division:<br>
            <select name='did'>
                <?php
                foreach ($divisions as $did => $desc) {
                    if ($leagues[$desc['f_lid']]['ring'] == Coach::T_RING_LOCAL_ADMIN) {
                        echo "<option value='$did'>".$leagues[$desc['f_lid']]['lname'].": $desc[dname]</option>\n";
                    }
                }
                ?>
            </select><br><br>
            <?php echo $ONLY_FOR_GLOBAL_ADMIN;?><br><br>
            <input type='submit' value='Delete' <?php echo (empty($divisions) || !$IS_GLOBAL_ADMIN) ? ' DISABLED ' : '';?> onclick="if(!confirm('Warning: You should only delete devisions when no matches are assigned to it.')){return false;}">
            <input type='hidden' name='type' value='del_division'>
            </form>
            </div>
        </div>
        </td>
    </tr>
    <tr><td colspan="3"><hr></td></tr>
    <tr>
        <td valign='top'>
        <div class="boxCommon">
            <div class="boxTitle<?php echo T_HTMLBOX_ADMIN;?>">Create league</div>
            <div class="boxBody">
            <form method="POST">
            Name:<br>
            <input type="text" name="name" <?php echo $IS_GLOBAL_ADMIN ? '' : 'DISABLED';?>><br><br>
            Location:<br>
            <input type="text" name="location" <?php echo $IS_GLOBAL_ADMIN ? '' : 'DISABLED';?>><br><br>
            Tie teams to divisions?
            <input type="checkbox" CHECKED name="tie_teams" <?php echo $IS_GLOBAL_ADMIN ? '' : 'DISABLED';?>><br><br>
            <?php echo $ONLY_FOR_GLOBAL_ADMIN;?><br><br>
            <input type='submit' value='Create' <?php echo $IS_GLOBAL_ADMIN ? '' : 'DISABLED';?>>
            <input type='hidden' name='type' value='new_league'>
            </form>
            </div>
        </div>
        </td>
        <td valign='top'>
        <div class="boxCommon">
            <div class="boxTitle<?php echo T_HTMLBOX_ADMIN;?>">Modify league</div>
            <div class="boxBody">
            <form method="POST">
            League:<br>
            <select name='lid'>
                <?php
                foreach ($leagues as $lid => $desc) {
                    if ($desc['ring'] == Coach::T_RING_LOCAL_ADMIN) {
                        echo "<option value='$lid'>$desc[lname]</option>\n";
                    }
                }
                ?>
            </select><br><br>
            New name:<br>
            <input type="text" name="name"><br><br>
            New location:<br>
            <input type="text" name="location"><br><br>
            Tie teams to divisions?
            <input type="checkbox" CHECKED name="tie_teams"><br><br>
            <input type='submit' value='Modify' <?php echo empty($leagues) ? ' DISABLED ' : '';?>>
            <input type='hidden' name='type' value='mod_league'>
            </form>
            </div>
        </div>
        </td>
        <td valign='top'>
        <div class="boxCommon">
            <div class="boxTitle<?php echo T_HTMLBOX_ADMIN;?>">Delete league</div>
            <div class="boxBody">
            <form method="POST">
            League:<br>
            <select name='lid' <?php echo (empty($leagues) || !$IS_GLOBAL_ADMIN) ? ' DISABLED ' : '';?>>
                <?php
                foreach ($leagues as $lid => $desc) {
                    echo "<option value='$lid'>$desc[lname]</option>\n";
                }
                ?>
            </select><br><br>
            <?php echo $ONLY_FOR_GLOBAL_ADMIN;?><br><br>
            <input type='submit' value='Delete' <?php echo (empty($leagues) || !$IS_GLOBAL_ADMIN) ? ' DISABLED ' : '';?> onclick="if(!confirm('Warning: You should only delete leagues if empty, ie. no divisions/matches assigned to them.')){return false;}">
            <input type='hidden' name='type' value='del_league'>
            </form>
            </div>
        </div>
        </td>
    </tr>
</table>
<?php
