<?php

function setupManageableTours() {
	global $tours, $divisions, $leagues;
	$manageable_tours = array();
	foreach ($tours as $trid => $desc) {
		$lid = $divisions[$desc['f_did']]['f_lid'];
		if ($leagues[$lid]['ring'] == Coach::T_RING_LOCAL_ADMIN) {
			$manageable_tours[$trid]['tour'] = new Tour($trid);
			$manageable_tours[$trid]['name'] = $leagues[$lid]['lname'] . ", " . $divisions[$desc['f_did']]['dname'];
		}
	}
	return array_reverse($manageable_tours, true);
}

$manageable_tours = setupManageableTours();
$selectedTour = current($manageable_tours);
$selectedTour = $selectedTour['tour'];

if (isset($_POST['type'])) {
	$trid = isset($_POST['trid']) ? $_POST['trid'] : 0;
	if ($_POST['type'] == 'create' || $_POST['type'] == 'new') {
	} else if (!in_array($trid, array_keys($manageable_tours))) {
        status(false, 'You do not have the permissions to manage the selected tournament.');
        $_POST['type'] = 'QUIT';
    } else {
        $t = $manageable_tours[$trid]['tour'];
		$selectedTour = $t;

    }

    switch ($_POST['type'])
    {
        case 'QUIT': break;

        case 'change':
            if (get_magic_quotes_gpc()) {
                $_POST['tname'] = stripslashes($_POST['tname']);
            }
            $selectedTour->rs =$_POST['rs'];
            $selectedTour->type =$_POST['ttype'];
            $selectedTour->name =$_POST['tname'];
            $selectedTour->locked = isset($_POST['locked']) ? $_POST['locked'] : 0;
            $selectedTour->allow_sched = isset($_POST['allow_sched']) ? $_POST['allow_sched'] : 0;
            status($selectedTour->save());
            break;

        case 'delete':
            status($IS_GLOBAL_ADMIN ? $t->delete(true) : false, $IS_GLOBAL_ADMIN ? '' : 'Note that only site admins may use this feature.');
            break;

        case 'new':

        	$input = array();
            if (get_magic_quotes_gpc()) {
                $input['name'] = stripslashes($_POST['tname']);
            } else {
                $input['name'] = $_POST['tname'];
            }
            $input['did'] = $_POST['did'];
            $input['rs'] = $_POST['rs'];
            $input['type'] =$_POST['ttype'];
            $input['allow_sched'] = isset($_POST['allow_sched']) ? $_POST['allow_sched'] : 0;
            $input['teams'] = array();
			$lid = $divisions[$input['did']]['f_lid'];
			if (strlen($input['name']) == 0) {
		        status(false, 'You must enter a name for the tournament.');
			} else if ($leagues[$lid]['ring'] == Coach::T_RING_LOCAL_ADMIN) {
				status(Tour::create($input));
				setupGlobalVars(T_SETUP_GLOBAL_VARS__COMMON);
				$manageable_tours = setupManageableTours();
				$selectedTour = current($manageable_tours);
				$selectedTour = $selectedTour['tour'];
			} else {
		        status(false, 'You do not have the permissions to create tournaments in that league.');
			}
            break;

        case 'select':
            break;
    }
}

title($lng->getTrn('menu/admin_menu/tour_man'));

?>
<div class="row">

<div class="boxCommon">
    <div class="boxTitle<?php echo T_HTMLBOX_ADMIN;?>">Select tournament</div>
    <div class="boxBody">
    <form method="POST">
        <div align=center><input type="submit" value="Create New"/></div><br />
        <input type="hidden" name="type" value="create">
    </form>
    <form method="POST">
        <b>Edit: </b> <?php
        echo HTMLOUT::nodeList(T_NODE_TOURNAMENT,'trid',array('OTHER' => array('ring' => Coach::T_RING_LOCAL_ADMIN)),array(),array('sel_id'=>$selectedTour->tour_id,'hide_empty'=>array(T_NODE_DIVISION)));
        ?>
        <input type="hidden" name="type" value="select">
        <input type="submit" value="OK" <?php echo (empty($manageable_tours)) ? 'DISABLED' : '';?>>
    </form>
    </div>
