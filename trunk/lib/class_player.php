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
    
    public $ach_nor_skills_cnt = 0;
    public $ach_dob_skills_cnt = 0;    

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
    public $value = 0;
    public $icon = "";
    public $qty = 0;
    public $date_died = '';
    public $choosable_skills = array('N skills' => array(), 'D skills' => array());
    
    // Relations
    public $team_name = "";
    public $coach_id = 0;
    public $coach_name = "";
    public $f_race_id = 0;
    public $race = ""; # Race name
        
    /***************
     * Methods 
     ***************/

    function __construct($player_id) {

        global $DEA, $rules;

        /* 
            MySQL stored player data
        */
        $result = mysql_query("SELECT 
            player_id, type, name, owned_by_team_id, nr, position, date_bought, date_sold, 
            ach_ma, ach_st, ach_ag, ach_av, extra_spp, extra_val,
            LENGTH(ach_nor_skills) - LENGTH(REPLACE(ach_nor_skills, ',', '')) + IF(LENGTH(ach_nor_skills) = 0, 0, 1) AS 'ach_nor_skills_cnt', 
            LENGTH(ach_dob_skills) - LENGTH(REPLACE(ach_dob_skills, ',', '')) + IF(LENGTH(ach_dob_skills) = 0, 0, 1) AS 'ach_dob_skills_cnt'
            FROM players WHERE player_id = $player_id");
        foreach (mysql_fetch_assoc($result) as $col => $val) {
            $this->$col = ($val) ? $val : 0;
        }
        $this->pos = $this->position;
        $this->setRelations();
        
        /* 
            Player value 
        */
        $this->value = $DEA[$this->race]['players'][$this->pos]['cost']
            + ($this->ach_ma + $this->ach_av) * 30000
            + $this->ach_ag                   * 40000
            + $this->ach_st                   * 50000
            + $this->ach_nor_skills_cnt       * 20000
            + $this->ach_dob_skills_cnt       * 30000
            + $this->extra_val;
        
        // Custom value reduction.
        $this->value -= 
            $this->inj_ma*$rules['val_reduc_ma'] + 
            $this->inj_st*$rules['val_reduc_st'] + 
            $this->inj_ag*$rules['val_reduc_ag'] + 
            $this->inj_av*$rules['val_reduc_av'];
        
        /* 
            Set general stats.
        */
        $this->setChrs();
        $this->setSkills();
        $this->setStatusses();
        $this->setStats(false,false,false);
                       
        /*
            Misc
        */
        $this->icon = PLAYER_ICONS.'/' . $DEA[$this->race]['players'][$this->pos]['icon'] . '.gif';
        $this->qty = $DEA[$this->race]['players'][$this->pos]['qty'];
        if (empty($this->name)) {
            $this->name = 'Unnamed';
        }
        if ($this->type == PLAYER_TYPE_JOURNEY) { # Check if player is journeyman like this - don't assume setStatusses() has ben called setting $this->is_journeyman.
            $this->position .= ' [J]';
        }
    }
    
    public function setStats($node, $node_id, $set_avg = false)
    {
        foreach (Stats::getAllStats(STATS_PLAYER, $this->player_id, $node, $node_id, false, false, $set_avg) as $key => $val) {
            $this->$key = $val;
        }
        
        // Corrections
        if (!$node) {
            $this->spp += $this->extra_spp;
        }
        
        return true;
    }
    
    private function setChrs() {
        
        global $DEA;
        
        // Injuries
        $NI = NI; $MA = MA; $AV = AV; $AG = AG; $ST = ST;
        $query = "SELECT 
            SUM(IF(inj = $NI, 1, 0) + IF(agn1 = $NI, 1, 0) + IF(agn2 = $NI, 1, 0)) AS 'inj_ni', 
            SUM(IF(inj = $MA, 1, 0) + IF(agn1 = $MA, 1, 0) + IF(agn2 = $MA, 1, 0)) AS 'inj_ma', 
            SUM(IF(inj = $AV, 1, 0) + IF(agn1 = $AV, 1, 0) + IF(agn2 = $AV, 1, 0)) AS 'inj_av', 
            SUM(IF(inj = $AG, 1, 0) + IF(agn1 = $AG, 1, 0) + IF(agn2 = $AG, 1, 0)) AS 'inj_ag', 
            SUM(IF(inj = $ST, 1, 0) + IF(agn1 = $ST, 1, 0) + IF(agn2 = $ST, 1, 0)) AS 'inj_st' 
            FROM match_data WHERE f_player_id = $this->player_id";
        if (($result = mysql_query($query)) && mysql_num_rows($result) > 0 && ($row = mysql_fetch_assoc($result))) {
            foreach ($row as $k => $v) {
                $this->$k = ($v) ? $v : 0;
            }
        }
        
        // Current characteristics
        foreach (array('ma', 'st', 'ag', 'av') as $chr) {
            $this->{"def_$chr"} = $DEA[$this->race]['players'][$this->pos][$chr];
            $this->$chr = $this->{"def_$chr"} + $this->{"ach_$chr"} - $this->{"inj_$chr"};        
        }
    }
    
    public function setChoosableSkills() {

        global $DEA;
        global $skillarray;
        
        $this->setSkills();
        $n_skills = $DEA[$this->race]['players'][$this->pos]['N skills'];
        $d_skills = $DEA[$this->race]['players'][$this->pos]['D skills'];
        
        foreach ($n_skills as $category) {
            foreach ($skillarray[$category] as $skill) {
                if (!in_array($skill, $this->ach_nor_skills) && !in_array($skill, $this->def_skills)) {
                    array_push($this->choosable_skills['N skills'], $skill);
                }
            }
        }
        foreach ($d_skills as $category) {
            foreach ($skillarray[$category] as $skill) {
                if (!in_array($skill, $this->ach_dob_skills) && !in_array($skill, $this->def_skills)) {
                    array_push($this->choosable_skills['D skills'], $skill);
                }
            }
        }
        
        /* Remove illegal combinations: */
        $all_skills = array_merge($this->def_skills, $this->extra_skills, $this->ach_nor_skills,$this->ach_dob_skills);
        global $IllegalSkillCombinations;
        foreach ($IllegalSkillCombinations as $hasSkill => $dropSkills) {
            if (in_array($hasSkill, $all_skills)) {
                foreach (array('N', 'D') as $type) {
                    $this->choosable_skills["$type skills"] = array_filter($this->choosable_skills["$type skills"], create_function('$skill', "return !in_array(\$skill, array('".implode("','",$dropSkills)."'));"));
                }
            }
        }
        
        return true;
    }

    private function setSkills() {

        // Skills
        global $DEA;        
        $result = mysql_query("SELECT ach_nor_skills, ach_dob_skills, extra_skills FROM players WHERE player_id = $this->player_id");
        $row = mysql_fetch_assoc($result);
        $this->ach_nor_skills = (!empty($row['ach_nor_skills'])) ? explode(',', $row['ach_nor_skills']) : array();
        $this->ach_dob_skills = (!empty($row['ach_dob_skills'])) ? explode(',', $row['ach_dob_skills']) : array();
        $this->extra_skills   = (!empty($row['extra_skills']))   ? explode(',', $row['extra_skills'])   : array();
        $this->def_skills     = $DEA[$this->race]['players'][$this->pos]['Def skills'];
        if ($this->type == PLAYER_TYPE_JOURNEY) { # Check if player is journeyman like this - don't assume setStatusses() has ben called setting $this->is_journeyman.
            array_push($this->def_skills, 'Loner');
        }
    }
    
    private function setStatusses() {

        // Status
        $status = $this->getStatus(-1);
        $this->is_dead       = ($status == DEAD);
        $this->is_mng        = ($status == MNG);
        $this->is_sold       = (bool) $this->date_sold;
        $this->is_journeyman = ($this->type == PLAYER_TYPE_JOURNEY);
    }
    
    private function setRelations() {

        // Player relations
        global $raceididx;
        $query = "SELECT coaches.name AS 'coach_name', coach_id, teams.name AS 'team_name', f_race_id
FROM teams, coaches WHERE teams.owned_by_coach_id = coaches.coach_id AND teams.team_id = $this->owned_by_team_id";
        if (($result = mysql_query($query)) && ($row = mysql_fetch_assoc($result))) {
            foreach ($row as $key => $val) {
                $this->$key = $val;
            }
        }
        $this->race = $raceididx[$this->f_race_id];
    }
    
    public function mayHaveNewSkill() {

        global $sparray;

        $skill_count =   $this->ach_nor_skills_cnt
                       + $this->ach_dob_skills_cnt
                       + $this->ach_ma
                       + $this->ach_st
                       + $this->ach_ag
                       + $this->ach_av;
                       
        $allowable_skills = 0; # Allowable skills = player level = SPR

        foreach (array_reverse($sparray) as $rank => $details) { # Loop through $sparray reversed so highest ranks come first.
            if ($this->spp >= $details['SPP']) {
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
            
        $price = ($this->is_journeyman) ? 0 : Player::price(array('race' => $this->race, 'position' => $this->pos));
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
        $price = Player::price(array('race' => $team->race, 'position' => $this->pos));
        
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
        $price = Player::price(array('race' => $team->race, 'position' => $this->pos));

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
    
        /**
         * Rename player.
         **/
    
        return mysql_query("UPDATE players SET name = '" . mysql_real_escape_string($new_name) . "' WHERE player_id = $this->player_id");
    }
    
    public function renumber($number) {

        /**
         * Renumber player.
         **/
    
        return ($number <= MAX_PLAYER_NR && mysql_query("UPDATE players SET nr = $number WHERE player_id = $this->player_id"));
    }

    public function dspp($delta) {
    
        /**
         * Add a delta to player's extra SPP.
         **/
        
        $query = "UPDATE players SET extra_spp = IF(extra_spp IS NULL, $delta, extra_spp + ($delta)) WHERE player_id = $this->player_id";
        return mysql_query($query);
    }

    public function dval($val = 0) {
    
        /**
         * Add a delta to player's value.
         **/
        
        $query = "UPDATE players SET extra_val = $val WHERE player_id = $this->player_id";
        return mysql_query($query);
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

        global $skillarray;
        global $DEA;
        
        $this->setChoosableSkills();

        // Don't allow new skill if not enough SPP, unless it is an extra skill.
        if ($type != 'E' && !$this->mayHaveNewSkill())
            return false;

        // Determine skill type.
        if ($type == "C" && preg_match("/^ach_\w{2}$/", $skill)) { # ach_XX ?
            if ($this->chrLimits('ach', preg_replace('/^ach_/', '', $skill)) && mysql_query("UPDATE players SET $skill = $skill + 1 WHERE player_id = $this->player_id"))
                return true;
        }
        else {

            // Valid skill?
            if ($type == "N" || $type == "D") {
                if (!in_array($skill, $this->choosable_skills[$type . ' skills']))
                    return false;
            }
            else { # Type = Extra
                $valid = false;
                foreach ($skillarray as $scat) {
                    foreach ($scat as $s) {
                        if ($skill == $s) {
                            $valid = true;
                            break 2;
                        }
                    }
                }
                
                if (!$valid)
                    return false;
            }

            $mysql_type = '';
            switch ($type) {
                case 'N': $mysql_type = 'ach_nor_skills'; break;
                case 'D': $mysql_type = 'ach_dob_skills'; break;
                case 'E': $mysql_type = 'extra_skills';   break;
            }

            if (!in_array($skill, $this->$mysql_type)) {
                array_push($this->$mysql_type, $skill);
                if (set_list('players', 'player_id', $this->player_id, $mysql_type, $this->$mysql_type))
                    return true;
            }
        }

        return false; # Unknown $type or other fall-through error.
    }

    public function rmSkill($type, $skill) {
        
        /**
         * Remove existing player skill.
         **/

        if ($type == 'N' || $type == 'D' || $type == 'E') {
            $mysql_type = '';
            switch ($type) {
                case 'N': $mysql_type = 'ach_nor_skills'; break;
                case 'D': $mysql_type = 'ach_dob_skills'; break;
                case 'E': $mysql_type = 'extra_skills';   break;                
            }

            if (!in_array($skill, $this->$mysql_type)) { # Quit if trying to remove a skill the player does not have!
                return false;
            }
            
            $new_skills = array_filter($this->$mysql_type, create_function('$xskill', "return (\$xskill == '$skill') ? false : true;"));
            if (set_list('players', 'player_id', $this->player_id, $mysql_type, $new_skills)) {
                return true;
            }
        }
        elseif ($type == "C" && preg_match("/^ach_\w{2}$/", $skill)) {
            if ($this->$skill == 0) # Don't allow MySQL type overflowing to 255.
                return false;
                
            if (mysql_query("UPDATE players SET $skill = $skill - 1 WHERE player_id = $this->player_id"))
                return true;
        }

        return false; # Unknown $type or other fall-through error.
    }
    
    public function getStatus($match_id) {

        /**
         * Returns player status for specific $match_id, or current status if $match_id == -1 (latest match).
         **/

        // Determine from what match to pull status from.
        $query = '';
        
        if ($match_id == -1) {
            $query = "SELECT inj FROM match_data, matches WHERE 
                            f_player_id = $this->player_id AND
                            match_id = f_match_id AND
                            date_played IS NOT NULL
                       ORDER BY date_played DESC LIMIT 1";
        }
        else {
            $match = new Match($match_id); # Assume that $match_id is valid.
            if (!$match->is_played) # If not is played, then date_played is not valid -> return current player status instead.
                return $this->getStatus(-1);
                
            $query = "SELECT inj FROM match_data, matches WHERE 
                            f_player_id = $this->player_id AND
                            match_id = f_match_id AND
                            date_played IS NOT NULL AND
                            date_played < '$match->date_played'
                       ORDER BY date_played DESC LIMIT 1";
        }

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
    
    public function getDateDied() {
        $query = "SELECT date_played FROM matches, match_data WHERE f_match_id = match_id AND f_player_id = $this->player_id AND inj = " . DEAD;
        return (($result = mysql_query($query)) && mysql_num_rows($result) > 0 && ($row = mysql_fetch_assoc($result))) ? ($this->date_died = $row['date_played']) : false;
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
    
        $chrs = array();
        $extras = $this->extra_skills;
        
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
        
        return implode(', ', array_merge($this->def_skills, $this->ach_nor_skills, $this->ach_dob_skills, $extras, $chrs));
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
     
    public static function getPlayers() {
        
        /**
         * Returns an array of all player objects.
         **/
         
        $players = array();
        
        $query  = "SELECT player_id FROM players";
        $result = mysql_query($query);
        if (mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                array_push($players, new Player($row['player_id']));
            }
        }
        
        return $players;
    }
    
    public static function price(array $input) {
    
        /**
         * Get the price of a specific player.
         *
         * Arguments: race, position
         **/
        
        global $DEA;

        // Check if race is valid.
        if (array_key_exists($input['race'], $DEA) && array_key_exists($input['position'], $DEA[$input['race']]['players']))
            return $DEA[$input['race']]['players'][$input['position']]['cost'];
        else
            return null;
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
         * Input: nr, position, name, team_id, (optional) forceCreate
         * Output: Returns array. First element: True/false, if false second element holds string containing error explanation.
         **/

        global $rules;
        global $DEA;
             
        $team    = new Team($input['team_id']);
        $players = $team->getPlayers();
        $price   = $journeyman ? 0 : Player::price(array('race' => $team->race, 'position' => $input['position']));
        
        // Ignore errors and force creation (used when importing teams)?
        if (!array_key_exists('forceCreate', $input) || !$input['forceCreate']) {
        
            if ($journeyman) {
                // Journeymen limit reached?
                if (count(array_filter($players, create_function('$p', "return (\$p->is_sold || \$p->is_dead || \$p->is_mng) ? false : true;"))) >= $rules['journeymen_limit'])
                    return array(false, "Journeymen limit is reached. Your team is able to fill $rules[journeymen_limit] positions.");

                // Is position valid to make a journeyman? 
                // Journeymen may be made from those positions, from which 16 players of the position is allowed on a team.
                if ($DEA[$team->race]['players'][$input['position']]['qty'] < (($rules['enable_lrb6x']) ? 12 : 16))
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
                if (!$team->isPlayerBuyable($input['position']))
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
                        position,
                        date_bought,
                        ach_ma,
                        ach_st,
                        ach_ag,
                        ach_av,
                        ach_nor_skills,
                        ach_dob_skills,
                        extra_skills,
                        extra_spp
                    )
                    VALUES
                    (
                        '" . mysql_real_escape_string($input['name']) . "', 
                        " . ($journeyman ? PLAYER_TYPE_JOURNEY : PLAYER_TYPE_NORMAL ) . ",
                        $input[team_id], 
                        $input[nr], 
                        '$input[position]', 
                        NOW(), 

                        0, 0, 0, 0,
                        '', '', '',
                        0
                    )";

        if (!mysql_query($query)) {

            // If execution made it here, the team needs its money back before returning an error.
            if (!$team->dtreasury($price))
                return array(false, 'Could not acquire new player and failed to pay money back to team! Please contact an admin.');

            return array(false, 'MySQL error: Could not add new player to team.'); // Gold was returned to team's treasury.
        }

        // Return player ID if successful.
        $query = "SELECT MAX(player_id) AS 'player_id' FROM players WHERE owned_by_team_id = $input[team_id] AND nr = $input[nr]";
        $result = mysql_query($query);
        $row = mysql_fetch_assoc($result);

        return array(true, $row['player_id']);
    }
}

?>
