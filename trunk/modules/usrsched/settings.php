<?php

$settings['usersched_local_view'] = false; # If true only tournaments from the current selected league view will be shown.


#The isset() check allows leagues to choose their own leegmgr settings.

global $settings;

if ( !isset($settings['usrsched_enabled']) )
            $settings['usrsched_enabled']  = false;   // Enables the user scheduler module.
?>
