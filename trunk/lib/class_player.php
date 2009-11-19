<?php

/*
 *  Copyright (c) Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2007-2009. All Rights Reserved.
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
 
// Maximum player-number a player can be assigned.
define("MAX_PLAYER_NR", 100);

// Stars and mercenaries.
define('ID_MERCS',       -1); // Mercenaries player_id.
define('ID_STARS_BEGIN', -5); // First star's player_id, second id is one smaller and so on.

// Player types.
define('PLAYER_TYPE_NORMAL',  1);
define('PLAYER_TYPE_JOURNEY', 2);

$skillcats = array(
    'N' => array('DEA_idx' => 'norm', 'obj_idx' => 'ach_nor_skills'), 
    'D' => array('DEA_idx' => 'doub', 'obj_idx' => 'ach_dob_skills'), 
    'E' => array('DEA_idx' => null,   'obj_idx' => 'extra_skills'),
);

class Player
{
    /***************
     * Properties 
     ***************/

    // MySQL stored information
    public $player_id = 0;
    public $type = 1;
    public $name = '';
    public $owned_by_team_id = 0;
    public $nr = 0;
    public $f_pos_id = 0;
    public $position = ''; public $pos = ''; // $position duplicate. $position may be edited for display purposes (=not actual position string used in $DEA). This is though.
    public $date_bought = '';
    public $date_sold   = '';
    public $ach_ma = 0;
    public $ach_st = 0;
    public $ach_ag = 0;
    public $ach_av = 0;
    public $ach_nor_skills = array();
    public $ach_dob_skills = array();
    public $extra_skills   = array();
    public $extra_spp = 0;
    public $extra_val = 0;
    
    public $value = 0;
    public $date_died = '';

    // Characteristics
    public $ma = 0;
    public $ag = 0;
    public $av = 0;
    public $st = 0;

    // Base characteristics
    public $def_ma = 0;
    public $def_av = 0;
    public $def_ag = 0;
    public $def_st = 0;
    public $def_skills = array();

    // Injuries
    public $inj_ma = 0;
    public $inj_st = 0;
    public $inj_ag = 0;
    public $inj_av = 0;
    public $inj_ni = 0;

    // Player status
    public $is_sold       = false;
    public $is_dead       = false;
    public $is_mng        = false;
    public $is_journeyman = false;

    // Others
    public $icon = "";
    public $qty = 0;
    public $choosable_skills = array('norm' => array(), 'doub' => array());
    
    // Relations
    public $f_tname = "";
    public $f_cid = 0;
    public $f_cname = "";
    public $f_rid = 0;
    public $f_rname = "";
        
    /***************
     * Methods 
     ***************/

    function __construct($player_id) {

        global $DEA;

        // Get relaveant store game data.
        $result = mysql_query("SELECT player_id,
            game_data_players.qty AS 'qty', game_data_players.pos AS 'pos', game_data_players.skills AS 'def_skills', 
            game_data_players.ma AS 'def_ma', game_data_players.st AS 'def_st', game_data_players.ag AS 'def_ag', game_data_players.av AS 'def_av'
            FROM players, game_data_players WHERE player_id = $player_id AND f_pos_id = pos_id");
        foreach (mysql_fetch_assoc($result) as $col => $val) {
            $this->$col = ($val) ? $val : 0;
        }
        $this->position = $this->pos;
        
        /* 
            Set general stats.
        */

        $this->setStats(false,false,false);
        
        $this->def_skills = empty($this->def_skills) ? array() : explode(',', $this->def_skills);
        $this->setSkills();
        
        $this->is_dead       = ($this->status == DEAD);
        $this->is_mng        = ($this->status == MNG);
        $this->is_sold       = (bool) $this->date_sold;
        $this->is_journeyman = ($this->type == PLAYER_TYPE_JOURNEY);
        
        /*
            Misc
        */
        
        $this->icon = PLAYER_ICONS.'/' . $DEA[$this->f_rname]['players'][$this->pos]['icon'] . '.gif';
        
        if (empty($this->name)) {
            $this->name = 'Unnamed';
        }
        
        if ($this->type == PLAYER_TYPE_JOURNEY) { # Check if player is journeyman like this - don't assume setStatusses() has ben called setting $this->is_journeyman.
            $this->position .= ' [J]';
            $this->def_skills[] = 99; # Loner.
        }
    }
    
    public function setStats($node, $node_id, $set_avg = false)
    {
        foreach (Stats::getAllStats(T_OBJ_PLAYER, $this->player_id, $node, $node_id, $set_avg) as $key => $val) {
            $this->$key = $val;
        }
        
        return true;
    }
    
    public function setSkills() {
        global $skillcats;
        foreach ($skillcats as $t => $grp) {
            $result = mysql_query("SELECT GROUP_CONCAT(f_skill_id) FROM players_skills WHERE f_pid = $this->player_id AND type = '$t'");
            $row = mysql_fetch_row($result);
            $this->{$grp['obj_idx']} = empty($row[0]) ? array() : explode(',', $row[0]);
        }
    }
    
    public function setChoosableSkills() {

        global $DEA, $skillarray, $skillcats;
        
        $this->setSkills();
        $n_skills = $DEA[$this->f_rname]['players'][$this->pos]['norm'];
        $d_skills = $DEA[$this->f_rname]['players'][$this->pos]['doub'];
        
        foreach ($n_skills as $category) {
            foreach ($skillarray[$category] as $id => $skill) {
                if (!in_array($id, $this->ach_nor_skills) && !in_array($id, $this->def_skills)) {
                    array_push($this->choosable_skills[ $skillcats['N']['DEA_idx'] ], $id);
                }
            }
        }
        foreach ($d_skills as $category) {
            foreach ($skillarray[$category] as $id => $skill) {
                if (!in_array($id, $this->ach_dob_skills) && !in_array($id, $this->def_skills)) {
                    array_push($this->choosable_skills[ $skillcats['D']['DEA_idx'] ], $id);
                }
            }
        }
        
        /* Remove illegal combinations: */
        $all_skills = array_merge($this->def_skills, $this->extra_skills, $this->ach_nor_skills,$this->ach_dob_skills);
        global $IllegalSkillCombinations;
        foreach ($IllegalSkillCombinations as $hasSkill => $dropSkills) {
            if (in_array($hasSkill, $all_skills)) {
                foreach (array($skillcats['N']['DEA_idx'], $skillcats['D']['DEA_idx']) as $type) {
                    $this->choosable_skills[$type] = array_filter($this->choosable_skills[$type], create_function('$skill', "return !in_array(\$skill, array(".implode(",",$dropSkills)."));"));
                }
            }
        }
        
        return true;
    }
    
    public function mayHaveNewSkill() {

        global $sparray;
        
        $this->setSkills();
        
        $skill_count =   count($this->ach_nor_skills)
                       + count($this->ach_dob_skills)
                       + $this->ach_ma
                       + $this->ach_st
                       + $this->ach_ag
                       + $this->ach_av;
                       
        $allowable_skills = 0; # Allowable skills = player level = SPR

        foreach (array_reverse($sparray) as $rank => $details) { # Loop through $sparray reversed so highest ranks come first.
            if ($this->mv_spp >= $details['SPP']) {
                $allowable_skills = $details['SPR'];
                break;
            }
        }

        return (($skill_count < $allowable_skills) && !$this->is_sold); # If fewer skills than able to have for current SPP-level -> allow new skill.
    }

    public function is_unbuyable() {
        // Is able to be un-bought, does not mean that player is not buyable!
        // If the player has NOT participated in any matches then player is un-buyable.
        $query = "SELECT COUNT(*) AS 'cnt' FROM match_data WHERE f_player_id = $this->player_id";
        return !(($result = mysql_query($query)) && ($row = mysql_fetch_assoc($result)) && $row['cnt'] > 0);
    }

    public function sell() {
    
        /**
         * Sell player.
         **/
    
        global $rules;
    
        if ($this->is_sold || $this->is_dead)
            return false;

        $team = new Team($this->owned_by_team_id);
        $val = $this->is_journeyman ? 0 : $this->value;
        
        if (!$team->dtreasury($val * $rules['player_refund']))
            return false;

        if (!mysql_query("UPDATE players SET date_sold = NOW() WHERE player_id = $this->player_id"))
            return false;

        $this->is_sold = true;
        return true;
    }

    public function unsell() {

        /**
         * Regret selling player (un-sell).
         **/

        global $rules;

        if (!$this->is_sold || $this->is_dead)
            return false;
            
        $team = new Team($this->owned_by_team_id);
        $val = $this->is_journeyman ? 0 : $this->value;
        
        if (!$team->dtreasury(-1 * $val * $rules['player_refund']))
            return false;

        if (!mysql_query("UPDATE players SET date_sold = NULL WHERE player_id = $this->player_id"))
            return false;

        $this->is_sold = false;
        return true;        
    }

    public function unbuy() { # "Un-create"
    
        /**
         * Regret hirering/purchasing player (un-buy).
         **/
    
        if (!$this->is_unbuyable() || $this->is_sold)
            return false;
            
        $price = ($this->is_journeyman) ? 0 : self::price($this->f_pos_id);
        $team = new Team($this->owned_by_team_id);

        if (!$team->dtreasury($price))
            return false;

        if (!mysql_query("DELETE FROM players WHERE player_id = $this->player_id"))
            return false;
            
        return true;
    }
    
    public function hireJourneyman() {

        /**
         * Permanently hire journeymen.
         **/

        if (!$this->is_journeyman || $this->is_sold || $this->is_dead)
            return false;
            
        $team = new Team($this->owned_by_team_id);
        $price = self::price($this->f_pos_id);
        
        if ($team->isFull() || !$team->isPlayerBuyable($this->pos) || $team->treasury < $price || !$team->dtreasury(-1 * $price))
            return false;

        $query = "UPDATE players SET type = ".PLAYER_TYPE_NORMAL." WHERE player_id = $this->player_id";
        
        if (mysql_query($query)) {
            return true;
        }
        // Return money.
        else {
            $team->dtreasury($price);
            return false;
        }
    }

    public function unhireJourneyman() {

        /**
         * Regret permanently hiring journeymen.
         **/

        if ($this->is_journeyman || $this->is_sold || $this->is_dead)
            return false;

        global $DEA;

        $team = new Team($this->owned_by_team_id);
        $price = self::price($this->f_pos_id);

        if ($this->qty != 16) # Journeymen are players from a 0-16 buyable position.
            return false;
       
        if (!$team->dtreasury($price))
            return false;

        $query = "UPDATE players SET type = ".PLAYER_TYPE_JOURNEY." WHERE player_id = $this->player_id";
        
        if (mysql_query($query)) {
            return true;
        }
        // Pull back money.
        else {
            $team->dtreasury(-1 * $price);
            return false;
        }        
    }

    public function rename($new_name) {
        return mysql_query("UPDATE players SET name = '" . mysql_real_escape_string($new_name) . "' WHERE player_id = $this->player_id");
    }
    
    public function renumber($number) {
        return ($number <= MAX_PLAYER_NR && mysql_query("UPDATE players SET nr = $number WHERE player_id = $this->player_id"));
    }

    public function dspp($delta) {
        $query = "UPDATE players SET extra_spp = IF(extra_spp IS NULL, $delta, extra_spp + ($delta)) WHERE player_id = $this->player_id";
        return mysql_query($query);
    }

    public function dval($val = 0) {
        $query = "UPDATE players SET extra_val = $val WHERE player_id = $this->player_id";
        return mysql_query($query) && self::forceUpdTrigger($this->player_id);
    }

    public function addSkill($type, $skill) {
    
        /**
         * Add new player skill.
         *
         *  $type may be:
         *  ------------- 
         *  "N" = Normal skill
         *  "D" = Double skill
         *  "E" = Extra skill
         *  "C" = Characteristics
         **/

        global $DEA, $skillididx, $skillcats;
        
        $this->setSkills();        
        $this->setChoosableSkills();

        // Don't allow new skill if not enough SPP, unless it is an extra skill.
        if ($type != 'E' && !$this->mayHaveNewSkill())
            return false;

        // Statuses
        $IS_REGULAR = (in_array($type, array('N', 'D')) && in_array($skill, $this->choosable_skills[$skillcats[$type]['DEA_idx']]));
        $IS_EXTRA   = ($type == 'E' && in_array($skill, array_keys($skillididx)));

        // Determine skill type.
        $query = '';
        if ($type == "C" && preg_match("/^ach_\w{2}$/", $skill)) { # ach_XX ?
            if ($this->chrLimits('ach', preg_replace('/^ach_/', '', $skill)))
                $query = "UPDATE players SET $skill = $skill + 1 WHERE player_id = $this->player_id";
        }
        elseif ($IS_REGULAR || $IS_EXTRA) {
            $this->{$skillcats[$type]['obj_idx']}[] = $skill;
            $query = "INSERT INTO players_skills(f_pid, f_skill_id, type) VALUES ($this->player_id, $skill, '$type')";
        }

        $ret = mysql_query($query);
        self::forceUpdTrigger($this->player_id); # Update player value.
        return $ret;
    }

    public function rmSkill($type, $skill) {
        
        /**
         * Remove existing player skill.
         **/
         
        global $skillcats;

        $query = '';
        if (in_array($type, array_keys($skillcats))) {
            $query = "DELETE FROM players_skills WHERE f_pid = $this->player_id AND type = '$type' AND f_skill_id = $skill";
        }
        elseif ($type == "C" && preg_match("/^ach_\w{2}$/", $skill)) {
            $query = "UPDATE players SET $skill = $skill - 1 WHERE player_id = $this->player_id";
        }
        $ret = mysql_query($query);
        self::forceUpdTrigger($this->player_id); # Update player value.
        return $ret;
    }
    
    public function getStatus($match_id) {
        return self::getPlayerStatus($this->player_id, $match_id);
    }
    
    public function chrLimits($type, $char) {

        /**
         * Characteristics limit handler. Returns the number of characteristic injuries/achievements the player is further allowed.
         **/

        $def = 'def_' . $char; # Default characteristic value - where $char is one of: MA, ST, AG or AV.
        $ret = 0;

        if ($type == 'ach') {
            
            /* 
                Returns the number of archived characteristics the player is allowed.
                Limits:
                    - Default + 2
                    - Max 10
            */

            if ($this->$def < 9)
                $ret = $this->$def + 2 - $this->$char;
            else
                $ret = 10 - $this->$char;                
        }
        elseif ($type == 'inj') {
            
            /* 
                Returns the number of characteristic injuries the player may sustain.
                Limits:
                    - Default - 2
                    - Min 1
            */
            
            if ($this->$def > 2)
                $ret = $this->$char - ($this->$def - 2);
            else
                $ret = $this->$char - 1;
        }
        
        return ($ret >= 0) ? $ret : 0; // Make sure we always get zero when no more injuries/ach. chars may be sustained/obtained.
    }
    
    public function getMatchMost($field) {
        
        /**
         * Returns an array structure with match data (and match obj.), for those matches, where $this player has the most of $field, 
         * compared to all other player in the same match.
         **/
        
        $matches = array();

        $matchesPlayed = "(SELECT DISTINCT f_match_id AS 'mid' FROM match_data WHERE f_player_id = $this->player_id) AS matchesPlayed";
        $max = "(SELECT f_match_id AS 'mid', MAX($field) AS 'maxVal' FROM match_data, $matchesPlayed WHERE f_match_id = mid GROUP BY f_match_id) AS max";
        $cntMax = "(SELECT f_match_id AS 'mid', COUNT(*) AS 'cnt', maxVal FROM match_data, $max WHERE f_match_id = mid AND ($field) = maxVal GROUP BY f_match_id) cntMax";
        $query = "
            SELECT 
                *
            FROM 
                match_data, $cntMax 
            WHERE 
                    f_match_id = mid 
                AND f_player_id = $this->player_id
                AND ($field) = maxVal 
                AND cnt = 1
        ";

        if (($result = mysql_query($query)) && mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                array_push($matches, array_merge(array('match_obj' => new Match($row['f_match_id'])), $row));
            }
        }
        
        return $matches;
    }

    public function getAchEntries($type) {

        /**
         * Returns an array structure with match data (and match obj.), for those matches, where $this player has an achivement of type $type.
         **/

        $mdata = array();

        $query = "SELECT mvp, cp, td, intcpt, bh, ki, si, f_match_id FROM match_data, matches WHERE match_id > 0 AND f_match_id = match_id AND f_player_id = $this->player_id AND ($type) > 0 ORDER BY date_played DESC";
        if (($result = mysql_query($query)) && mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                array_push($mdata, array_merge($row, array('match_obj' => new Match($row['f_match_id']))));
            }
        }
        
        return $mdata;
    }
    
    public function getMatchData($match_id) {
    
        /**
         * Returns array holding the match data entry from a specific match for this player.
         **/
    
        $query  = "SELECT * FROM match_data WHERE f_match_id = $match_id AND f_player_id = $this->player_id";
        $result = mysql_query($query);
        $row    = mysql_fetch_assoc($result);
        
        $mdat = array();
        foreach (array('mvp', 'cp', 'td', 'intcpt', 'bh', 'si', 'ki', 'inj', 'agn1', 'agn2') as $col) {
            $mdat[$col] = (isset($row[$col])) ? $row[$col] : 0;
        }

        return $mdat;
    }
    
    public function saveText($str) {
        $desc = new ObjDescriptions(T_TEXT_PLAYER, $this->player_id);
        return $desc->save($str);
    }

    public function getText() {
        $desc = new ObjDescriptions(T_TEXT_PLAYER, $this->player_id);
        return $desc->txt;
    }
    
    public function savePic($name = false) {
        $img = new ImageSubSys(IMGTYPE_PLAYER, $this->player_id);
        list($retstatus, $error) = $img->save($name);
        return $retstatus;
    }
    
    public function getSkillsStr($HTML = false) 
    {
        /**
         * Compiles skills string.
         **/
    
        $this->setSkills();
        $chrs = array();
        $extras = empty($this->extra_skills) ? array() : explode(', ', skillsTrans($this->extra_skills));

        // First italic-ize extra skills
        if ($HTML) {
            array_walk($extras, create_function('&$val,$key', '$val = "<i>$val</i>";'));
        }
        else {
            array_walk($extras, create_function('&$val,$key', '$val = "$val*";'));
        }

        if ($this->ach_ma > 0) array_push($chrs, "+$this->ach_ma Ma");
        if ($this->ach_st > 0) array_push($chrs, "+$this->ach_st St");
        if ($this->ach_ag > 0) array_push($chrs, "+$this->ach_ag Ag");
        if ($this->ach_av > 0) array_push($chrs, "+$this->ach_av Av");

        $skillstr = skillsTrans(array_merge($this->def_skills, $this->ach_nor_skills, $this->ach_dob_skills));
        return implode(', ', array_merge(empty($skillstr) ? array() : array($skillstr), $extras, $chrs));
    }
    
    public function getInjsStr($HTML = false) 
    {
        /**
         * Compiles injuries string.
         **/
    
        $injs = array();
        
        if ($this->inj_ma > 0) array_push($injs, "-$this->inj_ma Ma");
        if ($this->inj_st > 0) array_push($injs, "-$this->inj_st St");
        if ($this->inj_ag > 0) array_push($injs, "-$this->inj_ag Ag");
        if ($this->inj_av > 0) array_push($injs, "-$this->inj_av Av");
        if ($HTML) {
            if ($this->inj_ni > 0) array_push($injs, "<font color='red'>$this->inj_ni Ni</font>");
        }
        else {
            if ($this->inj_ni > 0) array_push($injs, "$this->inj_ni Ni");
        }
        if ($this->is_mng)     array_push($injs, "MNG");
        
        return implode(', ', $injs);
    }
    
    /***************
     * Statics
     ***************/

    public static function getPlayerStatus($player_id, $match_id) {

        /**
         * Returns player status for specific $match_id, or current status if $match_id == -1 (latest match).
         **/

        $query = "SELECT getPlayerStatus($player_id,$match_id) AS 'inj'";

        // Determine what status is.
        $result = mysql_query($query);
        if (is_resource($result) && mysql_num_rows($result) > 0) {
            $row = mysql_fetch_assoc($result);
            switch ($row['inj'])
            {
                case NONE: return NONE;
                case DEAD: return DEAD;
                default:   return MNG;
            }
        }
        else {
            return NONE;
        }
    }

    public static function price($pos_id) {
    
        /**
         * Get the price of a specific player.
         **/
        
        $result = mysql_query("SELECT cost FROM game_data_players WHERE pos_id = $pos_id");
        $row = mysql_fetch_row($result);
        return (int) $row[0];
    }
    
    public static function theDoctor($const) {

        /* The doctor translates PHP constants into their string equivalents. */
        
        global $STATUS_TRANS;
        return strtolower($STATUS_TRANS[$const]);
    }

    public static function create(array $input, $journeyman = false) {

        /**
         * Creates a new player.
         *
         * Input: nr, f_pos_id, name, team_id, (optional) forceCreate
         * Output: Returns array. First element: True/false, if false second element holds string containing error explanation.
         **/

        global $rules;
        global $DEA;
             
        $team    = new Team($input['team_id']);
        $players = $team->getPlayers();
        $price   = $journeyman ? 0 : self::price($input['f_pos_id']);
        
        // Ignore errors and force creation (used when importing teams)?
        if (!array_key_exists('forceCreate', $input) || !$input['forceCreate']) {
        
            if ($journeyman) {
                // Journeymen limit reached?
                if (count(array_filter($players, create_function('$p', "return (\$p->is_sold || \$p->is_dead || \$p->is_mng) ? false : true;"))) >= $rules['journeymen_limit'])
                    return array(false, "Journeymen limit is reached. Your team is able to fill $rules[journeymen_limit] positions.");

                // Is position valid to make a journeyman? 
                // Journeymen may be made from those positions, from which 16 players of the position is allowed on a team.
                if ($DEA[$team->f_rname]['players'][get_alt_col('game_data_players', 'pos_id', $input['f_pos_id'], 'pos')]['qty'] < (($rules['enable_lrb6x']) ? 12 : 16))
                    return array(false, 'May not make a journeyman from that player position.');       
            }
            else {
                // Team full?
                if ($team->isFull())
                    return array(false, "Team is full. You have filled all $rules[max_team_players] available positions.");

                // Enough money?
                if ($team->treasury - $price < 0)
                    return array(false, 'Not enough money.');

                // Reached max quantity of player position?
                if (!$team->isPlayerBuyable($input['f_pos_id']))
                    return array(false, 'Maximum quantity of player position is reached.');        
            }
        }
        
        // Player number to large?
        if ($input['nr'] > MAX_PLAYER_NR)
            return array(false, 'Player number too large.');

        // Player number already in use on team?
        foreach ($players as $p) {
            if ($p->nr == $input['nr'] && !$p->is_sold && !$p->is_dead) {
                return array(false, 'Player number in use.');
            }
        }

        // Withdraw the gold.
        if (!$team->dtreasury(-1 * $price))
            return array(false, 'Failed to withdraw money from treasury.');

        // Add player to team.
        $query = "INSERT INTO players
                    (
                        name,
                        type,
                        owned_by_team_id,
                        nr,
                        f_pos_id,
                        date_bought,
                        ach_ma,
                        ach_st,
                        ach_ag,
                        ach_av,
                        extra_spp
                    )
                    VALUES
                    (
                        '" . mysql_real_escape_string($input['name']) . "', 
                        " . ($journeyman ? PLAYER_TYPE_JOURNEY : PLAYER_TYPE_NORMAL ) . ",
                        $input[team_id], 
                        $input[nr], 
                        $input[f_pos_id], 
                        NOW(), 

                        0, 0, 0, 0,
                        0
                    )";

        if (!mysql_query($query)) {

            // If execution made it here, the team needs its money back before returning an error.
            if (!$team->dtreasury($price))
                return array(false, 'Could not acquire new player and failed to pay money back to team! Please contact an admin.');

            return array(false, 'MySQL error: Could not add new player to team.'); // Gold was returned to team's treasury.
        }
        
        // Return player ID if successful.
        $pid = (int) mysql_insert_id();
        self::forceUpdTrigger($pid);
        return array(true, $pid);
    }
    
    public static function forceUpdTrigger($pid) {
        return mysql_query("UPDATE players SET value = 0 WHERE player_id = $pid"); # Force update trigger to sync properties.
    }
}

?>
