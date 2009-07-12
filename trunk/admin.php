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

    global $rules, $DEA, $coach, $lng;

    // Quit if coach does not has administrator privileges.

    if (!is_object($coach) || $coach->ring > RING_COM)
        fatal("Sorry. Only site administrators and commissioners are allowed to access this section.");

    $ring_sys_access = array('usrman' => $lng->getTrn('secs/admin/um'), 'ldm' => $lng->getTrn('secs/admin/ldm'), 'import' => $lng->getTrn('secs/admin/import'), 'chtr' => $lng->getTrn('secs/admin/th'), 'ctman' => $lng->getTrn('secs/admin/delete'));
    $ring_com_access = array('tournament' => $lng->getTrn('secs/admin/schedule'), 'log' => $lng->getTrn('secs/admin/log'));
    
    if (isset($_GET['subsec']) && $coach->ring != RING_SYS && in_array($_GET['subsec'], array_keys($ring_sys_access)))
        fatal("Sorry. Your access level does not allow you opening the requested page.");

    // Permissions OK => Continue...
    /*
    title($lng->getTrn('secs/admin/administration'));

    ?>
    <div class="admin_menu">
        <?php
            foreach ($ring_com_access as $lnk => $desc) {
                echo "<a href='index.php?section=admin&amp;subsec=$lnk'>$desc</a>&nbsp;&nbsp;&nbsp;";
            }
            if ($coach->ring == RING_SYS) {
                foreach ($ring_sys_access as $lnk => $desc) {
                    echo "<a href='index.php?section=admin&amp;subsec=$lnk'>$desc</a>&nbsp;&nbsp;&nbsp;";
                }            
            }
        ?>
        <hr>
    </div>
    <?php
    */
    $coaches = Coach::getCoaches(); // Used by multiple sub-sections.
    
    // If an admin section was requested then call it, else show admin main page.
    if (isset($_GET['subsec']) && $_GET['subsec'] == 'usrman') {
        if (isset($_POST['button'])) {
            
            switch ($_POST['button']) {
            
                case 'Create coach':
                    if (get_magic_quotes_gpc()) {
                        $_POST['new_name'] = stripslashes($_POST['new_name']);
                        $_POST['new_realname'] = stripslashes($_POST['new_realname']);
                        $_POST['new_mail'] = stripslashes($_POST['new_mail']);
                        $_POST['new_phone'] = stripslashes($_POST['new_phone']);
                        $_POST['new_passwd'] = stripslashes($_POST['new_passwd']);
                    }
                    status(Coach::create(array(
                        'name'     => $_POST['new_name'], 
                        'realname' => $_POST['new_realname'], 
                        'passwd'   => $_POST['new_passwd'], 
                        'mail'     => $_POST['new_mail'], 
                        'phone'    => $_POST['new_phone'], 
                        'ring'     => $_POST['new_ring'],
                    )));
                    break;
                    
                case 'Change privileges':
                    $coach = new Coach($_POST['chring_coachid']);
                    if (!is_object($coach)) {
                        status(false);
                        return;
                    }
                    else {
                        status($coach->setRing($_POST['chring_ring']));
                    }
                    break;
                    
                case 'Change password':
                    $coach = new Coach($_POST['chpass_coachid']);
                    status($coach->setPasswd($_POST['ch_passwd']));
                    break;
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

        title($lng->getTrn('secs/admin/um'));
        objsort($coaches, array('+name'));
        $rings = array(
            RING_SYS    => 'Ring '.RING_SYS.': Site admin', 
            RING_COM    => 'Ring '.RING_COM.': League commissioner', 
            RING_COACH  => 'Ring '.RING_COACH.': Regular coach'
        );
        
        ?>
        <form method="POST" action="?section=admin&amp;subsec=usrman">
        
            <b>OBBLM access levels:</b>
            <ul>
                <li><?php echo $lng->getTrn('secs/admin/access/r2');?></li>
                <li><?php echo $lng->getTrn('secs/admin/access/r1');?></li>
                <li><?php echo $lng->getTrn('secs/admin/access/r0');?></li>
            </ul>
        
            <div class="adminBox">
                <div class="boxTitle3">
                    Create new coach
                </div>
                <div class="boxBody">
                    Coach name (login):<br> <input type="text" name="new_name" size="20" maxlength="50"><br><br>
                    Full name:<br> <input type="text" name="new_realname" size="20" maxlength="50"><br><br>
                    Mail (optional):<br> <input type="text" name="new_mail" size="20" maxlength="129"><br><br>
                    Phone (optional):<br> <input type="text" name="new_phone" size="20" maxlength="129"><br><br>
                    Password:<br> <input type="password" name="new_passwd" size="20" maxlength="50"><br><br>
                    Site access level:<br>
                    <select name="new_ring">
                        <?php
                        foreach ($rings as $r => $desc) {
                            echo "<option value='$r' ".(($r == RING_COACH) ? 'SELECTED' : '').">$desc</option>\n";
                        }
                        ?>
                    </select>
                    <br><br>
                    <input type="submit" name="button" value="Create coach">
                </div>
            </div>
            
            <div class="adminBox">
                <div class="boxTitle3">
                    Change coach access level
                </div>
                <div class="boxBody">
                    Coach:<br>
                    <select name="chring_coachid">
                        <?php
                        foreach ($coaches as $coach) {
                            echo "<option value='$coach->coach_id'>$coach->name</option>\n";
                        }
                        ?>
                    </select>
                    <br><br>
                    Site access level:<br>
                    <select name="chring_ring">
                        <?php
                        foreach ($rings as $r => $desc) {
                            echo "<option value='$r'>$desc</option>\n";
                        }
                        ?>
                    </select>
                    <br><br>
                    <input type="submit" name="button" value="Change privileges">
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
                                <td><b>Nickname</b></td>
                                <td><b>Phone</b></td>
                                <td><b>Mail</b></td>
                                <td><b>Coach ID</b></td>
                                <td><b>Access level</b></td>
                            </tr>
                        <?php
                        foreach ($coaches as $coach) {
                            echo "<tr>\n";
                            echo "<td>$coach->realname</td>\n";
                            echo "<td>$coach->name</td>\n";
                            echo "<td>".((empty($coach->phone)) ? '<i>None</i>' : $coach->phone)."</td>\n";
                            echo "<td>".((empty($coach->mail)) ? '<i>None</i>' : "<a href='mailto:$coach->mail'>$coach->mail</a>")."</td>\n";
                            echo "<td>$coach->coach_id</td>\n";
                            echo "<td>" . $rings[$coach->ring] . " </td>\n";
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
                </td>
                <td>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                </td>
                <td>
                    <div id="existTour" style="display:none;">
                        <b><font color="blue"><?php echo $lng->getTrn('secs/admin/add_match');?></font></b><br>
                        <select name="existTour">
                            <optgroup label="New FFA">
                                <option value='-1'><?php echo $lng->getTrn('secs/admin/new_tour');?></option>
                            </optgroup>
                            <optgroup label="Existing FFA">
                            <?php
                            foreach (Tour::getTours() as $t)
                                if ($t->type == TT_FFA)
                                    echo "<option value='$t->tour_id'>$t->name".(($t->locked) ? '&nbsp;&nbsp;(LOCKED)' : '')."</option>\n";
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
            <b><?php echo $lng->getTrn('secs/admin/tour_type');?>:</b><br>
            <input type="radio" onClick="chTour(this.value);" name="type" value="<?php echo TT_FFA;?>" CHECKED> FFA (free for all) single match<br>
            <input type="radio" onClick="chTour(this.value);" name="type" value="<?php echo TT_RROBIN;?>"> Round-Robin<br>
            <br>
            <?php echo $lng->getTrn('secs/admin/rrobin_rnds');?><br>
            <select name="rounds">
            <?php
            foreach (range(1, 10) as $i) echo "<option value='$i'>$i</option>\n";
            ?>
            </select>
            <br><br>
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
            var SOLD    = <?php echo SOLD; ?>;
        </script>
        <?php

        // Input sent?
        if (isset($_POST['button'])) {

            $in = array('coach', 'name', 'race', 'treasury', 'apothecary', 'rerolls', 'fan_factor', 'ass_coaches', 'cheerleaders', 'players',
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
            
        title($lng->getTrn('secs/admin/import'));
        echo $lng->getTrn('secs/admin/import_notice1');
        ?>
        <hr align="left" width="200px">
        <br>
        <?php echo $lng->getTrn('secs/admin/method1');?><br>
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
        <?php echo $lng->getTrn('secs/admin/method2');?><br>
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
            <u><?php echo $lng->getTrn('secs/admin/import_notice2/note');?></u> 
            <ul>
                <li><?php echo $lng->getTrn('secs/admin/import_notice2/e1');?></li>
                <li><?php echo $lng->getTrn('secs/admin/import_notice2/e2');?></li>
                <li><?php echo $lng->getTrn('secs/admin/import_notice2/e3');?></li>
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
    elseif (isset($_GET['subsec']) && $_GET['subsec'] == 'chtr') {

        if (isset($_POST['type'])) {
            switch ($_POST['type'])
            {
                case 'change':
                    $t = new Tour($_POST['trid']);
                    status($t->chRS($_POST['rs']) && $t->chType($_POST['ttype']) && $t->rename($_POST['tname']));
                    break;

                case 'delete':
                    if (isset($_POST['delete']) && $_POST['delete']) {
                        $t = new Tour($_POST['trid']);
                        status($t->delete(true));
                    }
                    else {
                        status(false, 'Please mark the agreement box before trying to delete a tournament.');
                    }
                    break;
                    
                case 'move':
                    $t = new Tour($_POST['trid']);
                    status($t->ch_did($_POST['did']));
                    break;
                    
                case 'lock':
                    $t = new Tour($_POST['trid']);
                    status($t->setLocked(isset($_POST['lock']) && $_POST['lock']));
                    break;                	
            }
        }

        title($lng->getTrn('secs/admin/th'));
        $tours = Tour::getTours();
        $nameChangeJScode = "e = document.forms['tourForm'].elements; e['tname'].value = e['trid'].options[e['trid'].selectedIndex].text;";
        
        ?>
        <div class="adminBox">
            <div class="boxTitle3"><?php echo $lng->getTrn('secs/admin/edit_tour');?></div>
            <div class="boxBody">
            <form id='tourForm' method="POST">
                <br>
                <b>Edit tournament:</b><br>
                <select name="trid" onChange="<?php echo $nameChangeJScode;?>">
                    <?php
                    foreach ($tours as $t) {
                        echo "<option value='$t->tour_id'>$t->name</option>\n";
                    }
                    ?>
                </select>
                <br><br>
                <hr>
                <br>
                <b>New name:</b><br>
                <input type='text' name='tname' length='20' value=''>

                <script language="JavaScript" type="text/javascript">
                    <?php echo $nameChangeJScode;?>
                </script>

                <br><br>
                <b>New ranking system:</b> (<?php echo $lng->getTrn('secs/admin/prefixes');?>)<br>
                <select name='rs'>
                <?php
                foreach (Tour::getRSSortRules(false, true) as $idx => $r) {
                    echo "<option value='$idx'>RS #$idx | $r</option>\n";
                }
                ?>
                </select>
               
                <br><br>
                <b>New tournament type:</b> <?php echo $lng->getTrn('secs/admin/conv_warn');?><br>
                <input type="radio" name="ttype" value="<?php echo TT_RROBIN;?>" > Round-Robin<br>
                <input type="radio" name="ttype" value="<?php echo TT_FFA;?>" CHECKED> FFA (free for all) single match<br>
                <br>
                
                <input type="hidden" name="type" value="change">
                <input type="submit" value="Submit changes" <?php echo (empty($tours)) ? 'DISABLED' : ''?>>
                <br>
            </form>
            </div>
        </div>

        <div class="adminBox">
            <div class="boxTitle3"><?php echo $lng->getTrn('secs/admin/tour_del');?></div>
            <div class="boxBody">
            <form method="POST">
                <b>I wish to delete the following tournament</b><br>
                <select name="trid">
                    <?php
                    foreach ($tours as $t) {
                        echo "<option value='$t->tour_id'>$t->name</option>\n";
                    }
                    ?>
                </select>
                <br><br>
                <b><?php echo $lng->getTrn('secs/admin/advise/have_read');?>:</b> 
                <input type="checkbox" name="delete" value="1">
                <br><br>
                <b><u>Advisement/warning:</u></b><br>
                <?php echo $lng->getTrn('secs/admin/advise/pre');?>
                <br>
                <ul>
                    <li><?php echo $lng->getTrn('secs/admin/advise/e1');?></li>
                    <li><?php echo $lng->getTrn('secs/admin/advise/e2');?></li>
                </ul>
                <br>           
                <input type="hidden" name="type" value="delete">
                <input type="submit" value="Delete" onclick="if(!confirm('Are you absolutely sure you want to delete this tournament?')){return false;}">
            </form>
            </div>
        </div>

        <div class="adminBox">
            <div class="boxTitle3"><?php echo $lng->getTrn('secs/admin/move_div');?></div>
            <div class="boxBody">
            <form method="POST">
                <b>Move tournament</b><br>
                <select name="trid">
                    <?php
                    foreach ($tours as $t) {
                        echo "<option value='$t->tour_id'>$t->name</option>\n";
                    }
                    ?>
                </select><br><br>
                <b>...to the division</b><br>
                <select name="did">
                    <?php
                    foreach (Division::getDivisions() as $d) {
                        echo "<option value='$d->did'>$d->name</option>\n";
                    }
                    ?>
                </select><br><br>
                <input type="hidden" name="type" value="move">
                <input type="submit" value="Move">
            </form>
            </div>
        </div>
        
        <div class="adminBox">
            <div class="boxTitle3">Lock/unlock tournament</div>
            <div class="boxBody">
            <form method="POST">
                <b>Tournament</b><br>
                <select name="trid">
                    <?php
                    foreach ($tours as $t) {
                        echo "<option value='$t->tour_id'>$t->name".(($t->locked) ? ' (is locked)' : '')."</option>\n";
                    }
                    ?>
                </select><br><br>
                Lock/unlock (check/unchecked)?
                <input type="checkbox" name="lock" value="1">
                <br><br>
                <input type="hidden" name="type" value="lock">
                <input type="submit" value="OK">
            </form>
            </div>
        </div>
        <?php
    }
    elseif (isset($_GET['subsec']) && $_GET['subsec'] == 'log') {
        title($lng->getTrn('secs/admin/log'));
        echo "<table style='width:100%;'>\n";
        echo "<tr><td><i>Date</i></td><td><i>Message</i></td></tr>\n";
        echo "<tr><td colspan='2'><br></td></tr>";
        foreach (SiteLog::getLogs() as $l) {
            echo "<tr><td>$l->date</td><td>$l->txt</td></tr>\n";
        }
        echo "</table>\n";
    }
    elseif (isset($_GET['subsec']) && $_GET['subsec'] == 'ctman') {
        
        title($lng->getTrn('secs/admin/delete'));
        
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
                    <b><?php echo $lng->getTrn('secs/admin/retire/note');?></b>
                    <ul>
                        <li><?php echo $lng->getTrn('secs/admin/retire/e1');?></li>
                        <li><?php echo $lng->getTrn('secs/admin/retire/e2');?></li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td valign='top'>
                    <div class="adminBox">
                        <div class="boxTitle3">Retire team</div>
                        <div class="boxBody">
                        <form method="POST">
                        <select name='id'>
                            <?php
                            foreach ($teams as $t) {
                                echo "<option value='$t->team_id' ".(($t->is_retired) ? 'style="background-color: orange;"' : '').">$t->name</option>\n";
                            }
                            ?>
                        </select><br><br>
                        <?php echo $lng->getTrn('secs/admin/retire/unretire');?> <input type='checkbox' name='unretire' value='1'><br><br>
                        <input type='submit' value='Retire/unretire'>
                        <input type='hidden' name='type' value='rt'>
                        </form>
                        </div>
                    </div>
                </td>
                <td valign='top'>
                    <div class="adminBox">
                        <div class="boxTitle3">Delete team</div>
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
                    <div class="adminBox">
                        <div class="boxTitle3">Retire coach</div>
                        <div class="boxBody">
                        <form method="POST">
                        <select name='id'>
                            <?php
                            foreach ($coaches as $c) {
                                echo "<option value='$c->coach_id' ".(($c->retired) ? 'style="background-color: orange;"' : '').">$c->name</option>\n";
                            }
                            ?>
                        </select><br><br>
                        <?php echo $lng->getTrn('secs/admin/retire/unretire');?> <input type='checkbox' name='unretire' value='1'><br><br>
                        <input type='submit' value='Retire/unretire'>
                        <input type='hidden' name='type' value='rc'>
                        </form>
                        </div>
                    </div>
                </td>
                <td valign='top'>
                    <div class="adminBox">
                        <div class="boxTitle3">Delete coach</div>
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
    }
    elseif (isset($_GET['subsec']) && $_GET['subsec'] == 'ldm') {
        
        if (isset($_POST['type'])) {
            if (get_magic_quotes_gpc()) {
                foreach (array('name', 'location',) as $i) {
                    $_POST[$i] = isset($_POST[$i]) ? stripslashes($_POST[$i]) : '';
                }
            }
            $l = (isset($_POST['lid'])) ? new League($_POST['lid']) : null;
            $d = (isset($_POST['did'])) ? new Division($_POST['did']) : null;
            switch ($_POST['type'])
            {
                case 'new_league':      status(League::create($_POST['name'], $_POST['location'])); break;
                case 'new_division':    status(Division::create($_POST['lid'], $_POST['name'])); break;
                case 'mod_league':      status($l->setName($_POST['name']) && $l->setLocation($_POST['location'])); break;
                case 'mod_division':    status($d->setName($_POST['name']) && $d->set_f_lid($_POST['lid'])); break;
                case 'del_league':      status($l->delete()); break;
                case 'del_division':    status($d->delete()); break;
            }
        }
        
        title($lng->getTrn('secs/admin/ldm'));
        $leagues = League::getLeagues();
        $divisions = Division::getDivisions();
        
        ?>
        <table>
            <tr>
                <td valign='top'>
                <div class="adminBox">
                    <div class="boxTitle3">Create division</div>
                    <div class="boxBody">
                    <form method="POST">
                    League:<br>
                    <select name='lid'>
                        <?php
                        foreach ($leagues as $l) {
                            echo "<option value='$l->lid'>$l->name</option>\n";
                        }
                        ?>
                    </select><br><br>
                    Name:<br>
                    <input type="text" name="name"><br><br>
                    <input type='submit' value='Create' <?php echo empty($leagues) ? ' DISABLED ' : '';?>>
                    <input type='hidden' name='type' value='new_division'>
                    </form>
                    </div>
                </div>
                </td>
                <td valign='top'>
                <div class="adminBox">
                    <div class="boxTitle3">Modify division</div>
                    <div class="boxBody">
                    <form method="POST">
                    Division:<br>
                    <select name='did'>
                        <?php
                        foreach ($divisions as $d) {
                            echo "<option value='$d->did'>$d->name ($d->league_name)</option>\n";
                        }
                        ?>
                    </select><br><br>
                    Assigned to league:<br>
                    <select name='lid'>
                        <?php
                        foreach ($leagues as $l) {
                            echo "<option value='$l->lid'>$l->name</option>\n";
                        }
                        ?>
                    </select><br><br>
                    Name:<br>
                    <input type="text" name="name"><br><br>
                    <input type='submit' value='Modify' <?php echo empty($divisions) ? ' DISABLED ' : '';?>>
                    <input type='hidden' name='type' value='mod_division'>
                    </form>
                    </div>
                </div>
                </td>
                <td valign='top'>
                <div class="adminBox">
                    <div class="boxTitle3">Delete division</div>
                    <div class="boxBody">
                    <form method="POST">
                    Division:<br>
                    <select name='did'>
                        <?php
                        foreach ($divisions as $d) {
                            echo "<option value='$d->did'>$d->name ($d->league_name)</option>\n";
                        }
                        ?>
                    </select><br><br>
                    <input type='submit' value='Delete' <?php echo empty($divisions) ? ' DISABLED ' : '';?> onclick="if(!confirm('Warning: You should only delete devisions when no matches are assigned to it.')){return false;}">
                    <input type='hidden' name='type' value='del_division'>
                    </form>
                    </div>
                </div>
                </td>
            </tr>
            <tr><td colspan="3"><hr></td></tr>
            <tr>
                <td valign='top'>
                <div class="adminBox">
                    <div class="boxTitle3">Create league</div>
                    <div class="boxBody">
                    <form method="POST">
                    Name:<br>
                    <input type="text" name="name"><br><br>
                    Location:<br>
                    <input type="text" name="location"><br><br>
                    <input type='submit' value='Create'>
                    <input type='hidden' name='type' value='new_league'>
                    </form>
                    </div>
                </div>
                </td>
                <td valign='top'>
                <div class="adminBox">
                    <div class="boxTitle3">Modify league</div>
                    <div class="boxBody">
                    <form method="POST">
                    League:<br>
                    <select name='lid'>
                        <?php
                        foreach ($leagues as $l) {
                            echo "<option value='$l->lid'>$l->name</option>\n";
                        }
                        ?>
                    </select><br><br>
                    Name:<br>
                    <input type="text" name="name"><br><br>
                    Location:<br>
                    <input type="text" name="location"><br><br>
                    <input type='submit' value='Modify' <?php echo empty($leagues) ? ' DISABLED ' : '';?>>
                    <input type='hidden' name='type' value='mod_league'>
                    </form>
                    </div>
                </div>
                </td>
                <td valign='top'>
                <div class="adminBox">
                    <div class="boxTitle3">Delete league</div>
                    <div class="boxBody">
                    <form method="POST">
                    League:<br>
                    <select name='lid'>
                        <?php
                        foreach ($leagues as $l) {
                            echo "<option value='$l->lid'>$l->name</option>\n";
                        }
                        ?>
                    </select><br><br>
                    <input type='submit' value='Delete' <?php echo empty($leagues) ? ' DISABLED ' : '';?> onclick="if(!confirm('Warning: You should only delete leagues if empty, ie. no divisions/matches assigned to them.')){return false;}">
                    <input type='hidden' name='type' value='del_league'>
                    </form>
                    </div>
                </div>
                </td>
            </tr>
        </table>
        <?php
    }
    else {
        ?>
        <div style="height: 400px;" id="admin_everything">
        <?php echo $lng->getTrn('secs/admin/pick'); ?>
        </div>
        <?php
    }
}
 
?>
