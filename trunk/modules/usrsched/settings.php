<?php
#The isset() check allows leagues to choose their own leegmgr settings.

global $settings;

if ( !isset($settings['usrsched_enabled']) )
            $settings['usrsched_enabled']  = false;   // Enables the user scheduler module.
?>