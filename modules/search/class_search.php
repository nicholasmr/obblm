<?php

class MyModule implements ModuleInterface
{

public static function main($argv) # argv = argument vector (array).
{
    // Implement later using AJAX.
}

public static function getModuleAttributes()
{
    return array(
        'author'     => 'Nicholas Mossor Rathmann',
        'moduleName' => 'Coach/team search',
        'date'       => 'Feb 2010',
        'setCanvas'  => true,
    );
}

public static function getModuleTables(){ return array();}    
public static function getModuleUpgradeSQL(){ return array();}
public static function triggerHandler($type, $argv){}
}
?>
