<?php

if (version_compare(PHP_VERSION, '5.0.0') == -1)
    die('<font color="red"><b>Sorry. OBBLM requires PHP version 5, you are running version ' . PHP_VERSION . '.</b></font>');
    
$iniRG = ini_get('register_globals');
if (strtolower($iniRG) == 'on' || $iniRG == 1)
    die('<font color="red">Sorry. OBBLM requires the PHP configuration directive <i>register_globals</i> set <b><u>off</u></b> in the <i>php.ini</i> configuration file. Please contact your web host.</font>');

session_start();
error_reporting(E_ALL);
require('header.php'); // Includes and constants.

if (!is_writable(IMG))
    fatal('Sorry. OBBLM needs to be able to write to the <i>images</i> directory in order to work probably. Please check the directory permissions.');

/********************
 *   Main routine
 ********************/

// MySQL connect.
$conn = mysql_up(true);

// Load language.
$lng = new Translations($settings['lang']);

// Create coach object.
$coach = (isset($_SESSION['logged_in'])) ? new Coach($_SESSION['coach_id']) : null;

/* Export team */
    
if (isset($_GET['team_id']) && !preg_match("/[^0-9]/", $_GET['team_id'])) {
	
    $team_id = $_GET['team_id'];
    $team = new Team($team_id);
    
    // Check if allowed
	$ALLOWED = false;
	if (is_object($coach) && is_object($team) && ($coach->admin || $team->owned_by_coach_id==$coach->coach_id))
		$ALLOWED = true; 
    
	if ($ALLOWED) {
		$team->cyanideSQLiteExport();
		
    	$export_file_path = TEAM_TEMPLATE_DIR ."/". $team->team_id .".db";
    	
    	$mime_type = "application/force-download";
    	
    	header('Content-Type: ' . $mime_type);
 		header('Content-Disposition: attachment; filename="'.$team->name.".db".'"');
 		header("Content-Transfer-Encoding: binary");
 		header('Accept-Ranges: bytes');
 		
 		header("Cache-control: private");
 		header('Pragma: private');
 		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
 		
    	header("Content-Length: ".filesize($export_file_path));
 		
		readfile($export_file_path);
 		
		@unlink($export_file_path);
	}
	else {
		title($lng->getTrn('secs/fixtures/forbidden'));
	}
}

?>
