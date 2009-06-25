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

class Tour
{

    /* 
        Please note: OBBLM also uses a match's "rounds" field to distinguish ordinary matches from semi-finals and finals. 
        This means, that some round numbers are reserved for the above purpose.
        See the constant definitions from class_match.php for reserved round numbers.
    */

    /***************
     * Properties 
     ***************/

    // MySQL stored information
    public $tour_id         = 0;
    public $f_did           = 0; // From division ID.
    public $name            = '';
    public $type            = 0;
    public $date_created    = '';
    public $rs              = 0; // Ranking system.

    // Other
    public $winner          = null; # Team ID.
    public $is_finished     = false;
    public $empty           = false; # Tournament has no matches assigned with it.
    public $begun           = false; # Tournament contains played matches?

    // Only for knock-out tournaments.
    public $koObj           = null; // Knock-out tournament object.

    /***************
     * Methods 
     ***************/

    function __construct($tour_id) {

        global $settings;

        // MySQL stored information.
        $result = mysql_query("SELECT * FROM tours WHERE tour_id = $tour_id");
        $row    = mysql_fetch_assoc($result);
        foreach ($row as $col => $val)
            $this->$col = $val ? $val : 0;

        // Empty tournament (all matches have been deleted)?
        $query  = "SELECT COUNT(match_id) AS 'count' FROM matches WHERE f_tour_id = $this->tour_id";
        $result = mysql_query($query);
        $row    = mysql_fetch_assoc($result);
        $this->empty = $row['count'] < 1 ? true : false;
        
        // Determine if tournament has begun (one or more matches have been played).
        $query  = "SELECT COUNT(match_id) AS 'count' FROM matches WHERE f_tour_id = $this->tour_id AND date_played IS NOT NULL";
        $result = mysql_query($query);
        $row    = mysql_fetch_assoc($result);
        $this->begun = $row['count'] > 0 ? true : false;

        // Delete MySQL tournament entry if no matches are assigned to tournament.
//        if ($this->empty) {
//            $this->delete();
//        }
                
        /* Determine tournament status and winner (if finished). */

        if ($this->type == TT_NOFINAL) {

            if ($this->empty) # Tournament with all matches deleted?
                return false;

            // Count un-played matches.
            $query  = "SELECT COUNT(match_id) AS 'count' FROM matches WHERE f_tour_id = $this->tour_id AND date_played IS NULL";
            $result = mysql_query($query);
            $row    = mysql_fetch_assoc($result);
            
            if ($row['count'] == 0) { # If all matches have been played.
                $this->is_finished = true;
                $teams = $this->getStandings();
                $this->winner = $teams[0]->team_id;
            }
        }
        elseif ($this->type == TT_FINAL || $this->type == TT_SEMI || $this->type == TT_KNOCKOUT) {
            $result = mysql_query("SELECT match_id FROM matches WHERE f_tour_id = $this->tour_id AND round = " . RT_FINAL);
            if (mysql_num_rows($result) > 0) { # There exists a final match for tournament.
                $row = mysql_fetch_assoc($result);
                $match = new Match($row['match_id']);
                
                if ($match->is_played && !$match->is_draw) { # Final matches may not end with tied results!
                    $this->is_finished = true;
                    $this->winner = $match->winner; # Winner of tournament is the winner of final match.
                }
            }
        }
        elseif ($this->type == TT_SINGLE) {
        
            // Count all non-played matches. If zero then tour is "finished" (FFA's aren't really finished per say).
            $query = "SELECT COUNT(*) 'non_played' FROM matches WHERE f_tour_id = $this->tour_id AND date_played IS NULL";
            $result = mysql_query($query);
            $row    = mysql_fetch_assoc($result);
            if ((int) $row['non_played'] == 0) {
                $this->is_finished = true;
            }
            
            // Winner determinable?
            $query = "SELECT IF(team1_score > team2_score, team1_id, team2_id) AS 'team_id' FROM matches WHERE f_tour_id = $this->tour_id AND round = ".RT_FINAL." AND date_played IS NOT NULL AND team1_score != team2_score";
            $result = mysql_query($query);
            if ($result && mysql_num_rows($result) > 0) {
                $row = mysql_fetch_assoc($result);
                $this->winner = $row['team_id'];
            }
        }
    }

