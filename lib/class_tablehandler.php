<?php

class Table
{
    public static function createTable($name, $tblStruct) {
        $query = 'CREATE TABLE '.$name.' (
            '.implode(', ', array_map(create_function('$key,$val', 'return "$key\t $val";'), array_keys($tblStruct), array_values($tblStruct))).'
        )';
        return mysql_query("DROP TABLE IF EXISTS $name") && mysql_query($query);
    }
    
    public static function createTableIfNotExists($name, $tblStruct) {
        $query = 'CREATE TABLE IF NOT EXISTS '.$name.' (
            '.implode(', ', array_map(create_function('$key,$val', 'return "$key\t $val";'), array_keys($tblStruct), array_values($tblStruct))).'
        )';
        return mysql_query($query);
    }
}