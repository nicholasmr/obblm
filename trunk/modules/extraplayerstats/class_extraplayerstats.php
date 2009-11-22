<?php

// Extra Player Stats

class EPS implements ModuleInterface
{

const TABLE = 'eps';

public static $types = array(
    # cat fs.txt | perl -ne 's/^(.*)\s*$/"$1" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",\n/ && print'
    "pass_attempts" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "pass_completions" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "interceptions_thrown" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "pass_distance" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "dumpoff_attempts" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "dumpoff_completions" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "catch_attempts" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "catches" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "handoffs" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "handoffs_received" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "handoff_catches" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "pickup_attempts" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "pickups" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "rushing_distance_leap" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "rushing_distance_push" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "rushing_distance_move" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "rushing_distance_block" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "rushing_distance_shadowing" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "leap_attempts" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "leaps" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "dodge_attempts" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "dodges" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "blitz_actions" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "gfi_attempts" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "gfis" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_blocks" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_defender_downs" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_defender_stumbles" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_pushes" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_both_downs" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_attacker_downs" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_knock_downs" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_strip_balls" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_sacks" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_crowd_surfs" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_stuns" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_kos" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_bhs" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_sis" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_kills" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "sustained_blocks" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "sustained_knocked_downs" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "sustained_sacks" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "sustained_crowd_surfs" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "sustained_stuns" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "sustained_kos" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "sustained_bhs" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "sustained_sis" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "sustained_kill" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_fouls" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_foul_stuns" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_foul_kos" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_foul_bhs" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_foul_sis" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_foul_kills" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "sustained_fouls" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "sustained_ejections" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "apothecary_used" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "ko_recovery_attempts" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "ko_recoveries" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "thickskull_used" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "regeneration_attempts" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "regenerations" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "kickoffs" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "kick_distance" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "dice_rolls" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "dice_natural_ones" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "dice_natural_sixes" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "dice_target_sum" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "dice_roll_sum" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "interceptions" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "casualties" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "touchdowns" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "big_guy_stupidity_attempts" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "big_guy_stupidity_successes" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "big_guy_stupidity_blitz_attempts" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "big_guy_stupidity_blitz_successes" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "throw_team_mate_attempts" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "throw_team_mate_successes" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "throw_team_mate_distance" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "throw_team_mate_to_safe_landing" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "times_thrown" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "landing_attempts" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "landings" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "distance_thrown" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "rushing_distance_thrown" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "bloodlust_rolls" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "bloodlust_successes" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "bloodfeeds" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "hypnoze_rolls" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "hypnoze_successes" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "fed_on" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "tentacles_rolls" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "tentacles_successes" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "injuries" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "mvp" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "improvement_roll1" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "improvement_roll2" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_bloodfeed_stuns" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_bloodfeed_kos" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_bloodfeed_bhs" => "TINYINT UNSIGNED NOT NULL DEFAULT 0",
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

public static function getModuleUpgradeSQL()
{
    return array();
}

public static function triggerHandler($type, $argv){}

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
