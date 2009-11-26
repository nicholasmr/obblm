<?php
MTS('Loading SQL core...');
require('lib/class_sqlcore.php');
MTS('SQL core loaded.');

if (isset($_POST['act'])) {
    $act = $_POST['act']; # Shortcut.
    if (preg_match('/\(\)$/', $act)) {
        status(mysql_query("CALL $act"), "Ran $act");
    }
    else {
        switch ($act) 
        {
            case 'gdsync': status(SQLCore::syncGameData(), 'PHP game data synced with DB'); break;
            case 'tblidx': status(SQLCore::installTableIndexes(), 'Indexes installed'); break;
            case 'funcs': status(SQLCore::installProcsAndFuncs(true), 'DB functions and procedures installed'); break;
        }
    }
}
title($lng->getTrn('menu/admin_menu/cpanel'));
?>
<p>
This page allows access to OBBLM back-end maintenance and synchronisation routines.
</p>
<form method="POST">
    <b>DB synchronisation procedures:</b><br>
    <INPUT TYPE=RADIO NAME="act" VALUE="syncAll()"><i>syncAll()</i> - Synchronises all stats, relations and dynamic properties - may take a few minutes!<br>
    <INPUT TYPE=RADIO NAME="act" VALUE="syncAllMVs()"><i>syncAllMVs()</i> - Synchronises all stats.<br>
    <INPUT TYPE=RADIO NAME="act" VALUE="syncAllDProps()"><i>syncAllDProps()</i> - Synchronises all dynamic properties (TVs, PVs etc.).<br>
    <INPUT TYPE=RADIO NAME="act" VALUE="syncAllRels()"><i>syncAllRels()</i> - Synchronises all object (player, team, coach) ownership relations.<br>
    <br><b>DB maitenance:</b><br>
    <INPUT TYPE=RADIO NAME="act" VALUE="gdsync">Synchronise the PHP-stored BB game data (<i>lib/game_data*.php</i> files) with DB. <u>Run this when</u> having changed the BB game data files.<br>
    <INPUT TYPE=RADIO NAME="act" VALUE="funcs">Re-install DB back-end procedures and functions. <u>Run this when</u> having altered the "house ranking systems" rule definitions defined in <i>settings.php</i>.<br>
    <INPUT TYPE=RADIO NAME="act" VALUE="tblidx">Re-install table indexes.<br>
    <br>
    <input type="submit" name='submit' value="Run">
</form>

<?php
?>
