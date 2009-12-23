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

    global $rules, $settings, $DEA, $coach, $lng, $league, $ring_sys_access, $ring_com_access;

    // Quit if coach does not has administrator privileges.

    if (!is_object($coach) || $coach->ring > RING_COM)
        fatal("Sorry. Only site administrators and commissioners are allowed to access this section.");

    if (isset($_GET['subsec']) && $coach->ring != RING_SYS && in_array($_GET['subsec'], array_keys($ring_sys_access)))
        fatal("Sorry. Your access level does not allow you opening the requested page.");

    list($GLOBAL_MANAGE, $coaches, $leagues, $coach_ids, $league_ids) = _getState(); // Used by multiple sub-sections.

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

function _getState()
{
    global $coach;
    
    $GLOBAL_MANAGE = ($coach->admin || $coach->f_lid == T_COACH_NO_ASSOC_LID); # Is global management allowed by the logged in $coach ?
    
    // Fellow coaches
    $query = "SELECT * FROM coaches".($GLOBAL_MANAGE ? '' : " WHERE f_lid = $coach->f_lid");
    $result = mysql_query($query);
    $coaches = $coach_ids = array();
    while ($c = mysql_fetch_object($result)) {$coaches[] = $c; $coach_ids[] = $c->coach_id;}

    // League(s) visibillity
    $query = "SELECT * FROM leagues".($GLOBAL_MANAGE ? '' : " WHERE lid = $coach->f_lid");
    $result = mysql_query($query);
    $leagues = $league_ids = array();
    while ($l = mysql_fetch_object($result)) {$leagues[] = $l; $league_ids[] = $l->lid;}
    if ($GLOBAL_MANAGE) {
        $leagues[] = (object) array('name' => '--No league--', 'lid' => T_COACH_NO_ASSOC_LID);
        $league_ids[] = T_COACH_NO_ASSOC_LID;
    }
    
    return array(
        $GLOBAL_MANAGE,
        $coaches,
        $leagues,
        $coach_ids,
        $league_ids,
    );
};


?>
