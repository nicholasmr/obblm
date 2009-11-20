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

// Injury/status constants:
define('NONE',  1);
define('MNG',   2);
define('NI',    3);
define('MA',    4);
define('AV',    5);
define('AG',    6);
define('ST',    7);
define('DEAD',  8);
define('SOLD',  9);

$STATUS_TRANS = array(
    NONE => 'NONE',
    MNG  => 'MNG',
    NI   => 'NI',
    MA   => 'MA',
    AV   => 'AV',
    AG   => 'AG',
    ST   => 'ST',
    DEAD => 'DEAD',
    SOLD => 'SOLD',
);

// Round types
define('RT_FINAL', 255);
define('RT_3RD_PLAYOFF', 254); # 3rd place playoff: The two knock-out matches between the final four teams with the winners progressing to the grand final. The losers are knocked-out, though take part in a third place play-off.
define('RT_SEMI', 253); # Semi-finals.
define('RT_QUARTER', 252); # Quarter-finals.
define('RT_ROUND16', 251); # Round of 16.

define('MAX_ROUNDNR', RT_ROUND16); # This should have the value of the smallest reserved round number.

// Reserved (non-real) matches:
define('MATCH_ID_IMPORT', -1);

class Match
{
    /***************
     * Properties 
     ***************/
    
    // MySQL stored information
    public $match_id        = 0;
    public $round           = 0;
    public $f_tour_id       = 0;
    public $locked          = false;
    public $submitter_id    = 0;
    public $stadium         = 0;
    public $gate            = 0;
    public $ffactor1        = 0;
    public $ffactor2        = 0;
    public $income1         = 0;
    public $income2         = 0;
    public $team1_id        = 0;
    public $team2_id        = 0;
    public $date_created    = '';
    public $date_played     = '';
    public $date_modified   = '';
    public $team1_score     = 0;
    public $team2_score     = 0;
    public $smp1            = 0;
    public $smp2            = 0;
    public $tcas1           = 0; // Team cas 1
    public $tcas2           = 0; // Team cas 2
    public $fame1           = 0;
    public $fame2           = 0;
    public $tv1             = 0;
    public $tv2             = 0;
    public $comment         = ''; // Summary, not match comment.
    
    // Other
    public $team1_name  = '';
    public $team2_name  = '';
    public $is_played   = false;
    public $is_draw     = false;
    public $winner      = 0; # Team ID
    
    /***************
     * Methods 
     ***************/
    
    function __construct($match_id) {

        // Check if $match_id is valid.
        if (!get_alt_col('matches', 'match_id', $match_id, 'match_id'))
            return null;

        // MySQL stored information
        $result = mysql_query("SELECT * FROM matches WHERE match_id = $match_id");
        if (mysql_num_rows($result) <= 0)
            return null;

        $row = mysql_fetch_assoc($result);
        foreach ($row as $col => $val) {
            $this->$col = ($val) ? $val : 0;
        }
        $this->locked = (bool) $this->locked;
        $this->is_played = !empty($this->date_played);
        
        // Make class string properties = empty strings, and not zero's.
        foreach (array('date_created', 'date_played', 'date_modified') as $field) {
            if (empty($this->$field))
                $this->$field = '';
        }
    
        // Match summary.
        $this->comment = $this->getText();
        if (empty($this->comment))
            $this->comment = '';
    
        // Other
        $this->team1_name = get_alt_col('teams', 'team_id', $this->team1_id, 'name');
        $this->team2_name = get_alt_col('teams', 'team_id', $this->team2_id, 'name');

        // Determine winner's team ID.
        if ($this->team1_score > $this->team2_score) {
            $this->winner = $this->team1_id;
        }
        elseif ($this->team1_score < $this->team2_score) {
            $this->winner = $this->team2_id;
        }
        else {
            $this->winner = 0;
            $this->is_draw = true;
        }
    }

    public function setLocked($lock) {
        $this->locked = (bool) $lock;
        return mysql_query("UPDATE matches SET locked = ".(($lock) ? 1 : 0)." WHERE match_id = $this->match_id");
    }

