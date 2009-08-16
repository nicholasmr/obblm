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

class Table
{
    public $tblName = null;
    public $tableStruct = array();
    
    private static $tables = array();

    public function __construct($tblName) 
    {
        $this->tblName = $tblName;
        $this->tables[$tblName] = ''; # Register table.
    }
    
    public function createTableIfNotExists($tblStruct)
    {
        $this->tables[$this->tblName] = ($this->tblStruct = $tblStruct); # Register table structure.
        $query = 'CREATE TABLE '.$this->tblName.' IF NOT EXISTS (
            '.implode(', ', array_map(create_function('$key,$val', 'return "$key\t $val";'), array_keys($this->tblStruct), array_values($this->tableStruct))).'
        )';
        return myqsl_query($query);
    }

    public function createColumnIfNotExists($colName, $type) 
    {
        myqsl_query("ALTER TABLE $this->tblName ADD COLUMN $colName $type");
        return true; # Don't care about errors when column exists.
    }
    
    public static function getTables()
    {
        return $this->tables;
    }
}
?>
