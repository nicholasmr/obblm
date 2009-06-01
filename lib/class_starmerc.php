<?php

/*
 *  Copyright (c) Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2008-2009. All Rights Reserved.
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

class Star 
{
    /***************
     * Attributes
     ***************/

    // General
    public $star_id = 0;
    public $ma = 0;
    public $st = 0;
    public $ag = 0;
    public $av = 0;
    public $skills = array();
    public $name    = '';
    public $icon    = '';
    public $cost    = 0;
    public $teams   = array(); // The teams that may hire this star.

    // Achievements
    public $mvp     = 0;
    public $cp      = 0;
    public $td      = 0;
    public $intcpt  = 0;
    public $bh      = 0;
    public $ki      = 0;
    public $si      = 0;
    public $cas     = 0;
    public $spp     = 0; // If the star could earn SPP, it would have this much.
    
    // Match statistics
    public $played  = 0;
    public $won     = 0;
    public $lost    = 0;
    public $draw    = 0;
    public $win_percentage = 0;
    public $row_won  = 0; // Won in row.
    public $row_lost = 0;
    public $row_draw = 0;

    // This star's achievement fields (fld) are summed up/calculated by only looking at achievements reflected by:
    public $fld_match_id = false;
    public $fld_team_id = false;
    public $fld_tour_id = false;    

    /***************
     * Methods
     ***************/

    public function __construct($star_id)
    {
        /* 
            Creates a star object with up-to-date fields/stats reflecting the star's participation in every match.
        */
        
        global $stars;
        
        $this->star_id = $star_id;
        
        foreach ($stars as $s => $d) {
            if ($d['id'] == $this->star_id) {
                $this->name = $s;
                break;
            }
        }

        $this->skills = $stars[$this->name]['Def skills'];
        $this->cost   = $stars[$this->name]['cost'];
        $this->teams  = $stars[$this->name]['teams'];
        $this->ma     = $stars[$this->name]['ma'];
        $this->st     = $stars[$this->name]['st'];
        $this->ag     = $stars[$this->name]['ag'];
        $this->av     = $stars[$this->name]['av'];
        $this->icon   = PLAYER_ICONS.'/'.$stars[$this->name]['icon'].'.png';
        
        $this->setStats(false, false, false);

        return true;
    }
    
    public function setStats($team_id = false, $match_id = false, $tour_id = false)
    {
        /*
            Overwrites object fields/stats.
        */
        
        // Set achievements
        foreach (Star::getStats($this->star_id, $team_id, $match_id, $tour_id) as $field => $val) {
            $this->$field = (!empty($val)) ? $val : 0;
        }
        
        // This star's ach. fields are now reflected by the star's ach. with respect to the passed arguments.
        $this->fld_team_id  = $team_id;
        $this->fld_match_id = $match_id; 
        $this->fld_tour_id  = $tour_id;
        
        return true;
    }
    
    public function setMatchStats($team_id = false, $match_id = false, $tour_id = false)
    {
        /* 
            Sets match statistics.
        */
        
        foreach ($this->getHireHistory($team_id, $match_id, $tour_id) as $m) {
            $this->played++;
            if ($m->is_draw)
                $this->draw++;
            elseif ($m->winner == $m->hiredBy)
                $this->won++;
            else
                $this->lost++;
        }
        
        $this->win_percentage = ($this->played == 0) ? 0 : 100*$this->won/$this->played;
    }
    
    public function setStreaks($tour_id)
    {
        foreach (Stats::getStreaks(STATS_PLAYER, $this->star_id, $tour_id) as $key => $val) {
            $this->$key = $val;
        }
        
        return true;
    }
    
    public function getHireHistory($team_id = false, $match_id = false, $tour_id = false)
    {
        /* 
            Returns an array of match objects for those matches which this star has participated in.
            New "fake" match fields are created for each match object with respect to this star object:
                - hiredBy           (team id)
                - hiredAgainst      (team id)
                - hiredByName       (team name)
                - hiredAgainstName  (team name)
        */
        
        $matches = array();
        
        $query = "SELECT DISTINCT f_match_id, f_team_id FROM match_data, matches WHERE 
            f_match_id = match_id 
            AND f_player_id = $this->star_id 
            " . (($team_id)  ? " AND f_team_id = $team_id "   : '') . " 
            " . (($match_id) ? " AND f_match_id = $match_id " : '') . " 
            " . (($tour_id)  ? " AND matches.f_tour_id = $tour_id "   : '') . " 
            ORDER BY date_played DESC";
            
        $result = mysql_query($query);
        if (mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                $m = new Match($row['f_match_id']);
                // Make fake match fields for this star's values.
                $m->hiredBy          = $row['f_team_id'];
                $m->hiredAgainst     = ($m->team1_id == $m->hiredBy) ? $m->team2_id : $m->team1_id;
                $m->hiredByName      = get_alt_col('teams', 'team_id', $m->hiredBy, 'name');
                $m->hiredAgainstName = get_alt_col('teams', 'team_id', $m->hiredAgainst, 'name');
                array_push($matches, $m);
            }
        }
        
        return $matches;
    }
    
    public function rmMatchEntry($match_id, $team_id = false)
    {
        /*
            Deletes a star's entry in a match.
        */
        
        $query = "DELETE FROM match_data WHERE f_player_id = $this->star_id AND f_match_id = $match_id" . (($team_id) ? " AND f_team_id = $team_id" : '');
        return mysql_query($query);
    }
    
    public function mkMatchEntry($match_id, $team_id, $mdat)
    {
        /*
            Creates an entry for this star in the specified match.
            This routine is like the routine from the match class, which adds ordinary player entries to a match.
        */
    
        if (!$this->rmMatchEntry($match_id, false)) {
            return false;
        }
        
        $mvp    = $mdat['mvp']    ? $mdat['mvp']     : 0;
        $cp     = $mdat['cp']     ? $mdat['cp']      : 0;
        $td     = $mdat['td']     ? $mdat['td']      : 0;
        $intcpt = $mdat['intcpt'] ? $mdat['intcpt']  : 0;
        $bh     = $mdat['bh']     ? $mdat['bh']      : 0;
        $si     = $mdat['si']     ? $mdat['si']      : 0;
        $ki     = $mdat['ki']     ? $mdat['ki']      : 0;
        
        $query = "INSERT INTO match_data
        (
            f_coach_id,
            f_team_id,
            f_match_id,
            f_tour_id,
            f_player_id,

            mvp,
            cp,
            td,
            intcpt,
            bh,
            si,
            ki,
            inj,
            agn1,
            agn2
        )
        VALUES
        (
            ".get_alt_col('teams', 'team_id', $team_id, 'owned_by_coach_id').",
            $team_id,
            $match_id,
            ".get_alt_col('matches', 'match_id', $match_id, 'f_tour_id').",
            $this->star_id,

            $mvp,
            $cp,
            $td,
            $intcpt,
            $bh,
            $si,
            $ki,
            0,
            0,
            0
        )";
        
        return mysql_query($query);
    }

    /***************
     * Statics
     ***************/

    public static function getStats($star_id, $team_id = false, $match_id = false, $tour_id = false) 
    {
        return Stats::getStats(array('pid' => $star_id, 'tid' => $team_id, 'mid' => $match_id, 'trid' => $tour_id));
    }
    
    public static function getStars($team_id = false, $match_id = false, $tour_id = false)
    {
        /*
            Returns an array of star objs for each (depending on arguments) star player.
        */
    
        $starObjs = array();
        
        if (!($team_id || $match_id || $tour_id)) {

            global $stars;
           
            foreach ($stars as $s) {
                array_push($starObjs, new Star($s['id']));
            }
        }
        else {
            $query = "SELECT DISTINCT f_player_id FROM match_data, matches WHERE 
                f_match_id = match_id 
                AND f_player_id <= ".ID_STARS_BEGIN." 
                " . (($team_id)  ? " AND f_team_id = $team_id "   : '') . " 
                " . (($match_id) ? " AND f_match_id = $match_id " : '') . " 
                " . (($tour_id)  ? " AND matches.f_tour_id = $tour_id "   : '');
            $result = mysql_query($query);
            if ($result && mysql_num_rows($result) > 0) {
                while ($row = mysql_fetch_assoc($result)) {
                    array_push($starObjs, new Star($row['f_player_id']));
                }
            }
        }
        
        return $starObjs;
    }
}

