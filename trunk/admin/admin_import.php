<?php

global $T_INJS, $T_PMD_ACH, $T_PMD_IR, $T_PMD_INJ;
$T_INJS_REV = array_flip($T_INJS);

// Input sent?
if (isset($_FILES['xmlfile'])) {

    $file = $_FILES['xmlfile']['tmp_name'];
    $xml = new DOMDocument();
    $xml->load($file); 
    $xmlteams = $xml->schemaValidate('xml/import.xsd') ? simplexml_load_file($file) : (object) array('team' => array());

    $map = array(
        # Team::$createEXPECTED => XML import schema field name
        # If field is the same do not define entry!
        'owned_by_coach_id' => 'coach_id', 
        'f_race_id' => 'race_id',
        'rerolls' => 'rr',
        'ff_bought' => 'ff',
        'won_0' => 'won',
        'lost_0' => 'lost',
        'draw_0' => 'draw',
        'wt_0' => 'wt',
        'gf_0' => 'gf',
        'ga_0' => 'ga',
        'tcas_0' => 'tcas',
    );

    foreach ($xmlteams->team as $t) {
        # Corrections
        $t->played_0 = $t->won+$t->lost+$t->draw;
        $t->imported = 1;
        $t->f_lid = 0;
        # Add team
        status($tid = Team::create(array_merge(
            array_intersect_key((array) $t, array_fill_keys(Team::$createEXPECTED, null)), # Fields which are correctly named in XMl file.
            array_combine(array_keys($map), array_values(array_intersect_key((array) $t, array_fill_keys(array_values($map), null)))) # Mapped fields.
        )), 
        "Created team '$t->name'");
        # Add players
        if ($tid) {
            foreach ($t->players->player as $p) {
                $p = (object) ((array) $p); # Get rid of SimpleXML objects.
                list($status1, $pid) = Player::create(array(
                    'nr' => $p->nr, 'f_pos_id' => $p->pos_id, 'name' => $p->name, 'team_id' => $tid, 'forceCreate' => true,
                ));
                $status2 = true;
                if ($status1) {
                    # The status must be set as the "inj" (not agn) field for EVERY match (import) entry. 
                    # This is because MySQL may pick a random match data entry from which to get the status from.
                    $pstatus = $T_INJS_REV[strtoupper($p->status)];
                    # Injuries
                    foreach (array('ma', 'st', 'ag', 'av', 'ni') as $inj) {
                        $agn = $T_INJS_REV[strtoupper($inj)];
                        while ($p->{$inj}-- > 0) {
                            $status2 &= Match::ImportEntry($pid, array_merge(array_fill_keys(array_merge($T_PMD_ACH, $T_PMD_IR),0), array_combine($T_PMD_INJ, array($pstatus,$agn,($p->{$inj}-- > 0) ? $agn : NONE))));
                        }
                    }
                    # Set player achievements
                    $status2 &= Match::ImportEntry($pid, array_merge(array_intersect_key((array) $p, array_fill_keys($T_PMD_ACH,null)), array_combine($T_PMD_INJ,array($pstatus,NONE,NONE)), array_fill_keys($T_PMD_IR,0)));
                }
                status($status1 && $status2, "Added to '$t->name' player '$p->name'");
            }
            
            # Set correct treasury.
            $team = new Team($tid);
            $team->dtreasury($t->treasury*1000 - $team->treasury); // $t->treasury + $delta = XML value
        }
        
        // SYNC DATA!
#        SQLTriggers::run(T_SQLTRIG_MATCH_UPD, array('mid' => T_IMPORT_MID, 'trid' => 0, 'tid1' => $tid, 'tid2' => 0, 'played' => 0));
    }
}

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
<br>
<b>Please note</b> that you <i>must</i> run the "syncAll()" synchronization procedure from the <a href='index.php?section=admin&amp;subsec=cpanel'>OBBLM core panel</a> after importing teams.<br>
<br>
When importing teams you will need to know the IDs of the respective coaches, races and player positionals.<br>
Coach IDs can be found in the information section of the Coach's Corner pages.<br>
Race IDs and the IDs of the player positionals allowed by the race can be found on the race's information pages.


