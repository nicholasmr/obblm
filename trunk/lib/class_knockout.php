<?php

/*
 *  Copyright (c) Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2008. All Rights Reserved.
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

define('FONT', 2);       // GD-lib default font size.
define('LFONT', FONT+1); // GD-lib large font size.

class Knockout
{
    /* Tournament bracket structure. */
    
    protected $bracket = array(
        /*
        0 => array(),   // Play-in round
        */
        1 => array(),   // First round
        /*
        N => array(     // Round N
        
            0 => array( // Match 0
                'c1' => Competitor 1
                's1' => Competitor 1 score
                'c2' => Competitor 2
                's2' => Competitor 2 score
            ),
            1 => array( // Match 1
                ...
            ),
            n => array( // Match n
                ...
            ),
        ), 
        */
    );
    
    public $roundsInfo = array(); // Rounds description array. This is the return value of getRoundsInfo().

    public function __construct(array $compets)
    {
        /* Constructor creates initial bracket structure. */
    
        // At least 2 competitors are required.
        if (($n = count($compets)) < 2)
            return false;
        
        // In round 1, there are $r1 competitors. The number of competitors must match 2^x, where x is a whole number.
        $r1 = pow(2, floor(log($n, 2)));
        
        // ...this leaves $r0 competitors, who must compete in the play-in round, round 0.
        $r0 = $n - $r1;
        
        // Create all matches for the first round.
        while (count($compets) > $r0) {
            array_push($this->bracket[1], array(
                'c1' => array_shift($compets),
                's1' => 0,
                'c2' => array_shift($compets),
                's2' => 0,
            ));
        }
        
        /* 
            Now, if any play-in matches are required ie. $r0 > 0, the following is done: 
            For each remaining competitor, a competitor from the first round must compete against the remaining competitor to decide, 
                which of the two is to play in the first round.
            
            This is done by tearing up the already created matches from the first round, and moving the competitor(s) to a match in the play-in round.
            This causes some matches to have undecided competitors. This is expressed by a match score of -1 for the undecided competitor.
            
            The competitors in the first round, who need not compete in the play-in round, receive a so-called "pass through" or "bye" for the play-in round.
        */
        
        if ($r0 > 0)
            array_unshift($this->bracket, array()); // Unshift to internally place the element before index 1 in the bracket structure.
        
        for ($i = 0, $c = 1; count($compets) > 0; $i += ($c == 2 ? 1 : 0), $c = ($c == 1 ? 2 : 1)) {
        
            /* 
                $i is the match number in the play-in round.
                $c is the competitor.
            */
            
            array_push($this->bracket[0], array(
                'c1' => $this->bracket[1][$i]['c'.$c],
                's1' => 0,
                'c2' => array_shift($compets),
                's2' => 0,
            ));

            // Make the relocated competitor undiceded in first round.
            $this->bracket[1][$i]['c'.$c] = null;
            $this->bracket[1][$i]['s'.$c] = -1;
        }
        
        // Store rounds info.
        $this->roundsInfo = $this->getRoundsInfo();
    }
    
    public function setResByMatch($m, $r, $s1, $s2)
    {
        /* Sets a match result by specifying match number and round number. */
    
        // Test if input is valid.
        if (!$this->isMatchCreated($m, $r) ||                                               // Valid round and match?
            $this->bracket[$r][$m]['s1'] == -1 || $this->bracket[$r][$m]['s2'] == -1 ||     // Are competitors "ready"/exist?
            !is_int($s1) || !is_int($s2) || $s1 < 0 || $s2 < 0 || $s1 == $s2) {             // Valid scores?
            return false;
        }
            
        // Insert data.
        $this->bracket[$r][$m]['s1'] = $s1;
        $this->bracket[$r][$m]['s2'] = $s2;
        
        // Was the updated match the final? If yes, noting is left to be done. Note though, that round 0, the play-in round, is allowed to have one match only!
        if ($r !== 0 && $this->roundsInfo[$r][1] == 2) // Is n = 2 ? where n is the number of players in the round.
            return true;

        /* Update the match(es) in the following round(s). */

        list($nm, $nc) = $this->getNextMatch($m);
        $nr = $r+1;

        // Was match $m in round $r not already played?
        if (!$this->isMatchCreated($nm, $nr) || $this->bracket[$nr][$nm]['s'.$nc] == -1) {

            // Place winner of match in next match in next round.
            $this->bracket[$nr][$nm]['c'.$nc] = ($s1 > $s2) ? $this->bracket[$r][$m]['c1'] : $this->bracket[$r][$m]['c2']; // $s1 and $s2 are never equal due to input testing.
            $this->bracket[$nr][$nm]['s'.$nc] = 0;

            // If the match does not already exist, then the second competitor is missing. If so, we create an "undecided" marker by setting the score = -1.
            if (!array_key_exists('c' . ($ncs = ($nc == 1) ? 2 : 1), $this->bracket[$nr][$nm])) { // $ncs = Next match competitor (second).
                $this->bracket[$nr][$nm]['c'.$ncs] = null;
                $this->bracket[$nr][$nm]['s'.$ncs] = -1;
            }
        }
        else {
        
            /* 
                Match $m in round $r had already been played...
                Now, if the new match result changes the match winner, we must update all match winners from match $m in round $r and on.
             */
            for (; $this->isMatchCreated($nm, $nr); $r++, $nr++, $m = $nm, list($nm, $nc) = $this->getNextMatch($m)){
                $this->bracket[$nr][$nm]['c'.$nc] = ($this->bracket[$r][$m]['s1'] > $this->bracket[$r][$m]['s2'])
                                                        ? $this->bracket[$r][$m]['c1'] 
                                                        : $this->bracket[$r][$m]['c2'];
            }
        }
        
        return true;
    }

    public function setResByCompets($c1, $c2, $s1, $s2)
    {
        /* Sets a match result by specifying the match competitors. */
        
        $match = $round = 0;
        $swap = $foundIt = false;
        
        foreach ($this->bracket as $r_idx => $r) {
            foreach ($r as $m_idx => $m) {
                if ($m['c1'] === $c1 && $m['c2'] === $c2 || $m['c1'] === $c2 && $m['c2'] === $c1 && $swap = true) {
                    if ($swap) {
                        $tmp = $s1;
                        $s1 = $s2;
                        $s2 = $tmp;
                    }
                    $match = $m_idx;
                    $round = $r_idx;
                    $foundIt = true;
                    break;
                }
            }
        }
    
        if ($foundIt && $this->setResByMatch($match, $round, $s1, $s2))
            return true;
        else
            return false;
    }

    protected function getRoundsInfo() 
    {
        /* 
            The bracket structure alone is somewhat insufficient regarding the details of each round.
            This method returns an array of arrays which further describe each round. 
            The elements of the out-most array are ordered, so that the index of each element corresponds to the round which the element describes.
            Each element is an array itself, and is structured like this: [0] = "round name", [1] = number of competitors in the round.
            
            For example:
            
                array(
                    0 => array('Play-in round', 2),
                    1 => array('Semi-finals', 4),
                    2 => array('Final', 2),
                )
        */
    
        $rounds = array();
        
        for ($r = 1, $n = count($this->bracket[1])*2; $n > 1; $r++, $n /= 2) {
            switch ($n)
            {
                case 16: $name = 'Round of 16'; break;
                case 8:  $name = 'Quarter-finals'; break;
                case 4:  $name = 'Semi-finals'; break;
                case 2:  $name = 'Final'; break;
                default: $name = "Round $r"; break;
            }
            
            array_push($rounds, array($name, $n));
        }
        
        // Bump up all array indicies so that they fit round numbers.
        array_unshift($rounds, array()); // Temporary placeholder.
        unset($rounds[0]);
        
        // If play-in round exists, we add it now.
        if (!empty($this->bracket[0]))
            array_unshift($rounds, array('Play-in round', count($this->bracket[0])*2));
        
        return $rounds;
    }
    
    public function getNextMatch($m)
    {
        /* For a match number, $m, in round r, the winner is to compete in round r+1 in match $nm (next match) as competitor number $nc (next competitor). */
        
        /*
            Round r       Round r+1
            
            Match 0 -----
                        |----- Match 0
            Match 1 -----
            
            Match 2 -----
                        |----- Match 1
            Match 3 -----
            
            For each match in round r+1, it requires two match winners from round r.

            The competitors, 'c1' and 'c2', in match 0 in r+1, are:
                'c1' = winner of match 0 in r.
                'c2' = winner of match 1 in r.
            
            ... and so forth for match N in r+1.
                
        */
    
        $nm = (int) floor($m/2); // Next match.
        $nc = ($m % 2) ? 2 : 1; // Next competitor.
        
        return array($nm, $nc);
    }
    
    public function getPrevMatch($m, $c)
    {
        /* Does the reverse of getNextMatch(), so $m == getPrevMatch(getNextMatch($m)) */
                
        $pm = $m*2 + (($c == 2) ? 1 : 0);
        
        return $pm;
    }
    
    public function isMatchCreated($m, $r) 
    {
        /* Tests if a specific match entry exists. */
    
        if (array_key_exists($r, $this->bracket) && array_key_exists($m, $this->bracket[$r]))
            return true;
        else
            return false;
    }
    
    public function isMatchPlayed($m, $r)
    {
        /* Tests if a specific match has been played. */
    
        if ($this->isMatchCreated($m, $r)) {
            $m = $this->bracket[$r][$m];
            if ($m['s1'] >= 0 && $m['s2'] >= 0 && $m['s1'] !== $m['s2'])
                return true;
        }
        
        return false;
    }
    
    public function renameCompets(array $dictionary) {
        
        /* Re-names competitors throughout the bracket. */
        
        foreach ($this->bracket as $r_idx => $matches) {
            foreach ($matches as $m_idx => $m) {
                $c1 = $this->bracket[$r_idx][$m_idx]['c1'];
                $c2 = $this->bracket[$r_idx][$m_idx]['c2'];
                $this->bracket[$r_idx][$m_idx]['c1'] = ($c1) ? $dictionary[$c1] : $c1;
                $this->bracket[$r_idx][$m_idx]['c2'] = ($c2) ? $dictionary[$c2] : $c2;
            }
        }
        
        return true;
    }
    
    public function getBracket()
    {
        return $this->bracket;
    }
}

