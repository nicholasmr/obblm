<?php

class RRobin {
    /* Properties */
    public $tour = array(
        1 => array(),  // Round 1
        #2 => array(), // Round 2
        #3 => array(), // Round 3
    );
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
        shuffle($list);
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
        } else { # Even
            $this->upper = range(2, $n/2);
            $this->lower = range($n-1, $n/2 + 1);
        }
        // Generate games
        for ($round = 0; $round < $n-1; $round++) {
            $this->tour[$round+1] = array(); // Initialize.
            if ($this->rot_out != -1) # If not ghost player.
                array_push($this->tour[$round+1], array($list[$this->fixed], $list[$this->rot_out]));
            for ($i = 0; $i <= count($this->upper)-1; $i++) { # -1 because of 0th element.
                if ($this->upper[$i] != -1 && $this->lower[$i] != -1) # If not ghost player.
                    array_push($this->tour[$round+1], array($list[$this->upper[$i]], $list[$this->lower[$i]]));
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