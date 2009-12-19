<?php

/*
FIX LIST:
    SOLD constant removed! 
*/

global $raceididx;
$err = false; // Invalid input data?

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
        if (!in_array($_POST['race'], array_keys($raceididx))) {
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

            if (!Player::price($_POST[$i.'position'])) {
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
            $t->dtreasury(Player::price($_POST[$i.'position'])); // Make sure we have enough money to buy player.
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

<hr align="left" width="200px">
<br>
Import a team by filling in a <a href="xml/import.xml">XML schema</a> (right click on link --> save as) and uploading it.<br>
<br>
<form enctype="multipart/form-data" action="index.php?section=admin&amp;subsec=import" method="POST">
    <b>XML file:</b><br>
    <input name="xmlfile" type="file"><br>
    <br>
    <input type="submit" name="button" value="Import via XML file">
</form>
