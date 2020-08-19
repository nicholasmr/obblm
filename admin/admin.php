<?php

 /*************************
 *  ADMINISTRATION
 *************************/

function sec_admin() {
    global $rules, $settings, $DEA, $coach, $lng, $admin_menu;
    global $leagues, $divisions, $tours;
    
    if (!is_object($coach))
        fatal('Please login.');
    if (!isset($_GET['subsec']))
        $_GET['subsec'] = '_NONE_';

    $IS_GLOBAL_ADMIN = ($coach->ring == Coach::T_RING_GLOBAL_ADMIN);
    $ONLY_FOR_GLOBAL_ADMIN = "Note: This feature may only be used by <i>global</i> administrators."; # Used string in a few common feature/action boxes.

    // Deny un-authorized users.
    if (!in_array($_GET['subsec'], array_keys($admin_menu)))
        fatal("Sorry. Your access level does not allow you opening the requested page.");

    switch ($_GET['subsec']) 
    {
        case 'usr_man':     include('admin/admin_usr_man.php'); break;
        case 'ct_man':      include('admin/admin_ct_man.php'); break;
        case 'nodes':       include('admin/admin_nodes.php'); break;
        case 'schedule':    include('admin/admin_schedule.php'); break;
        case 'import':      include('admin/admin_import.php'); break;
        case 'log':         Module::run('LogSubSys', array('logViewPage')); break;
        case 'cpanel':      include('admin/admin_cpanel.php'); break;
        default:            fatal('The requested admin page does not exist.');
    }
    echo "<br><br>";
    HTMLOUT::dnt();
}