class KnockoutGD extends Knockout {

    private $im = null; // GD-lib image resource.
    private $tc = 0;    // Text color.

    public function getImage($tourName = '')
    {
        /* Returns a GD-lib image resource */

        // Initial testing.
        if (empty($this->roundsInfo))
            return null;

        // Dimensional parameters.
        $fh   = imagefontheight(FONT);
        $fw   = imagefontwidth(FONT);
        $lpad = 30; // Line (branch) padding before and after competitor names and after scores.
        $hpad = 20; // Outer horizontal image padding.
        $vpad = 60; // Outer vertical image padding.
        $lw   = $this->getStrLen()*$fw + 3*$lpad; // Line (branch) width. Where getStrLen() gets the length of the longest string used in the image.

        // Initial calls.
        $dimensions = $this->getDimens($fh, $lw, $hpad, $vpad);
        $this->im   = imagecreate($dimensions['x'], $dimensions['y']);
        $bg         = imagecolorallocate($this->im, 255, 255, 255); // Set background color.
        $this->tc   = imagecolorallocate($this->im, 0, 0, 0); // Text color.
        $this->mkStr($dimensions['x'] - $hpad - imagefontwidth(LFONT)*strlen($tourName), $dimensions['y'] - $vpad/2, $tourName, LFONT); // Print tournament name.

        // Initial positioning values from which drawing begins.
        $rx = $hpad; // Round X-position.
        $ry = $vpad; // Round Y-position.
        $depth = 1; // Branch depth.

        // Start drawing the tournament bracket/tree.
        foreach ($this->roundsInfo as $r => $info) {

            $n = $info[1]; // Number of expected players in round $r.

            // If a match is no yet created, then a placeholder is made so that the bracket structure is still printable.
            for ($m = 0; $m <= $n/2 - 1; $m++) {
                if (!$this->isMatchCreated($m, $r)) {
                    $this->bracket[$r][$m]['c1'] = $this->bracket[$r][$m]['c2'] = null;
                    $this->bracket[$r][$m]['s1'] = $this->bracket[$r][$m]['s2'] = -1;
                }
            }

            // Now we generate round branches.
            $x = $rx;
            $y = $ry;
            $bheight = pow(2, $depth) * $fh; // This is the height of a match-branch, which increases as the tree depth increases.
            ksort($this->bracket[$r]);
            
            foreach ($this->bracket[$r] as $m) {
                for ($i = 1; $i <= 2; $i++, $y += $bheight) {
                    $this->mkStr($x+$lpad,     $y, $m['s'.$i] == -1 ? 'Undecided' : $m['c'.$i]);
                    $this->mkStr($x+$lw-$lpad, $y, $m['s'.$i] == -1 ? '?'         : $m['s'.$i]);
                    $this->mkLine($x, $y+$fh, $x+$lw, $y+$fh);
                }
                $this->mkLine($x+$lw, ($y+$fh)-$bheight, $x+$lw, ($y+$fh)-2*$bheight);
            }
            
            // Get ready for next loop.
            $rx += $lw;
            $ry += $bheight/2;
            $depth++;
        }

        // Add final branch/line for the tournament winner
        $fr = end(array_keys($this->roundsInfo)); // Final round.
        $s1 = $this->bracket[$fr][0]['s1'];
        $s2 = $this->bracket[$fr][0]['s2'];
        $winner = (!array_key_exists(0, $this->bracket[$fr]) || $s1 == -1 || $s2 == -1 || $s1 === $s2) 
                    ? '?' 
                    : (($s1 > $s2) 
                        ? $this->bracket[$fr][0]['c1'] 
                        : $this->bracket[$fr][0]['c2']);
                        
        $this->mkStr($rx+$lpad, $ry, 'Winner: ' . $winner);
        $this->mkLine($rx, $ry+$fh, $rx+$lw, $ry+$fh);

        // Now, we print the round titles.
        array_push($this->roundsInfo, array('Champion', 1)); // Add fictitious round for printing purposes only.
        foreach (array_reverse($this->roundsInfo) as $r) {
            $this->mkStr($rx+$lpad, $vpad/3, $r[0], LFONT);
            $rx -= $lw; // Move back one round/column.
        }
        array_pop($this->roundsInfo); // Remove fictitious round entry again.
        
        return $this->im;
    }

