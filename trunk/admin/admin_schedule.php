<?php
if (isset($_POST['button'])) {

    /*
        Input validation.
    */
    
    // Teams
    $team_ids = explode(',', $_POST['teams']);
    $teamsCount = count($team_ids);
    
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
            $mkNewFFA && $coach->f_lid != get_parent_id(T_NODE_DIVISION, $_POST['did'], T_NODE_LEAGUE)
            ||
            $addMatchToFFA && $coach->f_lid != get_parent_id(T_NODE_TOURNAMENT, $_POST['existTour'], T_NODE_LEAGUE) 
        ), 'You are not allowed to schedule matches in that league.'),
        array($_POST['type'] == TT_RROBIN && $teamsCount < 3, 'Please select at least 3 teams'),
        array($_POST['type'] == TT_FFA && ($teamsCount % 2 != 0), 'Please select an even number of teams'),
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
    // Shuffle team list if multiple teams are scheduled to play a FFA.
    if ($_POST['type'] == TT_FFA && $teamsCount > 2) {
        shuffle($team_ids);
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
            $status = true;
            for ($i = 0; $i < $teamsCount/2; $i++) {
                $status &= Match::create(array('team1_id' => $team_ids[$i*2], 'team2_id' => $team_ids[$i*2+1], 'round' => $rnd, 'f_tour_id' => (int) $_POST['existTour']));
            }
            status($status);
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
            slideUp(BOX_RR);
            slideDown(BOX_FFA);
            
            et2 = document.tourForm['existTour'];
            chFFATour(et2.options[et2.selectedIndex].value); // Re-load state of fields.
        }
        else if (t == TT_RROBIN) {
            n.disabled = false;
            r.disabled = false;
            slideUp(BOX_FFA);
            slideDown(BOX_RR);
            
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
    
    /*
        Team list related.
    */

    function addTeam(tid, name) 
    {
        TL.options[TL.selectedIndex] = null;
        SL.options[SL.length] = new Option(name, tid);
        SL.size++;
        
        TEAMS.value = TEAMS.value.concat( ((TEAMS.value.length == 0) ? '' : ',')+tid );
    }
    
    function removeLastTeam()
    {
        var last = SL.options[SL.length-1];
        SL.options[SL.length-1] = null;
        TL.options[TL.length] = new Option(last.text, last.value);
        SL.size--;

        TEAMS.value = TEAMS.value.substr(0, TEAMS.value.lastIndexOf(','));
//        alert('"'+TEAMS.value+'"');
    }
    
</script>

<?php
$result = mysql_query("SELECT COUNT(*) FROM leagues,divisions WHERE f_lid = lid");
if (($row = mysql_fetch_row($result)) && $row[0] == 0) {
    fatal($lng->getTrn('admin/schedule/create_LD'));
}
HTMLOUT::helpBox($lng->getTrn('admin/schedule/help'), $lng->getTrn('common/needhelp'));
list($leagues,$divisions,$tours) = Coach::allowedNodeAccess(Coach::NODE_STRUCT__FLAT, $coach->f_lid, array(T_NODE_TOURNAMENT => array('locked' => 'locked', 'type' => 'type')));
?><br>
<form method="POST" name="tourForm">
    <table>
    <tr>
    <td valign='top'>
        <b><?php echo $lng->getTrn('admin/schedule/tour_type');?>:</b><br>
        <input type="radio" onClick="chTour(this.value);" name="type" value="<?php echo TT_FFA;?>" CHECKED> <?php echo $lng->getTrn('admin/schedule/TT_FFA');?><br>
        <input type="radio" onClick="chTour(this.value);" name="type" value="<?php echo TT_RROBIN;?>"> <?php echo $lng->getTrn('admin/schedule/TT_RR');?><br>
        <br>
        <b><?php echo $lng->getTrn('common/division');?>:</b><br>
        <select name='did'>
            <?php
            foreach ($divisions as $did => $desc) {
                echo "<option value='$did'>$desc[dname]</option>\n";
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
        global $hrs;
        foreach ($hrs as $idx => $r) {
            echo "<option value='$idx'>#$idx: ".Tour::getRSstr($idx)."</option>\n";
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
        foreach ($tours as $trid => $desc) {
            if ($desc['type'] == TT_FFA) {
                $body .= "<option value='$trid' ".(($desc['locked']) ? 'DISABLED' : '').">$desc[tname]".(($desc['locked']) ? '&nbsp;&nbsp;(LOCKED)' : '')."</option>\n";
            }
        }
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
    <b><?php echo $lng->getTrn('admin/schedule/teams_avail');?>:</b><br>
    <?php
    $query = "SELECT team_id, teams.name AS 'name', f_cname FROM teams, coaches WHERE teams.owned_by_coach_id = coaches.coach_id ".(($coach->f_lid) ? "AND coaches.f_lid = $coach->f_lid" : '');
    $result = mysql_query($query);
    $teams = array();
    while ($o = mysql_fetch_object($result)) {
        $teams[] = $o;
    }
    $entriesToPrint = array();
    switch ($settings['scheduling_list_style'])
    {
        case 2:
            objsort($teams, array('+name'));
            $entriesToPrint = array_map(create_function('$t', 'return "<option value=\'$t->team_id\'>$t->name ($t->f_cname)</option>";'), $teams);
            break;
        # case 1:
        default: 
            objsort($teams, array('+f_cname', '+name'));
            $entriesToPrint = array_map(create_function('$t', 'return "<option value=\'$t->team_id\'>$t->f_cname\'s $t->name</option>";'), $teams);
            break;
    }
    print "<select id='teamlist' name='teamlist' ".(empty($teams) ? 'DISABLED' : '').">\n".implode("\n", $entriesToPrint)."\n</select>\n<br>";
    print "<a href='javascript:void(0);' onClick='var opt = TL.options[TL.selectedIndex]; addTeam(opt.value, opt.text);'>".$lng->getTrn('common/add')."</a><br><br>";
    print "<b>".$lng->getTrn('admin/schedule/teams_selected').":</b><br>";
    print "<select id='selectedlist' name='selectedlist' size='2' MULTIPLE></select>\n<br>";
    print "<a href='javascript:void(0);' onClick='removeLastTeam();'>".$lng->getTrn('common/remove')."</a><br>";
    print "<input type='hidden' id='teams' name='teams' value=''>";
    ?>
    <br>
    <hr align="left" width="200px">
    <input type="submit" name="button" value="<?php echo $lng->getTrn('common/create');?>" <?php echo (empty($divisions) || empty($teams) ? 'DISABLED' : '');?>>
</form>

<script language="JavaScript" type="text/javascript">
    var BOX_FFA = '<?php echo $BOX_FFA;?>';
    var BOX_RR = '<?php echo $BOX_RR;?>';
    chTour(<?php echo TT_FFA;?>);
    
    var TL = document.getElementById('teamlist');
    var SL = document.getElementById('selectedlist');
    var TEAMS = document.getElementById('teams');
</script>

<?php
