<?php
if (isset($_POST['button'])) {
    $STATUS = true;

    // Initialize needed HTML post values which due to tournament types are disabled by javascript.
    switch ($_POST['type'])
    {
        case TT_FFA:
            $_POST['rounds'] = 1;
            if (!isset($_POST['name'])) {
                $_POST['name'] = 'Single';
            }
            break;
        case TT_RROBIN:
            break;
    }

    // Check passed tournament name.
    if ((!isset($_POST['name']) || empty($_POST['name'])) && !($_POST['type'] == TT_FFA && $_POST['existTour'] != -1)) {
        status(false, "Please fill out the tournament name.<br>\n");
        $STATUS = false;
    }
    if (get_alt_col('tours', 'name', $_POST['name'], 'tour_id')) {
        status(false, "Tournament name already in use.<br>\n");
        $STATUS = false;
    }

    // Find passed team IDs.
    $team_ids = array();
    foreach (Team::getTeams() as $team) {
        if (isset($_POST[$team->team_id]))
            array_push($team_ids, $team->team_id);
    }

    $i = count($team_ids);
    if (
        ($_POST['type'] == TT_FFA    && $i < 2              && ($cnt = 2)) ||
        ($_POST['type'] == TT_RROBIN && $i < MIN_TOUR_TEAMS && ($cnt = MIN_TOUR_TEAMS))
    ) {
        status(false, "Please select at least $cnt participants.<br>\n");
        $STATUS = false;
    }

    // Reverse pair-up for FFA match?
    if ($_POST['type'] == TT_FFA && isset($_POST['reverse']) && $_POST['reverse']) {
        $team_ids = array_reverse($team_ids);
    }

    // Only create tour if all went well.
    if ($STATUS) {
        if (get_magic_quotes_gpc())
            $_POST['name'] = stripslashes($_POST['name']);

        // Is the whish to add a match to a FFA tour?
        if ($_POST['type'] == TT_FFA && $_POST['existTour'] != -1) {
            $rnd = (!isset($_POST['round'])) ? 1 : (int) $_POST['round'];
            status(Match::create(array('team1_id' => $team_ids[0], 'team2_id' => $team_ids[1], 'round' => $rnd, 'f_tour_id' => (int) $_POST['existTour'])));
        }
        // Create new tour...
        else {
            if ($_POST['type'] == TT_FFA) {
                $_POST['rounds'] = $_POST['round'];
            }
            status(Tour::create(array('did' => $_POST['did'], 'name' => $_POST['name'], 'type' => (int) $_POST['type'], 'rs' => (int) $_POST['rs'], 'teams' => $team_ids, 'rounds' => $_POST['rounds'])));
        }
    }
}

title($lng->getTrn('secs/admin/schedule'));
?>
<script language="JavaScript" type="text/javascript">
    // Global JavaScript Variables.
    var TT_FFA    = <?php echo TT_FFA; ?>;
    var TT_RROBIN = <?php echo TT_RROBIN; ?>;
</script>

<?php
echo $lng->getTrn('secs/admin/create_leag_div').'<br>';
echo $lng->getTrn('secs/admin/multiple_schedule');
$divisions = Division::getDivisions();
foreach ($divisions as $d) {
    $d->dispName = "$d->league_name: $d->name";
}
objsort($divisions, array('+dispName'));
?><br><br>

<form method="POST" name="tourForm" action="index.php?section=admin&amp;subsec=tournament">
    <b><?php echo $lng->getTrn('secs/admin/tour_type');?>:</b><br>
    <input type="radio" onClick="chTour(this.value);" name="type" value="<?php echo TT_FFA;?>" CHECKED> FFA match <i>(Free For All a.k.a. "open league format" - creates a single match)</i><br>
    <input type="radio" onClick="chTour(this.value);" name="type" value="<?php echo TT_RROBIN;?>"> Round-Robin<br>
    <br>
    <table><tr>
        <td>
            <b><?php echo $lng->getTrn('secs/admin/div_name');?>:</b><br>
            <select name='did'>
                <?php
                foreach ($divisions as $d) {
                    echo "<option value='$d->did'>$d->dispName</option>\n";
                }
                ?>
            </select>
            <br><br>
            <b><?php echo $lng->getTrn('secs/admin/tour_name');?>:</b><br>
            <input type="text" name="name" size="30" maxlength="50">
            <br><br>
            <b><?php echo $lng->getTrn('secs/admin/rank_sys');?>:</b> (<?php echo $lng->getTrn('secs/admin/prefixes');?>)<br>
            <select name='rs'>
            <?php
            foreach (Tour::getRSSortRules(false, true) as $idx => $r) {
                echo "<option value='$idx'>RS #$idx | $r</option>\n";
            }
            ?>
            </select>
            <br><br>
            <?php echo $lng->getTrn('secs/admin/rrobin_rnds');?><br>
            <select name="rounds">
            <?php
            foreach (range(1, 10) as $i) echo "<option value='$i'>$i</option>\n";
            ?>
            </select>
        </td>
        <td>
            &nbsp;&nbsp;&nbsp;&nbsp;
        </td>
        <td>
            <div id="existTour" style="display:none;">
                <b><font color="blue"><?php echo $lng->getTrn('secs/admin/add_match');?></font></b><br>
                <select name="existTour" onChange="chFFATour(this.options[this.selectedIndex].value);">
                    <optgroup label="New FFA">
                        <option value='-1'><?php echo $lng->getTrn('secs/admin/new_tour');?></option>
                    </optgroup>
                    <optgroup label="Existing FFA">
                    <?php
                    foreach (Tour::getTours() as $t)
                        if ($t->type == TT_FFA)
                            echo "<option value='$t->tour_id' ".(($t->locked) ? 'DISABLED' : '').">$t->name".(($t->locked) ? '&nbsp;&nbsp;(LOCKED)' : '')."</option>\n";
                    ?>
                    </optgroup>
                </select>
                <br><br>
                <b><font color="blue"><?php echo $lng->getTrn('secs/admin/as_type');?></font></b><br>
                <select name="round">
                <?php
                    foreach (array(RT_FINAL => 'Final', RT_3RD_PLAYOFF => '3rd play-off', RT_SEMI => 'Semi final', RT_QUARTER => 'Quarter final', RT_ROUND16 => 'Round of 16 match') as $r => $d) {
                        echo "<option value='$r'>$d</option>\n";
                    }
                    $pure_rounds = array();
                    for ($i=1;$i<30;$i++) $pure_rounds[$i] = "Round #$i match";
                    foreach ($pure_rounds as $r => $d) {
                        echo "<option value='$r'>$d</option>\n";
                    }
                ?>
                </select>
                <br><br>
                <b><font color="blue"><?php echo $lng->getTrn('secs/admin/as_reverse');?></font></b><br>
                <input type="checkbox" name="reverse" value="1">
            </div>
        </td>
    </tr></table>
    <br>
    <b><?php echo $lng->getTrn('secs/admin/participants');?>:</b><br>
    <?php
    $teams = Team::getTeams();
    objsort($teams, array('+coach_name'));
    foreach ($teams as $t)
        echo "<input type='checkbox' name='$t->team_id' value='$t->team_id'>$t->coach_name'".((substr($t->coach_name,-1)=='s')?'':'s')." $t->name<br>\n";
    ?>
    <br>
    <hr align="left" width="200px">
    <input type="submit" name="button" value="<?php echo $lng->getTrn('secs/admin/create');?>" <?php echo (empty($divisions) ? 'DISABLED' : '');?>>
</form>
<script language="JavaScript" type="text/javascript">
    chTour(<?php echo TT_FFA;?>);
</script>
<?php
