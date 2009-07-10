<?php

require('header.php'); // Includes and constants.

/*
$match_report_file = "match_reports/MatchReport.sqlite";
$match_report = new CyanideMatchReport($match_report_file);

echo "Home team : " .$match_report->home_team_name;
*/

for ($i=0;$i<100;$i++) {
	$name = getRandomName("Dwarf");
	echo $name ."<br>";
}

?>
