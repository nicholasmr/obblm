<?php

// Extra Player Stats

class EPS implements ModuleInterface
{

const TABLE = 'eps';

public static $types = array(
    # cat fs.txt | perl -ne 's/^(\w*)\s*$/"$1" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0",\n/ && print'
    # cat fs.txt | perl -ne 's/^\|\|(\w*)\|\|([^|]*)\|\|\s*$/"$1" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "$2"),\n/ && print'
"pass_attempts" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of pass throw attempts of the ball."),
"pass_completions" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of completions of throws of the ball (+1 spp)"),
"interceptions_thrown" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times the thrower has been intercepted."),
"pass_distance" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of squares progression the ball was thrown towards the endzone (this should be multiplied up to give number of paces (x5?)"),
"dumpoff_attempts" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of passes thrown which have been dumpoffs (this is informational, pass_attempts includes dump offs)."),
"dumpoff_completions" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of completions from dump offs (as above this is for informational purposes, pass_completions includes dump off completions)."),
"catch_attempts" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of catch attempts made my a player from a throw."),
"catches" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of catches made (including re-rolled)."),
"handoffs" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of hand offs this player has made"),
"handoffs_received" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player has been handed off too."),
"handoff_catches" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number times this player caught a hand off (including re-rolled)."),
"pickup_attempts" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times attempting to pick the ball up."),
"pickups" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of successful pick ups (including re-rolled)."),
"rushing_distance_leap" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Squares of progression towards the end zone leaping with the ball."),
"rushing_distance_push" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Squares of progression towards the end zone from pushes."),
"rushing_distance_move" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Squares of progression with the ball running towards the end zone in a normal move."),
"rushing_distance_block" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Squares of progression towards the end zone from blocks/blitzes."),
"rushing_distance_shadowing" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Squares of progression towards the end zone from shadowing."),
"leap_attempts" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of leap attempts."),
"leaps" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of successful leaps (including re-rolled)."),
"dodge_attempts" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of dodge attempts"),
"dodges" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of successful dodges (including re-rolled)"),
"blitz_actions" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player has blitzed."),
"gfi_attempts" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Go for it attempts"),
"gfis" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times Nuffle didn't shit on you."),
"inflicted_blocks" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player tried to throw a block."),
"inflicted_defender_downs" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times defender down was the selected result."),
"inflicted_defender_stumbles" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times defender stumbles was the selected result."),
"inflicted_pushes" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times push was the selected result."),
"inflicted_both_downs" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times both down was the selected result."),
"inflicted_attacker_downs" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times attacker down was the selected result."),
"inflicted_knock_downs" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player knocked the other player down blocking (as the attacker, the defends ends up down or as the defender the attacker ends up down)."),
"inflicted_strip_balls" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times strip ball has been used by this player."),
"inflicted_sacks" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player knocked the other player down blocking (as the attacker, the defends ends up down or as the defender the attacker ends up down), when that player was carrying the ball."),
"inflicted_crowd_surfs" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times the push result has ended up in as an injury roll (presuming from being crowd surfed)."),
"inflicted_stuns" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player knocked the other player down blocking (as the attacker, the defends ends up down or as the defender the attacker ends up down) and that player ended up stunned."),
"inflicted_kos" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player knocked the other player down blocking (as the attacker, the defends ends up down or as the defender the attacker ends up down) and that player ended up KOed."),
"inflicted_bhs" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player knocked the other player down blocking (as the attacker, the defends ends up down or as the defender the attacker ends up down) and that player ended up badly hurt (after apoth)."),
"inflicted_sis" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player knocked the other player down blocking (as the attacker, the defends ends up down or as the defender the attacker ends up down) and that player ended up seriously injured (after apoth)."),
"inflicted_kills" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player knocked the other player down blocking (as the attacker, the defends ends up down or as the defender the attacker ends up down) and that player ended up dead (after apoth)"),
"sustained_blocks" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player has been blocked."),
"sustained_knocked_downs" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number this this player was knocked down while blocking either from sustaining a block or when throwing a block."),
"sustained_sacks" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number this this player was knocked down while blocking either from sustaining a block or when throwing a block when carrying the ball."),
"sustained_crowd_surfs" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player has been pushed and required been required to make an injury roll (from crowd surfs)."),
"sustained_stuns" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Total number of times this player has been stunned (from any means). All these stats check player status at the end of the turn."),
"sustained_kos" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Total number of times this player has been KOed (from any means)."),
"sustained_bhs" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Total number of times this player has been badly hurt (from any means)."),
"sustained_sis" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Total number of times this player has been seriously injured (from any means)."),
"sustained_kill" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Total number of times this player has been seriously injured (from any means)... this would only ever be 1!"),
"inflicted_fouls" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player has fouled another."),
"inflicted_foul_stuns" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player stunned another through fouling"),
"inflicted_foul_kos" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player knocked out another through fouling"),
"inflicted_foul_bhs" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player badly hurt another through fouling"),
"inflicted_foul_sis" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player seriously injured another through fouling"),
"inflicted_foul_kills" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player killed another through fouling"),
"sustained_fouls" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player has been fouled."),
"sustained_ejections" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player was ejected for fouling."),
"apothecary_used" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times the apoth has been used on this player"),
"ko_recovery_attempts" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of recovery rolls from KOs"),
"ko_recoveries" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of successful KOs recoveries"),
"thickskull_used" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times thick skull was used by this player."),
"regeneration_attempts" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of time this player attempted to regenerate."),
"regenerations" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times the regenerate roll succeeded."),
"kickoffs" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player kicked off"),
"kick_distance" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Distance the ball was kicked in squares."),
"dice_rolls" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player rolled a simple roll or skill roll."),
"dice_natural_ones" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of natural ones rolled."),
"dice_natural_sixes" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of natural sixes rolled."),
"dice_target_sum" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Sum of the total targets required."),
"dice_roll_sum" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Sum of what was actually rolled (with above would be used to show averages)."),
#"interceptions" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player intercepted the ball."),
#"casualties" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of casualties caused earning spp."),
#"touchdowns" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of touchdowns this player scored."),
#"injuries" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Injuried sustained by this player."),
#"mvp" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "1 if this player was MVP"),
"improvement_roll1" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "The skill up improvement roll (d1)"),
"improvement_roll2" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "The skill up mprovement roll (d2)"),
"big_guy_stupidity_attempts" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of rolls for really stupid, bonehead, take root and wild animal."),
"big_guy_stupidity_successes" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times the really stupid, bonehead, take root and wild animal roll succeeded."),
"big_guy_stupidity_blitz_attempts" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this big guy declared a blitz"),
"big_guy_stupidity_blitz_successes" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this big guy was able to blitz"),
"throw_team_mate_attempts" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of attempts to throw a team mate by this player"),
"throw_team_mate_successes" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player successfully threw a team mate."),
"throw_team_mate_distance" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "How far this player has thrown team mates in squares."),
"throw_team_mate_to_safe_landing" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player successfully threw a team mate and the thrown player landed."),
"times_thrown" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player has been thrown"),
"landing_attempts" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player has attempted to land"),
"landings" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player successfully landed."),
"distance_thrown" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "The distance this player has been thrown"),
"rushing_distance_thrown" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "The distance the ball progressed towards the end zone when this player was thrown (should be added to rushing distance total stat)"),
"bloodlust_rolls" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of blood lust rolls"),
"bloodlust_successes" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player didn't succumb to blood lust."),
"bloodfeeds" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of blood feeds by this vampire"),
"hypnoze_rolls" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times hypnotic gaze was used"),
"hypnoze_successes" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times hypnotic gaze was successful"),
#"inflicted_bloodfeed_stuns" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of stuns from a blood feed (doesn't seem to be working, path is action blood feed, armour roll 2 for thrall, injury roll for thrall, end status stunned). Not sure why it doesn't go straight to the injury roll."),
#"inflicted_bloodfeed_kos" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of KOs from a blood feed (as above)"),
#"inflicted_bloodfeed_bhs" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of badly hurts from a blood feed (as above)"),
"fed_on" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this thrall player has been fed on."),
"tentacles_rolls" => array("type" => "SMALLINT UNSIGNED NOT NULL DEFAULT 0", "desc" => "Number of times this player used his tentacles"),
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
    return array(self::TABLE => array_merge(self::$relations, array_map(create_function('$t', 'return $t[type];'), self::$types)));
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
