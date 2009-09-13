<?php

if ( isset($_GET['replay']) )
{
    #<!-- BEGIN DOWNLOAD OF REPLAY -->
    #<!-- END DOWNLOAD OF REPLAY -->
    $mid = $_GET['replay'];
    header('Content-type: application/octec-stream');
    header('Content-Disposition: attachment; filename=match'.$mid.'.rep');
    $replayurl = "http://".$_SERVER["SERVER_NAME"]."/handler.php?type=leegmgr&replay=".$mid;
    $zip = file_get_contents( $replayurl );
    $start = strpos($zip, "<!-- BEGIN DOWNLOAD OF REPLAY -->") + 33;
    $end = strpos($zip, "<!-- END DOWNLOAD OF REPLAY -->") + 31;
    $zip = substr($zip, $start, $end - $start);

    $temp_path = sys_get_temp_dir();
    $tempname = tempnam($temp_path, "");

    $f_r = fopen($tempname, 'w+');
    fwrite($f_r, $zip);
    fseek($f_r, 0);

    #$test = fread( $f_r ,filesize($tempname) );
    #Print $test;

    $zip_r = zip_open($tempname);
    while ($zip_entry = zip_read($zip_r))
    {
        if (strpos(zip_entry_name($zip_entry),"replay.rep") !== false )
        {
            $replay = zip_entry_read($zip_entry, 100000);
            zip_entry_close($zip_entry);
        }
    }
    zip_close($zip_r);
    Print $replay;
}

?>