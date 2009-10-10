<?php

/*
 *  Copyright (c) Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2009. All Rights Reserved.
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

define('NO_STARTUP', true); # header.php hint.
require('header.php'); // Includes and constants.

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
    echo upgrade_database($_POST['version'])
        ? "<br><b><font color='green'>Done</font></b>"
        : "<br><b><font color='red'>Error</font></b>";
        
    echo "<br><hr>";
}
?>
Please make sure that the MySQL user and database you have specified in <i>settings.php</i> exist and are valid.<br><br>
Now, click the appropriate SQL code to run depending on the version upgrade you are doing.<br> 
If upgrading across two or more versions simply run the SQL code for the next version, one after the other until the latest version is reached.

<br><br>
<form method="POST">
    <INPUT TYPE=RADIO NAME="version" VALUE="075-080">v 0.75 --> v 0.80<br>
    <INPUT TYPE=RADIO NAME="version" VALUE="070-075">v 0.70 --> v 0.75<br>
    <INPUT TYPE=RADIO NAME="version" VALUE="037-070">v 0.37 --> v 0.70<br>
    <INPUT TYPE=RADIO NAME="version" VALUE="036-037">v 0.36 --> v 0.37<br>
    <INPUT TYPE=RADIO NAME="version" VALUE="035-036">v 0.35 --> v 0.36<br>
    <INPUT TYPE=RADIO NAME="version" VALUE="034-035">v 0.34 --> v 0.35<br>
    <br>
    <input type="submit" name='submit' value="Run upgrade SQLs">
</form>

</small>
</body>
</html>