    private function mkLine($x0, $y0, $x, $y) 
    {
        /* Wrapper for function that creates a line. */
        
        imageline($this->im, $x0, $y0, $x, $y, $this->tc);
    }

    private function mkStr($x, $y, $str, $font = false) 
    {
        /* Wrapper for function that writes a string. */
        
        imagestring($this->im, $font ? $font : FONT, $x, $y, $str, $this->tc);
    }

    private function getDimens($fontHeight, $lineWidth, $horizPad, $vertPad) 
    {
        /* Returns image dimensions based on the tournament bracket. */

        /*
            Vertically:
        
                Each match-branch is outputted like this:
                
                Team A
                -----------
                Padding
                Team B
                -----------
                Padding
                
                ... where "Padding" and "Team X" are of height $fontHeight, and "------" are branch lines with approximately no height.
            
            Horizontally:
            
                Each branch is outputted like this:
                
                ----- Team A ------
                                  |
                                  ----- Team C ------
                                  |
                ----- Team B ------
                
                ... where "Team X" branch has an absolute length of $lineWidth. The above therefore illustrates a length of 2*$lineWidth.

            The image length, $x, must be:
            
                $horizPad + number_of_rounds * $lineWidth + $horizPad
            
            And the image height, $y, must be:
            
                $vertPad + number_of_matches_in_first_round * 4*$fontHeight + $vertPad
            
            ... since the first round contains the most matches.
        */

        $frGames = count($this->bracket[1]); // Number of games in the first round.
        $playInExists = !empty($this->bracket[0]) ? true : false;
        
        /* 
            The y-size of the image must be calculated accordingly to the above description.
            Though, if a play-in round exists, then the play-in round is potentially the round which requires the most vertical space to draw.
            In this case we scale the image as if the play-in round was the first round.
            Due to the nature of the tournament bracket, this means that there are twice as many games in that round.
        */
        
        $y = 2*$vertPad  + ($playInExists ? $frGames*2 : $frGames) * 4*$fontHeight;
        
        /* 
            The x-size of the image is proportional to the number of rounds in the tournament,
                where the proportionality constant is the length of a branch, $lineWidth, ie. the width of a round.
            Since the number of players in the first round is equal to 2^R, where R is a whole number, and denotes the number of rounds required in the tournament,
                then R = log(players, 2), where "players" also can be found by 2 * matches_in_first_round (2 players pr. match).
    
            Like above, if a play-in round exists the x-size of the image must be changed.
            In this case, we merely add the length of a branch to the total x-size, since all branches are equal in length.
            
            Besides that, we add another whole branch length for the Winner/Champion branch, 
                which technically is not a part of the tournament bracket, but is shown anyway.
        */
        
        $x = 2*$horizPad + (log($frGames*2, 2) + ($playInExists ? 1 : 0) + 1) * $lineWidth;
        
        return array('x' => $x, 'y' => $y);
    }

    private function getStrLen() 
    {
        /* 
            Returns the length of longest string used in either rounds. 
            This is done by looking in both play-in round and first round, since all competitors are to be found there.
        */
        
        $len = 0;
        
        foreach (array_merge(array_key_exists(0, $this->bracket) ? $this->bracket[0] : array(), $this->bracket[1]) as $m) {
            if (($newlen = strlen($m['c1'])) > $len) $len = $newlen;
            if (($newlen = strlen($m['c2'])) > $len) $len = $newlen;
        }
        
        foreach ($this->roundsInfo as $arr) {
            if (($newlen = strlen($arr[0])) > $len) $len = $newlen;
        }

        return $len;
    }
}

?>