</div>
</div>
<?php
	// Show the edit tournament boxes
	if (isset($trid) and $trid > 0) {
		$tourLongName = $manageable_tours[$selectedTour->tour_id]['name'] . ": " . $selectedTour->name;

?>
<div class="row">
<div class="boxCommon">
    <div class="boxTitle<?php echo T_HTMLBOX_ADMIN;?>">Edit <?php echo $tourLongName;?></div>
    <div class="boxBody">
<?php

$rnkText= $lng->getTrn('admin/prefixes');
echo<<< EOQ
    <form id='tourForm' method="POST">
        <br>
        <b>New name:</b><br>
        <input type='text' name='tname' length='20' value='$selectedTour->name'>
        <br><br>
        <b>New ranking system:</b> ($rnkText)<br>
        <select name='rs'>
EOQ;
        global $hrs;
        foreach ($hrs as $idx => $r) {
	        $selected = $idx == $selectedTour->rs;
            echo "<option value='$idx'";
            if ($selected) {
            	echo " SELECTED ";
            }
            echo ">#$idx: ".Tour::getRSstr($idx)."</option>\n";
        }
        ?>
        </select>

        <br><br>
        <b>New tournament type:</b><br>
        <input type="radio" name="ttype" value="<?php echo TT_RROBIN ?>" <?php ; if ($selectedTour->type == TT_RROBIN) {echo " CHECKED ";} ?>> Round-Robin<br>
        <input type="radio" name="ttype" value="<?php echo TT_FFA;  ?>" <?php ; if ($selectedTour->type == TT_FFA) {echo " CHECKED ";} ?>> FFA<br>
        <br><input type="checkbox" name="locked" value="1" <?php echo (($selectedTour->locked) ? "CHECKED" : "") ;?> /><b>Locked</b>
        <br><input type="checkbox" name="allow_sched" value="1" <?php echo ($selectedTour->allow_sched) ? "CHECKED" : "" ; ?> /><b>Coaches can schedule their own matches</b>
		<br><br>
        <input type="hidden" name="type" value="change">
        <input type="hidden" name="trid" value="<?php echo $selectedTour->tour_id; ?>">
        <input type="submit" value="Submit changes" <?php echo (empty($manageable_tours)) ? 'DISABLED' : '';?>>
        <br>
    </form>
    </div>
</div>
<div class="boxCommon">
    <div class="boxTitle<?php echo T_HTMLBOX_ADMIN;?>">Tournament deletion</div>
    <div class="boxBody">
    <form method="POST">

        <b>I wish to delete the following tournament: <?php echo $tourLongName;?></b>

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
        <input type="hidden" name="trid" value="<?php echo $selectedTour->tour_id; ?>">
        <input type="submit" value="Delete" <?php echo ($IS_GLOBAL_ADMIN && !empty($manageable_tours)) ? '' : 'DISABLED';?> onclick="if(!confirm('Are you absolutely sure you want to delete this tournament?')){return false;}">
    </form>
    </div>
</div>
</div>
<?php
} else {
?>
<div class="row">
<div class="boxCommon">
    <div class="boxTitle<?php echo T_HTMLBOX_ADMIN;?>">New Tournament</div>
    <div class="boxBody">
<?php
$rnkText= $lng->getTrn('admin/prefixes');
echo<<< EOQ
    <form id='tourForm' method="POST">
        <br>
        <b>Division:</b>
        <select name='did'>
EOQ;
        print_r($leagues);
        print_r($divisions);
        foreach ($divisions as $idx => $div) {
            $lid = $div['f_lid'];
			if ($leagues[$lid]['ring'] == Coach::T_RING_LOCAL_ADMIN) {
				echo "<option value=\"$idx\">";
				echo $leagues[$lid]['lname'] .  ": " . $div['dname'] ."</option>";
			}
        }
echo<<< EOQ
        </select>
        <br>
        <b>New name:</b>
        <input type='text' name='tname' length='20' value="">
        <br><br>
        <b>New ranking system:</b> ($rnkText)<br>
        <select name='rs'>
EOQ;
        global $hrs;
        foreach ($hrs as $idx => $r) {
	        $selected = $idx == $selectedTour->rs;
            echo "<option value='$idx'";
            if ($selected) {
            	echo " SELECTED ";
            }
            echo ">#$idx: ".Tour::getRSstr($idx)."</option>\n";
        }
?>
        </select>

        <br><br>
        <b>New tournament type:</b><br>
        <input type="radio" name="ttype" value="<?php echo TT_RROBIN ?>"> Round-Robin<br>
        <input type="radio" name="ttype" value="<?php echo TT_FFA;  ?>" checked> FFA<br>
        <br><input type="checkbox" name="allow_sched" value="1" /><b>Coaches can schedule their own matches</b>
		<br><br>
        <input type="hidden" name="type" value="new">
        <input type="submit" value="Create Tournament">
        <br>
    </form>
    </div>
</div>
</div>
<?php
}
