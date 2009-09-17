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

    global $rules, $settings, $DEA, $coach, $lng;

    // Quit if coach does not has administrator privileges.

    if (!is_object($coach) || $coach->ring > RING_COM)
        fatal("Sorry. Only site administrators and commissioners are allowed to access this section.");

    $ring_sys_access = array('usrman' => $lng->getTrn('secs/admin/um'), 'ldm' => $lng->getTrn('secs/admin/ldm'), 'import' => $lng->getTrn('secs/admin/import'), 'chtr' => $lng->getTrn('secs/admin/th'), 'ctman' => $lng->getTrn('secs/admin/delete'));
    $ring_com_access = array('tournament' => $lng->getTrn('secs/admin/schedule'), 'log' => $lng->getTrn('secs/admin/log'));

    if (isset($_GET['subsec']) && $coach->ring != RING_SYS && in_array($_GET['subsec'], array_keys($ring_sys_access)))
        fatal("Sorry. Your access level does not allow you opening the requested page.");

    $coaches = Coach::getCoaches(); // Used by multiple sub-sections.

    // If an admin section was requested then call it, else show admin main page.
    if (!isset($_GET['subsec'])) {
        ?>
        <div style="height: 400px;" id="admin_everything">
        <?php echo $lng->getTrn('secs/admin/pick'); ?>
        </div>
        <?php
    }
    switch ($_GET['subsec']) 
    {
        case 'usrman':      include('admin/admin_coachman.php'); break;
        case 'tournament':  include('admin/admin_schedule.php'); break;
        case 'import':      include('admin/admin_teamimport.php'); break;
        case 'chtr':        include('admin/admin_tourman.php'); break;
        case 'ctman':       include('admin/admin_team_coach_retire_del.php'); break;
        case 'ldm':         include('admin/admin_league_division_man.php'); break;
        case 'log': 
            title($lng->getTrn('secs/admin/log'));
            echo "<table style='width:100%;'>\n";
            echo "<tr><td><i>Date</i></td><td><i>Message</i></td></tr><tr><td colspan='2'><hr></td></tr>\n";
            foreach (SiteLog::getLogs(LOG_HIST_LENGTH) as $l) {
                echo "<tr><td>".textdate($l->date)."</td><td>$l->txt</td></tr>\n";
            }
            echo "</table>\n";
            break;
   
    }
}

?>
