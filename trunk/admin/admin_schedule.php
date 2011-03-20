<?php

$addMatchToFFA = false; # Must declare here since we use it later to set default FFA tour selection.

if (isset($_POST['button'])) {

    /*
        Input validation.
    */
    
    // Teams
    $team_ids = explode(',', $_POST['teams']);
    $teamsCount = count($team_ids);

    // Shortcut booleans:
    $mkNewFFA       = ($_POST['type'] == 'FFA_TOUR');
    $addMatchToFFA  = ($_POST['type'] == 'FFA_SINGLE');
    $nameSet        = (isset($_POST['name']) && !empty($_POST['name']) && !ctype_space($_POST['name']));

    /* Error condition definitions. */
    
    /* 
        Here we test for illegal pair-ups due to league and division relations.
        Normally Match::create() does this too, but we don't want to end up creating a long list of matches first, but 
        then have to abort because (for example) the last pair of teams are an illegal paring.
    */
    $teams_OK['l'] = $teams_OK['d'] = true;
    $lid = ($GOT_DID = ($_POST['type'] == 'RR_TOUR' || $mkNewFFA)) ? get_parent_id(T_NODE_DIVISION, $_POST['did'], T_NODE_LEAGUE) : get_parent_id(T_NODE_TOURNAMENT, $_POST['existTour'], T_NODE_LEAGUE);
    $did = ($GOT_DID) ? $_POST['did'] : get_parent_id(T_NODE_TOURNAMENT, $_POST['existTour'], T_NODE_DIVISION);
    $TIE_TEAMS = get_alt_col('leagues', 'lid', $lid, 'tie_teams');
    foreach ($team_ids as $tid) {
        $query = "SELECT (t.f_did = $did) AS 'in_did', (t.f_lid = $lid) AS 'in_lid' FROM teams AS t WHERE t.team_id = $tid";
        $result = mysql_query($query);
        $state = mysql_fetch_assoc($result);
        if (!$state['in_lid']) {
            $teams_OK['l'] = false;
            break;
        }
        if ($TIE_TEAMS && !$state['in_did']) {
            $teams_OK['d'] = false;
            break;
        }
    }

    $errors = array(
        # "Halt if bool is true" => "Error message"
        array(!$nameSet && !$addMatchToFFA, "Please fill out the tournament name."),
        array($nameSet && get_alt_col('tours', 'name', $_POST['name'], 'tour_id'), "Tournament name already in use."),
        array(!$teams_OK['d'], 'You may not schedule matches between teams from different divisions in the selected league.'),
        array(!$teams_OK['l'], 'You may not schedule matches between teams from different leagues OR one or more of the selected teams are not from the chosen league.'),
        array($leagues[$lid]['ring'] != Coach::T_RING_LOCAL_ADMIN, 'You do not have the rights to schedule matches in the selected league.'),
        array($_POST['type'] == 'RR_TOUR' && $teamsCount < 3, 'Please select at least 3 teams'),
        array($_POST['type'] == 'FFA_TOUR' && ($teamsCount % 2 != 0), 'Please select an even number of teams'),
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
    if ($_POST['type'] == 'FFA_TOUR' && $teamsCount > 2) {
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
                list($exitStatus, $mid) = Match::create(array('team1_id' => $team_ids[$i*2], 'team2_id' => $team_ids[$i*2+1], 'round' => $rnd, 'f_tour_id' => (int) $_POST['existTour']));
                $status &= !$exitStatus;
                if ($exitStatus)
                    break;
            }
            status($status, $exitStatus ? Match::$T_CREATE_ERROR_MSGS[$exitStatus] : null);
        }
        // Create new tour...
        else {
            switch ($_POST['type']) {
                case 'FFA_TOUR': $TOUR_TYPE = TT_FFA; break;
                case 'RR_TOUR': $TOUR_TYPE = TT_RROBIN; break;
            }
            status(Tour::create(array('did' => $_POST['did'], 'name' => $_POST['name'], 'type' => $TOUR_TYPE, 'rs' => (int) $_POST['rs'], 'teams' => $team_ids, 'rounds' => $_POST['rounds'])));
        }
    }
}

