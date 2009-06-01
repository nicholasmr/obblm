<?php

/*
 *  Copyright (c) Niels Orsleff Justesen <njustesen@gmail.com> and Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2007-2009. All Rights Reserved.
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
 
function team_roaster($team_id) {

    global $DEA;
    global $skillarray;
    global $rules;
    global $settings;
    global $lng;
    
    // Is team id valid?
    if (!get_alt_col('teams', 'team_id', $team_id, 'team_id'))
        fatal("Invalid team ID.");

    // Determine if visitor is team coach
    $team       = new Team($team_id);
    $coach      = isset($_SESSION['logged_in']) ? new Coach($_SESSION['coach_id']) : null;
    $ALLOW_EDIT = false;
    $JMP_ANC    = false; // Jump to team actions boxes HTML anchor after page load?

    if (is_object($coach) && ($team->owned_by_coach_id == $coach->coach_id || $coach->admin) && !$team->is_retired)
        $ALLOW_EDIT = true;
    
    // Detailed view wanted?
    $DETAILED = (isset($_GET['detailed']) && $_GET['detailed'] == 1) ? true : false;
    
    // Team management actions sent?
    if ($ALLOW_EDIT && isset($_POST['type'])) {

        if (get_magic_quotes_gpc()) {
            $_POST['name']     = stripslashes(isset($_POST['name'])  ? $_POST['name']  : '');
            $_POST['skill']    = stripslashes(isset($_POST['skill']) ? $_POST['skill'] : '');
            $_POST['thing']    = stripslashes(isset($_POST['thing']) ? $_POST['thing'] : '');
            $_POST['teamtext'] = stripslashes(isset($_POST['teamtext']) ? $_POST['teamtext'] : '');
            $_POST['txt']      = stripslashes(isset($_POST['txt']) ? $_POST['txt'] : '');
        }
        
        $p = (isset($_POST['player']) && $_POST['type'] != 'hire_player') ? new Player($_POST['player']) : null;
    
        switch ($_POST['type']) {

            case 'hire_player':
                $status = Player::create(array(
                    'nr'        => $_POST['number'], 
                    'position'  => $_POST['player'], 
                    'team_id'   => $team->team_id, 
                    'name'      => $_POST['name']),
                    (isset($_POST['as_journeyman']) && $_POST['as_journeyman']) ? true : false);
                status($status[0], (($status[0] == true) ? null : $status[1]));
                break;

            case 'hire_journeyman': status($p->hireJourneyman()); break;
            case 'fire_player':     status($p->sell()); break;
            case 'rename_player':   status($p->rename($_POST['name'])); break;
            case 'renumber_player': status($p->renumber($_POST['number'])); break;
            case 'rename_team':     status($team->rename($_POST['name'])); break;
            case 'buy_goods':       status($team->buy($_POST['thing'])); break;
            case 'drop_goods':      status($team->drop($_POST['thing'])); break;
            case 'ready_state':     status($team->setReady(isset($_POST['bool']))); break;
            
            case 'skill':        
                $type = null;
                $p->setChoosableSkills();
                if     (in_array($_POST['skill'], $p->choosable_skills['N skills'])) $type = 'N';
                elseif (in_array($_POST['skill'], $p->choosable_skills['D skills'])) $type = 'D';
                elseif (preg_match('/^ach_/', $_POST['skill']))                      $type = 'C';
                status($p->addSkill($type, $_POST['skill']));
                break;

            case 'teamtext': status($team->saveText($_POST['teamtext'])); break;
            case 'news':     status($team->writeNews($_POST['txt'])); break;
            case 'newsdel':  status($team->deleteNews($_POST['news_id'])); break;
            case 'newsedit': status($team->editNews($_POST['news_id'], $_POST['txt'])); break;

            case 'pic': 
                if (isset($_FILES['pic_stad'])) 
                    status(!$team->saveStadiumPic('pic_stad'));
                elseif (isset($_FILES['pic_logo']))
                    status(!$team->saveLogo('pic_logo'));
                break;
        }

        // Administrator tools used?
        if ($coach->admin) {

            switch ($_POST['type']) {
                
                case 'unbuy_player':      status($p->unbuy()); break;
                case 'unhire_journeyman': status($p->unhireJourneyman()); break;
                case 'unsell_player':     status($p->unsell()); break;
                case 'unbuy_goods':       status($team->unbuy($_POST['thing'])); break;
                case 'bank':              status($team->dtreasury(($_POST['sign'] == '+' ? 1 : -1) * $_POST['amount'] * 1000)); break;
                case 'chown':             status($team->setOwnership((int) $_POST['cid'])); break;
                case 'spp':               status($p->dspp(($_POST['sign'] == '+' ? 1 : -1) * $_POST['amount'])); break;
                case 'dval':              status($p->dval(($_POST['sign'] == '+' ? 1 : -1) * $_POST['amount']*1000)); break;
                
                case 'extra_skills':    
                    $func = $_POST['sign'] == '+' ? 'addSkill' : 'rmSkill';
                    status($p->$func('E', $_POST['skill'])); 
                    break;
                    
                case 'ach_skills':
                    $type = null;
                    if     (in_array($_POST['skill'], $p->ach_nor_skills))  $type = 'N';
                    elseif (in_array($_POST['skill'], $p->ach_dob_skills))  $type = 'D';
                    else                                                    $type = 'C'; # Assume it's a characteristic.
                    status($p->rmSkill($type, $_POST['skill']));
                    break;
            }
        }
    }
    
    // Set anchor jump value.
    if (isset($_POST['menu_tmanage']) || isset($_POST['menu_admintools'])) {
        $JMP_ANC = true;
    }
    
    // Lets prepare the players for the roster.
    $team = new Team($team_id); # Update team object in case of changes to team were made.
    $team->setExtraStats();
    $team->setStreaks(false);
    $players_org = $team->getPlayers(); 
    // Make two copies: We will be overwriting $players later when the roster has been printed, so that the team actions boxes have the correct untempered player data to work with.
    $players = array();
    foreach ($players_org as $p) {
        array_push($players, clone $p);
    }
    
    // Filter players depending on settings and view mode.
    $tmp_players = array();
    foreach ($players as $p) {
        if (
            !$DETAILED && ($p->is_dead || $p->is_sold) ||
            $DETAILED && !$settings['show_sold_journeymen'] && $p->is_journeyman && $p->is_sold
            ) {
            continue;
        }
        array_push($tmp_players, $p);
    }
    $players = $tmp_players;

    // Make the players ready for roster printing.
    foreach ($players as $p) {
    
        /* 
            Misc
        */
        $p->name = preg_replace('/\s/', '&nbsp;', $p->name);
        $p->position = preg_replace('/\s/', '&nbsp;', $p->position);
    
        /* 
            Colors
        */        
        
        // Fictive player color fields used for creating player table.
        $p->HTMLfcolor = '#000000';
        $p->HTMLbcolor = COLOR_HTML_NORMAL;
        
        if     ($p->is_sold && $DETAILED)   $p->HTMLbcolor = COLOR_HTML_SOLD; # Sold has highest priority.
        elseif ($p->is_dead && $DETAILED)   $p->HTMLbcolor = COLOR_HTML_DEAD;
        elseif ($p->is_mng)                 $p->HTMLbcolor = COLOR_HTML_MNG;
        elseif ($p->is_journeyman)          $p->HTMLbcolor = COLOR_HTML_JOURNEY;
        elseif ($p->mayHaveNewSkill())      $p->HTMLbcolor = COLOR_HTML_NEWSKILL;
        elseif ($DETAILED)                  $p->HTMLbcolor = COLOR_HTML_READY;

        $p->skills   = '<small>'.$p->getSkillsStr(true).'</small>';
        $p->injs     = $p->getInjsStr(true);
        $p->position = "<table style='border-spacing:0px;'><tr><td><img align='left' src='$p->icon' alt='player avatar'></td><td>$p->position</td></tr></table>";

        if ($DETAILED) {
            $p->cas = "$p->bh/$p->si/$p->ki";
            $p->spp = "$p->spp/$p->extra_spp";
        }
        
        // Characteristic's colors
        foreach (array('ma', 'ag', 'av', 'st') as $chr) {
            $sub = $p->$chr - $p->{"def_$chr"};
            if ($sub == 0) {
                // Nothing!
            }
            elseif ($sub == 1)  $p->{"${chr}_color"} = COLOR_HTML_CHR_EQP1;
            elseif ($sub > 1)   $p->{"${chr}_color"} = COLOR_HTML_CHR_GTP1;
            elseif ($sub == -1) $p->{"${chr}_color"} = COLOR_HTML_CHR_EQM1;
            elseif ($sub < -1)  $p->{"${chr}_color"} = COLOR_HTML_CHR_LTM1;
        }
        
        /* 
            New skills drop-down.
        */      
          
        $x = '';
        if ($ALLOW_EDIT && $p->mayHaveNewSkill()) {
            $p->setChoosableSkills();
            
            $x .= "<form method='POST'>\n";
            $x .= "<select name='skill'>\n";

            $x .= "<optgroup label='Normal skills'>\n";
            foreach ($p->choosable_skills['N skills'] as $s) {
                $x .= "<option value='$s'>$s</option>\n";
            }
            $x .= "</optgroup>\n";

            $x .= "<optgroup label='Double skills'>\n";
            foreach ($p->choosable_skills['D skills'] as $s) {
                $x .= "<option value='$s'>$s</option>\n";
            }
            $x .= "</optgroup>\n";
            
            $x .= "<optgroup label='Other'>\n";
            foreach (array('ma', 'st', 'ag', 'av') as $s) {
                if ($p->chrLimits('ach', $s))
                    $x .= "<option value='ach_$s'>+ " . ucfirst($s) . "</option>\n";
            }
            $x .= "</optgroup>\n";

            $x .= '
            </select>
            <input type="submit" name="button" value="OK">
            <input type="hidden" name="type" value="skill">
            <input type="hidden" name="player" value="'.$p->player_id.'">
            </form>
            </td>
            ';
        }
        $p->skills .= $x;
    }
    
    /* If enabled add stars and summed mercenaries entries to the roster */
    
    if ($DETAILED && $settings['show_stars_mercs'] && $rules['enable_stars_mercs']) {
    
        $stars = array();
        foreach (Star::getStars($team->team_id, false, false) as $s) {
            $s->name = preg_replace('/\s/', '&nbsp;', $s->name);
            $s->player_id = $s->star_id;
            $s->nr = 0;
            $s->position = "<table style='border-spacing:0px;'><tr><td><img align='left' src='$s->icon' alt='player avatar'></td><td><i>Star&nbsp;player</i></td></tr></table>";
            $s->skills = '<small>'.implode(', ', $s->skills).'</small>';
            $s->injs = '';
            $s->value = 0;
            $s->setStats($team->team_id, false, false);
            $s->cas = "$s->bh/$s->si/$s->ki"; // Must come after setStats(), since it else would overwrite.
            $s->is_dead = $s->is_sold = $s->is_mng = $s->is_journeyman = false;
            $s->HTMLbcolor = COLOR_HTML_STARMERC;
            array_push($stars, $s);
        }
        $players = array_merge($players, $stars);
        
        $smerc = (object) null;
        $smerc->mvp = $smerc->td = $smerc->cp = $smerc->intcpt = $smerc->bh = $smerc->si = $smerc->ki = $smerc->skills = 0;
        foreach (Mercenary::getMercsHiredByTeam($team->team_id) as $merc) {
            $smerc->mvp += $merc->mvp;
            $smerc->td += $merc->td;
            $smerc->cp += $merc->cp;
            $smerc->intcpt += $merc->intcpt;
            $smerc->bh += $merc->bh;
            $smerc->si += $merc->si;
            $smerc->ki += $merc->ki;
            $smerc->skills += $merc->skills;
        }
        $smerc->player_id = ID_MERCS;
        $smerc->nr = 0;
        $smerc->name = 'All&nbsp;mercenary&nbsp;hirings';
        $smerc->position = "<i>Mercenaries</i>";
        $smerc->cas = "$smerc->bh/$smerc->si/$smerc->ki";
        $smerc->ma = '-';
        $smerc->st = '-';
        $smerc->ag = '-';
        $smerc->av = '-';
        $smerc->skills = 'Total bought extra skills: '.$smerc->skills;
        $smerc->injs = '';
        $smerc->spp = '-';
        $smerc->value = 0;
        $smerc->is_dead = $smerc->is_sold = $smerc->is_mng = $smerc->is_journeyman = false;
        $smerc->HTMLbcolor = COLOR_HTML_STARMERC;
        array_push($players, $smerc);
    }

    /******************************
     * Team players table
     * ------------------
     *
     * Contains player information and menu(s) for skill choice.
     *
     ******************************/

    title($team->name . (($team->is_retired) ? ' <font color="red"> (Retired)</font>' : ''));
    
    $fields = array(
        'nr'        => array('desc' => 'Nr.'), 
        'name'      => array('desc' => 'Name', 'href' => array('link' => 'index.php?section=coachcorner', 'field' => 'player_id', 'value' => 'player_id')),
        'position'  => array('desc' => 'Position', 'nosort' => true), 
        'ma'        => array('desc' => 'Ma'), 
        'st'        => array('desc' => 'St'), 
        'ag'        => array('desc' => 'Ag'), 
        'av'        => array('desc' => 'Av'), 
        'skills'    => array('desc' => 'Skills', 'nosort' => true),
        'injs'      => array('desc' => 'Injuries', 'nosort' => true),
        'cp'        => array('desc' => 'Cp'), 
        'td'        => array('desc' => 'Td'), 
        'intcpt'    => array('desc' => 'Int'), 
        'cas'       => array('desc' => ($DETAILED) ? 'BH/SI/Ki' : 'Cas', 'nosort' => ($DETAILED) ? true : false),
        'mvp'       => array('desc' => 'MVP'), 
        'spp'       => array('desc' => ($DETAILED) ? 'SPP/extra' : 'SPP', 'nosort' => ($DETAILED) ? true : false),
        'value'     => array('desc' => 'Value', 'kilo' => true, 'suffix' => 'k'),  
    );

    sort_table(
        $lng->getTrn('secs/teams/playersof').' '.$team->name, 
        "index.php?section=coachcorner&amp;team_id=$team->team_id".(($DETAILED) ? '&amp;detailed=1' : '&amp;detailed=0'), 
        $players, 
        $fields, 
        ($DETAILED) ? array('+is_dead', '+is_sold', '+is_mng', '+is_journeyman', '+nr', '+name') : sort_rule('player'), 
        (isset($_GET['sort'])) ? array((($_GET['dir'] == 'a') ? '+' : '-') . $_GET['sort']) : array(),
        array('color' => ($DETAILED) ? true : false, 'doNr' => false)
    );
    
    // Okey, lets restore the $players array.
    $players = $players_org;
    
    /* 
        Show color descriptions in detailed view and links to special team page actions. 
    */

    ?>
    <table class="text">
        <tr>
            <td style="width: 100%;"> </td>
            <?php
            if ($DETAILED) {
                ?>
                <td style="background-color: <?php echo COLOR_HTML_READY;   ?>;"><font color='black'>Ready</font></td>
                <td style="background-color: <?php echo COLOR_HTML_MNG;     ?>;"><font color='black'>MNG</font></td>
                <td style="background-color: <?php echo COLOR_HTML_JOURNEY; ?>;"><font color='black'>Journey</font></td>
                <td style="background-color: <?php echo COLOR_HTML_DEAD;    ?>;"><font color='black'>Dead</font></td>
                <td style="background-color: <?php echo COLOR_HTML_SOLD;    ?>;"><font color='black'>Sold</font></td>
                <td style="background-color: <?php echo COLOR_HTML_STARMERC;?>;"><font color='black'>Star/merc</font></td>
                <td style="background-color: <?php echo COLOR_HTML_NEWSKILL;?>;"><font color='black'>New&nbsp;skill</font></td>
                <?php
            }
            ?>
        </tr>
        <tr>
            <td colspan="8">
            <?php
            $botocs = "";
            if ($settings['leegmgr_enabled']) $botocs = " <a href='leegmgr/xml_roster.php?teamid=$_GET[team_id]'>BOTOCS-XML</a>";
            echo "<a href='index.php?section=coachcorner&amp;team_id=$_GET[team_id]&amp;detailed=".(($DETAILED) ? 0 : 1)."'><b>".(($DETAILED) ? $lng->getTrn('secs/teams/n_view') : $lng->getTrn('secs/teams/d_view'))."</b></a>\n";
            echo "&nbsp;|&nbsp;<b><a href='handler.php?type=roster&amp;team_id=$_GET[team_id]&amp;detailed=" . ($DETAILED ? '1' : '0') . "'>PDF</a> <a href='handler.php?type=xmlexport&amp;tid=$_GET[team_id]'>XML</a>{$botocs} ".$lng->getTrn('secs/teams/roster')."</b>\n";
            if ($rules['enable_stars_mercs']) {
                echo "&nbsp;|&nbsp;<a href='javascript:void(0)' onClick=\"shh=document.getElementById('SHH'); if (shh.style.display != 'none'){shh.style.display='none'}else{shh.style.display='block'};\" title='Show/hide star hire history'><b>Star HH</b></a>\n";
                echo "&nbsp;|&nbsp;<a href='javascript:void(0)' onClick=\"mhh=document.getElementById('MHH'); if (mhh.style.display != 'none'){mhh.style.display='none'}else{mhh.style.display='block'};\" title='Show/hide mercenary hire history'><b>Merc. HH</b></a>\n";
            }
