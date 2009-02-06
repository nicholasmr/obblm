<?php

/*
 *  Copyright (c) Niels Orsleff Justesen <njustesen@gmail.com> and Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2007-2008. All Rights Reserved.
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

class Coach
{
    /***************
     * Properties 
     ***************/
    
    // MySQL stored information
    public $coach_id    = 0;
    public $name        = '';
    public $passwd      = '';
    public $mail        = '';
    public $admin       = false;
    
    // General (total) calcualted fields
    public $mvp         = 0;
    public $cp          = 0;
    public $td          = 0;
    public $intcpt      = 0;
    public $bh          = 0;
    public $si          = 0;
    public $ki          = 0;
    public $cas         = 0; // bh+ki+si
    public $tdcas       = 0; // Is td+cas. Used by some ranking systems. 
#    public $spp         = 0;
    //-------------------
    public $played      = 0;
    public $won         = 0;
    public $lost        = 0;
    public $draw        = 0;
    public $win_percentage = 0;
    public $score_team  = 0;    // Total score.
    public $score_opponent = 0; // Total score made against.
    public $score_diff  = 0;    // score_team - score_opponent.
    public $fan_factor  = 0;
    public $points      = 0; // Total points.
    public $smp         = 0; // Sportsmanship points.
    public $tcas        = 0; // Team cas.
    //-------------------    
    
    // Non-constructor filled fields.
    
        // By setExtraStats().
        public $won_tours       = 0;
        public $teams           = array();
        public $teams_cnt       = 0;
        public $avg_team_value  = 0;
    
        // By setStreaks().
        public $row_won  = 0; // Won in row.
        public $row_lost = 0;
        public $row_draw = 0;
    
    /***************
     * Methods 
     ***************/
    
    function __construct($coach_id) {
    
        // MySQL stored information
        $result = mysql_query("SELECT * FROM coaches WHERE coach_id = $coach_id");
        $row    = mysql_fetch_assoc($result);
        foreach ($row as $col => $val)
            $this->$col = $val ? $val : 0;
            
        $this->admin = $row['admin'] ? true : false; # Re-define as boolean.
        if (empty($this->mail)) $this->mail = ''; # Re-define as empty string, and not numeric zero.
        
        $this->setStats(false);
        
        return true;
    }
    
    public function setStats($tour_id = false) {
        
        /**
         * Overwrites object's stats fields.
         **/
         
        foreach (array_merge(Stats::getStats(false, false, $this->coach_id, false, $tour_id), Stats::getMatchStats(STATS_COACH, $this->coach_id, $tour_id)) as $field => $val) {
            $this->$field = $val;
        }

        return true;
    }
    
    public function setExtraStats() {
        
        /**
         * Set extra coach stats.
         **/
        
        $this->won_tours      = count($this->getWonTours());
        $this->teams          = $this->getTeams();
        $this->teams_cnt      = count($this->teams);
        
        $this->avg_team_value = 0;
        if ($this->teams_cnt > 0) {
            foreach ($this->teams as $t) {
                $this->avg_team_value += $t->value;            
            }
            $this->avg_team_value = $this->avg_team_value/$this->teams_cnt;
        }
        
        return true;
    }

    public function setStreaks($trid = false) {

        /**
         * Counts most won, lost and draw matches in a row.
         **/

        foreach (Stats::getStreaks(STATS_COACH, $this->coach_id, $trid) as $key => $val) {
            $this->$key = $val;
        }

        return true;
    }

    public function getTeams() {

        /**
         * Returns an array of team objects for those teams owned by this coach.
         **/
    
        $teams = array();
        
        $result = mysql_query("SELECT team_id FROM teams WHERE owned_by_coach_id = $this->coach_id ORDER BY name ASC");
        if ($result && mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                array_push($teams, new Team($row['team_id']));
            }
        }
        
        return $teams;
    }    

    public function getWonTours() {

        /**
         * Returns an array of tournament objects for those tournaments the coach's teams have won.
         **/
        
        $tours = array();
        
        foreach ($this->getTeams() as $t) {
            foreach ($t->getWonTours() as $tour) {
                array_push($tours, $tour);
            }
        }
        
        return $tours;
    }

    public function setAttr(array $input) {
        
        /**
         * Changes coach attributes: name, mail passwd, admin.
         *
         *  Input:
         *  ------
         *      type        = [name | passwd | mail | admin]
         *      new_value   = The new value for above type.
         **/

        if ($input['type'] == 'name' || $input['type'] == 'passwd' || $input['type'] == 'mail') {

            // To avoid confusion only unique coach names are allowed.
            if ($input['type'] == 'name' && get_alt_col('coaches', 'name', $input['new_value'], 'name'))
                return false;

            if (mysql_query("UPDATE coaches 
                            SET $input[type] = '" . ($input['type'] == 'passwd' ? md5($input['new_value']) : mysql_real_escape_string($input['new_value'])) . "' 
                            WHERE coach_id = $this->coach_id")) {
                $this->$input['type'] = ($input['type'] == 'passwd') ? md5($input['new_value']) : $input['new_value'];
                return true;
            }
        }
        elseif ($input['type'] == 'admin' && mysql_query("UPDATE coaches SET admin = '" . ($input['new_value'] ? 1 : 0) . "' WHERE coach_id = $this->coach_id")) {
            $this->admin = ($input['new_value'] ? true : false);
            return true;
        }

        return false;
    }

    public function isInMatch($match_id) {
    
        /**
         * Returns the boolean evaluation of a coach's participation in a specific match.
         **/
    
        $result = mysql_query("SELECT team1_id, team2_id FROM matches WHERE match_id = $match_id");
        $row    = mysql_fetch_assoc($result);
        $coach_id1 = get_alt_col('teams', 'team_id', $row['team1_id'], 'owned_by_coach_id');
        $coach_id2 = get_alt_col('teams', 'team_id', $row['team2_id'], 'owned_by_coach_id');

        return ($this->coach_id == $coach_id1 || $this->coach_id == $coach_id2);
    }
    
    public function saveText($str) {
        
        $desc = new TDesc(T_TEXT_COACH, $this->coach_id);
        return $desc->save($str);
    }

    public function getText() {

        $desc = new TDesc(T_TEXT_COACH, $this->coach_id);
        return $desc->txt;
    }
    
    public function savePic($name) {
        return save_pic($name, IMG_COACHES, $this->coach_id);
    }
    
    public function getPic() {
        return get_pic(IMG_COACHES, $this->coach_id);
    }

    /***************
     * Statics
     ***************/

    public static function getCoaches() {
    
        /**
         * Returns an array of all coach objects.
         **/
         
        $coaches = array();
        
        $query  = "SELECT coach_id FROM coaches";
        $result = mysql_query($query);
        if ($result && mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                array_push($coaches, new Coach($row['coach_id']));
            }
        }
                    
        return $coaches;
    }
    
    public static function create(array $input) {
        
        /**
         * Creates a new coach.
         *
         * Input: name, passwd, mail, admin
         **/

        if (empty($input['name']) || empty($input['passwd']) || get_alt_col('coaches', 'name', $input['name'], 'coach_id')) # Name exists already?
            return false;

        $query = "INSERT INTO coaches (name, passwd, mail, admin) 
                    VALUES ('" . mysql_real_escape_string($input['name']) . "',
                            '" . md5($input['passwd']) . "', 
                            '" . mysql_real_escape_string($input['mail']) . "', 
                            '" . ($input['admin'] ? 1 : 0)."')";
                            
        if (mysql_query($query))
            return true;
        else
            return false;
    }
}
?>
