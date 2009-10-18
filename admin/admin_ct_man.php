<?php

title($lng->getTrn('menu/admin_menu/ct_man'));

if (isset($_POST['type'])) {
    switch ($_POST['type'])
    {
        case 'rt':
            $t = new Team($_POST['id']);
            status($t->setRetired(!(isset($_POST['unretire']) && $_POST['unretire'])));
            break;

        case 'rc':
            $c = new Coach($_POST['id']);
            status($c->setRetired(!(isset($_POST['unretire']) && $_POST['unretire'])));
            break;

        case 'dt':
            $t = new Team($_POST['id']);
            status($t->delete());
            break;

        case 'dc':
            $c = new Coach($_POST['id']);
            status($c->delete());
            break;
    }
}

$teams = Team::getTeams();
objsort($teams, array('+name'));
$coaches = Coach::getCoaches();
objsort($coaches, array('+name'));

?>
<table>
    <tr>
        <td colspan='2'>
            <b>Please note:</b>
            <ul>
                <li>For the sake of keeping league statistics intact you are not allowed to delete teams or coaches which have played matches.</li>
                <li>Once retired a coach cannot login and teams are no longer manageable/editable from their team pages.</li>
            </ul>
        </td>
    </tr>
    <tr>
        <td valign='top'>
            <div class="boxCommon">
                <div class="boxTitle<?php echo T_HTMLBOX_ADMIN;?>">Retire team</div>
                <div class="boxBody">
                <form method="POST">
                <select name='id'>
                    <?php
                    foreach ($teams as $t) {
                        echo "<option value='$t->team_id' ".(($t->is_retired) ? 'style="background-color: orange;"' : '').">$t->name</option>\n";
                    }
                    ?>
                </select><br><br>
                Un-retire (ie. regret retiring) instead of retiring? <input type='checkbox' name='unretire' value='1'><br><br>
                <input type='submit' value='Retire/unretire'>
                <input type='hidden' name='type' value='rt'>
                </form>
                </div>
            </div>
        </td>
        <td valign='top'>
            <div class="boxCommon">
                <div class="boxTitle<?php echo T_HTMLBOX_ADMIN;?>">Delete team</div>
                <div class="boxBody">
                <form method="POST">
                <select name='id'>
                    <?php
                    foreach ($teams as $t) {
                        if ($t->isDeletable())
                            echo "<option value='$t->team_id'>$t->name</option>\n";
                    }
                    ?>
                </select>
                <br><br>
                <input type='submit' value='Delete' onclick="if(!confirm('Are you sure you want to delete? This can NOT be undone.')){return false;}">
                <input type='hidden' name='type' value='dt'>
                </form>
                </div>
            </div>
        </td>
    </tr>
    <tr><td colspan="2"><hr></td></tr>
    <tr>
        <td valign='top'>
            <div class="boxCommon">
                <div class="boxTitle<?php echo T_HTMLBOX_ADMIN;?>">Retire coach</div>
                <div class="boxBody">
                <form method="POST">
                <select name='id'>
                    <?php
                    foreach ($coaches as $c) {
                        echo "<option value='$c->coach_id' ".(($c->retired) ? 'style="background-color: orange;"' : '').">$c->name</option>\n";
                    }
                    ?>
                </select><br><br>
                Un-retire (ie. regret retiring) instead of retiring? <input type='checkbox' name='unretire' value='1'><br><br>
                <input type='submit' value='Retire/unretire'>
                <input type='hidden' name='type' value='rc'>
                </form>
                </div>
            </div>
        </td>
        <td valign='top'>
            <div class="boxCommon">
                <div class="boxTitle<?php echo T_HTMLBOX_ADMIN;?>">Delete coach</div>
                <div class="boxBody">
                <form method="POST">
                <select name='id'>
                    <?php
                    foreach ($coaches as $c) {
                        if ($c->isDeletable())
                            echo "<option value='$c->coach_id'>$c->name</option>\n";
                    }
                    ?>
                </select>
                <br><br>
                <input type='submit' value='Delete' onclick="if(!confirm('Are you sure you want to delete? This can NOT be undone.')){return false;}">
                <input type='hidden' name='type' value='dc'>
                </form>
                </div>
            </div>
        </td>
    </tr>
</table>
<?php