//            echo "&nbsp;|&nbsp;<a href='#anc_about'><b>About</b></a>\n";
            echo "&nbsp;|&nbsp;<a href='#anc_news'><b>News</b></a>\n";
            echo "&nbsp;|&nbsp;<a href='handler.php?type=inducements&amp;team_id=$team->team_id'><b>".$lng->getTrn('secs/teams/indctry')."</b></a>\n";
            echo "&nbsp;|&nbsp;<a href='handler.php?type=graph&amp;gtype=".SG_T_TEAM."&amp;id=$team->team_id''><b>Vis. stats</b></a>\n";
//            echo "&nbsp;|&nbsp;<a href='#gp'><b>Matches</b></a>\n";
//            echo "&nbsp;|&nbsp;<a href='#tr'><b>Rankings</b></a>\n";
            ?>
            </td>
        </tr>
        <tr><td class='seperator' colspan='8'></td></tr>
        <tr>
            <td colspan='8'>
            <div id='SHH'>
                <?php
                if ($rules['enable_stars_mercs']) {
                    $mdat = array();
                    foreach (Star::getStars($team->team_id, false, false) as $s) {
                        foreach ($s->getHireHistory($team->team_id, false, false) as $m) {
                            $o = (object) array();
                            foreach (array('match_id', 'date_played', 'hiredAgainstName') as $k) {
                                $o->$k = $m->$k;
                            }
                            $s->setStats(false, $m->match_id, false);
                            foreach (array('name', 'star_id', 'td', 'cp', 'intcpt', 'cas', 'bh', 'si', 'ki', 'mvp', 'spp') as $k) {
                                $o->$k = $s->$k;
                            }
                            $o->match = '[view]';
                            array_push($mdat, $o);
                        }
                    }
                    $fields = array(
                        'date_played'       => array('desc' => 'Hire date'), 
                        'name'              => array('desc' => 'Star', 'href' => array('link' => 'index.php?section=stars', 'field' => 'sid', 'value' => 'star_id')), 
                        'hiredAgainstName'  => array('desc' => 'Opponent team'), 
                        'match'             => array('desc' => 'Match', 'href' => array('link' => 'index.php?section=fixturelist', 'field' => 'match_id', 'value' => 'match_id'), 'nosort' => true), 
                        'cp'     => array('desc' => 'Cp'), 
                        'td'     => array('desc' => 'Td'), 
                        'intcpt' => array('desc' => 'Int'), 
                        'cas'    => array('desc' => 'Cas'), 
                        'bh'     => array('desc' => 'BH'), 
                        'si'     => array('desc' => 'Si'), 
                        'ki'     => array('desc' => 'Ki'), 
                        'mvp'    => array('desc' => 'MVP'), 
                        'spp'    => array('desc' => 'SPP'),
                    );
                    sort_table(
                        "<a name='shhanc'>$team->name's star hiring history</a>", 
                        "index.php?section=coachcorner&amp;team_id=$team->team_id".(($DETAILED) ? '&amp;detailed=1' : '&amp;detailed=0'), 
                        $mdat, 
                        $fields, 
                        sort_rule('star_HH'), 
                        (isset($_GET['sortshh'])) ? array((($_GET['dirshh'] == 'a') ? '+' : '-') . $_GET['sortshh']) : array(),
                        array('GETsuffix' => 'shh', 'doNr' => false, 'anchor' => 'shhanc')
                    );
                }
                ?>
            </div>
            </td>
        </tr>
        <tr><td class='seperator' colspan='8'></td></tr>
        <tr>
            <td colspan='8'>
            <div id='MHH'>
                <?php
                if ($rules['enable_stars_mercs']) {
                    $mdat = array();
                    foreach (Mercenary::getMercsHiredByTeam($team->team_id, false) as $m) {
                        $o = (object) array();
                        $o->date_played = get_alt_col('matches', 'match_id', $m->match_id, 'date_played');
                        $o->opponent = get_alt_col('teams', 
                            'team_id', 
                            ((get_alt_col('matches', 'match_id', $m->match_id, 'team1_id') == $team->team_id) 
                                ? get_alt_col('matches', 'match_id', $m->match_id, 'team2_id') 
                                : get_alt_col('matches', 'match_id', $m->match_id, 'team1_id')
                            ), 
                            'name');
                        $o->match = '[view]';
                        foreach (array('match_id', 'skills', 'mvp', 'cp', 'td', 'intcpt', 'bh', 'ki', 'si') as $f) {
                            $o->$f = $m->$f;
                        }
                        $o->cas = $o->bh+$o->ki+$o->si;
                        array_push($mdat, $o);
                    }
                    $fields = array(
                        'date_played'   => array('desc' => 'Hire date'), 
                        'opponent'      => array('desc' => 'Opponent team'), 
                        'match'         => array('desc' => 'Match', 'href' => array('link' => 'index.php?section=fixturelist', 'field' => 'match_id', 'value' => 'match_id'), 'nosort' => true), 
                        'skills' => array('desc' => 'Additional skills'), 
                        'cp'     => array('desc' => 'Cp'), 
                        'td'     => array('desc' => 'Td'), 
                        'intcpt' => array('desc' => 'Int'), 
                        'cas'    => array('desc' => 'Cas'), 
                        'bh'     => array('desc' => 'BH'), 
                        'si'     => array('desc' => 'Si'), 
                        'ki'     => array('desc' => 'Ki'), 
                        'mvp'    => array('desc' => 'MVP'), 
                    );
                    sort_table(
                        "<a name='mhhanc'>$team->name's mercenary hiring history</a>", 
                        "index.php?section=coachcorner&amp;team_id=$team->team_id".(($DETAILED) ? '&amp;detailed=1' : '&amp;detailed=0'), 
                        $mdat, 
                        $fields, 
                        sort_rule('star_HH'), 
                        (isset($_GET['sortmhh'])) ? array((($_GET['dirmhh'] == 'a') ? '+' : '-') . $_GET['sortmhh']) : array(),
                        array('GETsuffix' => 'mhh', 'doNr' => false, 'anchor' => 'mhhanc')
                    );
                }
                ?>
            </div>
            </td>
        </tr>
    </table>  
    
    <script language="JavaScript" type="text/javascript">
        <?php if (!isset($_GET['sortshh'])) echo "document.getElementById('SHH').style.display='none';\n"?>
        <?php if (!isset($_GET['sortmhh'])) echo "document.getElementById('MHH').style.display='none';\n"?>
    </script>
    
    <?php
    /******************************
     * Team management
     * ---------------
     *   
     * Here we are able to view team stats and manage the team, depending on visitors privileges.
     *
     ******************************/
    ?>
    <div class="tpageBox">
        <div class="boxTitle1"><a name='aanc'><?php echo $lng->getTrn('secs/teams/box_info/title');?></a></div>
        <div class="boxBody">
            <table width="100%">
                <tr>
                    <td><?php echo $lng->getTrn('secs/teams/box_info/coach');?></td>
                    <td><a href="index.php?section=coaches&amp;coach_id=<?php echo $team->owned_by_coach_id;?>"><?php echo $team->coach_name; ?></a></td>
                </tr>
                <tr>
                    <td><?php echo $lng->getTrn('secs/teams/box_info/race');?></td>
                    <td><a href='index.php?section=races&amp;race=<?php echo $team->f_race_id; ?>'><?php echo $team->race; ?></a></td>
                </tr>
                <tr>
                    <td><?php echo $lng->getTrn('secs/teams/box_info/ready');?></td>
                    <td><?php echo ($team->rdy) ? $lng->getTrn('secs/teams/yes') : $lng->getTrn('secs/teams/no'); ?></td>
                </tr>                
                <tr>
                    <td>TV</td>
                    <td><?php echo $team->value/1000 . 'k'; ?></td>
                </tr>
                <tr>
                    <td>Treasury</td>
                    <td><?php echo $team->treasury/1000 . 'k'; ?></td>
                </tr>
                <tr>
                <?php
                if ($team->race == 'Necromantic' || $team->race == 'Undead') {
                    ?>
                    <td>Necromancer</td>
                    <td>Yes</td>
                    <?php
                }
                elseif ($team->race != 'Khemri' && $team->race != 'Nurgle') {
                    echo "<td>Apothecary</td>\n";
                    echo "<td>" . ($team->apothecary ? $lng->getTrn('secs/teams/yes') : $lng->getTrn('secs/teams/no')) . "</td>\n";
                }
                ?>
                </tr>
                <tr>
                    <td>Rerolls</td>
                    <td><?php echo $team->rerolls; ?></td>
                </tr>
                <tr>
                    <td>Fan&nbsp;Factor</td>
                    <td><?php echo $team->fan_factor; ?></td>
                </tr>
                <tr>
                    <td>Ass.&nbsp;Coaches</td>
                    <td><?php echo $team->ass_coaches; ?></td>
                </tr>
                <tr>
                    <td>Cheerleaders</td>
                    <td><?php echo $team->cheerleaders; ?></td>
                </tr>
                <tr>
                    <td colspan=2><hr></td>
                </tr>
                <tr>
                    <td><?php echo $lng->getTrn('secs/teams/box_info/gp');?></td>
                    <td><?php echo $team->played; ?></td>
                </tr>
                <tr>
                    <td><?php echo $lng->getTrn('secs/teams/box_info/pct_won');?></td>
                    <td><?php echo sprintf("%1.1f", $team->win_percentage).'%'; ?></td>
                </tr>
                <tr>
                    <td><?php echo $lng->getTrn('secs/teams/box_info/tours_won');?></td>
                    <td><?php echo $team->won_tours; ?></td>
                </tr>
                <tr>
                    <td><?php echo $lng->getTrn('secs/teams/box_info/ws');?></td>
                    <td><?php echo $team->row_won; ?></td>
                </tr>
                <tr>
                    <td><?php echo $lng->getTrn('secs/teams/box_info/ls');?></td>
                    <td><?php echo $team->row_lost; ?></td>
                </tr>
                <tr>
                    <td><?php echo $lng->getTrn('secs/teams/box_info/ds');?></td>
                    <td><?php echo $team->row_draw; ?></td>
                </tr>
                <tr>
                    <td><?php echo $lng->getTrn('secs/teams/box_info/ltour');?></td>
                    <td><?php $lt = $team->getLatestTour(); echo ($lt) ? get_alt_col('tours', 'tour_id', $lt, 'name') : '<i>'.$lng->getTrn('secs/teams/none').'</i>'; ?></td>
                </tr>
                <tr valign="top">
                    <td><?php echo $lng->getTrn('secs/teams/box_info/toursplayed');?></td>
                    <td><small><?php $tours = $team->getToursPlayedIn(false); echo (empty($tours)) ? '<i>'.$lng->getTrn('secs/teams/none').'</i>' : implode(', ', array_map(create_function('$val', 'return $val->name;'), $tours)); ?></small></td>
                </tr>
                <tr valign="top">
                    <td><?php echo $lng->getTrn('secs/teams/box_info/prizes');?></td>
                    <td><small><?php $prizes = $team->getPrizes(true); echo (empty($prizes)) ? '<i>'.$lng->getTrn('secs/teams/none').'</i>' : $prizes; ?></small></td>
                </tr>
            </table>
        </div>
    </div>
    
    <?php
    if ($ALLOW_EDIT) {
        ?>
        <div class="tpageBox">
            <div class="boxTitle2"><?php echo $lng->getTrn('secs/teams/box_tm/title');?></div>
            <div class="boxBody">
                <?php
                
                $tmanage = array(
                    'hire_player'       => $lng->getTrn('secs/teams/box_tm/hire_player'),
                    'hire_journeyman'   => $lng->getTrn('secs/teams/box_tm/hire_journeyman'),
                    'fire_player'       => $lng->getTrn('secs/teams/box_tm/fire_player'),
                    'rename_player'     => $lng->getTrn('secs/teams/box_tm/rename_player'),
                    'renumber_player'   => $lng->getTrn('secs/teams/box_tm/renumber_player'),
                    'rename_team'       => $lng->getTrn('secs/teams/box_tm/rename_team'),
                    'buy_goods'         => $lng->getTrn('secs/teams/box_tm/buy_goods'),
                    'drop_goods'        => $lng->getTrn('secs/teams/box_tm/drop_goods'),
                    'ready_state'       => $lng->getTrn('secs/teams/box_tm/ready_state'),
                );

                // Set default choice.
                if (!isset($_POST['menu_tmanage'])) {
                    reset($tmanage);
                    $_POST['menu_tmanage'] = key($tmanage);
                }

                // If action is already chosen, then make it the default selected.
                if (isset($_POST['type']) && array_key_exists($_POST['type'], $tmanage)) {
                    $_POST['menu_tmanage'] = $_POST['type'];
                }
                
                ?>
                <form method="POST">
                    <select name="menu_tmanage">
                        <?php
                        foreach ($tmanage as $opt => $desc)
                            echo "<option value='$opt'" . ($_POST['menu_tmanage'] == $opt ? 'SELECTED' : '') . ">$desc</option>";
                        ?>
                    </select>
                    <input type="submit" name="tmanage" value="OK">
                </form>

                <br><i><?php echo $lng->getTrn('secs/teams/desc');?>:</i><br><br>
                <form name="form_tmanage" method="POST" enctype="multipart/form-data">
                <?php
                $DISABLE = false;
                
                switch ($_POST['menu_tmanage']) {
                
                    /**************
                     * Hire player
                     **************/
                        
                    case 'hire_player':
                        echo $lng->getTrn('secs/teams/box_tm/desc/hire_player');
                        ?>
                        <hr><br>
                        Player:<br>
                        <select name='player'>
                        <?php
                        $active_players = array_filter($players, create_function('$p', "return (\$p->is_sold || \$p->is_dead || \$p->is_mng) ? false : true;"));
                        $DISABLE = true;
                        foreach ($DEA[$team->race]['players'] as $pos => $details) {
                        
                            // Show players on the select list if buyable, or if player is a potential journeyman AND team has not reached journeymen limit.
                            if (($team->isPlayerBuyable($pos) && $team->treasury >= $details['cost']) || 
                                (($details['qty'] == 16 || (($rules['enable_lrb6x']) ? ($details['qty'] == 12) : false)) && count($active_players) < $rules['journeymen_limit'])) {
                                echo "<option value='$pos'>" . $details['cost']/1000 . "k | $pos</option>\n";
                                $DISABLE = false;
                            }
                        }
                        echo "</select>\n";
                        ?>
                        <br><br>
                        Number:<br>
                        <select name="number">
                        <?php
                        foreach (range(1, MAX_PLAYER_NR) as $i) {
                            foreach ($players as $p) {
                                if ($p->nr == $i && !$p->is_sold && !$p->is_dead)
                                    continue 2;
                            }
                            echo "<option value='$i'>$i</option>\n";
                        }
                        ?>
                        </select>
                        <br><br>
                        As journeyman: <input type="checkbox" name="as_journeyman" value="1">
                        <br><br>
                        Name:<br>
                        <input type="text" name="name">
                        <input type="hidden" name="type" value="hire_player">
                        <?php
                        break;
                        
                    /**************
                     * Hire journeymen
                     **************/
                    
                    case 'hire_journeyman':
                        echo $lng->getTrn('secs/teams/box_tm/desc/hire_journeyman');
                        ?>
                        <hr><br>
                        Player:<br>
                        <select name="player">
                        <?php
                        $DISABLE = true;
                        foreach ($players as $p) {
                            $price = $DEA[$team->race]['players'][$p->pos]['cost'];
                            if (!$p->is_journeyman || $p->is_sold || $p->is_dead || 
                                $team->treasury < $price || !$team->isPlayerBuyable($p->pos) || $team->isFull()) {
                                continue;
                            }

                            echo "<option value='$p->player_id'>$p->name | " . $price/1000 . " k</option>\n";
                            $DISABLE = false;
                        }
                        ?>
                        </select>
                        <input type="hidden" name="type" value="hire_journeyman">
                        <?php
                        break;

                    /**************
                     * Fire player
                     **************/
                        
                    case 'fire_player':
                        echo $lng->getTrn('secs/teams/box_tm/desc/fire_player').' '.$rules['player_refund']*100 . "%.\n";
                        ?>
                        <hr><br>
                        Player:<br>
                        <select name="player">
                        <?php
                        $DISABLE = true;
                        foreach ($players as $p) {
                            if ($p->is_dead || $p->is_sold)
                                continue;

                            echo "<option value='$p->player_id'>" . (($p->value/1000)*$rules['player_refund']) . "k refund | $p->name</option>\n";
                            $DISABLE = false;
                        }
                        ?>
                        </select>
                        <input type="hidden" name="type" value="fire_player">
                        <?php
                        break;
                        
                    /**************
                     * Rename player
                     **************/
                        
                    case 'rename_player':
                        echo $lng->getTrn('secs/teams/box_tm/desc/rename_player');
                        ?>
                        <hr><br>
                        Player:<br>
                        <select name="player">
                        <?php
                        $DISABLE = true;
                        foreach ($players as $p) {
                            unset($color);
                            if ($p->is_dead)
                                $color = COLOR_HTML_DEAD;
                            elseif ($p->is_sold)
                                $color = COLOR_HTML_SOLD;

                            echo "<option value='$p->player_id' ".(isset($color) ? "style='background-color: $color;'" : '').">$p->name</option>\n";
                            $DISABLE = false;
                        }
                        ?>
                        </select>
                        <br><br>
                        New name:<br>
                        <input type='text' name='name' maxlength=50 size=20>
                        <input type="hidden" name="type" value="rename_player">
                        <?php
                        break;

                    /**************
                     * Renumber player
                     **************/
                        
                    case 'renumber_player':
                        echo $lng->getTrn('secs/teams/box_tm/desc/renumber_player');
                        ?>
                        <hr><br>
                        Player:<br>
                        <select name="player">
                        <?php
                        $DISABLE = true;
                        foreach ($players as $p) {
                            unset($color);
                            if ($p->is_dead)
                                $color = COLOR_HTML_DEAD;
                            elseif ($p->is_sold)
                                $color = COLOR_HTML_SOLD;

                            echo "<option value='$p->player_id' ".(isset($color) ? "style='background-color: $color;'" : '').">$p->name</option>\n";
                            $DISABLE = false;
                        }
                        ?>
                        </select>
                        <br><br>
                        Number:<br>
                        <select name="number">
                        <?php
                        foreach (range(1, MAX_PLAYER_NR) as $i) {
                            echo "<option value='$i'>$i</option>\n";
                        }
                        ?>
                        </select>
                        <input type="hidden" name="type" value="renumber_player">
                        <?php
                        break;
                        
                    /**************
                     * Rename team
                     **************/
                        
                    case 'rename_team':
                        echo $lng->getTrn('secs/teams/box_tm/desc/rename_team');
                        ?>
                        <hr><br>
                        New name:<br>
                        <input type='text' name='name' maxlength='50' size='20'>
                        <input type="hidden" name="type" value="rename_team">
                        <?php
                        break;
                        
                    /**************
                     * Buy team goods
                     **************/
                        
                    case 'buy_goods':
                        echo $lng->getTrn('secs/teams/box_tm/desc/buy_goods');
                        $goods_temp = $team->getGoods();
                        if ($DEA[$team->race]['other']['RerollCost'] != $goods_temp['rerolls']['cost']) {
                            echo $lng->getTrn('secs/teams/box_tm/desc/buy_goods_warn');
                        }
                        ?>
                        <hr><br>
                        Thing:<br>
                        <select name="thing">
                        <?php
                        $DISABLE = true;
                        foreach ($team->getGoods() as $name => $details) {
                            if ($name == 'fan_factor' && !$rules['post_game_ff'] && $team->played > 0)
                                continue;
                            if (($team->$name < $details['max'] || $details['max'] == -1) && $team->treasury >= $details['cost']) {
                                echo "<option value='$name'>" . $details['cost']/1000 . "k | $details[item]</option>\n";
                                $DISABLE = false;
                            }
                        }
                        ?>
                        </select>
                        <input type="hidden" name="type" value="buy_goods">
                        <?php
                        break;
                        
                    /**************
                     * Let go (drop) of team goods
                     **************/
                        
                    case 'drop_goods':
                        echo $lng->getTrn('secs/teams/box_tm/desc/drop_goods');
                        ?>
                        <hr><br>
                        Thing:<br>
                        <select name="thing">
                        <?php
                        $DISABLE = true;
                        foreach ($team->getGoods() as $name => $details) {
                            if ($name == 'fan_factor' && !$rules['post_game_ff'] && $team->played > 0)
                                continue;
                            if ($team->$name > 0) {
                                echo "<option value='$name'>$details[item]</option>\n";
                                $DISABLE = false;
                            }
                        }
                        ?>
                        </select>
                        <input type="hidden" name="type" value="drop_goods">
                        <?php
                        break;
                        
                    /**************
                     * Set ready state
                     **************/
                        
                    case 'ready_state':
                        echo $lng->getTrn('secs/teams/box_tm/desc/ready_state');
                        ?>
                        <hr><br>
                        Team ready? 
                        <input type="checkbox" name="bool" value="1" <?php echo ($team->rdy) ? 'CHECKED' : '';?>>
                        <input type="hidden" name="type" value="ready_state">
                        <?php
                        break;
                    }
                    ?>
                    <br><br>
                    <input type="submit" name="button" value="OK" <?php echo ($DISABLE ? 'DISABLED' : '');?> >
                </form>
            </div>
        </div>
        <?php
        if ($coach->admin) {
            ?>
            <div class="tpageBox">
                <div class="boxTitle3"><?php echo $lng->getTrn('secs/teams/box_admin/title');?></div>
                <div class="boxBody">
                    <?php

                    $admin_tools = array(
                        'unbuy_player'      => $lng->getTrn('secs/teams/box_admin/unbuy_player'),
                        'unhire_journeyman' => $lng->getTrn('secs/teams/box_admin/unhire_journeyman'),
                        'unsell_player'     => $lng->getTrn('secs/teams/box_admin/unsell_player'),
                        'unbuy_goods'       => $lng->getTrn('secs/teams/box_admin/unbuy_goods'),
                        'bank'              => $lng->getTrn('secs/teams/box_admin/bank'),
                        'chown'             => $lng->getTrn('secs/teams/box_admin/chown'),
                        'spp'               => $lng->getTrn('secs/teams/box_admin/spp'),
                        'dval'              => $lng->getTrn('secs/teams/box_admin/dval'),
                        'extra_skills'      => $lng->getTrn('secs/teams/box_admin/extra_skills'),
                        'ach_skills'        => $lng->getTrn('secs/teams/box_admin/ach_skills'),
                    );

                    // Set default choice.
                    if (!isset($_POST['menu_admintools'])) {
                        reset($admin_tools);
                        $_POST['menu_admintools'] = key($admin_tools);
                    }

                    // If action is already chosen, then make it the default selected.
                    if (isset($_POST['type']) && array_key_exists($_POST['type'], $admin_tools)) {
                        $_POST['menu_admintools'] = $_POST['type'];
                    }
                    
                    ?>
                    <form method="POST">
                        <select name="menu_admintools">
                            <?php
                            foreach ($admin_tools as $opt => $desc)
                                echo "<option value='$opt'" . ($_POST['menu_admintools'] == $opt ? 'SELECTED' : '') . ">$desc</option>";
                            ?>
                        </select>
                        <input type="submit" name="admintools" value="OK">
                    </form>

                    <br><i><?php echo $lng->getTrn('secs/teams/desc');?>:</i><br><br>
                    <form name='form_admintools' method='POST'>
                        <?php
                        $DISABLE = false;

                        switch ($_POST['menu_admintools']) {

                            /***************
                             * Un-buy player
                             **************/
                                
                            case 'unbuy_player':
                                echo $lng->getTrn('secs/teams/box_admin/desc/unbuy_player');
                                ?>
                                <hr><br>
                                Player:<br>
                                <select name="player">
                                <?php
                                $DISABLE = true;
                                foreach ($players as $p) {
                                    if ($p->is_unbuyable && !$p->is_sold) {
                                            echo "<option value='$p->player_id'>$p->name</option>\n";
                                            $DISABLE = false;
                                    }
                                }
                                ?>
                                </select>
                                <input type="hidden" name="type" value="unbuy_player">
                                <?php
                                break;
                            
                            /***************
                             * Un-hire journeymen
                             **************/

                            case 'unhire_journeyman':
                                echo $lng->getTrn('secs/teams/box_admin/desc/unhire_journeyman');
                                ?>
                                <hr><br>
                                Player:<br>
                                <select name="player">
                                <?php
                                $DISABLE = true;
                                foreach ($players as $p) {
                                    if ($p->is_sold || $p->is_dead || $p->is_journeyman || $p->qty != 16)
                                        continue;
                                        
                                    echo "<option value='$p->player_id'>$p->name</option>\n";
                                    $DISABLE = false;
                                }
                                ?>
                                </select>
                                <input type="hidden" name="type" value="unhire_journeyman">
                                <?php
                                break;

                            /***************
                             * Un-sell player
                             **************/
                                
                            case 'unsell_player':
                                echo $lng->getTrn('secs/teams/box_admin/desc/unsell_player');
                                ?>
                                <hr><br>
                                Player:<br>
                                <select name="player">
                                <?php
                                $DISABLE = true;
                                foreach ($players as $p) {
                                    if ($p->is_sold) {
                                            echo "<option value='$p->player_id'>$p->name</option>\n";
                                            $DISABLE = false;
                                    }
                                }
                                ?>
                                </select>
                                <input type="hidden" name="type" value="unsell_player">
                                <?php
                                break;
                                
                            /***************
                             * Un-buy team goods
                             **************/
                                
                            case 'unbuy_goods':
                                echo $lng->getTrn('secs/teams/box_admin/desc/unbuy_goods');
                                ?>
                                <hr><br>
                                <select name="thing">
                                <?php
                                $DISABLE = true;
                                    foreach ($team->getGoods() as $name => $details) {
                                    if ($team->$name > 0) { # Only allow to un-buy those things which we already have some of.
                                        echo "<option value='$name'>$details[item]</option>\n";
                                        $DISABLE = false;
                                    }
                                }
                                ?>
                                </select>
                                <input type="hidden" name="type" value="unbuy_goods">
                                <?php
                                break;
                                
                            /***************
                             * Gold bank
                             **************/
                                
                            case 'bank':
                                echo $lng->getTrn('secs/teams/box_admin/desc/bank');
                                ?>
                                <hr><br>
                                &Delta; team treasury:<br>
                                <input type="radio" CHECKED name="sign" value="+">+
                                <input type="radio" name="sign" value="-">-
                                <input type='text' name="amount" maxlength=5 size=5>k
                                <input type="hidden" name="type" value="bank">
                                <?php
                                break;

                            /***************
                             * Change team ownership
                             **************/
                                
                            case 'chown':
                                echo $lng->getTrn('secs/teams/box_admin/desc/chown');
                                ?>
                                <hr><br>
                                New owner:<br>
                                <select name="cid">
                                <?php
                                foreach (Coach::getCoaches() as $c) {
                                    echo "<option value='$c->coach_id'>$c->name</option>\n";
                                }
                                ?>
                                </select>
                                <input type="hidden" name="type" value="chown">
                                <?php
                                break;
                                
                            /***************
                             * Manage extra SPP
                             **************/
                                
                            case 'spp':
                                echo $lng->getTrn('secs/teams/box_admin/desc/spp');
                                ?>
                                <hr><br>
                                Player:<br>
                                <select name="player">
                                <?php
                                $DISABLE = true;
                                objsort($players, array('+is_dead', '+name'));
                                foreach ($players as $p) {
                                    if (!$p->is_sold) {
                                        echo "<option value='$p->player_id'".(($p->is_dead) ? ' style="background-color:'.COLOR_HTML_DEAD.';"' : '').">$p->name</option>";
                                        $DISABLE = false;
                                    }
                                }
                                objsort($players, array('+nr'));
                                ?>
                                </select>
                                <br><br>
                                <input type="radio" CHECKED name="sign" value="+">+
                                <input type="radio" name="sign" value="-">-
                                <input type='text' name='amount' maxlength="5" size="5"> &Delta; SPP
                                <input type="hidden" name="type" value="spp">
                                <?php
                                break;

                            /***************
                             * Manage extra player value
                             **************/
                                
                            case 'dval':
                                echo $lng->getTrn('secs/teams/box_admin/desc/dval');
                                ?>
                                <hr><br>
                                Player:<br>
                                <select name="player">
                                <?php
                                $DISABLE = true;
                                objsort($players, array('+is_dead', '+name'));
                                foreach ($players as $p) {
                                    if (!$p->is_sold) {
                                        echo "<option value='$p->player_id'".(($p->is_dead) ? ' style="background-color:'.COLOR_HTML_DEAD.';"' : '').">$p->name (current extra = ".($p->extra_val/1000)."k)</option>";
                                        $DISABLE = false;
                                    }
                                }
                                objsort($players, array('+nr'));
                                ?>
                                </select>
                                <br><br>
                                Set extra value to<br>
                                <input type="radio" CHECKED name="sign" value="+">+
                                <input type="radio" name="sign" value="-">-
                                <input type='text' name='amount' maxlength="10" size="6">k
                                <input type="hidden" name="type" value="dval">
                                <?php
                                break;

                            /***************
                             * Manage extra skills
                             **************/
                                
                            case 'extra_skills':
                                echo $lng->getTrn('secs/teams/box_admin/desc/extra_skills');
                                ?>
                                <hr><br>
                                Player:<br>
                                <select name="player">
                                <?php
                                $DISABLE = true;
                                foreach ($players as $p) {
                                    if (!$p->is_sold && !$p->is_dead) {
                                        echo "<option value='$p->player_id'>$p->name</option>";
                                        $DISABLE = false;
                                    }
                                }
                                ?>
                                </select>
                                <br><br>
                                Skill:<br>
                                <select name="skill">
                                <?php
                                foreach ($skillarray as $cat => $skills) {
                                    if ($cat == 'Achieved characteristics')
                                        continue;
                                        
                                    echo "<OPTGROUP LABEL='$cat'>";
                                    foreach ($skills as $skill) {
                                        echo "<option value='$skill'>$skill</option>";
                                    }
                                    echo "</OPTGROUP>";
                                }
                                ?>
                                </select>
                                <br><br>
                                Action (add/remove)<br>
                                <input type="radio" CHECKED name="sign" value="+">+
                                <input type="radio" name="sign" value="-">-
                                <input type="hidden" name="type" value="extra_skills">
                                <?php
                                break;

                            /***************
                             * Remove achived skills
                             **************/
                                
                            case 'ach_skills':
                                echo $lng->getTrn('secs/teams/box_admin/desc/ach_skills');
                                ?>
                                <hr><br>
                                Player:<br>
                                <select name="player">
                                <?php
                                $DISABLE = true;
                                foreach ($players as $p) {
                                    if (!$p->is_dead && !$p->is_sold) {
                                        echo "<option value='$p->player_id'>$p->name</option>\n";
                                        $DISABLE = false;
                                    }
                                }
                                ?>
                                </select>
                                <br><br>
                                Skill<br>
                                <select name="skill">
                                <?php
                                foreach ($skillarray as $cat => $skills) {
                                    if ($cat == 'Achieved characteristics')
                                        continue;
                                        
                                    echo "<OPTGROUP LABEL='$cat'>";
                                    foreach ($skills as $skill) {
                                        echo "<option value='$skill'>$skill</option>";
                                    }
                                    echo "</OPTGROUP>";
                                }
                                echo "<optgroup label='Other'>\n";
                                foreach (array('ma', 'st', 'ag', 'av') as $type) {
                                    echo "<option value='ach_$type'>+ " . ucfirst($type) . "</option>\n";
                                }
                                echo "</optgroup>\n";
                                ?>
                                </select>
                                <input type="hidden" name="type" value="ach_skills">
                                <?php
                                break;
                        }
                        ?>
                        <br><br>
                        <input type="submit" name="button" value="OK" <?php echo ($DISABLE ? 'DISABLED' : '');?> >
                    </form>
                </div>
            </div>
            <?php
        }
    }

    title("<a name='anc_about'>".$lng->getTrn('secs/teams/about')." $team->name</a>");
    ?>
    <table class='picAndText'>
        <tr>
            <td class='light'><b><?php echo $lng->getTrn('secs/teams/logo');?></b></td>
            <td class='light'><b><?php echo $lng->getTrn('secs/teams/stad');?></b></td>
            <td class='light'><b><?php echo $lng->getTrn('secs/teams/about');?></b></td>
        </tr>
        <tr>
            <td>
                <?php
                pic_box($team->getLogo(), $ALLOW_EDIT, '_logo');
                ?>
            </td>
            <td>
                <?php
                pic_box($team->getStadiumPic(), $ALLOW_EDIT,  '_stad');
                ?>
            </td>
            <td valign='top' style='width: 100%;'>
                <?php
                $txt = $team->getText();
                if (empty($txt)) {
                    $txt = $lng->getTrn('secs/teams/nowrite')." $team->name."; 
                }
                
                if ($ALLOW_EDIT) {
                    ?>
                    <form method='POST'>
                        <textarea name='teamtext' rows='15' style='width: 100%;'><?php echo $txt;?></textarea>
                        <br><br>
                        <input type="hidden" name="type" value="teamtext">
                        <center>
                        <input type="submit" name='Save' value='<?php echo $lng->getTrn('secs/teams/save');?>'>
                        </center>
                    </form>
                    <?php
                }
                else {
                    echo '<p>'.$txt."</p>\n";
                }
                ?>
            </td>
        </tr>
    </table>
    <?php
  
    title("<a name='anc_news'>".$lng->getTrn('secs/teams/news')."</a>");
    $news = $team->getNews(MAX_TNEWS);
    ?>
    <div class="row">
        <div class="tnewsBox">
            <div class="boxTitle1"><?php echo $lng->getTrn('secs/teams/tnews');?></div>
            <div class="boxBody">
            <?php
            $news_2 = array();
            foreach ($news as $n) {
                $news_2[] = '<p>'.$n->txt.
                '<div id="newsedit'.$n->news_id.'" style="display:none; clear:both;"><form method="POST">
                    <textarea name="txt" cols="60" rows="4">'.$n->txt.'</textarea>
                    <input type="hidden" name="type" value="newsedit">
                    <input type="hidden" name="news_id" value="'.$n->news_id.'">
                    <br><br>
                    <input type="submit" value="'.$lng->getTrn('secs/teams/submitnews').'">
                </form></div>
                <div style="text-align: right;"><p style="display: inline;">'.textdate($n->date, true).
                (($ALLOW_EDIT) 
                    ? " | <form method='POST' name='newsForm$n->news_id' style='display:inline; margin:0px;'>
                        <input type='hidden' name='type' value='newsdel'>
                        <input type='hidden' name='news_id' value='$n->news_id'>
                        <a href='javascript:void(0);' onClick='document.newsForm$n->news_id.submit();'>[".$lng->getTrn('secs/teams/delete')."]</a>
                        </form>".
                        "| <a href='javascript:void(0);' onClick=\"document.getElementById('newsedit".$n->news_id."').style.display='block';\">[".$lng->getTrn('secs/teams/edit')."]</a>"
                    : '')
                .'</p></div><br></p>';
            }
            echo implode("<hr>\n", $news_2);
            if (empty($news)) {
                echo '<i>'.$lng->getTrn('secs/teams/nonews').'</i>';
            }

            if ($ALLOW_EDIT) {
                ?>
                <hr>
                <br>
                <b><?php echo $lng->getTrn('secs/teams/wnews');?></b>
                <form method="POST">
                    <textarea name='txt' cols='60' rows='4'></textarea>
                    <br><br>
                    <input type="hidden" name="type" value="news">
                    <input type='submit' value="<?php echo $lng->getTrn('secs/teams/submitnews');?>">
                </form>
                <?php
            }
            ?>
            </div>    
        </div>
    </div>
    <?php

  
    title("<a name='gp'>".$lng->getTrn('secs/teams/gamesplayed')."</a>");
    
    if (isset($_POST['opid']) || isset($_POST['trid'])) {
        $_SESSION['opid'] = (int) $_POST['opid'];
        $_SESSION['trid'] = (int) $_POST['trid'];
    }
    $trid = isset($_SESSION['trid']) ? $_SESSION['trid'] : false;
    $opid = isset($_SESSION['opid']) ? $_SESSION['opid'] : false;
    
    ?>
    <form method="POST">
        <b><?php echo $lng->getTrn('secs/teams/showagainst');?></b>
        <select name="opid">
            <option value='-1'><?php echo $lng->getTrn('secs/teams/all');?></option>
            <?php
            $teams = Team::getTeams();
            objsort($teams, array('+name'));
            foreach ($teams as $t) {
                if ($t->team_id == $team->team_id)
                    continue;
                    
                echo "<option value='$t->team_id' ".(($opid && $t->team_id == $opid) ? ' SELECTED ' : '').">$t->name</option>\n";
            }
            ?>
        </select>
        &nbsp;&nbsp;<b><?php echo $lng->getTrn('secs/teams/fromtour');?></b>
        <select name="trid">
            <option value='-1'><?php echo $lng->getTrn('secs/teams/all');?></option>
            <?php
            foreach (Tour::getTours() as $tr) {
                echo "<option value='$tr->tour_id' ".(($trid && $tr->tour_id == $trid) ? ' SELECTED ' : '').">$tr->name</option>\n";
            }
            ?>        
        </select>
        <input type="hidden" name="gamesSort" value="1">
        &nbsp;&nbsp;&nbsp;
        <input type="submit" value="<?php echo $lng->getTrn('secs/teams/showmatches');?>">
        <br><br>
    </form>
    <?php
    
    if (isset($_POST['gamesSort'])) {
        ?>
        <script language="JavaScript" type="text/javascript">
            window.location = "#gp";
        </script>
        <?php
    }
    
    $matches = Stats::getPlayedMatches(
        STATS_TEAM, 
        $team->team_id, 
        MAX_RECENT_GAMES, 
        true,
        (($trid && $trid != -1) ? $trid : false), 
        (($opid && $opid != -1) ? $opid : false)
    );
    foreach ($matches as $m) {
        $me = ($team->team_id == $m->team1_id) ? 1 : 2;
        $op = ($me == 1) ? 2 : 1;
        $m->opponent = $m->{"team${op}_name"};
        $m->stadium = get_alt_col('teams', 'team_id', $m->stadium, 'name');
        $m->score = $m->team1_score. ' - ' . $m->team2_score;
        $m->result = matchresult_icon((($m->is_draw) ? 'D' : (($m->winner == $team->team_id) ? 'W' : 'L')));
        $m->match = '[view]';
        $m->tour = get_alt_col('tours', 'tour_id', $m->f_tour_id, 'name');
    }
    
    $fields = array(
        'date_played' => array('desc' => 'Date'),
        'tour'     => array('desc' => 'Tournament'),
        'opponent' => array('desc' => 'Opponent'), 
        'stadium'  => array('desc' => 'Stadium'), 
        'gate'     => array('desc' => 'Gate', 'kilo' => true, 'suffix' => 'k', 'href' => false), 
        'score'    => array('desc' => 'Score', 'nosort' => true), 
        'result'   => array('desc' => 'Result', 'nosort' => true), 
        'match'    => array('desc' => 'Match', 'href' => array('link' => 'index.php?section=fixturelist', 'field' => 'match_id', 'value' => 'match_id'), 'nosort' => true), 
    );
    
    sort_table(
        $lng->getTrn('secs/teams/gamesplayed'), 
        "index.php?section=coachcorner&amp;team_id=$team->team_id", 
        $matches, 
        $fields, 
        sort_rule('match'), 
        (isset($_GET['sortrg'])) ? array((($_GET['dirrg'] == 'a') ? '+' : '-') . $_GET['sortrg']) : array(),
        array('GETsuffix' => 'rg', 'anchor' => 'gp', 'doNr' => false) // recent games.
    );
    
    title("<a name='tr'>".$lng->getTrn('secs/teams/tourranks')."</a>");

    $tours = $team->getTourRankings();
    foreach ($tours as $t) {
        $ELORanks = ELO::getRanks($t->tour_id);
        $t->elo = $ELORanks[$team->team_id];
        
        $t->stnLink = '[View]';
        $t->finished = ($t->is_finished) ? 'Y' : 'N';
        if ($t->winner)
            $t->winner = get_alt_col('teams', 'team_id', $t->winner, 'name');        
        else
            $t->winner = '?';
    }

    $fields = array(
        'name'          => array('desc' => 'Tournament'),
        'date_created'  => array('desc' => 'Date started'),
        'finished'      => array('desc' => 'Finished (Y/N)?', 'nosort' => true),
        'teamRank'      => array('desc' => 'Rank/placement'), 
        'elo'           => array('desc' => 'ELO'), 
        'winner'        => array('desc' => 'Winner'), 
        'stnLink'       => array('desc' => 'Standings', 'href' => array('link' => 'index.php?section=fixturelist', 'field' => 'tour_id', 'value' => 'tour_id'), 'nosort' => true), 
    );
    
    sort_table(
        $lng->getTrn('secs/teams/tourranks'), 
        "index.php?section=coachcorner&amp;team_id=$team->team_id", 
        $tours, 
        $fields, 
        array('-date_created'), 
        (isset($_GET['sorttr'])) ? array((($_GET['dirtr'] == 'a') ? '+' : '-') . $_GET['sorttr']) : array(),
        array('GETsuffix' => 'tr', 'anchor' => 'tr', 'doNr' => false)
    );

    // If an team action was chosen, jump to actions HTML anchor.
    if ($JMP_ANC) {
        ?>
        <script language="JavaScript" type="text/javascript">
        window.location = "#aanc";
        </script>
        <?php
    }
   
    return true;
}

