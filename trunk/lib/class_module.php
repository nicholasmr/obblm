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

class Module
{

    private static $modules = array(
    /*
        'modname' => array(
                'author'        => string,
                'date'          => string,
                'setCanvas'     => bool, # Set page canvas (menu, frames, etc.)
                'main'          => function name (must be normal function or static method like 'class::methodName'),
                'filesLoadTime' => array('file1', 'file2', 'file3'),
                'filesRunTime'  => array('file1', 'file2', 'file3'),
        ),
    */
    );
    const MOD_RPATH = 'modules/'; # Relative path from base path to the modules directory in which modules (dirs) are placed.
    
    public static function register(array $struct)
    {
        self::$modules[$struct['modname']] = $struct;
        unset(self::$modules[$struct['modname']]['modname']);
        /*
            From manual/en/function.include.php
            
            When a file is included, the code it contains inherits the variable scope of the line on which the include occurs. 
            Any variables available at that line in the calling file will be available within the called file, from that point forward. 
            However, all functions and classes defined in the included file have the global scope. 
        */
        foreach ($struct['filesLoadTime'] as $file) {require_once(self::MOD_RPATH . $file);} # Load module files.
    }
    
    public static function unregister($modname)
    {
        unset(self::$modules[$modname]);
    }
    
    public static function run($modname, array $argv)
    {
        $module = self::$modules[$modname]; # Shortcut.
        foreach ($module['filesRunTime'] as $file) {require_once(self::MOD_RPATH . $file);} # Load module files.
        global $coach; # Used for fetching stylesheet.
        if ($module['setCanvas']) {HTMLOUT::frame_begin(is_object($coach) ? $coach->settings['theme'] : false);}
        $return = call_user_func_array($module['main'], $argv);
        if ($module['setCanvas']) {HTMLOUT::frame_end();}
        
        return $return;
    }

    public static function isRegistered($modname)
    {
        return in_array($modname, array_keys(self::$modules));
    }
    
    public static function getInfo($modname)
    {
        $module = self::$modules[$modname];
        return array($module['author'], $module['date']);
    }
    
    public static function getRegistered()
    {
        return array_keys(self::$modules);
    }
}
