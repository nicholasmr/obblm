<?php

// Extra Player Stats

class EPS implements ModuleInterface
{

const TABLE = 'eps';

public static $types = array(
    # cat fs.txt | perl -ne 's/^(\w*)\s*$/"$1" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",\n/ && print'
    "pass_attempts" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "pass_completions" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "interceptions_thrown" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "pass_distance" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "dumpoff_attempts" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "dumpoff_completions" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "catch_attempts" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "catches" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "handoffs" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "handoffs_received" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "handoff_catches" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "pickup_attempts" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "pickups" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "rushing_distance_leap" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "rushing_distance_push" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "rushing_distance_move" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "rushing_distance_block" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "rushing_distance_shadowing" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "leap_attempts" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "leaps" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "dodge_attempts" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "dodges" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "blitz_actions" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "gfi_attempts" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "gfis" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_blocks" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_defender_downs" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_defender_stumbles" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_pushes" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_both_downs" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_attacker_downs" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_knock_downs" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_strip_balls" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_sacks" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_crowd_surfs" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_stuns" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_kos" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_bhs" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_sis" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_kills" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "sustained_blocks" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "sustained_knocked_downs" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "sustained_sacks" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "sustained_crowd_surfs" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "sustained_stuns" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "sustained_kos" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "sustained_bhs" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "sustained_sis" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "sustained_kill" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_fouls" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_foul_stuns" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_foul_kos" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_foul_bhs" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_foul_sis" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "inflicted_foul_kills" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "sustained_fouls" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "sustained_ejections" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "apothecary_used" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "ko_recovery_attempts" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "ko_recoveries" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "thickskull_used" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "regeneration_attempts" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "regenerations" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "kickoffs" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "kick_distance" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "dice_rolls" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "dice_natural_ones" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "dice_natural_sixes" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "dice_target_sum" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "dice_roll_sum" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
#    "interceptions" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
#    "casualties" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
#    "touchdowns" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "big_guy_stupidity_attempts" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "big_guy_stupidity_successes" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "big_guy_stupidity_blitz_attempts" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "big_guy_stupidity_blitz_successes" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "throw_team_mate_attempts" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "throw_team_mate_successes" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "throw_team_mate_distance" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "throw_team_mate_to_safe_landing" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "times_thrown" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "landing_attempts" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "landings" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "distance_thrown" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "rushing_distance_thrown" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "bloodlust_rolls" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "bloodlust_successes" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "bloodfeeds" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "hypnoze_rolls" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "hypnoze_successes" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "fed_on" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "tentacles_rolls" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "tentacles_successes" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
#    "injuries" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
#    "mvp" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "improvement_roll1" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
    "improvement_roll2" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
#    "inflicted_bloodfeed_stuns" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
#    "inflicted_bloodfeed_kos" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
#    "inflicted_bloodfeed_bhs" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",
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

public static function triggerHandler($type, $argv){
    switch ($type) {
        case T_TRIGGER_MATCH_DELETE:
            return mysql_query("DELETE FROM ".self::TABLE." WHERE f_mid = $argv[0]");
            break;
    }
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