function player_roaster($player_id) {

    global $lng;

    // Is player id valid?
    if (!get_alt_col('players', 'player_id', $player_id, 'player_id') || !is_object($p = new Player($_GET['player_id'])))
        fatal("Invalid player ID.");

    $team = new Team(get_alt_col('players', 'player_id', $player_id, 'owned_by_team_id'));
    $coach = isset($_SESSION['logged_in']) ? new Coach($_SESSION['coach_id']) : null;
    $ALLOW_EDIT = (is_object($coach) && ($team->owned_by_coach_id == $coach->coach_id || $coach->admin) && !$team->is_retired);
    $p->setExtraStats(false);
    $p->setStreaks(false);
    $p->skills = $p->getSkillsStr(true);
    $p->injs = $p->getInjsStr(true);

    // Any save-actions made?
    if ($ALLOW_EDIT && isset($_POST['type'])) {
        switch ($_POST['type'])
        {
            case 'pic': 
                status(!$p->savePic('pic'));
                break;
                
            case 'playertext': 
                if (get_magic_quotes_gpc()) {
                    $_POST['playertext'] = stripslashes($_POST['playertext']);
                }
                status($p->saveText($_POST['playertext']));                
                break;
        }
    }

    /* Print player profile... */

    title($p->name);
    $players = $team->getPlayers();
    $i = $next = $prev = 0;
    $end = end(array_keys($players));
    foreach ($players as $player) {
        if ($player->player_id == $p->player_id) {
            if ($i == 0) {
                $prev = $end;
                $next = 1;
            }
            elseif ($i == $end) {
                $prev = $end - 1;
                $next = 0;
            }
            else {
                $prev = $i-1;
                $next = $i+1;
            }
        }
        $i++;
    }
    if (count($players) > 1) {
        echo "<center><a href='index.php?section=coachcorner&amp;player_id=".$players[$prev]->player_id."'>[".$lng->getTrn('secs/playerprofile/prev')."]</a> &nbsp;|&nbsp; <a href='index.php?section=coachcorner&amp;player_id=".$players[$next]->player_id."'>[".$lng->getTrn('secs/playerprofile/next')."]</a></center><br>";
    }
    ?>
    <div class="row">
        <div class="pboxShort">
            <div class="boxTitle2"><?php echo $lng->getTrn('secs/playerprofile/about');?></div>
            <div class="boxBody">
                <table class="pbox">
                    <tr>
                        <td><b><?php echo $lng->getTrn('secs/playerprofile/name');?></b></td>
                        <td><?php echo "$p->name (#$p->nr)"; ?></td>
                    </tr>
                    <tr>
                        <td><b><?php echo $lng->getTrn('secs/playerprofile/pos');?></b></td>
                        <td><?php echo $p->position; ?></td>
                    </tr>
                    <tr>
                        <td><b><?php echo $lng->getTrn('secs/playerprofile/team');?></b></td>
                        <td><a href="index.php?section=coachcorner&amp;team_id=<?php echo $p->owned_by_team_id; ?>"><?php echo $p->team_name; ?></a></td>
                    </tr>
                    <tr>
                        <td><b><?php echo $lng->getTrn('secs/playerprofile/bought');?></b></td>
                        <td><?php echo $p->date_bought; ?></td>
                    </tr>
                    <tr>
                        <td><b><?php echo $lng->getTrn('secs/playerprofile/status');?></b></td>
                        <td>
                        <?php 
                            if ($p->is_dead) {
                                $p->getDateDied();
                                echo "<b><font color='red'>DEAD</font></b> ($p->date_died)";
                            }
                            elseif ($p->is_sold) {
                                echo "<b>SOLD</b> ($p->date_sold)";
                            }
                            else {
                                echo (($status = strtolower($p->getStatus(-1))) == 'none') ? '<b><font color="green">Ready</font></b>' : "<b><font color='blue'>$status</font></b>"; 
                            }
                        ?>
                        </td>
                    </tr>
                    <tr>
                        <td><b>Value</b></td>
                        <td><?php echo $p->value/1000 .'k' ?></td>
                    </tr>
                    <tr>
                        <td><b>SPP/extra</b></td>
                        <td><?php echo "$p->spp/$p->extra_spp" ?></td>
                    </tr>
                    <tr>
                        <td><b>Wanted</b></td>
                        <td><?php echo ($p->isWanted()) ? '<b><font color="red">Yes</font></b>' : 'No';?></td>
                    </tr>
                    <tr>
                        <td><b>In HoF</b></td>
                        <td><?php echo ($p->isInHOF()) ? '<b><font color="green">Yes</font></b>' : 'No';?></td>
                    </tr>
                    <tr>
                        <td><b>Won</b></td>
                        <td><?php echo "$p->won ($p->row_won streaks)"; ?></td>
                    </tr>
                    <tr>
                        <td><b>Lost</b></td>
                        <td><?php echo "$p->lost ($p->row_lost streaks)"; ?></td>
                    </tr>
                    <tr>
                        <td><b>Draw</b></td>
                        <td><?php echo "$p->draw ($p->row_draw streaks)"; ?></td>
                    </tr>
                    <tr>
                        <td><b>Vis. stats</b></td>
                        <td><?php echo "<a href='handler.php?type=graph&amp;gtype=".SG_T_PLAYER."&amp;id=$p->player_id''><b>View</b></a>\n";; ?></td>
                    </tr>                    
                    <tr>
                        <td colspan="2"><hr></td>
                    </tr> 
                    <tr>
                        <td><b>Ma</b></td>
                        <td><?php echo $p->ma; ?></td>
                    </tr>
                    <tr>
                        <td><b>St</b></td>
                        <td><?php echo $p->st; ?></td>
                    </tr>
                    <tr>
                        <td><b>Ag</b></td>
                        <td><?php echo $p->ag; ?></td>
                    </tr>
                    <tr>
                        <td><b>Av</b></td>
                        <td><?php echo $p->av; ?></td>
                    </tr>
                    <tr>
                        <td><b>Skills</b></td>
                        <td><?php echo (empty($p->skills)) ? '<i>None</i>' : $p->skills; ?></td>
                    </tr>
                    <tr>
                        <td><b>Injuries</b></td>
                        <td><?php echo (empty($p->injs)) ? '<i>None</i>' : $p->injs; ?></td>
                    </tr>
                    <tr>
                        <td><b>Cp</b></td>
                        <td><?php echo $p->cp; ?></td>
                    </tr>
                    <tr>
                        <td><b>Td</b></td>
                        <td><?php echo $p->td; ?></td>
                    </tr>
                    <tr>
                        <td><b>Int</b></td>
                        <td><?php echo $p->intcpt; ?></td>
                    </tr>
                    <tr>
                        <td><b>BH/SI/Ki</b></td>
                        <td><?php echo "$p->bh/$p->si/$p->ki"; ?></td>
                    </tr>
                    <tr>
                        <td><b>Cas</b></td>
                        <td><?php echo $p->cas; ?></td>
                    </tr>
                    <tr>
                        <td><b>MVP</b></td>
                        <td><?php echo $p->mvp; ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="pboxShort">
            <div class="boxTitle2"><?php echo $lng->getTrn('secs/playerprofile/profile');?></div>
            <div class="boxBody">
                <i><?php echo $lng->getTrn('secs/playerprofile/pic');?></i><hr>
                <?php
                pic_box($p->getPic(), $ALLOW_EDIT);
                ?>
                <br><br>
                <i><?php echo $lng->getTrn('secs/playerprofile/pic');?></i><hr>
                <?php
                $txt = $p->getText(); 
                if (empty($txt)) {
                    $txt = $lng->getTrn('secs/playerprofile/nowrite').' '.$p->name; 
                }
                if ($ALLOW_EDIT) {
                    ?>
                    <form method="POST" enctype="multipart/form-data">
                        <textarea name='playertext' rows='8' cols='45'><?php echo $txt;?></textarea>
                        <br><br>
                        <input type="hidden" name="type" value="playertext">
                        <input type="submit" name='Save' value='<?php echo $lng->getTrn('secs/playerprofile/save');?>'>
                    </form>
                    <?php
                }
                else {
                    echo "<p>$txt</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="pboxLong">
            <div class="boxTitle3"><a href='javascript:void(0);' onClick="obj=document.getElementById('ach'); if (obj.style.display != 'none'){obj.style.display='none'}else{obj.style.display='block'};"><b>[+/-]</b></a> &nbsp;<?php echo $lng->getTrn('secs/playerprofile/ach');?></div>
            <div class="boxBody" id="ach">
                <table class="pbox">
                    <tr>
                        <td><b>Type</b></td>
                        <td><b>Tournament</b></td>
                        <td><b>Opponent</b></td>
                        <td><b>MVP</b></td>
                        <td><b>Cp</b></td>
                        <td><b>Td</b></td>
                        <td><b>Int</b></td>
                        <td><b>Cas</b></td>
                        <td><b>Score</b></td>
                        <td><b>Result</b></td>
                        <td><b>Match</b></td>
                    </tr>
                    <?php
                    foreach (array('intcpt' => 'Interceptions', 'cp' => 'Completions', 'td' => 'Touchdowns', 'mvp' => 'MVP awards', 'bh+ki+si' => 'Cas') as $s => $desc) {
                        $been_there = false;
                        foreach ($p->getAchEntries($s) as $entry) {
                            if (!$been_there)
                                echo "<tr><td colspan='11'><hr></td></tr>";
                            ?>
                            <tr>
                                <?php
                                $m = $entry['match_obj'];
                                if ($been_there) {
                                    echo '<td></td>'; 
                                }
                                else {
                                    echo "<td><i>$desc: " . (($desc == 'Cas') ? $p->cas : $p->$s) . "</i></td>";
                                    $been_there = true;
                                }
                                ?>
                                <td><?php echo get_alt_col('tours', 'tour_id', $m->f_tour_id, 'name'); ?></td>
                                <td><?php echo ($p->owned_by_team_id == $m->team1_id) ? $m->team2_name : $m->team1_name; ?></td>
                                <td><?php echo $entry['mvp']; ?></td>
                                <td><?php echo $entry['cp']; ?></td>
                                <td><?php echo $entry['td']; ?></td>
                                <td><?php echo $entry['intcpt']; ?></td>
                                <td><?php echo $entry['bh']+$entry['si']+$entry['ki']; ?></td>
                                <td><?php echo $m->team1_score .' - '. $m->team2_score; ?></td>
                                <td><?php echo matchresult_icon((($m->is_draw) ? 'D' : (($m->winner == $p->owned_by_team_id) ? 'W' : 'L'))); ?></td>
                                <td><a href='javascript:void(0)' onClick="window.open('index.php?section=fixturelist&amp;match_id=<?php echo $m->match_id;?>');">[view]</a></td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </table>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="pboxLong">
            <div class="boxTitle3"><a href='javascript:void(0);' onClick="obj=document.getElementById('mbest'); if (obj.style.display != 'none'){obj.style.display='none'}else{obj.style.display='block'};"><b>[+/-]</b></a> &nbsp;<?php echo $lng->getTrn('secs/playerprofile/best');?></div>
            <div class="boxBody" id="mbest">
                <table class="pbox">
                    <tr>
                        <td><b>Type</b></td>
                        <td><b>Tournament</b></td>
                        <td><b>Opponent</b></td>
                        <td><b>Td</b></td>
                        <td><b>Ki</b></td>
                        <td><b>Score</b></td>
                        <td><b>Result</b></td>
                        <td><b>Match</b></td>
                    </tr>
                    <?php
                    foreach (array('td' => 'scorer', 'ki' => 'killer') as $s => $desc) {
                        $been_there = false;
                        $matches = $p->getMatchMost($s);
                        foreach ($matches as $entry) {
                            if (!$been_there)
                                echo "<tr><td colspan='8'><hr></td></tr>";
                            ?>
                            <tr>
                                <?php
                                $m = $entry['match_obj'];
                                if ($been_there) {
                                    echo '<td></td>'; 
                                }
                                else {
                                    echo "<td><i>Top $desc: " . count($matches) . " times</i></td>";
                                    $been_there = true;
                                }
                                ?>
                                <td><?php echo get_alt_col('tours', 'tour_id', $m->f_tour_id, 'name'); ?></td>
                                <td><?php echo ($p->owned_by_team_id == $m->team1_id) ? $m->team2_name : $m->team1_name; ?></td>
                                <td><?php echo $entry['td']; ?></td>
                                <td><?php echo $entry['ki']; ?></td>
                                <td><?php echo $m->team1_score .' - '. $m->team2_score; ?></td>
                                <td><?php echo matchresult_icon((($m->is_draw) ? 'D' : (($m->winner == $p->owned_by_team_id) ? 'W' : 'L'))); ?></td>
                                <td><a href='javascript:void(0)' onClick="window.open('index.php?section=fixturelist&amp;match_id=<?php echo $m->match_id;?>');">[view]</a></td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="pboxLong">
            <div class="boxTitle3"><a href='javascript:void(0);' onClick="obj=document.getElementById('played'); if (obj.style.display != 'none'){obj.style.display='none'}else{obj.style.display='block'};"><b>[+/-]</b></a> &nbsp;<?php echo $lng->getTrn('secs/playerprofile/playedmatches');?></div>
            <div class="boxBody" id="played">
                <table class="pbox">
                    <tr>
                        <td><b>Date played</b></td>
                        <td><b>Tournament</b></td>
                        <td><b>Opponent</b></td>
                        <td><b>Score</b></td>
                        <td><b>Result</b></td>                        
                        <td><b>Match</b></td>
                    </tr>
                    <tr>
                        <td colspan="6"><hr></td>
                    </tr>
                    <?php
                    foreach (Stats::getPlayedMatches(STATS_PLAYER, $p->player_id, MAX_RECENT_GAMES, true) as $m) {
                        ?>
                        <tr>
                            <td><?php echo $m->date_played; ?></td>
                            <td><?php echo get_alt_col('tours', 'tour_id', $m->f_tour_id, 'name'); ?></td>
                            <td><?php echo ($p->owned_by_team_id == $m->team1_id) ? $m->team2_name : $m->team1_name; ?></td>
                            <td><?php echo $m->team1_score .' - '. $m->team2_score; ?></td>
                            <td><?php echo matchresult_icon((($m->is_draw) ? 'D' : (($m->winner == $p->owned_by_team_id) ? 'W' : 'L'))); ?></td>
                            <td><a href='javascript:void(0)' onClick="window.open('index.php?section=fixturelist&amp;match_id=<?php echo $m->match_id;?>');">[view]</a></td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Default open/close values for boxes -->
    <script language="JavaScript" type="text/javascript">
        document.getElementById('ach').style.display    = 'none';
        document.getElementById('mbest').style.display  = 'none';
        document.getElementById('played').style.display = 'block';
    </script>
    
    <?php

    return true;
}

function disp_teams($coach_id = null) {

    /* 
        First generate array of team objects. 
    */
    
    $teams = array();
    
    // Coach teams only?
    if (isset($coach_id)) {
        $coach = new Coach($coach_id);
        $teams = $coach->getTeams();
    }
    // All teams
    else {
        $teams = Team::getTeams();
    }
    
    objsort($teams, array('+name'));
    
    foreach ($teams as $t) {
        $retired = (($t->is_retired) ? '<b><font color="red">[R]</font></b>' : '');
        $t->name .= "</a>&nbsp;$retired<br><small>$t->coach_name</small><a>"; // The <a> tags are a little hack so that sort_table does not create the team link on coach name too.
        $t->logo = "<img border='0px' height='50' width='50' alt='Team race picture' src='" . $t->getLogo() . "'>";
        $t->retired = ($t->is_retired) ? '<b>Yes</b>' : 'No';
        $lt = $t->getLatestTour();
        $t->latest_tour = ($lt) ? get_alt_col('tours', 'tour_id', $lt, 'name') : '-';
        $prizes = $t->getPrizes(true);
        $t->prizes = (empty($prizes)) ? '<i>None</i>' : $prizes;
        $t->rdy = ($t->rdy) ? '<font color="green">Yes</font>' : '<font color="red">No</font>';
    }

    $fields = array(
        'logo'      => array('desc' => 'Logo', 'href' => array('link' => 'index.php?section=coachcorner', 'field' => 'team_id', 'value' => 'team_id'), 'nosort' => true), 
        'name'      => array('desc' => 'Name', 'href' => array('link' => 'index.php?section=coachcorner', 'field' => 'team_id', 'value' => 'team_id')),
        'rdy'       => array('desc' => 'Ready', 'nosort' => true), 
        'race'      => array('desc' => 'Race'), 
        'latest_tour' => array('desc' => 'Latest tour'), 
        'prizes'      => array('desc' => 'Prizes', 'nosort' => true), 
        'played'    => array('desc' => 'Games'), 
        'value'     => array('desc' => 'TV', 'kilo' => true, 'suffix' => 'k'),  
    );

    sort_table(
        "Teams ". (($coach_id) ? "<a href='javascript:void(0);' onclick=\"window.open('html/coach_corner_teams.html','ccorner_TeamsHelp','width=350,height=400')\">[?]</a>" : ''), 
        "index.php?section=".(($coach_id) ? 'coachcorner' : 'teams'), 
        $teams, 
        $fields, 
        array('+name'), 
        (isset($_GET['sort'])) ? array((($_GET['dir'] == 'a') ? '+' : '-') . $_GET['sort']) : array(),
        array('doNr' => false, 'noHelp' => true)
    );
    
    return true;
}


?>
