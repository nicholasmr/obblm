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
if (isset($_POST['upgrade'])) {
    echo upgrade_database('')
        ? "<br><b><font color='green'>Done</font></b>"
        : "<br><b><font color='red'>Error</font></b>";
    
    ?>
    <?php
}
else {
    ?>
    Please make sure that the MySQL user and database you have specified in <i>settings.php</i> exist and are valid.<br><br>
    Now, click the appropriate SQL to run depending on the version upgrade you are doing.<br> 
    If upgrading across two or more versions simply run the SQL code for the next version, one after the other until the latest version is reached.

    <br><br>
    <form method="POST">
        <input type="submit" name="075-080" value="v 0.75 --> v 0.80"><br><br>
        <input type="submit" name="070-075" value="v 0.70 --> v 0.75"><br><br>
        <input type="submit" name="037-070" value="v 0.37 --> v 0.70"><br><br>
        <input type="submit" name="036-037" value="v 0.36 --> v 0.37"><br><br>
        <input type="submit" name="035-036" value="v 0.35 --> v 0.36"><br><br>
        <input type="submit" name="034-035" value="v 0.34 --> v 0.35"><br><br>
    </form>
    <?php
}
?>
</small>
</body>
</html>
