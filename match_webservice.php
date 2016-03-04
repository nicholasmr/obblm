<?php
require("header.php");

$_VISSTATE['COOCKIE'] = Coach::cookieLogin(); # If not already logged in then check for login-cookie and try to log in using the stored credentials.

if (!Coach::isLoggedIn())
    die("You must be logged into OBBLM to use this webservice.");

$action = $_REQUEST["action"];
if($action == "update") {
    $match = new Match($_POST["match_id"]);

    $match->update(array(
        'submitter_id'  => (int) $_SESSION['coach_id'],
        'stadium'       => (int) $_POST['stadium'],
        'gate'          => (int) $_POST['gate']*1000,
        'fans'          => (int) $_POST['fans'],
        'ffactor1'      => (int) $_POST['ff1'],
        'ffactor2'      => (int) $_POST['ff2'],
        'income1'       => (int) $_POST['inc1']*1000,
        'income2'       => (int) $_POST['inc2']*1000,
        'team1_score'   => (int) $_POST['result1'],
        'team2_score'   => (int) $_POST['result2'],
        'smp1'          => (int) $_POST['smp1'],
        'smp2'          => (int) $_POST['smp2'],
        'tcas1'         => (int) $_POST['tcas1'],
        'tcas2'         => (int) $_POST['tcas2'],
        'fame1'         => (int) $_POST['fame1'],
        'fame2'         => (int) $_POST['fame2'],
        'tv1'           => (int) $_POST['tv1']*1000,
        'tv2'           => (int) $_POST['tv2']*1000,
    ));

    $team = new Team($_POST["team_id"]);    
    foreach ($team->getPlayers() as $player) {
        if (!Match::player_validation($player, $match))
            continue;

        // We create zero entries for MNG player(s). This is required!
        $pid = $player->player_id;
        if ($player->getStatus($match->match_id) == MNG) {
            $_POST["mvp_$pid"]      = 0;
            $_POST["cp_$pid"]       = 0;
            $_POST["td_$pid"]       = 0;
            $_POST["intcpt_$pid"]   = 0;
            $_POST["bh_$pid"]       = 0;
            $_POST["si_$pid"]       = 0;
            $_POST["ki_$pid"]       = 0;
            $_POST["ir1_d1_$pid"]   = 0;
            $_POST["ir1_d2_$pid"]   = 0;
            $_POST["ir2_d1_$pid"]   = 0;
            $_POST["ir2_d2_$pid"]   = 0;
            $_POST["ir3_d1_$pid"]   = 0;
            $_POST["ir3_d2_$pid"]   = 0;
            $_POST["inj_$pid"]      = NONE;
            $_POST["agn1_$pid"]     = NONE;
            $_POST["agn2_$pid"]     = NONE;
        } 
        
        $match->entry($player->player_id, array(
            'mvp'     => $_POST["mvp_$pid"],
            'cp'      => $_POST["cp_$pid"],
            'td'      => $_POST["td_$pid"],
            'intcpt'  => $_POST["intcpt_$pid"],
            'bh'      => $_POST["bh_$pid"],
            'si'      => $_POST["si_$pid"],
            'ki'      => $_POST["ki_$pid"],
            'ir1_d1'  => $_POST["ir1_d1_$pid"],
            'ir1_d2'  => $_POST["ir1_d2_$pid"],
            'ir2_d1'  => $_POST["ir2_d1_$pid"],
            'ir2_d2'  => $_POST["ir2_d2_$pid"],
            'ir3_d1'  => $_POST["ir3_d1_$pid"],
            'ir3_d2'  => $_POST["ir3_d2_$pid"],
            'inj'     => $_POST["inj_$pid"],
            'agn1'    => $_POST["agn1_$pid"],
            'agn2'    => $_POST["agn2_$pid"]
        ));
    }
    
    $match->finalizeMatchSubmit();
    
} else if($action == "getplayerentries") {
    $match = new Match($_REQUEST["match_id"]);
    $team = new Team($_REQUEST["team_id"]);
    $playerEntries = array();
    foreach($team->getPlayers() as $player) {
        $playerId = $player->player_id;
        $playerEntry = $match->getPlayerEntry($playerId);
        
        if(!$playerEntry) {
            $playerEntry['mvp'] = 0;
            $playerEntry['cp'] = 0;
            $playerEntry['td'] = 0;
            $playerEntry['intcpt'] = 0;
            $playerEntry['bh'] = 0;
            $playerEntry['si'] = 0;
            $playerEntry['ki'] = 0;
            $playerEntry['ir1_d1'] = 0;
            $playerEntry['ir1_d2'] = 0;
            $playerEntry['ir2_d1'] = 0;
            $playerEntry['ir2_d2'] = 0;
            $playerEntry['ir3_d1'] = 0;
            $playerEntry['ir3_d2'] = 0;
            $playerEntry['inj'] = NONE;
            $playerEntry['agn1'] = NONE;
            $playerEntry['agn2'] = NONE;
        }
        
        $playerEntries[$playerId] = $playerEntry;
    }
    
    echo json_encode($playerEntries);
}

?>