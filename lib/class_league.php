<?php

/*
 *  Copyright (c) Niels Orsleff Justesen <njustesen@gmail.com> and Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2007-2008. All Rights Reserved.
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

class League
{
/***************
 * Properties 
 ***************/
 
public $lid = 0; // League ID.
public $name = '';
public $date = ''; 
public $location = ''; // Physical location of league.
 
/***************
 * Methods 
 ***************/

function __construct($lid) {
    $result = mysql_query("SELECT * FROM leagues WHERE lid = $lid");
    $row = mysql_fetch_assoc($result);
    foreach ($row as $col => $val) {
        $this->$col = ($val) ? $val : 0;
    }
    
    if (!$this->name) {$this->name = '';} # Make $name empty string and not zero when empty in mysql.
    if (!$this->location) {$this->location = '';}
    if (!$this->date) {$this->date = '';}
}

public function delete()
{
    $status = true;
    foreach ($this->getDivisions() as $d) {
        $status &= $d->delete();
    }
    return ($status && mysql_query("DELETE FROM leagues WHERE lid = $this->lid"));
}

public function setName($name)
{
    $query = "UPDATE leagues SET name = '".mysql_real_escape_string($name)."' WHERE lid = $this->lid";
    return mysql_query($query);
}

public function setLocation($location)
{
    $query = "UPDATE leagues SET location = '".mysql_real_escape_string($location)."' WHERE lid = $this->lid";
    return mysql_query($query);
}

public function getDivisions($onlyIds = false)
{
    $divisions = array();
    $result = mysql_query("SELECT did FROM divisions WHERE f_lid = $this->lid");
    if ($result && mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_assoc($result)) {
            array_push($divisions, ($onlyIds) ? $row['did'] : new Division($row['did']));
        }
    }
    return $divisions;    
}

public static function getLeagues($onlyIds = false)
{
    $leagues = array();
    $result = mysql_query("SELECT lid FROM leagues");
    if ($result && mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_assoc($result)) {
            array_push($leagues, ($onlyIds) ? $row['lid'] : new League($row['lid']));
        }
    }
    return $leagues;
}

public static function create($name, $location)
{
    $query = "INSERT INTO leagues (date, location, name) VALUES (NOW(), '".mysql_real_escape_string($location)."', '".mysql_real_escape_string($name)."')";
    return mysql_query($query);
}
}