    public function delete() {
    
        /**
         * Deletes this match (ignoring consequences).
         **/
    
        // Delete match entry and match data.
        $q = array();
        $q[] = "DELETE FROM matches     WHERE match_id = $this->match_id";
        $q[] = "DELETE FROM match_data  WHERE f_match_id = $this->match_id";
        $status = true;
        foreach ($q as $query) {
            $status &= mysql_query($query);
        }
        
        // Subtract team treasury.
        $t1 = new Team($this->team1_id);
        $t2 = new Team($this->team2_id);
        $status &= $t1->dtreasury(-1*$this->income1) && $t2->dtreasury(-1*$this->income2);
        
        // Run triggers.
        SQLTriggers::run(T_SQLTRIG_MATCH_DEL, array('mid' => $this->match_id, 'trid' => $this->f_tour_id, 'tid1' => $this->team1_id, 'tid2' => $this->team2_id));
        Module::runTriggers(T_TRIGGER_MATCH_DELETE, array($this->match_id));
        
        return $status;
    }

    public function reset() {

        /**
         * Clears all match data resetting the match to its initial not-yet-played-state.
         **/
        
        $q = array();
        $q[] = "DELETE FROM match_data WHERE f_match_id = $this->match_id";
        $q[] = "UPDATE matches SET 
            date_played = NULL, date_modified = NULL, 
            team1_score = NULL, team2_score = NULL,
            smp1 = 0, smp2 = 0, 
            tcas1 = 0, tcas2 = 0, 
            fame1 = 0, fame2 = 0, 
            tv1 = 0, tv2 = 0, 
            income1 = NULL, income2 = NULL,
            ffactor1 = NULL, ffactor2 = NULL, 
            fans = 0, gate = NULL, stadium = NULL, submitter_id = NULL, locked = NULL
            WHERE match_id = $this->match_id";
            
        $status = true;
        foreach ($q as $qry) {
            $status &= mysql_query($qry);
        }
        
        // Reset team treasuries
        $t1 = new Team($this->team1_id);
        $t2 = new Team($this->team2_id);
        $t1->dtreasury(-1*$this->income1);
        $t2->dtreasury(-1*$this->income2);
        
        // Run triggers
        SQLTriggers::run(T_SQLTRIG_MATCH_DEL, array('mid' => $this->match_id, 'trid' => $this->f_tour_id, 'tid1' => $this->team1_id, 'tid2' => $this->team2_id));
        Module::runTriggers(T_TRIGGER_MATCH_RESET, array($this->match_id));
        
        return $status;
    }

