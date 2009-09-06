<?php

global $settings;

$settings['leegmgr_enabled']  = true;  // Enables upload of BOTOCS LRB5 application match reports.
$settings['leegmgr_schedule'] = true; // Uploads report to a scheduled match.  The options are [false|true|"strict"]
                                       // false does not check for scheduled matches
                                       // true checks for scheduled matches and will create a match if not found
                                       // "strict" will allow only scheduled matches to be used

?>
