<?php

/*
 *  Copyright (c) Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2007-2009. All Rights Reserved.
 *      
 *
 *  This file is part of OBBLM.
 *
 *  OBBLM is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  OBBLM is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *   
 */
 
 /*************************
 *
 *  ADMINISTRATION
 *
 *************************/

function sec_admin() {

    global $rules, $DEA, $coach;

    // Quit if coach does not has administrator privileges.

    if (!is_object($coach) || !$coach->admin)
        fatal("Sorry. Only administrators are allowed to access the administration section.");

    title("Administration");

    ?>
    <div class="admin_menu">
        <a href="index.php?section=admin&amp;subsec=usrman">User management</a>&nbsp;&nbsp;&nbsp;
        <a href="index.php?section=admin&amp;subsec=tournament">Schedule matches</a>&nbsp;&nbsp;&nbsp;
        <a href="index.php?section=admin&amp;subsec=import">Import team</a>&nbsp;&nbsp;&nbsp;
        <a href="index.php?section=admin&amp;subsec=chrs">Change tour RS</a>&nbsp;&nbsp;&nbsp;
        <a href="index.php?section=admin&amp;subsec=log">Log</a>&nbsp;&nbsp;&nbsp;
        <hr>
    </div>
    <?php
    
    $coaches = Coach::getCoaches(); // Used by multiple sub-sections.
    
    // If an admin section was requested then call it, else show admin main page.
    if (isset($_GET['subsec']) && $_GET['subsec'] == 'usrman') {
        if (isset($_POST['button'])) {
        
            if ($_POST['button'] == 'Create coach') {
                if (get_magic_quotes_gpc()) {
                    $_POST['new_name'] = stripslashes($_POST['new_name']);
                    $_POST['new_mail'] = stripslashes($_POST['new_mail']);
                    $_POST['new_passwd'] = stripslashes($_POST['new_passwd']);
                }
                status(Coach::create(array('name' => $_POST['new_name'], 
                                            'passwd' => $_POST['new_passwd'], 
                                            'mail' => $_POST['new_mail'], 
                                            'admin' => isset($_POST['new_admin']) ? true : false)));
            }
            elseif ($_POST['button'] == 'Change status') {
                $coach = new Coach($_POST['chadmin_coachid']);
                if (!is_object($coach)) {
                    status(false);
                    return;
                }
                else {
                    status($coach->setAttr(array('type' => 'admin', 'new_value' => isset($_POST['chadmin_status']) ? true : false)));
                }
            }
            elseif ($_POST['button'] == 'Change password') {
                $coach = new Coach($_POST['chpass_coachid']);
                status($coach->setAttr(array('type' => 'passwd', 'new_value' => $_POST['ch_passwd'])));
            }
            
            // Reload coaches objects.
            $coaches = Coach::getCoaches();
        }
        
        // Interface related correction to make long coach names not break visual layout.
        foreach ($coaches as $c) {
            if (strlen($c->name) > 28)
                $c->name = substr($c->name, 0, 24) . '...';
            if (strlen($c->mail) > 28)
                $c->mail = substr($c->mail, 0, 24) . '...';
        }

        title('User management');

        ?>
        <form method="POST" action="?section=admin&amp;subsec=usrman">
        
            <div class="adminBox">
                <div class="boxTitle3">
                    Create new coach
                </div>
                <div class="boxBody">
                    Coach name:<br> <input type="text" name="new_name" size="20" maxlength="50"><br><br>
                    Mail (optional):<br> <input type="text" name="new_mail" size="20" maxlength="129"><br><br>
                    Password:<br> <input type="password" name="new_passwd" size="20" maxlength="50"><br><br>
                    Admin: <INPUT TYPE="CHECKBOX" NAME="new_admin"><br><br>
                    <input type="submit" name="button" value="Create coach">
                </div>
            </div>
            
            <div class="adminBox">
                <div class="boxTitle3">
                    Toggle admin-status
                </div>
                <div class="boxBody">
                    Coach:<br>
                    <select name="chadmin_coachid">
                        <?php
                        foreach ($coaches as $coach) {
                            echo "<option value='$coach->coach_id'>$coach->name</option>\n";
                        }
                        ?>
                    </select>
                    <br><br>
                    Admin: <INPUT TYPE="CHECKBOX" NAME="chadmin_status"><br><br>
                    <input type="submit" name="button" value="Change status">
                </div>
            </div>
            
            <div class="adminBox">
                <div class="boxTitle3">
                    Change coach password
                </div>
                <div class="boxBody">
                    Coach:<br>
                    <select name="chpass_coachid">
                        <?php
                        foreach ($coaches as $coach) {
                            echo "<option value='$coach->coach_id'>$coach->name</option>\n";
                        }
                        ?>
                    </select>
                    <br><br>
                    Password:<br> <input type="password" name="ch_passwd" size="20" maxlength="50"><br><br>
                    <input type="submit" name="button" value="Change password">
                </div>
            </div>
            
            <div class="adminBox" style="clear: both;">
                <div class="boxTitle4">
                    Coaches
                </div>
                <div class="boxBody">
                    <?php
                    if (!empty($coaches)) {
                        ?>
                        <table class="boxTable">
                            <tr>
                                <td><b>Name</b></td>
                                <td><b>Mail</b></td>
                                <td><b>Admin</b></td>
                            </tr>
                        <?php
                        foreach ($coaches as $coach) {
                            echo "<tr>\n";
                            echo "<td>$coach->name</td>\n";
                            echo "<td><a href='mailto:$coach->mail'>$coach->mail</a></td>\n";
                            echo "<td>" . ($coach->admin ? 'Yes' : 'No') . " </td>\n";
                            echo "</tr>\n";
                        }
                        echo "</table>\n";
                    }
                    ?>
                </div>
            </div>
            
        </form>
        <?php
    }
    elseif (isset($_GET['subsec']) && $_GET['subsec'] == 'tournament') {
        if (isset($_POST['button'])) {
            $STATUS = true;

            // Initialize needed HTML post values which due to tournament types are disabled by javascript.
            switch ($_POST['type']) 
            {
                case TT_SINGLE:
                    if (!isset($_POST['name']))
                        $_POST['name'] = 'Single';
                case TT_KNOCKOUT:
                    $_POST['rounds'] = 1;
            }
            
            // Check passed tournament name.
            if ((!isset($_POST['name']) || empty($_POST['name'])) && !($_POST['type'] == TT_SINGLE && $_POST['existTour'] != -1)) {
                status(false, "Please fill out the tournament name.<br>\n");
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
                ($_POST['type'] == TT_SEMI   && $i < 4 && $cnt = 4) || 
                ($_POST['type'] == TT_SINGLE && $i < 2 && $cnt = 2) || 
                ($_POST['type'] != TT_SEMI && $_POST['type'] != TT_SINGLE && $i < MIN_TOUR_TEAMS && $cnt = MIN_TOUR_TEAMS) 
            ) {
                status(false, "Please select at least $cnt participants.<br>\n");
                $STATUS = false;
            }
            
            // Only create tour if all went well.
            if ($STATUS) {
                if (get_magic_quotes_gpc())
                    $_POST['name'] = stripslashes($_POST['name']);
                    
                // Is the whish to add a match to a FFA tour?
                if ($_POST['type'] == TT_SINGLE && $_POST['existTour'] != -1) {
                    $rnd = (!isset($_POST['round'])) ? 1 : (int) $_POST['round'];
                    status(Match::create(array('team1_id' => $team_ids[0], 'team2_id' => $team_ids[1], 'round' => $rnd, 'f_tour_id' => (int) $_POST['existTour'])));
                }
                // Create new tour...
                else {
                    if ($_POST['type'] == TT_SINGLE) {
                        $_POST['rounds'] = $_POST['round'];
                    }
                    status(Tour::create(array('name' => $_POST['name'], 'type' => (int) $_POST['type'], 'rs' => (int) $_POST['rs'], 'teams' => $team_ids, 'rounds' => $_POST['rounds'])));
                }
            }
        }

        title('Schedule matches');
        ?>
        <script language="JavaScript" type="text/javascript">
            // Global JavaScript Variables.
            var TT_NOFINAL  = <?php echo TT_NOFINAL; ?>;
            var TT_FINAL    = <?php echo TT_FINAL; ?>;
            var TT_SEMI     = <?php echo TT_SEMI; ?>;
            var TT_KNOCKOUT = <?php echo TT_KNOCKOUT; ?>;
            var TT_SINGLE   = <?php echo TT_SINGLE; ?>;
        </script>
        
        <i>Note:</i> You are free to schedule multiple tournaments running concurrently.<br><br>
        
        <form method="POST" name="tourForm" action="index.php?section=admin&amp;subsec=tournament">
            <table><tr>
                <td>
                    <b>Tournament name:</b><br>
                    <input type="text" name="name" size="30" maxlength="50">
                    <br><br>
                    <b>Ranking system:</b> (the prefixes + and - specify least of and most of)<br>
                    <select name='rs'>
                    <?php
                    foreach (Tour::getRSSortRules(false, true) as $idx => $r) {
                        if ($idx == 0) {
                            continue;
                        }
                        else {
                            echo "<option value='$idx'>RS #$idx | $r</option>\n";
                        }
                    }
                    ?>
                    </select>
                </td>
                <td>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                </td>
                <td>
                    <div id="existTour" style="display:none;">
                        <b><font color="blue">Add the single match to...</font></b><br>
                        <select name="existTour">
                            <optgroup label="New FFA">
                                <option value='-1'>A new FFA tour (fill out left fields too!)</option>
                            </optgroup>
                            <optgroup label="Existing FFA">
                            <?php
                            foreach (Tour::getTours() as $t)
                                if ($t->type == TT_SINGLE)
                                    echo "<option value='$t->tour_id'>$t->name</option>\n";
                            ?>
                            </optgroup>
                        </select>
                        <br><br>
                        <b><font color="blue">As type...</font></b><br>
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
                    </div>
                </td>
            </tr></table>
            <br>
            <b>Tournament type:</b><br>
            <input type="radio" onClick="chTour(this.value);" name="type" value="<?php echo TT_NOFINAL;?>" > Round-Robin without final<br>
            <input type="radio" onClick="chTour(this.value);" name="type" value="<?php echo TT_FINAL;?>" CHECKED> Round-Robin with final<br>
            <input type="radio" onClick="chTour(this.value);" name="type" value="<?php echo TT_SEMI;?>" > Round-Robin with final and semi-finals<br>
            <input type="radio" onClick="chTour(this.value);" name="type" value="<?php echo TT_KNOCKOUT;?>" > Knock-out (AKA. single-elimination, cup, sudden death)<br>
            <input type="radio" onClick="chTour(this.value);" name="type" value="<?php echo TT_SINGLE;?>" > FFA (free for all) single match<br>
            <br>
            <b>Number of rounds:</b> (Round-Robin only)<br>
            <select name="rounds">
            <?php
            foreach (range(1, MAX_ALLOWED_ROUNDS) as $i) echo "<option value='$i'>$i</option>\n";
            ?>
            </select>
            <br><br>
            <b>Participating teams:</b><br>
            <?php
            $teams = Team::getTeams();
            objsort($teams, array('+coach_name'));
            foreach ($teams as $t) 
                echo "<input type='checkbox' name='$t->team_id' value='$t->team_id'>$t->coach_name'".((substr($t->coach_name,-1)=='s')?'':'s')." $t->name<br>\n";
            ?>
            <br>
            <hr align="left" width="200px">
            <input type="submit" name="button" value="Create">
        </form>
        <?php
    }
    elseif (isset($_GET['subsec']) && $_GET['subsec'] == 'import') {

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
        </script>
        <?php

        // Input sent?
        if (isset($_POST['button'])) {

            $in = array('coach', 'name', 'race', 'treasury', 'apothecary', 'rerolls', 'fan_factor', 'ass_coaches', 'cheerleaders', 'players');
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
                if (!in_array($_POST['race'], array_keys(get_races()))) {
                    status(false, "Invalid race chosen.");
                    $err = true;            
                }
                if (!is_numeric($_POST['treasury'])) {
                    status(false, "Treasury amount must be numeric.");
                    $err = true;
                }
                foreach (array('apothecary', 'rerolls', 'fan_factor', 'ass_coaches', 'cheerleaders') as $a) {
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
            if (!$err && Team::create(array('coach_id' => $_POST['coach'], 'name' => $_POST['name'], 'race' => $_POST['race']))) {
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

                $t->dtreasury($_POST['treasury']*1000 - $t->treasury); // $t->treasury + $delta = $_POST['treasury']

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
                    if (!in_array($_POST[$i.'status'], array(NONE, MNG, DEAD))) {
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
                    $ret = Player::create(array('nr' => $i, 'position' => $_POST[$i.'position'], 'name' => $_POST[$i.'name'], 'team_id' => $t->team_id));
                    
                    if ($ret[0]) {
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
            
        title('Import team');
        ?>
        
        This page allows you to create a customized team for an existing coach.<br> 
        This is useful if you and your league wish to avoid starting from scratch in order to use OBBLM.<br>
        <u>Note</u>: If you discover errors after having imported your team, you can either repair the errors<br> via the admin tools in the coach corner, or simply delete the team and import a new.<br>
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
                foreach (get_races() as $race => $icon_file)
                    echo "<option value='$race' ".(($err && $_POST['race'] == $race) ? 'SELECTED' : '').">$race</option>\n";
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
                        <select name="fan_factor">
                            <?php
                            foreach (range(0, ($rules['max_fan_factor'] == -1) ? UNLIMITED : $rules['max_fan_factor']) as $i)
                                echo "<option value='$i' ".(($err && $_POST['fan_factor'] == $i) ? 'SELECTED' : '').">$i</option>\n";
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
                    
            <br>
            <b>Players:</b>
            <br><br>
            <u>Please note:</u> 
            <ul>
                <li>Player entries are ignored if player name is empty.</li>
                <li>Player skills and characteristics are chosen via coach corner.</li>
                <li>Empty cells are equal to zero.</li>
            </ul>

            <table>
                <tr>
                    <td>#</td>
                    <td>Name</td>
                    <td>Position</td>
                    <td>Status</td>
                    <td>Cp/Td/Int/BH/SI/Ki/Mvp</td>
                    <td>Inj.: Ma/St/Ag/Av/Ni</td>
                </tr>
                <?php
                for ($i = 1; $i <= 25; $i++) {
                    echo "<tr id='row${i}'><td></td></tr>\n";
                }
                ?>
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
    }
    elseif (isset($_GET['subsec']) && $_GET['subsec'] == 'chrs') {

        if (isset($_POST['trid']) && isset($_POST['rs'])) {
            $t = new Tour($_POST['trid']);
            status($t->chRS($_POST['rs']));
        }

        title('Change tournament ranking system');
        
        ?>
        <form method="POST">
            <b>Tournament:</b><br>
            <select name="trid">
                <?php
                foreach (Tour::getTours() as $t) {
                    echo "<option value='$t->tour_id'>$t->name</option>\n";
                }
                ?>
            </select>

            <br><br>
            <b>New ranking system:</b> (the prefixes + and - specify least of and most of)<br>
            <select name='rs'>
            <?php
            foreach (Tour::getRSSortRules(false, true) as $idx => $r) {
                if ($idx == 0) {
                    continue;
                }
                else {
                    echo "<option value='$idx'>RS #$idx | $r</option>\n";
                }
            }
            ?>
            </select>
            <br><br>
            <input type="submit" value="Change ranking system">
        </form>
        <?php
    }
    elseif (isset($_GET['subsec']) && $_GET['subsec'] == 'log') {
        title('Log');
        echo "<table style='width:100%;'>\n";
        echo "<tr><td><i>Date</i></td><td><i>Message</i></td></tr>\n";
        echo "<tr><td colspan='2'><br></td></tr>";
        foreach (SiteLog::getLogs() as $l) {
            echo "<tr><td>$l->date</td><td>$l->txt</td></tr>\n";
        }
        echo "</table>\n";
    }
    else {
        ?>
        <div style="height: 400px;" id="admin_everything">
        Please pick one of the above links.
        </div>
        <?php
    }
}
 
?>
