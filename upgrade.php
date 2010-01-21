<?php

/*
 *  Copyright (c) Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2009-2010. All Rights Reserved.
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

define('T_NO_STARTUP', true);
require('header.php');

?>
<html>
<head>
</head>
<body>
<br>
<big>
    <center>
        <b>OBBLM MySQL upgrade script</b>
    </center>
</big>
<br>
<small>
<?php
if (isset($_POST['version'])) {
    echo ($upgradeMsgs = upgrade_database($_POST['version']))
        ? "<br><b><font color='green'>Done</font></b>"
        : "<br><b><font color='red'>Error</font></b>";
        
    echo "<br><br><b>IMPORTANT</b>:<br><ul>";
    echo array_strpack('<li>%s</li>', array_merge($upgradeMsgs,array('You are required to visit the <a href="index.php?section=admin&amp;subsec=cpanel">OBBLM core panel</a> in the admin menu and run the "syncAll()" DB synchronisation procedure in order to syncronize all statistics.')), "\n");
    echo "</ul><br><hr>";
}
?>
Please make sure that the MySQL user and database you have specified in <i>settings.php</i> exist and are valid AND that the rules fields of the old settings file are consistant with the new settings file for those fields which are common.<br><br>
Now, click the appropriate SQL code to run depending on the version upgrade you are doing.<br><br>
<b>Please note:</b>
<ul>
<li>ALWAYS make sure you have a backup/dump of your OBBLM database before running the upgrade script.</li>
<li>If upgrading across two or more versions simply run the SQL code for the next version, one after the other until the latest version is reached.</li>
<li>If upgrading <i>from</i> versions previous of v. 0.75 you must consult the <i>INSTALL</i> file and run the listed SQL queries <u>manually</u>.</li>
</ul>

<br>
<form method="POST">
    <INPUT TYPE=RADIO NAME="version" VALUE="075-080">v 0.75 to v 0.80<br>
    <br>
    <input type="submit" name='submit' value="Run upgrade SQLs">
</form>

</small>
</body>
</html>
