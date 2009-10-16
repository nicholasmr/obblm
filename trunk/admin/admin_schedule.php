<?php
if (isset($_POST['button'])) {

    $all_teams = get_rows('teams', array('team_id', 'name')); // Fast team objects.
    $team_ids = array_values(array_map(create_function('$t', 'return $t->team_id;'), array_filter($all_teams, create_function('$t', 'return isset($_POST[$t->team_id]);'))));
    $teamsCount = count($team_ids);
    
    /*
        Input validation.
    */
    
    // Shortcut booleans:
    $mkNewFFA       = ($_POST['type'] == TT_FFA && $_POST['existTour'] == -1);
    $addMatchToFFA  = ($_POST['type'] == TT_FFA && $_POST['existTour'] != -1);
    $nameSet        = (isset($_POST['name']) && !empty($_POST['name']));

    // Error condition definitions.
    $errors = array(
        # "Halt if bool is true" => "Error message"
        array(!$nameSet && !$addMatchToFFA, "Please fill out the tournament name."),
        array($nameSet && get_alt_col('tours', 'name', $_POST['name'], 'tour_id'), "Tournament name already in use."),
        array($coach->ring == RING_COM && (
            $mkNewFFA && $coach->com_lid != get_parent_id(T_NODE_DIVISION, $_POST['did'], T_NODE_LEAGUE)
            ||
            $addMatchToFFA && $coach->com_lid != get_parent_id(T_NODE_TOURNAMENT, $_POST['existTour'], T_NODE_LEAGUE) 
        ), 'You are not allowed to schedule matches in that league.'),
        array($_POST['type'] == TT_FFA && $teamsCount != 2, 'Please select only 2 teams'),
        array($_POST['type'] == TT_RROBIN && $teamsCount < 3, 'Please select at least 3 teams'),
    );
    
    // Print errors.
    $STATUS = true;
    foreach ($errors as $e) {
        if ($e[0]) {
            status(false, $e[1]."<br>\n");
            $STATUS = false;        
        }
    }

    /*
        Input modification.
    */
    
    if ($nameSet && get_magic_quotes_gpc()) {
        $_POST['name'] = stripslashes($_POST['name']);
    }
    // Reverse pair-up for FFA match?
    if ($_POST['type'] == TT_FFA && isset($_POST['reverse']) && $_POST['reverse']) {
        $team_ids = array_reverse($team_ids);
    }
    // When creating a new FFA tour the "rounds" input is intepreted as the round number of the initial match being created in the new tour.
    if ($mkNewFFA) {
        $_POST['rounds'] = $_POST['round'];
    }

    /*
        Create the requested matches.
    */
    if ($STATUS) { # Did all input pass verification?
        // Add match to existing FFA?
        if ($addMatchToFFA) {
            $rnd = (!isset($_POST['round'])) ? 1 : (int) $_POST['round'];
            status(Match::create(array('team1_id' => $team_ids[0], 'team2_id' => $team_ids[1], 'round' => $rnd, 'f_tour_id' => (int) $_POST['existTour'])));
        }
        // Create new tour...
        else {
            status(Tour::create(array('did' => $_POST['did'], 'name' => $_POST['name'], 'type' => (int) $_POST['type'], 'rs' => (int) $_POST['rs'], 'teams' => $team_ids, 'rounds' => $_POST['rounds'])));
        }
    }
}

title($lng->getTrn('menu/admin_menu/schedule'));
?>
<script language="JavaScript" type="text/javascript">

    var TT_FFA    = <?php echo TT_FFA; ?>;
    var TT_RROBIN = <?php echo TT_RROBIN; ?>;
    
    function chTour(t)
    {
        /*
            Handles the disabling and enabling of form elements depending on what tournament type is chosen.
            
            Expects TT_* PHP constants to be globally available to javascript.
        */

        n = document.tourForm['name'];
        r = document.tourForm['rounds'];
        FFA = document.getElementById(BOX_FFA);
        RR = document.getElementById(BOX_RR);

        if (t == TT_FFA) {
            n.disabled = false;
            r.disabled = true;
            slideDown(BOX_FFA);
            slideUp(BOX_RR);
            
            et2 = document.tourForm['existTour'];
            chFFATour(et2.options[et2.selectedIndex].value); // Re-load state of fields.
        }
        else if (t == TT_RROBIN) {
            n.disabled = false;
            r.disabled = false;
            slideDown(BOX_RR);
            slideUp(BOX_FFA);
            
            chFFATour(-1); // Re-load state of fields.
        }
        
        return true;
    }

    function chFFATour(trid)
    {
        /*
            Disables fields "name", "ranking system" and such when adding a FFA match to an existing tour, in which case they are not needed.
        */
        
        var val = !(trid == -1);
        document.tourForm['did'].disabled  = val;
        document.tourForm['name'].disabled = val;
        document.tourForm['rs'].disabled   = val;
    }
</script>

<?php
echo $lng->getTrn('admin/schedule/create_leag_div').'<br>';
echo $lng->getTrn('admin/schedule/multiple_schedule');
$divisions = Division::getDivisions();
foreach ($divisions as $d) {
    $d->dispName = "$d->league_name: $d->name";
}
objsort($divisions, array('+dispName'));
?><br><br>

