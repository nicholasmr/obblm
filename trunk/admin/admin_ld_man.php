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
        case 'new_league':      status(League::create($_POST['name'], $_POST['location'])); break;
        case 'new_division':    status(Division::create($_POST['lid'], $_POST['name'])); break;
        case 'mod_league':      status($l->setName($_POST['name']) && $l->setLocation($_POST['location'])); break;
        case 'mod_division':    status($d->setName($_POST['name']) && $d->set_f_lid($_POST['lid'])); break;
        case 'del_league':      status($l->delete()); break;
        case 'del_division':    status($d->delete()); break;
    }
}

title($lng->getTrn('menu/admin_menu/ld_man'));
$leagues = League::getLeagues();
$divisions = Division::getDivisions();

?>
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
                foreach ($leagues as $l) {
                    echo "<option value='$l->lid'>$l->name</option>\n";
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
                foreach ($divisions as $d) {
                    echo "<option value='$d->did'>$d->name ($d->league_name)</option>\n";
                }
                ?>
            </select><br><br>
            Assigned to league:<br>
            <select name='lid'>
                <?php
                foreach ($leagues as $l) {
                    echo "<option value='$l->lid'>$l->name</option>\n";
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
                foreach ($divisions as $d) {
                    echo "<option value='$d->did'>$d->name ($d->league_name)</option>\n";
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
                foreach ($leagues as $l) {
                    echo "<option value='$l->lid'>$l->name</option>\n";
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
                foreach ($leagues as $l) {
                    echo "<option value='$l->lid'>$l->name</option>\n";
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
</table>
<?php
