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

define('T_NO_STARTUP', true);
require('header.php'); // Includes and constants.

?>
<html>
<head>
</head>
<body>
<br>
<big>
    <center>
        <b>OBBLM MySQL setup script</b>
    </center>
</big>
<br>
<small>
<?php
if (isset($_POST['setup'])) {
    echo setup_database()
        ? "<br><b><font color='green'>Done</font></b>"
        : "<br><b><font color='red'>Error</font></b>";
    
    ?>
    <br><br>
    Use the coach account 'root' with password 'root' first time you log in.<br> 
    From there you may enter the administration section and add new users (coaches) including changing the root password.
    <?php
}
else {
    ?>
    Please make sure that the MySQL user and database you have specified in <i>settings.php</i> exist and are valid.<br><br>

    <form method="POST">
        <input type="submit" name="setup" value="Setup DB for OBBLM">
    </form>
    <?php
}
?>
</small>
</body>
</html>
