<?php
global $DEA;
global $raceididx;

foreach ($DEA as $race_name => $attrs) {

	if (!in_array($race_name, $settings['cyanide_races'])) {
		unset($DEA[$race_name]);
	}
}

$raceididx = array();
foreach (array_keys($DEA) as $race) {
	$raceididx[$DEA[$race]['other']['race_id']] = $race;
}

?>