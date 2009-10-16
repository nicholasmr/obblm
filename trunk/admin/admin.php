<?php

/*
 *  Copyright (c) Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2007-2009. All Rights Reserved.
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

    // Quit if coach does not has administrator privileges.

    if (!is_object($coach) || $coach->ring > RING_COM)
        fatal("Sorry. Only site administrators and commissioners are allowed to access this section.");

    if (isset($_GET['subsec']) && $coach->ring != RING_SYS && in_array($_GET['subsec'], array_keys($ring_sys_access)))
        fatal("Sorry. Your access level does not allow you opening the requested page.");

    switch ($_GET['subsec']) 
    {
        case 'usr_man':     include('admin/admin_coachman.php'); break;
        case 'tour_man':    include('admin/admin_tourman.php'); break;
        case 'ct_man':      include('admin/admin_team_coach_retire_del.php'); break;
        case 'ld_man':      include('admin/admin_league_division_man.php'); break;
        case 'schedule':    include('admin/admin_schedule.php'); break;
        case 'import':      include('admin/admin_teamimport.php'); break;
        case 'log':         Module::run('LogSubSys', array('logViewPage')); break;
        default:            fatal('The requested admin page does not exist.');
    }
}

?>
