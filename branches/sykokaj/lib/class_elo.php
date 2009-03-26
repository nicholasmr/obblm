<?php

/*
 *  Copyright (c) Daniel Straalman <email is protected> 2009. All Rights Reserved.
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

/*
    See http://en.wikipedia.org/wiki/Elo_rating_system for more info regarding ELO ranking.

    The formula used in OBBLM differs from chess ratings described in wikipedia.

    Starting rank is 200 for a team with no played matches.
    K = 40 / number of players in match
    D = 231
    Win  = 1p
    Draw = 0.5p
    Loss = 0p
    Your Win Probability = 1/(10^(Opponent's rating - Your rating)/D)+ 1)
    Opponents Win Probability = 1/(10^(Your rating - Opponent's rating)/D)+ 1)
    Your new rating = Your old rating + (K * (Scoring Points - Your Win Probability))
    Opponents new rating = Opponents old rating + (K * (Scoring Points - Opponents Win Probability))

    A very short explanation: K determines how many points are won or lost in a match. D determines how large the range is.
    With the values for D and K used here a win vs someone at your ranking +-10 points will give you 10p for a win, and -10p for a loss.
    Opponent wins or looses the same amount of points as you.
    If you are underdog with 35p you will win ~11p if you win, and lose ~9p if you lose. (With some decimals.)
    At 296p difference you will get 19p for a win if you are underdog. (1p for the overdog in case he wins.)

    How ELO works, also very short explanation: If you have a high rating, you are expected to win vs a lower rated team.
    The larger the difference in rating points the more likely the overdog will win that match, and the less points overdog wins.
    If underdog wins, that means he is underrated (and opponent is overrated), and the ELO system takes that into account.
    The less likely chance that underdog will win, the more points he will win, if he wins.
    Loser looses the same amount of points that the winner wins.
    In case of a draw the points are halved. Overdog is expected to win and if the match is a tie he looses half the amount of points
    he would have lost if he lost the match. Underdog wins as much points as overdog lost in this case too.
*/

define('D', 231);
define('K', 40);
define('ELO_DEF_RANK', 200);

class ELO 
{
    public static function getRanks($trid = false) {
        
        $r = array();
        
        foreach (ELO::getTIDs() as $tid) {
            $r[$tid] = ELO_DEF_RANK;
        }
        $r[0] = ELO_DEF_RANK; // Fake entry for import matches (they use team ids = 0).
        
        foreach (ELO::getMatches($trid) as $m) {
        
            $tid1 = $m['tid1'];
            $tid2 = $m['tid2'];
            
            if ($m['s1'] > $m['s2']) { // Team1 won
                $res1 = 1;
                $res2 = 0;
            }
            elseif ($m['s1'] < $m['s2']) {  // Team2 won
                $res1 = 0;
                $res2 = 1;
            }
            else { // Draw
                $res1 = 0.5;
                $res2 = 0.5;
            }
            
            // Get new ranks for the teams in match
            list($r[$tid1], $r[$tid2]) = ELO::getNewRank($r[$tid1], $r[$tid2], $res1, $res2, 2);
            if ($r[$tid1] < 0) $r[$tid1] = 0;
            if ($r[$tid2] < 0) $r[$tid2] = 0;
        }
       
        return $r;
    }
  
    protected static function getNewRank($rnk1, $rnk2, $res1, $res2, $cntCompets = 2) {
    
        $k = K/$cntCompets;
        $d = D;
        
        // Win Probabilities
        $WP1 = 1/(pow(10,($rnk2-$rnk1)/$d)+1);
        $WP2 = 1/(pow(10,($rnk1-$rnk2)/$d)+1);
        
        // New ranks.
        return array(
            $rnk1+($k*($res1-$WP1)), 
            $rnk2+($k*($res2-$WP2)),
        );
    }
  
    protected static function getMatches($trid = false) {

        $r = array();    

        $sql = "
            SELECT 
                team1_id AS 'tid1', team2_id AS 'tid2', date_played, team1_score AS 's1', team2_score AS 's2'
            FROM 
                matches 
            WHERE 
                date_played IS NOT NULL
                ".(($trid) ? " AND f_tour_id = $trid " : '')."
            ORDER BY date_played ASC
        ";
        
        $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                array_push($r, $row);
            }
        }

        return $r;
    }
    
    protected static function getTIDs() {
        
        $tids = array();
        
        $sql = "SELECT team_id FROM teams ORDER BY team_id ASC";
        
        $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                array_push($tids, $row['team_id']);
            }
        }
        
        return $tids;
    }
}

?>