class Mercenary
{
    /*
        About mercenaries data stored in MySQL:
        Mercenary number is stored in the "inj" field, and the number of extra skills bought in "agn1".
    */

    /***************
     * Attributes
     ***************/

    public $nr       = 0;
    public $match_id = 0;
    public $skills   = 0;

    public $mvp     = 0;
    public $cp      = 0;
    public $td      = 0;
    public $intcpt  = 0;
    public $bh      = 0;
    public $ki      = 0;
    public $si      = 0;
    public $cas     = 0;

    /***************
     * Methods
     ***************/

    public function __construct($match_id, $nr)
    {
        /*
            Make new merc obj.
            
            The field values of a merc obj consists of the accomplishments of the merc in a specific match.
            No two mercenaries are the same therefore mercs have no ID. 
            This means that to operate on a specific merc from a match, we must know the match ID and some kind of in-match-merc-id so that we 
            may distinguish two mercs from each other in the same match (ie. the merc $nr).
        */
        
        $this->nr = $nr;
        $this->match_id = $match_id;
        
        $query = "SELECT * FROM match_data WHERE f_match_id = $match_id AND f_player_id = ".ID_MERCS." AND inj = $nr";
        $result = mysql_query($query);
        $row = mysql_fetch_assoc($result);

        $this->mvp = $row['mvp'];
        $this->cp = $row['cp'];
        $this->td = $row['td'];
        $this->intcpt = $row['intcpt'];
        $this->bh = $row['bh'];
        $this->ki = $row['ki'];
        $this->si = $row['si'];
        $this->cas = $this->bh + $this->ki + $this->si;
        $this->skills = $row['agn1'];
        
        return true;
    }

