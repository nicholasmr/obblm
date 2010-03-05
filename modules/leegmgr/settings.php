<?php
#The isset() check allows leagues to choose their own leegmgr settings.

global $settings;

if ( !isset($settings['leegmgr_enabled']) )
    $settings['leegmgr_enabled']  = true;   // Enables upload of BOTOCS LRB5 application match reports.
if ( !isset($settings['leegmgr_schedule']) )
    $settings['leegmgr_schedule'] = true;   // Uploads report to a scheduled match.  The options are [false|true|"strict"]
                                            // false does not check for scheduled matches
                                            // true checks for scheduled matches and will create a match if not found
                                            // "strict" will allow only scheduled matches to be used
if ( !isset($settings['leegmgr_extrastats']) )
    $settings['leegmgr_extrastats'] = true; // Enables the reporting of extra stats and the use of the alternate XSD file.

if ( !isset($settings['leegmgr_cyanide']) )
    $settings['leegmgr_cyanide'] = false; // Setting to false here is preferred as this can be set to true in each specific league.

if ( !isset($settings['leegmgr_botocs']) )
    $settings['leegmgr_botocs'] = false; // Setting to false here is preferred as this can be set to true in each specific league.

?>