    public function getMatches($opt = array()) {

        /**
         * Returns an array of match objects for those matches which are assigned to this tournament.
         **/

        $matches = array();
        $result = mysql_query("SELECT match_id FROM matches 
            WHERE f_tour_id = $this->tour_id ".
            ((array_key_exists('tid', $opt) && $opt['tid']) ? " AND (team1_id = $opt[tid] OR team2_id = $opt[tid]) " : '' ).
            ((array_key_exists('played', $opt) && $opt['played']) ? " AND date_played IS NOT NULL " : '' ).
            " ORDER BY match_id ASC");
        if (mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                array_push($matches, new Match($row['match_id']));
            }
        }
        
        return $matches;
    }

    // Teams in tournament.
    public function getTeams($only_return_ids = false) {

        /**
         * Returns an array of team objects for those teams which participate in this tournament.
         **/

        $teams = array();
        $team_ids = array();
        $result = mysql_query("SELECT team1_id, team2_id FROM matches, tours WHERE f_tour_id = tour_id AND tour_id = $this->tour_id");
        if (mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                if (!in_array($row['team1_id'], $team_ids) && $row['team1_id'] != 0) # Don't add KO tour stand-by entries (team ID = 0).
                    array_push($team_ids, $row['team1_id']);
                if (!in_array($row['team2_id'], $team_ids) && $row['team2_id'] != 0)
                    array_push($team_ids, $row['team2_id']);
            }
            
            sort($team_ids);
            
            if ($only_return_ids)
                return $team_ids;
                
            foreach ($team_ids as $team_id) {
                array_push($teams, new Team($team_id));
            }
        }
                
        return $teams;
    }

    public function getRSSortRule($mkStr = false) {

        /**
         * Returns this tournament's sort rule for determining standings.
         **/
    
        return Tour::getRSSortRules($this->rs, $mkStr); // rule 0 = array().
    }
    
    public function isRSWithPoints() {

        /**
         * Returns bool for wheter or not this tournament's ranking system uses points.
         **/

        return ($this->rs != 1); // RS 1 is currently the only RS that does not use points.
    }

    public function getStandings() {
    
        /**
         * Returns an array of team objects sorted by who is leading the tournament, using the specified rule from the settings.
         **/
    
        $teams = $this->getTeams();
        
        foreach ($teams as $t) {
            $t->setStats(STATS_TOUR, $this->tour_id); # Calculate team stats for latest tournament.
        }
    
        objsort($teams, $this->getRSSortRule(false));
        
        return $teams;
    }

    public function delete($force = false) {
    
        /**
         * Deletes this tournament, if no matches are assigned to it, unless forced.
         **/
        
        if ($force) {
            $q = array();
            // Don't use the match delete() routines. We do it ourselves.
            $q[] = "DELETE FROM match_data WHERE f_tour_id = $this->tour_id";
            $q[] = "DELETE FROM matches    WHERE f_tour_id = $this->tour_id";
            $q[] = "DELETE FROM tours      WHERE tour_id = $this->tour_id";
            $status = true;
            foreach ($q as $query) {
                $status &= mysql_query($query);
            }
            return $status;
        }
        elseif ($this->empty) {
            $query = "DELETE FROM tours WHERE tour_id = $this->tour_id";
            if (mysql_query($query))
                return true;
        }
        else {
            return false;
        }
    }
    
    public function rename($name) {
    
        /**
         * Rename tournament title.
         **/
    
        return (mysql_query("UPDATE tours SET name = '" . mysql_real_escape_string($name) . "' WHERE tour_id = $this->tour_id"));
    }

    public function chType($type) {
    
        /**
         * Change tournament type.
         **/
    
        return (mysql_query("UPDATE tours SET type = $type WHERE tour_id = $this->tour_id"));
    }

    public function chRS($rs) {
    
        $query = "UPDATE tours SET rs = $rs WHERE tour_id = $this->tour_id";
        return mysql_query($query);
    }

    public function ch_did($did) {
        $query1 = "UPDATE tours SET f_did = $did WHERE tour_id = $this->tour_id";
        $query2 = "UPDATE match_data SET f_did = $did WHERE f_tour_id = $this->tour_id";
        return (mysql_query($query1) && mysql_query($query2));
    }

    public function update() {
    
        /*  
            Updates tournament: Creates further needed rounds/finals/semi-finals.
            
            Return values:
            --------------
                If tournament was updated (new matches were created) = returns true.
                else returns false.
        */
        
        if ($this->type == TT_SINGLE) {
            // Nothing to do.
            return false;
        }
        elseif ($this->type == TT_NOFINAL) {
            // Note: We don't care about locking matches for this type of tournament.
            return false; # No update needed for this tournament type!
        }
        elseif ($this->type == TT_FINAL) {

            // If not all matches in tournament have been played yet or if the final already has been created, then no updating is needed.
            $query = "SELECT match_id FROM matches WHERE f_tour_id = $this->tour_id AND (date_played IS NULL OR round = " . RT_FINAL . ") LIMIT 1";
            $result = mysql_query($query);
            if (mysql_num_rows($result) > 0)
                return false;
            
            // Lock all matches when final is to be created.
            $query = "UPDATE matches SET locked = 1 WHERE f_tour_id = $this->tour_id";
            mysql_query($query);
            
            // Find the two teams with highest ranks.
            $teams = $this->getStandings();
            
            // Create final
            if (Match::create(array('team1_id' => $teams[0]->team_id, 'team2_id' => $teams[1]->team_id, 'round' => RT_FINAL, 'f_tour_id' => $this->tour_id)))
                return true;
            else
                return false;
        }
        elseif ($this->type == TT_SEMI) {
        
            // Is tournament finished / waiting for matches to complete -> do nothing.
            $query = "SELECT match_id FROM matches WHERE f_tour_id = $this->tour_id AND (date_played IS NULL OR round = " . RT_FINAL . ") LIMIT 1";
            $result = mysql_query($query);
            if (mysql_num_rows($result) > 0)
                return false;

            // Lock all matches when finals are to be created.
            $query = "UPDATE matches SET locked = 1 WHERE f_tour_id = $this->tour_id";
            mysql_query($query);

            // What is the current largest round number in use?
            $query  = "SELECT MAX(round) AS max FROM matches WHERE f_tour_id = $this->tour_id";
            $result = mysql_query($query);            
            $row    = mysql_fetch_assoc($result);
            
            // All matches have been played and the latest match in tournament was a semi final -> create 3rd place playoff and final.
            if ($row['max'] == RT_SEMI) {
                $query  = "SELECT team1_id AS c1, team2_id AS c2, team1_score AS s1, team2_score as s2 FROM matches WHERE f_tour_id = $this->tour_id AND round = " . RT_SEMI;
                $result = mysql_query($query);
                $m1  = mysql_fetch_assoc($result);
                $m2  = mysql_fetch_assoc($result);

                // Semi-finals may not end even.
                if ($m1['s1'] == $m1['s2'] || $m2['s1'] == $m2['s2'])
                    return false;

                Match::create(array(
                    'team1_id'  => (($m1['s1'] > $m1['s2']) ? $m1['c1'] : $m1['c2']), 
                    'team2_id'  => (($m2['s1'] > $m2['s2']) ? $m2['c1'] : $m2['c2']), 
                    'round'     => RT_FINAL, 
                    'f_tour_id' => $this->tour_id));
                Match::create(array(
                    'team1_id'  => (($m1['s1'] < $m1['s2']) ? $m1['c1'] : $m1['c2']), 
                    'team2_id'  => (($m2['s1'] < $m2['s2']) ? $m2['c1'] : $m2['c2']), 
                    'round'     => RT_3RD_PLAYOFF, 
                    'f_tour_id' => $this->tour_id));
            }
            // All initial round matches have been played -> create semi-finals.
            else {
                $teams = $this->getStandings();
                // Create matches.
                Match::create(array('team1_id' => $teams[0]->team_id, 'team2_id' => $teams[1]->team_id, 'round' => RT_SEMI, 'f_tour_id' => $this->tour_id));
                Match::create(array('team1_id' => $teams[2]->team_id, 'team2_id' => $teams[3]->team_id, 'round' => RT_SEMI, 'f_tour_id' => $this->tour_id));
            }
        }
        elseif ($this->type == TT_KNOCKOUT) {
            
            /*
                When updating a knockout tournament the following operations need to be done:
                
                1. Create and update the K.O. bracket.
                2. Fill in the competitor identity of an undecided player, once the non-finished match from the previous round has been played.
                3. Create new matches when required.
            */
            

            /* 
                1. Operation
                
                Update K.O. object with the matches stored in database.
            */

            // Create the appropriate K.O. object depending on whether or not GD-lib is installed.
            $koClass = (function_exists("gd_info")) ? 'KnockoutGD' : 'Knockout';
            $this->koObj = new $koClass($this->getTeams(true));

            // These are the two main arrays which together contain all the stored matches in database.
            $pm = array(); // Played matches
            $wm = array(); // Waiting matches
            
            // Lets fill the arrays.
            $query = "SELECT team1_id, team2_id, team1_score, team2_score, round, date_played, match_id FROM matches WHERE f_tour_id = $this->tour_id ORDER BY round ASC";
            $result = mysql_query($query);
            
            if (!$result || mysql_num_rows($result) == 0)
                return;

            while ($row = mysql_fetch_assoc($result)) {
                $row['round'] = $this->transKORound(2, $row['round']); // Translate round number from MySQL numbering to bracket numbering.
                $var = (empty($row['date_played'])) ? 'wm' : 'pm';
                if (!array_key_exists($row['round'], ${$var}))
                    ${$var}[$row['round']] = array(); // Make the entry an array so that we may push elements onto it.
                array_push(${$var}[$row['round']], $row);
            }

            foreach ($pm as $r => $matches) {
                foreach ($matches as $nr => $m) {
                    $this->koObj->setResByCompets($m['team1_id'], $m['team2_id'], (int) $m['team1_score'], (int) $m['team2_score']);
                }
            }
            
            $bracket = $this->koObj->getBracket(); // Get a copy of the filled bracket structure.

            /* 
                2. Operation
                
                Add 2. competitor to waiting matches, if available.
            */

            foreach ($wm as $r => $matches) {
                       
                // Play-in rounds contain only known competitors -> no need for updating.
                if ($r == 0)
                    continue;

                foreach ($matches as $m) {

                    $wm_obj = new Match($m['match_id']); // Match object of waiting match.

                    // If round does not yet have any known competitors in it -> no matches in round -> round does not have an index in structure -> skip it.
                    if (!array_key_exists($r, $bracket)) {
                        $wm_obj->delete();
                        continue;                    
                    }
                
                    /*
                        For each match waiting because of containing an undecided player (team id = 0 in MySQL or score = -1 for K.O. bracket), our objective is:
                            - Determine the team ID and competitor position (1 or 2) of the known competitor in the waiting match.
                            - Use above information to locate the match index in the bracket structure. If not found it should not exist -> delete match.
                            - Now that we know the match index of the waiting match, we can go backwards and decide what match in the previous round will be providing the identity of the undecided competitor for the waiting match, once the previous match has been played.
                            - Knowing the the previous match index we can check to see if it's been played, and if so we can update our undecided competitor with the identity of the winner from the previous match.

                    */

                    // If the waiting match has no undecided competitors, meaning that the match has simply not been played yet, then nothing is to be done.
                    if ($m['team1_id'] != 0 && $m['team2_id'] != 0)
                        continue;

                    // Used variables.
                    $p_k      = ($m['team1_id'] == 0) ? 2 : 1; // Match position of known competitor
                    $p_u      = ($p_k == 1) ? 2 : 1;           // Match position of unknown competitor
                    $id_k     = $m["team${p_k}_id"];           // Team ID of known competitor.
                    $id_u     = false;                         // Team ID of unknown competitor.
                    $wm_idx   = false;                         // Bracket index of the Waiting Match.
                    $pm_u_idx = false;                         // Bracket index of Previous Match whose winner will play at unknown position in the waiting match.
                     
                    // Determine index in bracket of waiting match.
                    foreach ($bracket[$r] as $idx => $bm) {
                        if ($bm['c'.$p_k] == $id_k) {
                            $wm_idx = $idx;
                            break;
                        }
                    }

                    // Quit if unable to find match in bracket. We need an index of this match to continue. Note: If not found, then match should not exist!
                    if ($wm_idx === false) {
                        $wm_obj->delete();
                        continue;
                    }

                    // Determine previous match index of both competitor positions.
                    $pm_u_idx = $this->koObj->getPrevMatch($wm_idx, $p_u);

                    // Now, determine who was the winner of the previous match for the unknown position in the waiting match. If not played -> quit.
                    if (!array_key_exists($pm_u_idx, $bracket[$r-1]))
                        continue;
                        
                    $s1 = $bracket[$r-1][$pm_u_idx]['s1'];
                    $s2 = $bracket[$r-1][$pm_u_idx]['s2'];
                    $id_u = ($s1 == $s2) 
                                ? false
                                : (($s1 > $s2)
                                    ? $bracket[$r-1][$pm_u_idx]['c1']
                                    : $bracket[$r-1][$pm_u_idx]['c2']);
                    
                    // Don't continue if previous match is not yet played.
                    if ($id_u === false || $s1 == -1 || $s2 == -1)
                        continue;

                    // Update undecided competitor.
                    $wm_obj->chTeamId($p_u, $id_u);
                    $bracket[$r][$wm_idx]['c'.$p_u] = $id_u;
                    
                    // Unlock match.
                    if ($wm_obj->locked)
                        $wm_obj->toggleLock();
                }
            }
            
            /* 
                3. Operation
                
                Create new matches, if required.
            */

            foreach ($bracket as $r => $matches) {
                foreach ($matches as $m) {

                    // Although the bracket/K.O. class operates with matches where both competitor positions are undecided, we do not wish to create these matches since we cannot handle them.
                    if ($m['s1'] == -1 && $m['s2'] == -1)
                        continue;

                    // MySQL correction for undecided competitors.
                    if ($m['s1'] == -1) $m['c1'] = 0;
                    if ($m['s2'] == -1) $m['c2'] = 0;

                    $query = "SELECT match_id FROM matches WHERE f_tour_id = $this->tour_id AND team1_id = $m[c1] AND team2_id = $m[c2]";
                    $result = mysql_query($query);

                    if (!$result || mysql_num_rows($result) == 0) {
                        Match::create(array(
                            'team1_id'  => $m['c1'],
                            'team2_id'  => $m['c2'],
                            'round'     => $this->transKORound(1, $r),
                            'f_tour_id' => $this->tour_id,
                        ));
                    }
                }
            }
        }
        
        return true;
    }

    private function transKORound($dir, $r, $roundsInfo = false) {

        /**
         * Translates K.O. round numbers between MySQL numbering system and bracket structure numbering system, either way.
         **/

        /*
            In the bracket structure the rounds are, independently of round type, increment in number by 1 for each next round. 
            This means, that the round number of the final round is not constant, and depends on the bracket size ie. the number of initial competitors.
            But, on the other hand, matches stored in MySQL are stored so that the final round always has the round number RT_FINAL and so forth with the last 5 rounds (round of 16, quarter, semi, 3rd place playoff, final).
        */

        if (!$roundsInfo)
            $roundsInfo = $this->koObj->roundsInfo;

        $final_idx = end(array_keys($roundsInfo)); // Final bracket index corresponds to tournament final, which has MySQL match id = RT_FINAL.
            
        if ($dir == 1) { // to MySQL numbering.
            if ($r != 0) {
                switch ($final_idx - $r)
                {
                    case 0: $r = RT_FINAL; break;
                    case 1: $r = RT_SEMI; break;
                    case 2: $r = RT_QUARTER; break;
                    case 3: $r = RT_ROUND16; break;
                }
            }
        }
        elseif ($dir == 2) { // to bracket numbering.

            /*
            In order to translate the round numbers of the matches stored in MySQL to fit the round numbers of the K.O. bracket, the following must be true:
            
                bracket_final + diff = RT_FINAL
            
            where "bracket_final" is the round number of the final round in the K.O. bracket.
            This means, that for each MySQL round number which is larger than "bracket_final", we simply subtract 

                RT_FINAL - bracket_final = diff
            */
            
            $diff = RT_FINAL - $final_idx;
            if ($r > $final_idx)
                $r -= ($r == RT_FINAL) ? $diff : $diff-1; // This correction is made due the constant RT_3RD_PLAYOFF.
        }
        
        return $r;
    }

    /***************
     * Statics
     ***************/

    public static function getTours() {

        /**
         * Returns an array of all tournament objects.
         **/

        $tours = array();
        $result = mysql_query("SELECT tour_id FROM tours ORDER BY date_created DESC");
        if (mysql_num_rows($result) > 0) {    
            while ($row = mysql_fetch_assoc($result)) {
                array_push($tours, new Tour($row['tour_id']));
            }
        }
        
        return $tours;
    }

    public static function getLatestTour() {
    
        /**
         * Returns the tournament object for the latest tournament.
         **/

        $result = mysql_query("SELECT tour_id FROM tours ORDER BY date_created DESC LIMIT 1");

        if (mysql_num_rows($result) > 0) {    
            $row = mysql_fetch_assoc($result);
            return (new Tour($row['tour_id']));
        }
        else {
            return null;
        }

    }
    
    public static function getRSSortRules($rs = false, $mkStr = false) {
        
        /**
         * Returns all possible sort rules for tournaments - unless else specified by arguments.
         **/
        
        $rules = $points = array();
        
        $rules[0] = array();
        $rules[1] = array('-won', '-draw', '+lost', '-score_diff', '-cas', '+name');
        $rules[2] = array('-points', '-td', '+name');
        $rules[3] = array('-points', '-tdcas', '+name');
        $rules[4] = array('-points', '-td', '+name');
        
        // String descriptions of how points are calculated. Only used when $mkStr is true.
        $pts[0] = '{}';
        $pts[1] = '{}';
        $pts[2] = '{3*[won] + [draw]}';
        $pts[3] = '{[won]/[played] + 0.5*[draw]/[played]}';
        $pts[4] = '{10*[won] + 5*[draw] + TDs + CAS | TDs & CAS max 3 per match}';
        
        // Add house ranking systems.
        global $hrs;
        foreach ($hrs as $i) {
            $rules[] = $i['rule'];
            $pts[] = '{'.$i['points_desc'].'}';
        }
        
        // Substitue points field with string definition?
        if ($mkStr) {
            foreach ($rules as $i => &$rule) {
                $rule = implode(', ', rule_dict($rule));
                $rule = preg_replace('/points/', $pts[$i], $rule);
            }
        }
        
        // Delete fake zero entry.
        unset($rules[0]);
        unset($pts[0]);
        
        return ($rs) ? $rules[$rs] : $rules;    
    }

    public static function create(array $input) {
    
        /**
         * Creates a new tournament.
         *
         * Arguments:
         * ----------
         *  name, type, rs, teams => array(team_ids, ...), 'rounds' (ONLY if round-robin or single matches, else ignored)
         **/

        /* Check input */
        
        // Empty name or name already in use?
        if (empty($input['name']) || get_alt_col('tours', 'name', $input['name'], 'name'))
            return false;
        
        // Valid tournament type?
        if ($input['type'] < TT_MIN || $input['type'] > TT_MAX)
            return false;
            
        // Team array OK?
        if (empty($input['teams']) || !is_array($input['teams']) || (count($input['teams']) < MIN_TOUR_TEAMS && $input['type'] != TT_SINGLE))
            return false;

        /* Create tournament */
        
       
        // Quit if can't make tournament entry.
        $query = "INSERT INTO tours (name, f_did, type, rs, date_created) VALUES ('" . mysql_real_escape_string($input['name']) . "', $input[did], $input[type], $input[rs], NOW())";
        if (!mysql_query($query)) {
            return false;
        }
            
        $tour_id = get_alt_col('tours', 'name', $input['name'], 'tour_id'); # Save tour_id
                
        /* Generate matches depending on type */
        
        // Single match?
        if ($input['type'] == TT_SINGLE) {
            return (Match::create(
                array(
                    'team1_id'  => $input['teams'][0], 
                    'team2_id'  => $input['teams'][1], 
                    'round'     => (($input['rounds']) ? $input['rounds'] : 1), 
                    'f_tour_id' => $tour_id
                )
            ));
        }
        // Round-Robin?
        elseif ($input['type'] != TT_KNOCKOUT) {
            
            // Quit if can't make tournament schedule.
            $robin = new RRobin();
            if (!$robin->create($input['teams'])) # If can't create Round-Robin tour -> quit.
                return false;

            // Okey, so $input['rounds'] is incorrect in the sense that this is the multiplier of times to schedule the same round-set comprising the RR tour.
            // Instead we denote $real_rounds to be the actual number of rounds in the scheduled RR tour.
            $real_rounds = count($robin->tour);

            // Create inverse depiction round.
            foreach ($robin->tour as $ridx => $r) {
                foreach ($r as $idx => $m) {
                    $robin->tour_inv[$ridx][$idx] = array($m[1], $m[0]);
                }
            }

            // Rounds between MIN_ALLOWED_ROUNDS - MAX_ALLOWED_ROUNDS ?
            if ($input['rounds'] < MIN_ALLOWED_ROUNDS || $input['rounds'] > MAX_ALLOWED_ROUNDS)
                return false;
            
            for ($i = 1; $i <= $input['rounds']; $i++) {
                foreach ($robin->{(($i % 2) ? 'tour' : 'tour_inv')} as $ridx => $r) {
                    foreach ($r as $match) { // Depict round's match compets inversely for every other round.
                        Match::create(array('team1_id' => $match[0], 'team2_id' => $match[1], 'round' => $ridx + ($i-1)*($real_rounds), 'f_tour_id' => $tour_id));
                    }
                }
            }
                
            return true;
        }
        // KnockOut tournament
        else {

            if (!is_object($ko = new Knockout($input['teams'])))
                return false;

            foreach ($ko->getBracket() as $r => $matches) {
                $r = Tour::transKORound(1, $r, $ko->roundsInfo);
                foreach ($matches as $m) {

                    // Don't create matches with both players undecided.
                    if ($m['s1'] == -1 && $m['s2'] == -1)
                        continue;
                        
                    Match::create(array(
                        'team1_id'  => ($m['s1'] == -1) ? 0 : $m['c1'], 
                        'team2_id'  => ($m['s2'] == -1) ? 0 : $m['c2'], 
                        'round'     => $r, 
                        'f_tour_id' => $tour_id));
                }
            }
            
            return true;
        }
        
        return false; # Return false if $type was not recognized.
    }
}

?>