    public static function rmMatchEntries($match_id, $team_id = false)
    {
        /*
            Deletes a merc's entry in a match.
        */
        
        $query = "DELETE FROM match_data WHERE f_player_id = ".ID_MERCS." AND f_match_id = $match_id".(($team_id) ? " AND f_team_id = $team_id" : '');
        return mysql_query($query);
    }
    
    public static function mkMatchEntry($match_id, $nr, $team_id, $mdat)
    {
        /*
            Creates an entry for a merc in the specified match.
            This routine is like the routine from the match class, which adds ordinary player entries to a match.
        */
        
        $mvp    = $mdat['mvp']    ? $mdat['mvp']     : 0;
        $cp     = $mdat['cp']     ? $mdat['cp']      : 0;
        $td     = $mdat['td']     ? $mdat['td']      : 0;
        $intcpt = $mdat['intcpt'] ? $mdat['intcpt']  : 0;
        $bh     = $mdat['bh']     ? $mdat['bh']      : 0;
        $si     = $mdat['si']     ? $mdat['si']      : 0;
        $ki     = $mdat['ki']     ? $mdat['ki']      : 0;
        $skills = $mdat['skills'] ? $mdat['skills'] : 0;
        
        $query = "INSERT INTO match_data
        (
            f_coach_id,
            f_team_id,
            f_match_id,
            f_tour_id,
            f_player_id,

            mvp,
            cp,
            td,
            intcpt,
            bh,
            si,
            ki,
            
            inj,
            agn1,
            agn2
        )
        VALUES
        (
            ".get_alt_col('teams', 'team_id', $team_id, 'owned_by_coach_id').",
            $team_id,
            $match_id,
            ".get_alt_col('matches', 'match_id', $match_id, 'f_tour_id').",
            ".ID_MERCS.",

            $mvp,
            $cp,
            $td,
            $intcpt,
            $bh,
            $si,
            $ki,
            
            $nr,
            $skills,
            0
        )";
        return mysql_query($query);
    }

    public static function getMercsHiredByTeam($team_id, $f_match_id = false)
    {
        /*
            Returns an array of merc objects, which have in common that they are from the same team ID and match.
            By not specifying the match ID this methods returns all merc objects hired by the team in question.
        */
        
        $mercs = array();
        
        $query = "SELECT inj, f_match_id FROM match_data, matches WHERE f_match_id = match_id AND f_team_id = $team_id AND f_player_id = ".ID_MERCS.(($f_match_id) ? " AND f_match_id = $f_match_id" : '') . ' ORDER BY date_played DESC, inj ASC';
        $result = mysql_query($query);
        if ($result && mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                array_push($mercs, new Mercenary($row['f_match_id'], $row['inj']));
            }
        }
        
        return $mercs;
    }
}

?>
