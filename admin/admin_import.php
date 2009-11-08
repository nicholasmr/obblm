<?php
define('HTML', 1);
define('XML', 2);
$inputType = null;
$err = false; // Invalid input data?

define('INIT_PLAYERS', 11); // Initial player entries.
define('UNLIMITED', 20); // Used for limiting the allowed amount of team items/things.
?>
<script language="JavaScript" type="text/javascript">
    // Global JavaScript Variables.
    var NONE    = <?php echo NONE; ?>;
    var MNG     = <?php echo MNG; ?>;
    var DEAD    = <?php echo DEAD; ?>;
    var SOLD    = <?php echo SOLD; ?>;
</script>
<?php

// Input sent?
if (isset($_POST['button'])) {

    $in = array('coach', 'name', 'race', 'treasury', 'apothecary', 'rerolls', 'ff_bought', 'ass_coaches', 'cheerleaders', 'players',
        'won_0', 'lost_0', 'draw_0', 'sw_0', 'sl_0', 'sd_0', 'wt_0', 'gf_0', 'ga_0', 'tcas_0', 'elo_0');
    $inputType = (isset($_FILES['xmlfile'])) ? XML : HTML;

    // Is input given as XML file? If so, make it appear as if it was submitted via the HTML import page (POST).
    if ($inputType == XML && $xml = simplexml_load_file($_FILES['xmlfile']['tmp_name'])) {

        // Team related.
        foreach ($in as $field) {
            if (isset($xml->$field))
                $_POST[$field] = (string) $xml->$field;
        }
        $_POST['coach'] = ($tmp = get_alt_col('coaches', 'name', $_POST['coach'], 'coach_id')) ? $tmp : 0;
        $_POST['players'] = count($xml->player);

        // Players.
        for ($i = 1; $i <= $_POST['players']; $i++) {
            foreach (array('name', 'position', 'status', 'stats', 'injs') as $field) {
                if (isset($xml->player[$i-1]->$field))
                    $_POST[$i.$field] = (string) $xml->player[$i-1]->$field;
            }
            switch ((isset($_POST[$i.'status'])) ? $_POST[$i.'status'] : '')
            {
                case 'ready': $a = NONE; break;
                case 'mng':   $a = MNG; break;
                case 'dead':  $a = DEAD; break;
                case 'sold':  $a = SOLD; break;
                default: $a = -1; // Unknown.
            }
            $_POST[$i.'status'] = $a;
        }
    }

    if ($inputType == XML && !$xml) {
        status(false, 'Something is wrong with the passed XML file.');
        $err = true;
    }

    // Validate input.
    foreach ($in as $field) {
        if (!isset($_POST[$field])) {
            status(false, "Field '$field' could not be found.");
            $err = true;
        }
    }
    if (!$err) {
        if (!get_alt_col('coaches', 'coach_id', $_POST['coach'], 'coach_id')) {
            status(false, "Invalid team coach.");
            $err = true;
        }
        if (empty($_POST['name']) || get_alt_col('teams', 'name', $_POST['name'], 'team_id')) {
            status(false, "The team name must not be empty or identical to an existing team name.");
            $err = true;
        }
        if (!in_array($_POST['race'], Race::getRaces(false))) {
            status(false, "Invalid race chosen.");
            $err = true;
        }
        if (!is_numeric($_POST['treasury'])) {
            status(false, "Treasury amount must be numeric.");
            $err = true;
        }
        foreach (array('apothecary', 'rerolls', 'ff_bought', 'ass_coaches', 'cheerleaders') as $a) {
            if (!is_numeric($_POST[$a])) {
                status(false, "Field '$a' must be numeric.");
                $err = true;
            }
        }
    }

    if ($inputType == HTML && get_magic_quotes_gpc()) {
        $_POST['name'] = stripslashes($_POST['name']);
        $_POST['race'] = stripslashes($_POST['race']);
    }

    // If received input was valid, then create the team.
    if (!$err && Team::create(
        array('coach_id' => $_POST['coach'], 'name' => $_POST['name'], 'race' => $_POST['race']),
        array('won' => $_POST['won_0'], 'lost' => $_POST['lost_0'], 'draw' => $_POST['draw_0'], 'sw' => $_POST['sw_0'], 'sl' => $_POST['sl_0'], 'sd' => $_POST['sd_0'], 'wt' => $_POST['wt_0'], 'gf' => $_POST['gf_0'], 'ga' => $_POST['ga_0'], 'tcas' => $_POST['tcas_0'], 'elo' => $_POST['elo_0']-ELO_DEF_RANK) # ELO_DEF_RANK + true_elo_0 = $_POST['elo_0']
        )) {
        status(true, 'Team created.');

        // Now lets correct team properties to fit the requested.
        $t = new Team(get_alt_col('teams', 'name', $_POST['name'], 'team_id'));

        foreach ($t->getGoods() as $name => $details) {
            $cur = $t->$name;
            if ($_POST[$name] > $cur) {
                for ($i = 1; $i <= ($_POST[$name] - $cur); $i++) {
                    $t->buy($name); // Buy the item.
                    $t->dtreasury($details['cost']); // Give money back for item.
                }
            }
            elseif ($_POST[$name] < $cur) {
                for ($i = 1; $i <= ($cur - $_POST[$name]); $i++) {
                    $t->drop($name); // Throw away the item.
                }
            }
        }

        // Now we create the players.
        for ($i = 1; $i <= $_POST['players']; $i++) { // Note $i is the player number.

            // Validate player input.
            $in = array('name', 'position', 'status', 'stats', 'injs');

            foreach ($in as $field) {
                if (!isset($_POST[$i.$field])) {
                    status(false, "Player #$i field '$field' could not be found.");
                    continue 2;
                }
            }

            if (!Player::price(array('race' => $_POST['race'], 'position' => $_POST[$i.'position']))) {
                // If we are able to find a price for the player at the specified position, then the position must be valid!
                status(false, "The player position of player #$i is invalid.");
                continue;
            }
            if (!in_array($_POST[$i.'status'], array(NONE, MNG, DEAD, SOLD))) {
                status(false, "The status of player $i is invalid.");
                continue;
            }
            if ((count($injsCnt = explode('/', $_POST[$i.'injs'])) != 5 && $attr = 'injuries') ||
                (count($stats = explode('/', $_POST[$i.'stats'])) != 7  && $attr = 'stats')) {
                status(false, "Not enough fields in player #$i attribute '$attr'.");
                continue;
            }

            if (get_magic_quotes_gpc()) {
                $_POST[$i.'name'] = stripslashes($_POST[$i.'name']);
                $_POST[$i.'position'] = stripslashes($_POST[$i.'position']);
            }

            // Skip player entries with empty names.
            if (empty($_POST[$i.'name']))
                continue;

            // Create the player.
            $t->dtreasury(Player::price(array('race' => $t->race, 'position' => $_POST[$i.'position']))); // Make sure we have enough money to buy player.
            $ret = Player::create(array('nr' => $i, 'position' => $_POST[$i.'position'], 'name' => $_POST[$i.'name'], 'team_id' => $t->team_id, 'forceCreate' => true));

            if ($ret[0]) {

                if ($_POST[$i.'status'] == SOLD) {
                    $p = new Player($ret[1]);
                    $p->sell();
                    $_POST[$i.'status'] = NONE;
                }

                status(true, "Created player #$i.");

                /*
                    Since we are only able to store 3 injuries per player per match entry, we might need to create more than one fake match entry.
                    Therefore we simply store all injuries in an array, an keep pop'ing them out until empty.
                */

                $injs = array();
                $injs_idx = array('ma' => 0, 'st' => 1, 'ag' => 2, 'av' => 3, 'ni' => 4);
                foreach (array('ma' => MA, 'st' => ST, 'ag' => AG, 'av' => AV, 'ni' => NI) as $a => $b) {
                    for ($j = 1; $j <= $injsCnt[$injs_idx[$a]]; $j++) {
                        array_push($injs, $b);
                    }
                }

                Match::fakeEntry(array(
                    'player_id' => $ret[1],
                    'mvp'     => $stats[6],
                    'cp'      => $stats[0],
                    'td'      => $stats[1],
                    'intcpt'  => $stats[2],
                    'bh'      => $stats[3],
                    'si'      => $stats[4],
                    'ki'      => $stats[5],
                    'inj'     => $_POST[$i.'status'],
                    'agn1'    => ($tmp = array_pop($injs)) ? $tmp : NONE,
                    'agn2'    => ($tmp = array_pop($injs)) ? $tmp : NONE,
                ));

                while (!empty($injs)) {
                    Match::fakeEntry(array(
                        'player_id' => $ret[1],
                        'mvp'     => 0,
                        'cp'      => 0,
                        'td'      => 0,
                        'intcpt'  => 0,
                        'bh'      => 0,
                        'si'      => 0,
                        'ki'      => 0,
                        'inj'     => $_POST[$i.'status'], // This field value must exist for all entries for else the player status is forgotten.
                        'agn1'    => ($tmp = array_pop($injs)) ? $tmp : NONE,
                        'agn2'    => ($tmp = array_pop($injs)) ? $tmp : NONE,
                    ));
                }
            }
            else {
                status(false, "Could not create player #$i. " . $ret[1]);
            }
        }

        // Set correct treasury.
        $t = new Team($t->team_id); # Update team object to get current treasury.
        $t->dtreasury($_POST['treasury']*1000 - $t->treasury); // $t->treasury + $delta = $_POST['treasury']
    }
    else {
        status(false, 'Unable to create team. Halting.');
    }
}