title($lng->getTrn('menu/admin_menu/schedule'));
?>
<script language="JavaScript" type="text/javascript">

    function chTour(t)
    {
        /*
            Handles the disabling and enabling of form elements depending on what tournament type is chosen.
            
            Expects TT_* PHP constants to be globally available to javascript.
        */

        if (t == 'FFA_SINGLE') {
            fadeOut('OPTS_NEW_TOUR');
            fadeOut('OPTS_RR_TOUR_SETS');
            fadeIn('OPTS_FFA_TOUR_SETS');
            fadeIn('OPTS_FFA_SINGLE_SETS');
        }
        else if (t == 'FFA_TOUR') {
            fadeOut('OPTS_RR_TOUR_SETS');
            fadeOut('OPTS_FFA_SINGLE_SETS');
            fadeIn('OPTS_NEW_TOUR');
            fadeIn('OPTS_FFA_TOUR_SETS');
        }
        else if (t == 'RR_TOUR') {
            fadeOut('OPTS_FFA_SINGLE_SETS');
            fadeOut('OPTS_FFA_TOUR_SETS');
            fadeIn('OPTS_RR_TOUR_SETS');
            fadeIn('OPTS_NEW_TOUR');
        }

        return true;
    }

    /*
        Team list related.
    */

    var TID = false;
    var TNAME = false;
    
    function verifyTeam(name) 
    {
        $.ajax({
           type: "POST",
           async: true,
           url: "handler.php?type=verifyteam",
           data: {tname:  name},
           success: function(tid){
                tid = tid.replace(new RegExp("(\n|\r)", "g" ),'');
                TID = tid;
                TNAME = name;
                if (TID > 0) {
                    addTeam();
                }
                else {
                    document.getElementById("team_verify").innerHTML = '<font color="red">Does not exist</font>';
                }
           }
         });
    }

    function addTeam() 
    {
        var tid;
        var name;
        
        if (TID == 0) {
            return false;
        }
        else {
            tid = TID;
            name = TNAME;
        }
        for (i = 0; i < SL.length; i++) {
            if (SL.options[i].value == tid) {
                return false;
            }
        }

        SL.options[SL.length] = new Option(name, tid);
        SL.size++;
        TEAMS.value = TEAMS.value.concat( ((TEAMS.value.length == 0) ? '' : ',')+tid );
        
        document.getElementById("team_verify").innerHTML = '';
    }
    
    function removeLastTeam()
    {
        SL.options[SL.length-1] = null;
        SL.size--;
        TEAMS.value = TEAMS.value.substr(0, TEAMS.value.lastIndexOf(','));
    }
    
</script>

