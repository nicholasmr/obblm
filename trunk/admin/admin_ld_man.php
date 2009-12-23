<?php
if (isset($_POST['type'])) {
    if (get_magic_quotes_gpc()) {
        foreach (array('name', 'location',) as $i) {
            $_POST[$i] = isset($_POST[$i]) ? stripslashes($_POST[$i]) : '';
        }
    }
    $l = (isset($_POST['lid'])) ? new League($_POST['lid']) : null;
    $d = (isset($_POST['did'])) ? new Division($_POST['did']) : null;
    switch ($_POST['type'])
    {
        case 'new_league':      status($GLOBAL_MANAGE && League::create($_POST['name'], $_POST['location'])); break;
        case 'new_division':    status(in_array($_POST['lid'], $league_ids) && Division::create($_POST['lid'], $_POST['name'])); break;
        case 'mod_league':      status(in_array($_POST['lid'], $league_ids) && $l->setName($_POST['name']) && $l->setLocation($_POST['location'])); break;
        case 'mod_division':    status($d->setName($_POST['name']) && $d->set_f_lid($_POST['lid'])); break;
        case 'del_league':      status($GLOBAL_MANAGE && $l->delete()); break;
        case 'del_division':    status($d->delete()); break;
    }
}

title($lng->getTrn('menu/admin_menu/ld_man'));
list($leagues,$divisions) = Coach::allowedNodeAccess(Coach::NODE_STRUCT__FLAT, $coach->f_lid, array(T_NODE_TOURNAMENT => array('locked' => 'locked', 'type' => 'type')));

?>
<b>Please note:</b> When modifying or deleting any of the below data seperation layers (divisions and leagues) a "syncAll()" re-synchronisation should be run afterwards from the <a href='index.php?section=admin&amp;subsec=cpanel'>OBBLM core panel</a>.
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
                    echo "<option value='$lid'>$desc[lname]</option>\n";
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
                    echo "<option value='$did'>$desc[dname] ($d->league_name)</option>\n";
                }
                ?>
            </select><br><br>
            Assigned to league:<br>
            <select name='lid'>
                <?php
                foreach ($leagues as $lid => $desc) {
                    echo "<option value='$lid'>$desc[lname]</option>\n";
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
                    echo "<option value='$did'>$desc[dname] ($d->league_name)</option>\n";
                }
                ?>
            </select><br><br>
            <input type='submit' value='Delete' <?php echo empty($divisions) ? ' DISABLED ' : '';?> onclick="if(!confirm('Warning: You should only delete devisions when no matches are assigned to it.')){return false;}">
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
            <input type="text" name="name"><br><br>
            Location:<br>
            <input type="text" name="location"><br><br>
            <input type='submit' value='Create'>
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
                    echo "<option value='$lid'>$desc[lname]</option>\n";
                }
                ?>
            </select><br><br>
            New name:<br>
            <input type="text" name="name"><br><br>
            New location:<br>
            <input type="text" name="location"><br><br>
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
            <select name='lid'>
                <?php
                foreach ($leagues as $lid => $desc) {
                    echo "<option value='$lid'>$desc[lname]</option>\n";
                }
                ?>
            </select><br><br>
            <input type='submit' value='Delete' <?php echo empty($leagues) ? ' DISABLED ' : '';?> onclick="if(!confirm('Warning: You should only delete leagues if empty, ie. no divisions/matches assigned to them.')){return false;}">
            <input type='hidden' name='type' value='del_league'>
            </form>
            </div>
        </div>
        </td>
    </tr>
    <!--
    @FIXME - NOT YET IMPLEMENTED
    <tr>
        <td valign='top' colspan="3">
        <div class="boxCommon">
            <div class="boxTitle<?php echo T_HTMLBOX_ADMIN;?>">Change league welcome message</div>
            <div class="boxBody">
            <form method="POST">
            League:<br>
            <select name='lid'>
                <?php
                foreach ($leagues as $lid => $desc) {
                    echo "<option value='$lid'>$desc[lname]</option>\n";
                }
                ?>
            </select><br><br>
            Message:<br>
            <textarea cols='80' rows="15" name='welcome'></textarea>
            <br><br>
            <input type='submit' value='Change welcome message' <?php echo empty($leagues) ? ' DISABLED ' : '';?>>
            <input type='hidden' name='type' value='ch_welcome'>
            </form>
            </div>
        </div>
        </td>
    </tr>
    -->
</table>
<?php
