<?php

$manageable_tours = array();
foreach ($tours as $trid => $desc) {
    if ($leagues[$divisions[$desc['f_did']]['f_lid']]['ring'] == Coach::T_RING_LOCAL_ADMIN) {
        $manageable_tours[$trid] = $desc;
        $manageable_tours[$trid]['f_dname'] = $divisions[$desc['f_did']]['dname'];        
        $manageable_tours[$trid]['f_lid'] = $lid = $divisions[$desc['f_did']]['f_lid'];
        $manageable_tours[$trid]['f_lname'] = $leagues[$lid]['lname'];
    }
}

if (isset($_POST['type'])) {
    if (!in_array($trid = $_POST['trid'], array_keys($manageable_tours))) {
        status(false, 'You do not have the permissions to manage the selected tournament.');
        $_POST['type'] = 'QUIT';
    }
    else {
        $t = new Tour($trid);
    }
    
    switch ($_POST['type'])
    {
        case 'QUIT': break;
        
        case 'change':
            status($t->chRS($_POST['rs']) && $t->chType($_POST['ttype']) && $t->rename($_POST['tname']));
            break;

        case 'delete':
            $_OK = ($IS_GLOBAL_ADMIN && isset($_POST['delete']) && $_POST['delete']);
            status($_OK ? $t->delete(true) : false, $_OK ? '' : 'Please mark the agreement box before trying to delete a tournament. Also note that only site admins may use this feature.');
            break;

        case 'lock':
            status($t->setLocked(isset($_POST['lock']) && $_POST['lock']));
            break;
    }
}

title($lng->getTrn('menu/admin_menu/tour_man'));

?>

<div class="row">

<div class="boxCommon">
    <div class="boxTitle<?php echo T_HTMLBOX_ADMIN;?>">Lock/unlock tournament</div>
    <div class="boxBody">
    <form method="POST">
        <b>Tournament</b><br>
        <select name="trid">
            <?php
            foreach ($manageable_tours as $trid => $desc) {
                echo "<option value='$trid'>$desc[f_lname], $desc[f_dname]: $desc[tname]".(($desc['locked']) ? ' (is locked)' : '')."</option>\n";
            }
            ?>
        </select><br><br>
        <b>Set locked state to</b> (locked/unlocked = checked/unchecked):
        <input type="checkbox" name="lock" value="1">
        <br><br>
        <input type="hidden" name="type" value="lock">
        <input type="submit" value="OK" <?php echo (empty($manageable_tours)) ? 'DISABLED' : '';?>>
    </form>
    </div>
</div>
</div>
<div class="row">
<div class="boxCommon">
    <div class="boxTitle<?php echo T_HTMLBOX_ADMIN;?>">Edit existing tournament</div>
    <div class="boxBody">
    <form id='tourForm' method="POST">
        <br>
        <b>Edit tournament:</b><br>
        <select name="trid">
            <?php
            foreach ($manageable_tours as $trid => $desc) {
                echo "<option value='$trid'>$desc[f_lname], $desc[f_dname]: $desc[tname]</option>\n";
            }
            ?>
        </select>
        <br><br>
        <hr>
        <br>
        <b>New name:</b><br>
        <input type='text' name='tname' length='20' value=''>
        <br><br>
        <b>New ranking system:</b> (<?php echo $lng->getTrn('admin/prefixes');?>)<br>
        <select name='rs'>
        <?php
        global $hrs;
        foreach ($hrs as $idx => $r) {
            echo "<option value='$idx'>#$idx: ".Tour::getRSstr($idx)."</option>\n";
        }
        ?>
        </select>

        <br><br>
        <b>New tournament type:</b><br>
        <input type="radio" name="ttype" value="<?php echo TT_RROBIN;?>" > Round-Robin<br>
        <input type="radio" name="ttype" value="<?php echo TT_FFA;?>" CHECKED> FFA<br>
        <br>

        <input type="hidden" name="type" value="change">
        <input type="submit" value="Submit changes" <?php echo (empty($manageable_tours)) ? 'DISABLED' : '';?>>
        <br>
    </form>
    </div>
</div>
</div>
<div class="boxCommon">
    <div class="boxTitle<?php echo T_HTMLBOX_ADMIN;?>">Tournament deletion</div>
    <div class="boxBody">
    <form method="POST">
        <b>I wish to delete the following tournament</b><br>
        <select name="trid">
            <?php
            foreach ($manageable_tours as $trid => $desc) {
                echo "<option value='$trid'>$desc[f_lname], $desc[f_dname]: $desc[tname]</option>\n";
            }
            ?>
        </select>
        <br><br>
        <b>I have read the below advisement:</b>
        <input type="checkbox" name="delete" value="1">
        <br><br>
        <b><u>Advisement/warning:</u></b><br>
        This feature is only meant to be used for non-played or empty tournaments and test-tournaments.<br>
        If you decide to delete a proper tournament you should know that this will
        <br>
        <ul>
            <li>delete the tournament associated data forever (this includes team and player gained stats in the tournament).</li>
            <li>generate incorrect player statuses for those matches following (date-wise) the matches deleted. Re-saving/changing old matches may therefore be problematic.</li>
        </ul>
        <br>
        <?php echo $ONLY_FOR_GLOBAL_ADMIN;?><br>
        <br>
        <input type="hidden" name="type" value="delete">
        <input type="submit" value="Delete" <?php echo ($IS_GLOBAL_ADMIN && !empty($manageable_tours)) ? '' : 'DISABLED';?> onclick="if(!confirm('Are you absolutely sure you want to delete this tournament?')){return false;}">
    </form>
    </div>
</div>
<?php
