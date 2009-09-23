<?php

// Extra Player Stats

class EPS implements ModuleInterface
{

const TABLE = 'eps';

public static $types = array(
    "iMatchPlayed" 		        => "BOOLEAN NOT NULL DEFAULT 0",
    "Inflicted_iPasses" 		=> "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "Inflicted_iCatches" 		=> "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "Inflicted_iInterceptions" 	=> "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "Inflicted_iTouchdowns" 	=> "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "Inflicted_iCasualties"     => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "Inflicted_iTackles" 	    => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "Inflicted_iKO" 		    => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "Inflicted_iStuns" 		    => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "Inflicted_iInjuries" 		=> "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "Inflicted_iDead" 		    => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "Inflicted_iMetersRunning" 	=> "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "Inflicted_iMetersPassing" 	=> "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "Sustained_iInterceptions" 	=> "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "Sustained_iCasualties" 	=> "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "Sustained_iTackles" 		=> "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "Sustained_iKO" 		    => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "Sustained_iStuns" 		    => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "Sustained_iInjuries" 	    => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "Sustained_iDead" 		    => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
);

public static $relations = array(
    "f_cid"  => 'MEDIUMINT UNSIGNED',   # Coach ID
    "f_tid"  => 'MEDIUMINT UNSIGNED',   # Team ID
    "f_pid"  => 'MEDIUMINT SIGNED',     # Player ID
    "f_rid"  => 'TINYINT UNSIGNED',     # Race ID
    "f_mid"  => 'MEDIUMINT SIGNED',     # Match ID
    "f_trid" => 'MEDIUMINT UNSIGNED',   # Tournament ID
    "f_did"  => 'MEDIUMINT UNSIGNED',   # Division ID
    "f_lid"  => 'MEDIUMINT UNSIGNED',   # League ID
);

public static function main($argv) # argv = argument vector (array).
{
    $func = array_shift($argv);
    return call_user_func_array(array(__CLASS__, $func), $argv);
}

public static function getModuleAttributes()
{
    return array(
        'author'     => 'Nicholas Mossor Rathmann',
        'moduleName' => 'Extra Player Stats',
        'date'       => '2009', # For example '2009'.
        'setCanvas'  => false, # If true, whenever your main() is run through Module::run() your code's output will be "sandwiched" into the standard HTML frame.
    );
}

public static function getModuleTables()
{
    return array(self::TABLE => array_merge(self::$relations, self::$types));
}    

public static function makeEntry(array $relations, array $playerData)
{
    // Ready the data.
    # Required keys/columns.
    $_expectedInput = array_merge(self::$relations, self::$types); ksort($_expectedInput);
    $KEYS           = array_keys($_expectedInput); 
    # Recieved data.
    $_receivedInput = array_merge($relations, $playerData); ksort($_receivedInput);
    $INPUT_KEYS     = array_keys($_receivedInput);
    $INPUT_VALUES   = array_values($_receivedInput);
    
    // Verify input.
    if ($INPUT_KEYS !== $KEYS)
        return false;
    
    // Delete entry if already exists (we don't use MySQL UPDATE on rows for simplicity)
    $WHERE = "f_mid = $relations[f_mid] AND f_pid = $relations[f_pid]";
    $query = 'SELECT f_mid FROM '.self::TABLE." WHERE $WHERE";
    if (($result = mysql_query($query)) && mysql_num_rows($result) > 0) {
        mysql_query('DELETE FROM '.self::TABLE." WHERE $WHERE");
    }
    
    // Insert entry.
    $query  = 'INSERT INTO '.self::TABLE.' ('.implode(',', $KEYS).') VALUES ('.implode(',', $INPUT_VALUES).')';
    return mysql_query($query);
}

public static function deleteMatchEntries($mid)
{
    return mysql_query('DELETE FROM '.self::TABLE." WHERE f_mid = $mid");
}

}
?>
