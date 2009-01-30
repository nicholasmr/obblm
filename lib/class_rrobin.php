<?php

/*
 *  Copyright (c) Niels Orsleff Justesen <njustesen@gmail.com> and Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2007. All Rights Reserved.
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

class RRobin {

    /* Properties */
    
    public $tour = array();
    public $tot_games = 0;
    
    // Pair 2->n/2.
    private $upper = array();
    private $lower = array();
    // Pair 1.
    private $fixed = 0;     # Locked competitor.
    private $rot_out = 1;   # Rotated out competitor to play against locked competitor.
    
    /* Methods */
    
    // Create Round-Robin tournament
    public function create($list = array()) {
        
        // Test input
        if (!is_array($list))
            return false;
        $n = count($list);
        if ($n < 3)
            return false;
        
        // Other
        $this->tot_games = ($n/2)*($n-1);
        
        // Initial array content
        if ($n % 2) { # Odd
            $n++;
            $this->upper = range(2, $n/2);
            $this->lower = range($n-1, $n/2 + 1);
            $this->lower[0] = -1; # Set as ghost player.
        }
        else { # Even
            $this->upper = range(2, $n/2);
            $this->lower = range($n-1, $n/2 + 1);
        }
        
        // Generate games
        for ($round = 0; $round < $n-1; $round++) {
            if ($this->rot_out != -1) # If not ghost player.
                array_push($this->tour, array($list[$this->fixed], $list[$this->rot_out]));
            for ($i = 0; $i <= count($this->upper)-1; $i++) { # -1 because of 0th element.
                if ($this->upper[$i] != -1 && $this->lower[$i] != -1) # If not ghost player.
                    array_push($this->tour, array($list[$this->upper[$i]], $list[$this->lower[$i]]));
            }
            $this->rotate();
        }
        
        return true;
    }
    
    // Rotate arrays
    private function rotate() {
        array_unshift($this->upper, $this->rot_out);
        array_push($this->lower, array_pop($this->upper));
        $this->rot_out = array_shift($this->lower);
    }
}
?>