    public function chTeamId($nr, $new_tid) {

        /**
         * Changes team $nr id to $new_tid.
         **/

        // If both competitor IDs are to become zero, then the match entry is unuseable, therefore we delete it.
        if ($new_tid == 0 && $this->{'team'.(($nr == 1) ? 2 : 1).'_id'} == 0) {
            $this->delete();
            return true;
        }

        $old_tid = $this->{"team{$nr}_id"};
        
        $query1 = "UPDATE matches SET team${nr}_id = $new_tid WHERE match_id = $this->match_id";
        $query2 = "DELETE FROM match_data WHERE f_match_id = $this->match_id AND f_team_id = $old_tid";
        
        if (mysql_query($query1) && mysql_query($query2)) {
            $this->{"team{$nr}_id"} = $new_tid;
            if ($new_tid == 0) {
                $query = "UPDATE matches SET team1_score = NULL, team2_score = NULL, date_played = NULL, date_modified = NULL, locked = 1 WHERE match_id = $this->match_id";
                if (mysql_query($query)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    public function update(array $input) {
 
        /**
         * Updates general match data.
         **/

        // Input check.
        if ($this->locked || !get_alt_col('coaches', 'coach_id', $input['submitter_id'], 'coach_id')) # If invalid submitter ID (coach ID) then quit.
            return false;

        // Determine if team fan-factors are within the "> 0" limit. If not, don't save the negative fan-factor.
        $team1 = new Team($this->team1_id);
        $team2 = new Team($this->team2_id);
        if ($team1->rg_ff - $this->ffactor1 + $input['ffactor1'] < 0) $input['ffactor1'] = $this->ffactor1;
        if ($team2->rg_ff - $this->ffactor2 + $input['ffactor2'] < 0) $input['ffactor2'] = $this->ffactor2;

        // Update match entry.
        $query = "UPDATE matches SET 
                    submitter_id    = $input[submitter_id],
                    stadium         = $input[stadium],
                    gate            = $input[gate],
                    fans            = $input[fans],
                    ffactor1        = $input[ffactor1],
                    ffactor2        = $input[ffactor2],
                    income1         = $input[income1],
                    income2         = $input[income2],
                    date_played     = ".(($this->is_played) ? 'date_played' : 'NOW()').",
                    date_modified   = NOW(),
                    team1_score     = $input[team1_score],
                    team2_score     = $input[team2_score],
                    smp1            = $input[smp1],
                    smp2            = $input[smp2],
                    tcas1           = $input[tcas1],
                    tcas2           = $input[tcas2],
                    fame1           = $input[fame1],
                    fame2           = $input[fame2],
                    tv1             = $input[tv1],
                    tv2             = $input[tv2]
        WHERE match_id = $this->match_id";

        if (!mysql_query($query))
            return false;
            
        // Update team treasury
        if ($input['income1'] != $this->income1 || $input['income2'] != $this->income2) {
            $delta1 = $input['income1'] - $this->income1;
            $delta2 = $input['income2'] - $this->income2;

            if (!$team1->dtreasury($delta1) || !$team2->dtreasury($delta2))
                return false;
        }

        // Save match summary.
        $this->saveText($input['comment']);
        
        // Run triggers.
        SQLTriggers::run(T_SQLTRIG_MATCH_UPD, array('mid' => $this->match_id, 'trid' => $this->f_tour_id, 'tid1' => $this->team1_id, 'tid2' => $this->team2_id));
        Module::runTriggers(T_TRIGGER_MATCH_SAVE, array($this->match_id));
        
        return true;
    }

    public function entry(array $input) {
    
        /**
         * Updates player match data.
         **/
         
        if ($this->locked || empty($input['player_id'])) {
            return false;
        }

        /********************
         *   Input 
         ********************/

        // Ref. IDs
        $pid = $input['player_id'];
        $tid = $input['team_id'];
        $cid = get_alt_col('teams', 'team_id', $tid, 'owned_by_coach_id');
        $rid = get_alt_col('teams', 'team_id', $tid, 'f_race_id');
        // Node IDs
        $mid  = $this->match_id;
        $trid = $this->f_tour_id;
        $did  = get_alt_col('tours', 'tour_id', $trid, 'f_did');
        $lid  = get_alt_col('divisions', 'did', $did, 'f_lid');
        // Match data.
        
        $MG = (int) (Player::getPlayerStatus($pid,$mid) == MNG); // Missed (this) Game (ie. had a MNG from previous match)?
        foreach (array('mvp', 'cp', 'td', 'intcpt', 'bh', 'si', 'ki') as $a) {
            ${$a} = ($input[$a] && !$MG) ? $input[$a] : 0;
        }
        // Injs
        $inj = $agn1 = $agn2 = NONE;
        
        // Ordinary player?
        if ($pid > 0) {
        
                $p = new Player($pid);
            
                $cur_injs = array('ma' => 0, 'av' => 0, 'ag' => 0, 'st' => 0); # Current player injuries for match, if player entry already exists for match, else 0.
                
                // Test if player entry for match exists. If so, we need to fill $cur_injs.
                $query  = "SELECT inj, agn1, agn2 FROM match_data WHERE f_player_id = $pid AND f_match_id = $mid";
                $result = mysql_query($query);
                if (mysql_num_rows($result) > 0) {
                    $row = mysql_fetch_assoc($result);
                    foreach (array('inj', 'agn1', 'agn2') as $col) {

                        if ($row[$col] == NONE || $row[$col] == NI || $row[$col] == MNG || $row[$col] == DEAD) 
                            continue;
                            
                        $prescription = Player::theDoctor($row[$col]);
                        $cur_injs[$prescription]++;
                    }
                }

                /* 
                    We now need to determine if the inputted player injuries conflict with the injury limits for the specific player.
                    We can determine this by knowing what injuries the submitter inputted, and what (if any) was earlier submitted (determined above).
                    If an injury limit is reached, we simply chose to refuse saving the input to avoid overflowing the limits.
                    
                    Note that injury limits are only relevant for MA, AV, AG and ST.
                */        

                $inj    = NONE;
                $agn1   = NONE;
                $agn2   = NONE;
                $fields = array();
                $cnt_injs = array('ma' => 0, 'av' => 0, 'ag' => 0, 'st' => 0); # Counted number of approved/accepted injuries submitted.
                
                if ($input['inj'] == NONE || $input['inj'] == NI || $input['inj'] == MNG || $input['inj'] == DEAD)
                    $inj = $input['inj'];
                else
                    array_push($fields, 'inj');

                if ($input['agn1'] == NONE || $input['agn1'] == NI)
                    $agn1 = $input['agn1'];
                else
                    array_push($fields, 'agn1');
                    
                if ($input['agn2'] == NONE || $input['agn2'] == NI)
                    $agn2 = $input['agn2'];
                else
                    array_push($fields, 'agn2');

                foreach ($fields as $field) {
                    $chr = Player::theDoctor($input[$field]); # Characteristic
                    if ($chr && $p->chrLimits('inj', $chr) + $cur_injs[$chr] > $cnt_injs[$chr]) { # Are injuries reported within injury limits?
                        $$field = $input[$field];
                        $cnt_injs[$chr]++;
                    }
                }
            
                /* 
                    Before we write player's match data, we need to check if player's status was...
                        - Set to DEAD? In which case we must delete all the player's match data from matches played after this match (if any played).
                        - Set to MNG? In which case we must zero set the player's match data from match played after this match (if this match is not the latest).
                */
            
                if ($this->is_played) { # Must be played to have a date to compare with.
                    if ($input['inj'] == DEAD) {
                    
                        $result = mysql_query("SELECT match_id FROM matches WHERE date_played IS NOT NULL AND date_played > '$this->date_played' ORDER BY date_played ASC");
                        
                        if (mysql_num_rows($result) > 0) { # Skip if the current match is the newest.
                            while ($row = mysql_fetch_assoc($result)) {
                                mysql_query("DELETE FROM match_data WHERE f_match_id = $row[match_id] AND f_player_id = $pid");
                            }
                        }
                    }
                    elseif ($input['inj'] != NONE) { # Player has MNG status.

                        $result = mysql_query("SELECT match_id FROM matches WHERE 
                                    date_played IS NOT NULL AND 
                                    date_played > '$this->date_played' AND 
                                    (team1_id = $tid OR team2_id = $tid) 
                                    ORDER BY date_played ASC LIMIT 1");
                                    
                        if (mysql_num_rows($result) > 0) { # Skip if the current match is the newest.
                            $row = mysql_fetch_assoc($result);
                            mysql_query("UPDATE match_data SET 
                                mvp     = 0, 
                                cp      = 0,
                                td      = 0,
                                intcpt  = 0,
                                bh      = 0,
                                si      = 0,
                                ki      = 0,
                                inj     = ".NONE.",
                                agn1    = ".NONE.",
                                agn2    = ".NONE.",
                                mg      = TRUE
                            
                                WHERE f_match_id = $row[match_id] AND f_player_id = $pid");
                        }
                    }
                }
        }
        // Star player?
        elseif ($pid <= ID_STARS_BEGIN) {
            $rid = 'NULL'; // Star stats/match_data should not be counted/considered as race stats when a team of a given race hires the star.
        }
        // Mercenary?
        elseif ($pid == ID_MERCS) {
            // Mercs use the injs/agn fields differently from ordinary players. 
            // Nr:      #Merc hired by that team. 
            // Skills:  Extra skill bought count for the merc.
            $inj  = $input['nr'];
            $agn1 = $input['skills'];
            $agn2 = NONE;
        }

        /********************
         *  Insert data into MySQL 
         ********************/

        // Do we need to update or create entry?
        $result = mysql_query("SELECT f_player_id FROM match_data WHERE f_player_id = $pid AND f_match_id = $mid");
        if (mysql_num_rows($result) > 0 && $pid != ID_MERCS) { // Don't allow updating if merc - this will overwrite other merc entries (if +1 merc in match).

            $query = "UPDATE match_data SET
                        mvp     = $mvp,
                        cp      = $cp,
                        td      = $td,
                        intcpt  = $intcpt,
                        bh      = $bh,
                        si      = $si,
                        ki      = $ki,
                        inj     = $inj,
                        agn1    = $agn1,
                        agn2    = $agn2,
                        mg      = $MG

                        WHERE f_player_id = $pid AND f_match_id = $mid";
        }
        else {
                    
            $query = "INSERT INTO match_data
            (
                f_player_id,
                f_team_id,
                f_coach_id,
                f_race_id,
                
                f_match_id,
                f_tour_id,
                f_did,
                f_lid,

                mvp,
                cp,
                td,
                intcpt,
                bh,
                si,
                ki,
                inj,
                agn1,
                agn2,
                mg
            )
            VALUES
            (
                $pid,
                $tid,
                $cid,
                $rid,
                
                $mid,
                $trid,
                $did,
                $lid,

                $mvp,
                $cp,
                $td,
                $intcpt,
                $bh,
                $si,
                $ki,
                $inj,
                $agn1,
                $agn2,
                $MG
            )";
        }

        return mysql_query($query) && SQLTriggers::run(T_SQLTRIG_MATCHDATA, array('pid' => $pid, 'trid' => $trid));
    }
    
    public function getSummedAch($s) {
        
        /**
         * Returns a two-element array with a match summed field, $s (td or int or ...), for each team. Index 1 = team 1, index 2 = team 2.
         **/
         
         $v = array();
         
         foreach (array(1,2) as $i) {
             $query = "SELECT SUM($s) as '$s' FROM matches, match_data WHERE f_match_id = match_id AND match_id = $this->match_id AND f_team_id = team${i}_id";
             $result = mysql_query($query);
             $row = mysql_fetch_assoc($result);
             $v[$i] = ($row[$s]) ? $row[$s] : 0;
         }
         
         return $v;
    }
    
    public function saveText($str) {
        
        $txt = new MatchSummary($this->match_id);
        return $txt->save($str);
    }

    public function getText() {

        $txt = new MatchSummary($this->match_id);
        return $txt->txt;
    }
    
    public function hasComments() {
        return MatchComment::matchHasComments($this->match_id);
    }

    public function getComments() {
        return MatchComment::getComments($this->match_id, '-');
    }
    
    public function newComment($sid, $txt) {
        return MatchComment::create($this->match_id, $sid, $txt);
    }
    
    public function deleteComment($cid) {
        $cmt = new MatchComment($cid);
        return $cmt->delete();
    }
    
    /***************
     * Statics
     ***************/
     
    public static function getMatches($n = false, $node = false, $node_id = false, $getUpcomming = false) {
    
        /**
         * Returns an array of match objects for the latest $n matches, or all if $n = false.
         **/
         
        $m = array();
        switch ($node) 
        {
            case STATS_TOUR:     $where = "f_tour_id = $node_id"; break;
            case STATS_DIVISION: $where = "f_did = $node_id"; break;
            case STATS_LEAGUE:   $where = "f_lid = $node_id"; break;
            default: $where = false;
        }
        $query = "SELECT match_id FROM matches, tours, divisions 
            WHERE date_played IS ".(($getUpcomming) ? '' : 'NOT')." NULL AND match_id > 0 AND f_tour_id = tour_id AND f_did = did
            ".(($where) ? " AND $where " : '')."
            ORDER BY date_played DESC" . (($n) ? " LIMIT $n" : '');
        $result = mysql_query($query);
        
        if ($result && mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                array_push($m, new Match($row['match_id']));
            }
        }
        
        return $m;
    }
    
    public static function fakeEntry(array $input) {
        
        /*
            This routine is somewhat a hack in our own system!
            We use a fake match as match reference for inputting player match_data for imported players.
            
            If a request for creating a fake match_data entry is made, we must first make sure that this fake match exist.
        */
        
        $mid = null;
        
        // Does fake match not exist?
        if (!get_alt_col('matches', 'match_id', MATCH_ID_IMPORT, 'match_id')) {
            $query = "INSERT INTO matches (match_id, team1_id,  team2_id, round, f_tour_id, date_created, date_played)
                                    VALUES (".MATCH_ID_IMPORT.", 0, 0, 0, 0, 0, 0)";
            mysql_query($query);
        }
        
        $mid = MATCH_ID_IMPORT;
        
        // Input
        $p = new Player($input['player_id']);
        $pid    = $p->player_id;
        $tid    = $p->owned_by_team_id;
        $cid    = $p->coach_id;
        $rid    = get_alt_col('teams', 'team_id', $tid, 'f_race_id');
    
        $mvp    = $input['mvp']    ? $input['mvp']     : 0;
        $cp     = $input['cp']     ? $input['cp']      : 0;
        $td     = $input['td']     ? $input['td']      : 0;
        $intcpt = $input['intcpt'] ? $input['intcpt']  : 0;
        $bh     = $input['bh']     ? $input['bh']      : 0;
        $si     = $input['si']     ? $input['si']      : 0;
        $ki     = $input['ki']     ? $input['ki']      : 0;
        $inj    = $input['inj']    ? $input['inj']     : 0;
        $agn1   = $input['agn1']   ? $input['agn1']    : 0;
        $agn2   = $input['agn2']   ? $input['agn2']    : 0;
    
        $query = "INSERT INTO match_data
        (
            f_coach_id,
            f_team_id,
            f_match_id,
            f_tour_id,
            f_did,
            f_lid,
            f_player_id,
            f_race_id,

            mvp,
            cp,
            td,
            intcpt,
            bh,
            si,
            ki,
            inj,
            agn1,
            agn2,
            mg
        )
        VALUES
        (
            $cid,
            $tid,
            $mid,
            0,
            0,
            0,
            $pid,
            $rid,

            $mvp,
            $cp,
            $td,
            $intcpt,
            $bh,
            $si,
            $ki,
            $inj,
            $agn1,
            $agn2,
            FALSE
        )";
        
        return mysql_query($query);
    }
    
    public static function create(array $input) {

        /**
         * Creates a new match.
         *
         * Input: team1_id, team2_id, round, f_tour_id
         **/

        global $settings;
    
        if ($input['team1_id'] == $input['team2_id'] || ($isLocked = get_alt_col('tours', 'tour_id', $input['f_tour_id'], 'locked')))
            return false;
            
        // If team->league relations are on don't allow teams from different leagues to play each other.
        // If tour (f_tour_id) is not a node under the league associated with both teams, then deny match creation.
        $tr = get_alt_col('divisions', 'did', get_alt_col('tours', 'tour_id', $input['f_tour_id'], 'f_did'), 'f_lid');
        $t1 = get_alt_col('teams', 'team_id', $input['team1_id'], 'f_lid');
        $t2 = get_alt_col('teams', 'team_id', $input['team2_id'], 'f_lid');
        if ($settings['relate_team_to_league'] && ($t1 == $t2 && $t1 != $tr || $t1 != $t2))
            return false;

        $query = "INSERT INTO matches (team1_id, team2_id, round, f_tour_id, date_created)
                    VALUES ($input[team1_id], $input[team2_id], $input[round], '$input[f_tour_id]', NOW())";

        return mysql_query($query) && Module::runTriggers(T_TRIGGER_MATCH_CREATE, array(mysql_insert_id()));
    }
}

?>