<form method="POST" name="tourForm">
    <b><?php echo $lng->getTrn('admin/schedule/tour_type');?>:</b><br>
    <input type="radio" onClick="chTour(this.value);" name="type" value="<?php echo TT_FFA;?>" CHECKED> FFA match <i>(Free For All a.k.a. "open league format" - creates a single match)</i><br>
    <input type="radio" onClick="chTour(this.value);" name="type" value="<?php echo TT_RROBIN;?>"> Round-Robin<br>
    <br>
    <table>
    <tr>
    <td valign='top'>
        <b><?php echo $lng->getTrn('common/division');?>:</b><br>
        <select name='did'>
            <?php
            foreach ($divisions as $d) {
                if ($coach->ring == RING_SYS || $coach->ring == RING_COM && $coach->com_lid == $d->f_lid) {
                    echo "<option value='$d->did'>$d->dispName</option>\n";
                }
            }
            ?>
        </select>
        <br><br>
        <b><?php echo $lng->getTrn('admin/schedule/tour_name');?>:</b><br>
        <input type="text" name="name" size="30" maxlength="50">
        <br><br>
        <b><?php echo $lng->getTrn('admin/schedule/rank_sys');?>:</b> (<?php echo $lng->getTrn('admin/prefixes');?>)<br>
        <select name='rs'>
        <?php
        foreach (Tour::getRSSortRules(false, true) as $idx => $r) {
            echo "<option value='$idx'>RS #$idx | $r</option>\n";
        }
        ?>
        </select>
    </td>
    <td>
        &nbsp;&nbsp;&nbsp;&nbsp;
    </td>
    <td valign='top'>
        <?php
        $body = '';
        // FFA settings.
        $body .= '<b>'.$lng->getTrn('admin/schedule/FFA_settings').'</b><br><hr><br>';
        $body .= '<b>'.$lng->getTrn('admin/schedule/add_match').'</b><br>';
        $body .= '<select name="existTour" onChange="chFFATour(this.options[this.selectedIndex].value);">';
        $body .= '<optgroup label="New FFA">';
        $body .= "<option value='-1'>".$lng->getTrn('admin/schedule/new_tour').'</option>';
        $body .= '</optgroup>';
        $body .= '<optgroup label="Existing FFA">';
        foreach (Tour::getTours() as $t)
            if ($t->type == TT_FFA && ($coach->ring == RING_SYS || $coach->ring == RING_COM && $coach->com_lid == get_alt_col('divisions', 'did', $t->f_did, 'f_lid')))
                $body .= "<option value='$t->tour_id' ".(($t->locked) ? 'DISABLED' : '').">$t->name".(($t->locked) ? '&nbsp;&nbsp;(LOCKED)' : '')."</option>\n";
        $body .= '</optgroup>';
        $body .= '</select>';
        $body .= '<br><br>';
        $body .= '<b>'.$lng->getTrn('admin/schedule/as_type').'</b><br>';
        $body .= '<select name="round">';
        foreach (array(RT_FINAL => 'Final', RT_3RD_PLAYOFF => '3rd play-off', RT_SEMI => 'Semi final', RT_QUARTER => 'Quarter final', RT_ROUND16 => 'Round of 16 match') as $r => $d) {
            $body .= "<option value='$r'>$d</option>\n";
        }
        $pure_rounds = array();
        for ($i=1;$i<30;$i++) $pure_rounds[$i] = "Round #$i match";
        foreach ($pure_rounds as $r => $d) {
            $body .= "<option value='$r'>$d</option>\n";
        }
        $body .= '</select>';
        $body .= '<br><br>';
        $body .= '<b>'.$lng->getTrn('admin/schedule/as_reverse').'</b><br>';
        $body .= '<input type="checkbox" name="reverse" value="1">';
        $BOX_FFA = HTMLOUT::assistantBox($body);
        
        // Round robin seed multiplier.
        $body = '';
        $body .= '<b>'.$lng->getTrn('admin/schedule/RR_settings').'</b><br><hr><br>';
        $body .= $lng->getTrn('admin/schedule/rrobin_rnds')."<br>N = <select name='rounds'>";
        foreach (range(1, 10) as $i) $body .= "<option value='$i'>$i</option>\n";
        $body .= "</select></div>\n";
        $BOX_RR = HTMLOUT::assistantBox($body, 'display:none;');
        ?>
    </td>
    </tr>
    </table>
    <br>
    <b><?php echo $lng->getTrn('admin/schedule/participants');?>:</b><br>
    <?php
    $teams = Team::getTeams();
    $entriesToPrint = array();
    switch ($settings['scheduling_list_style'])
    {
        case 2:
            objsort($teams, array('+name'));
            $entriesToPrint = array_map(create_function('$t', 'return "<input type=\'checkbox\' name=\'$t->team_id\' value=\'$t->team_id\'><b>$t->name</b> ($t->coach_name)";'), $teams);
            break;
        # case 1:
        default: 
            objsort($teams, array('+coach_name', '+name'));
            $entriesToPrint = array_map(create_function('$t', 'return "<input type=\'checkbox\' name=\'$t->team_id\' value=\'$t->team_id\'>$t->coach_name\'s $t->name";'), $teams);
            break;
    }
    print implode("<br\n", $entriesToPrint);
    ?>
    <br>
    <hr align="left" width="200px">
    <input type="submit" name="button" value="<?php echo $lng->getTrn('common/create');?>" <?php echo (empty($divisions) ? 'DISABLED' : '');?>>
</form>

<script language="JavaScript" type="text/javascript">
    var BOX_FFA = '<?php echo $BOX_FFA;?>';
    var BOX_RR = '<?php echo $BOX_RR;?>';
    chTour(<?php echo TT_FFA;?>);
</script>

<?php