// We use JavaScript to manage populating the player position selection depending on what race chosen.
$easyconvert = new array_to_js();
@$easyconvert->add_array($DEA, 'gd'); // Load Game Data array into JavaScript array.
echo $easyconvert->output_all();

/*
    If there was en error in the inputted data, $err is true, and the data did NOT come from a HTML form,
    then don't try to recover it by filling out input fields with the received values.
*/
if ($err && $inputType == XML)
    $err = false;

title($lng->getTrn('menu/admin_menu/import'));
?>
This page allows you to create a customized team for an existing coach.<br> 
This is useful if you and your league wish to avoid starting from scratch in order to use OBBLM.<br>
<u>Note</u>: If you discover errors after having imported your team, you can either repair the errors<br> 
via the admin tools in the coach corner, or simply delete the team and import a new.<br>
<?php

if (Module::isRegistered('cyanide_team_import')) {
    ?>
    <hr align="left" width="200px">
    <br>
    <div style="background-color:#C8C8C8; border: solid 2px; border-color: #C0C0C0; width:40%; padding: 10px;">
    <b>Import a Cyanide team</b>:
    <a href='handler.php?type=cyanide_team_import'>Click here</a>
    </div>
    <br>
    <?php
}
?>
<hr align="left" width="200px">
<br>
<i>Method 1:</i> Import a team by filling in a <a href="xml/import.xml">XML schema</a> (right click on link --> save as) and uploading it.<br>
<br>
<form enctype="multipart/form-data" action="index.php?section=admin&amp;subsec=import" method="POST">
    <b>XML file:</b><br>
    <input name="xmlfile" type="file"><br>
    <br>
    <input type="submit" name="button" value="Import via XML file">
