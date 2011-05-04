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
    echo ($upgradeMsgs = upgrade_database($_POST['version'], array('lrb' => isset($_POST['lrb']) ? $_POST['lrb'] : false)))
        ? "<br><b><font color='green'>Done</font></b>"
        : "<br><b><font color='red'>Error</font></b>";
        
    echo "<br><br><b>IMPORTANT</b>:<br><ul>";
    echo array_strpack('<li>%s</li>', $upgradeMsgs, "\n");
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
<table border='1' style='font-size:small; mergin: 5px;'>
    <tr style='font-weight:bold;'><td></td><td>Version upgrade</td><td>Required upgrade parameters</td></tr>
    <tr>
        <td><INPUT TYPE=RADIO NAME="version" VALUE="080-090"></td>
        <td>0.80 to 0.90</td>
        <td>&mdash;</td>
    </tr>
    <tr>
        <td><INPUT TYPE=RADIO NAME="version" VALUE="075-080"></td>
        <td>0.75 to 0.80</td>
        <td>The <u>current</u> 0.75 LRB used is: LRB5<INPUT TYPE=RADIO NAME="lrb" VALUE="5"> LRB5b/LRB6x<INPUT TYPE=RADIO NAME="lrb" VALUE="6x"></td>
    </tr>
</table>
    <br>
    <input type="submit" name='submit' value="Run upgrade SQLs" onclick="if(!confirm('Please backup your current database if you have not done so already.\n\nAre you sure you wish to continue?')){return false;}">
</form>

</small>
</body>
</html>
