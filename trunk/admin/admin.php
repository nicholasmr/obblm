<?php

/*
 *  Copyright (c) Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2007-2010. All Rights Reserved.
 *
 *
 *  This file is part of OBBLM.
 *
 *  OBBLM is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  OBBLM is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

 /*************************
 *
 *  ADMINISTRATION
 *
 *************************/

function sec_admin() {

    global $rules, $settings, $DEA, $coach, $lng, $ring_sys_access, $ring_com_access;
    global $leagues, $divisions, $tours;
    
    if (!is_object($coach))
        fatal('Please login.');

    // What is the largest ring of logged in coach's leagues.
    $LARGEST_LOCAL_RING = Coach::T_RING_LOCAL_REGULAR;
    foreach ($leagues as $lid => $desc) {
        if ($desc['ring'] > $LARGEST_LOCAL_RING) {
            $LARGEST_LOCAL_RING = $desc['ring'];
        }
    }

    // Quit if coach does not has administrator privileges.
    if (!( ($IS_GLOBAL_ADMIN = ($coach->ring == Coach::T_RING_GLOBAL_ADMIN)) || $LARGEST_LOCAL_RING > Coach::T_RING_LOCAL_REGULAR))
        fatal("Sorry. Only site administrators and league commissioners are allowed to access this section.");

    // Deny local commish trying to access global admin pages.
    if (isset($_GET['subsec']) && !$IS_GLOBAL_ADMIN && in_array($_GET['subsec'], array_keys($ring_sys_access)))
        fatal("Sorry. Your access level does not allow you opening the requested page.");

    switch ($_GET['subsec']) 
    {
        case 'usr_man':     include('admin/admin_usr_man.php'); break;
        case 'tour_man':    include('admin/admin_tour_man.php'); break;
        case 'ct_man':      include('admin/admin_ct_man.php'); break;
        case 'ld_man':      include('admin/admin_ld_man.php'); break;
        case 'schedule':    include('admin/admin_schedule.php'); break;
        case 'import':      include('admin/admin_import.php'); break;
        case 'log':         Module::run('LogSubSys', array('logViewPage')); break;
        case 'cpanel':      include('admin/admin_cpanel.php'); break;
        default:            fatal('The requested admin page does not exist.');
    }
}

?>