</form>
<br>
<hr align="left" width="200px">
<br>
<i>Method 2:</i> Import a team by filling in the below formular.<br>
<br>
<form method="POST" action="index.php?section=admin&amp;subsec=import" name="importForm">

    <b>Coach:</b><br>
    <select name="coach">
        <?php
        foreach ($coaches as $c)
            echo "<option value='$c->coach_id' ".(($err && $_POST['coach'] == $c->coach_id) ? 'SELECTED' : '').">$c->name </option>\n";
        ?>
    </select>

    <br><br>
    <b>Team name:</b><br>
    <input type="text" name="name" size="20" maxlength="50" value="<?php echo ($err) ? $_POST['name'] : '';?>">

    <br><br>
    <b>Race:</b><br>
    <select name="race" onchange="chRace(this.options[this.selectedIndex].value)">
        <?php
        foreach (Race::getRaces() as $r)
            echo "<option value='$r' ".(($err && $_POST['race'] == $r) ? 'SELECTED' : '').">$r</option>\n";
        ?>
    </select>

    <br><br>
    <b>Treasury:</b><br>
    <input type="text" name="treasury" size="10" maxlength="10" value="<?php echo ($err) ? $_POST['treasury'] : '';?>" onChange="numError(this);">k gold pieces

    <br><br>
    <b>Apothecary</b> (ignored if chosen race can't buy a apothecary):<br>
    <input type="radio" name="apothecary" value="1" <?php echo ($err && $_POST['apothecary']) ? 'CHECKED' : '';?>> Yes<br>
    <input type="radio" name="apothecary" value="0" <?php echo ($err && !$_POST['apothecary'] || !$err) ? 'CHECKED' : '';?>> No

    <br><br>
    <table>
        <tr>
            <td><b>Re-rolls:</b>&nbsp;&nbsp;&nbsp;</td>
            <td><b>Fan factor:</b>&nbsp;&nbsp;&nbsp;</td>
            <td><b>Assistant coaches:</b>&nbsp;&nbsp;&nbsp;</td>
            <td><b>Cheerleaders:</b>&nbsp;&nbsp;&nbsp;</td>
        </tr>
        <tr>
            <td>
                <select name="rerolls">
                    <?php
                    foreach (range(0, ($rules['max_rerolls'] == -1) ? UNLIMITED : $rules['max_rerolls']) as $i)
                        echo "<option value='$i' ".(($err && $_POST['rerolls'] == $i) ? 'SELECTED' : '').">$i</option>\n";
                    ?>
                </select>
            </td>
            <td>
                <select name="ff_bought">
                    <?php
                    foreach (range(0, ($rules['max_fan_factor'] == -1) ? UNLIMITED : $rules['max_fan_factor']) as $i)
                        echo "<option value='$i' ".(($err && $_POST['ff_bought'] == $i) ? 'SELECTED' : '').">$i</option>\n";
                    ?>
                </select>
            </td>
            <td>
                <select name="ass_coaches">
                    <?php
                    foreach (range(0, ($rules['max_ass_coaches'] == -1) ? UNLIMITED : $rules['max_ass_coaches']) as $i)
                        echo "<option value='$i' ".(($err && $_POST['ass_coaches'] == $i) ? 'SELECTED' : '').">$i</option>\n";
                    ?>
                </select>
            </td>
            <td>
                <select name="cheerleaders">
                    <?php
                    foreach (range(0, ($rules['max_cheerleaders'] == -1) ? UNLIMITED : $rules['max_cheerleaders']) as $i)
                        echo "<option value='$i' ".(($err && $_POST['cheerleaders'] == $i) ? 'SELECTED' : '').">$i</option>\n";
                    ?>
                </select>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td><b>Won matches</b>&nbsp;&nbsp;&nbsp;</td>
            <td><b>Lost matches</b>&nbsp;&nbsp;&nbsp;</td>
            <td><b>Draw matches</b>&nbsp;&nbsp;&nbsp;</td>
            <td><b>Largest win streak</b>&nbsp;&nbsp;&nbsp;</td>
            <td><b>Largest lose streak</b>&nbsp;&nbsp;&nbsp;</td>
            <td><b>Largest draw streak</b>&nbsp;&nbsp;&nbsp;</td>
        </tr>
        <tr>
            <td><input type="text" name="won_0"  size="4" maxlength="10" value="<?php echo ($err) ? $_POST['won_0'] : '0';?>"></td>
            <td><input type="text" name="lost_0" size="4" maxlength="10" value="<?php echo ($err) ? $_POST['lost_0'] : '0';?>"></td>
            <td><input type="text" name="draw_0" size="4" maxlength="10" value="<?php echo ($err) ? $_POST['draw_0'] : '0';?>"></td>
            <td><input type="text" name="sw_0"   size="4" maxlength="10" value="<?php echo ($err) ? $_POST['sw_0'] : '0';?>"></td>
            <td><input type="text" name="sl_0"   size="4" maxlength="10" value="<?php echo ($err) ? $_POST['sl_0'] : '0';?>"></td>
            <td><input type="text" name="sd_0"   size="4" maxlength="10" value="<?php echo ($err) ? $_POST['sd_0'] : '0';?>"></td>
        </tr>
        <tr>
            <td><b>Tournaments won</b>&nbsp;&nbsp;&nbsp;</td>
            <td><b>Goals by team</b>&nbsp;&nbsp;&nbsp;</td>
            <td><b>Goals against team</b>&nbsp;&nbsp;&nbsp;</td>
            <td><b>Total team cas</b>&nbsp;&nbsp;&nbsp;</td>
            <td><b>Team's current ELO</b>&nbsp;&nbsp;&nbsp;</td>
            <td> </td>
        </tr>
        <tr>
            <td><input type="text" name="wt_0"   size="4" maxlength="10" value="<?php echo ($err) ? $_POST['wt_0'] : '0';?>"></td>
            <td><input type="text" name="gf_0"   size="4" maxlength="10" value="<?php echo ($err) ? $_POST['gf_0'] : '0';?>"></td>
            <td><input type="text" name="ga_0"   size="4" maxlength="10" value="<?php echo ($err) ? $_POST['ga_0'] : '0';?>"></td>
            <td><input type="text" name="tcas_0" size="4" maxlength="10" value="<?php echo ($err) ? $_POST['tcas_0'] : '0';?>"></td>
            <td><input type="text" name="elo_0"  size="4" maxlength="10" value="<?php echo ($err) ? $_POST['elo_0'] : '0';?>"></td>
            <td> </td>
        </tr>
    </table>

    <br>
    <b>Players:</b>
    <br><br>
    <u>Please note:</u>
    <ul>
        <li>Player entries are ignored if player name is empty.</li>
        <li>Player skills and characteristics are chosen via coach corner.</li>
        <li>Empty cells are equal to zero.</li>
    </ul>

    <table id="playerTable">
        <tr>
            <td>#</td>
            <td>Name</td>
            <td>Position</td>
            <td>Status</td>
            <td>Cp/Td/Int/BH/SI/Ki/Mvp</td>
            <td>Inj.: Ma/St/Ag/Av/Ni</td>
        </tr>
        <!-- Body (player rows) are added by javascript -->
    </table>
    <a href="javascript:void(0)" onClick="addPlayerEntry();return false;" title="Add new player entry"><b>[+]</b></a>
    <br><br>
    <hr align="left" style="width:150px; height:3px;">
    <input type="hidden" name="players" value="0">
    <input type="submit" name="button" value="Create team">
</form>

<?php
for ($i = 1; $i <= INIT_PLAYERS; $i++) {
    ?>
    <script language="JavaScript" type="text/javascript">addPlayerEntry();</script>
    <?php
}
?>
<script language="JavaScript" type="text/javascript">
    scrollTop();
</script>
<?php
