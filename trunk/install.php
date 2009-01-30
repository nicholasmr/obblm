<?php

/*
 *  Copyright (c) Niels Orsleff Justesen <njustesen@gmail.com> and Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2007. All Rights Reserved.
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

require('header.php'); // Includes and constants.

function mk_tables() {
    if (setup_tables())
        echo "<br><b><font color='green'>Done</font></b>";
    else
        echo "<br><b><font color='red'>Error</font></b>";
    
    ?>
    <br><br>
    Use the coach account 'root' with password 'root' first time you log in.<br> 
    From there you may enter the administration section and add new users (coaches) including changing the root password.
    <?php
}

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

// Option A?
if (isset($_POST['opt_a'])) {

    // Login as root and create database + user.
    $conn = mysql_connect($db_host, 'root', $_POST['passwd']);
    if ($conn) {
        mysql_query("DELETE FROM mysql.user WHERE User = '$db_user'");
        mysql_query("FLUSH PRIVILEGES");
        mysql_query("CREATE USER $db_user IDENTIFIED BY '$db_passwd'");
        mysql_query("DROP DATABASE $db_name");
        mysql_query("CREATE DATABASE $db_name");
        mysql_query("GRANT ALL PRIVILEGES ON $db_name.* TO '$db_user'@'$db_host' IDENTIFIED BY '$db_passwd' WITH GRANT OPTION");
        mysql_close($conn);
    }

    mk_tables();
}
// Option B?
elseif (isset($_POST['opt_b'])) {

    // Erase old tables first.
    $conn = mysql_up();
    if ($conn) {
        mysql_select_db($db_name);
        mysql_query("DROP TABLES IF EXISTS coaches, teams, players, matches, tours, match_data, texts");
        mysql_close($conn);
    }

    mk_tables();
}
else {
    ?>
    <hr>
    <b><font color="blue">Option A</font></b><br>
    <br>
    <b>I do not have an existing MySQL user and database to use for OBBLM. Please create them for me accordingly to the information I have stored in <i>settings.php</i>, followed by the actual database setup.</b>
    <br><br>
    <form method="POST">
        <i>MySQL root password:</i>
        <input type="password" name="passwd" size="20" maxlength="20"> 
        <input type="submit" name="opt_a" value="Create new user and database for me!">
    </form>
    <br>
    <hr>
    <b><font color="blue">Option B</font></b><br>
    <br>
    <b>The MySQL user and database I have specified in settings.php exist and are valid. Please setup the database for using OBBLM without re-creating the database and user.</b>
    <br><br>
    <form method="POST">
        <input type="submit" name="opt_b" value="Setup my existing database!">
    </form>
    <?php
}
?>
</small>
</body>
</html>