<?php
if (count($leagues) < 0 || count($divisions) < 0) {
    fatal($lng->getTrn('admin/schedule/create_LD'));
}
HTMLOUT::helpBox($lng->getTrn('admin/schedule/help'), '<b>'.$lng->getTrn('common/needhelp').'</b>');
$commonStyle = "float:left; width:45%; height:300px; margin:10px;";
?><br>
<form method="POST" name="tourForm">

    <div style='margin:7px;'>
    <b><?php echo $lng->getTrn('admin/schedule/sched_type');?></b><br>
    <input type="radio" onClick="chTour(this.value);" name="type" value="FFA_SINGLE" CHECKED> <?php echo $lng->getTrn('admin/schedule/TT_FFA_SINGLE');?><br>
    <input type="radio" onClick="chTour(this.value);" name="type" value="FFA_TOUR""> <?php echo $lng->getTrn('admin/schedule/TT_FFA');?><br>
    <input type="radio" onClick="chTour(this.value);" name="type" value="RR_TOUR"> <?php echo $lng->getTrn('admin/schedule/TT_RR');?><br>
    </div>
    <div style='clear:both;'>
    <div class='boxCommon' style='<?php echo $commonStyle;?>'>
    <h3 class='boxTitle<?php echo T_HTMLBOX_ADMIN;?>'><?php echo $lng->getTrn('common/options');?></h3>
    <div class='boxBody'>

        <div id='OPTS_NEW_TOUR'>
            <b><?php echo $lng->getTrn('common/division');?></b><br>
            <select name='did'>
                <?php
                foreach ($divisions as $did => $desc) {
                    if ($leagues[$desc['f_lid']]['ring'] == Coach::T_RING_LOCAL_ADMIN) {
                        echo "<option value='$did'>".$leagues[$desc['f_lid']]['lname'].": $desc[dname]</option>\n";
                    }
                }
                ?>
            </select>
            <br><br>
            <b><?php echo $lng->getTrn('admin/schedule/tour_name');?></b><br>
            <input type="text" name="name" size="30" maxlength="50">
            <br><br>
            <b><?php echo $lng->getTrn('admin/schedule/rank_sys');?></b> (<?php echo $lng->getTrn('admin/prefixes');?>)<br>
            <select name='rs'>
            <?php
            global $hrs;
            foreach ($hrs as $idx => $r) {
                echo "<option value='$idx'>#$idx: ".Tour::getRSstr($idx)."</option>\n";
            }
            ?>
            </select>
            <br><br>
        </div> <!-- /OPTS_NEW_TOUR -->
        
        <?php
        $body = '';
        // FFA settings.
        $body .= '<b>'.$lng->getTrn('admin/schedule/as_type').'</b><br>';
        $body .= '<select name="round">';
        global $T_ROUNDS;
        foreach ($T_ROUNDS as $r => $d) {
                $body .= "<option value='$r' ".(($addMatchToFFA && isset($_POST['round']) && $r == $_POST['round']) ? 'SELECTED' : '').">".$lng->getTrn($d)."</option>\n";
        }
        $body .= '</select>';
        echo "<div id='OPTS_FFA_TOUR_SETS'>$body</div>\n";
        
        // Round robin seed multiplier.
        $body = '';
        $body .= '<b>'.$lng->getTrn('admin/schedule/rrobin_rnds')."</b><br><select name='rounds'>";
        foreach (range(1, 10) as $i) $body .= "<option value='$i'>$i</option>\n";
        $body .= "</select>&nbsp;".$lng->getTrn('admin/schedule/times')."\n";
        echo "<div id='OPTS_RR_TOUR_SETS'>$body</div>\n";
        ?>
        <div id='OPTS_FFA_SINGLE_SETS'>
            <br>
            <?php
            $body = '';
            $body .= '<b>'.$lng->getTrn('admin/schedule/in_tour').'</b><br>';
            $body .= '<select name="existTour">';
            foreach ($tours as $trid => $desc) {
                if ($desc['type'] == TT_FFA && $leagues[$divisions[$desc['f_did']]['f_lid']]['ring'] == Coach::T_RING_LOCAL_ADMIN) {
                    $body .= "<option value='$trid' ".(($desc['locked']) ? 'DISABLED' : '')." ".(($addMatchToFFA && isset($_POST['existTour']) && $trid == $_POST['existTour']) ? 'SELECTED' : '').">".$divisions[$desc['f_did']]['dname'].": $desc[tname]".(($desc['locked']) ? '&nbsp;&nbsp;(LOCKED)' : '')."</option>\n";
                }
            }
            $body .= '</select>';
            echo $body;
            ?>
        </div>
    </div>
    </div>
    <div class='boxCommon' style='<?php echo $commonStyle;?>'>
    <h3 class='boxTitle<?php echo T_HTMLBOX_ADMIN;?>'><?php echo $lng->getTrn('admin/schedule/add_team');?></h3>
    <div class='boxBody'>
    <b><?php echo $lng->getTrn('admin/schedule/add_team');?>:</b><br>    
    <script>
        $(document).ready(function(){
            var options, a;
            options = { minChars:2, serviceUrl:'handler.php?type=autocomplete&obj=<?php echo T_OBJ_TEAM; ?>' };
            a = $('#team').autocomplete(options);
        });
    </script>
    
    <input id='team' type="text" name="team" size="30" maxlength="50"> <a href='javascript:void(0);' onClick="verifyTeam(document.getElementById('team').value);"><?php echo $lng->getTrn('common/add');?></a> <span id='team_verify'></span><br>
    <?php
    print "<br>";
    print "<b>".$lng->getTrn('admin/schedule/teams_selected').":</b><br>";
    print "<select id='selectedlist' name='selectedlist' size='2' MULTIPLE></select>\n<br>";
    print "<a href='javascript:void(0);' onClick='removeLastTeam();'>".$lng->getTrn('common/remove')."</a><br>";
    print "<input type='hidden' id='teams' name='teams' value=''>";
    ?>
    </div>
    </div>
    </div>
    <div style='clear:both;'>
    <br>
    &nbsp;&nbsp;&nbsp;<input type="submit" name="button" value="<?php echo $lng->getTrn('common/create');?>" <?php echo (empty($divisions) ? 'DISABLED' : '');?>>
    </div>
</form>

<script language="JavaScript" type="text/javascript">
    chTour('FFA_SINGLE');
    
    var SL = document.getElementById('selectedlist');
    var TEAMS = document.getElementById('teams');
</script>

<?php
