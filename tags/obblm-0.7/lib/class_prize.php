<?php

/*
 *  Copyright (c) Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2009. All Rights Reserved.
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

class Prize
{

/***************
 * Properties 
 ***************/

// MySQL stored information    
public $prize_id = 0;
public $team_id  = 0;
public $tour_id  = 0;
public $type     = 0; // Is equal to a PRIZE_* constant.
public $date     = '';
public $title    = '';
public $txt      = '';
public $pic      = '';

/***************
 * Methods 
 ***************/
    
function __construct($prid) 
{
    $result = mysql_query("SELECT * FROM prizes WHERE prize_id = $prid");
    if ($result && mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_assoc($result)) {
            foreach ($row as $key => $val) {
                $this->$key = $val;
            }
        }
    }
    
    $this->pic = $this->getPic();
    
    return true;
}

public function delete()
{
    return (mysql_query("DELETE FROM prizes WHERE prize_id = $this->prize_id"));
}

public function edit($type, $tid, $trid, $title, $txt, $pic = false)
{
    if (mysql_query("UPDATE prizes SET 
                    title = '".mysql_real_escape_string($title)."', 
                    txt = '".mysql_real_escape_string($txt)."',
                    team_id = $tid,
                    tour_id = $trid,
                    type = $type 
                    WHERE prize_id = $this->prize_id")) {
        $this->txt   = $txt;
        $this->title = $title;
        $this->team_id  = $tid;
        $this->tour_id = $trid;
        $this->type  = $type;
        if ($pic) {
            Prize::savePic($pic, $this->prize_id);
        }
        return true;
    }
    else
        return false;
}

private function getPic() 
{
    return get_pic(IMG_PRIZES, $this->prize_id);
}

/***************
 * Statics
 ***************/

private static function savePic($name, $prize_id) 
{
    return save_pic($name, IMG_PRIZES, $prize_id);
}

public static function getTypes() 
{
    return array(PRIZE_1ST => 'First place', PRIZE_2ND => 'Second place', PRIZE_3RD => 'Third place', PRIZE_LETHAL => 'Most lethal', PRIZE_FAIR => 'Fair play');
}

public static function getPrizesByTour($trid = false, $n = false) {
    
    $tours = array();
    
    if (!$trid) {
        $query = "SELECT DISTINCT(prizes.tour_id) AS 'tour_id' FROM prizes, tours WHERE prizes.tour_id = tours.tour_id ORDER BY date_created DESC ".(($n) ? " LIMIT $n" : '');
        $result = mysql_query($query);
        if ($result && mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                $tours[] = new Tour($row['tour_id']);
            }
        }
    }
    else {
        $tours[] = new Tour($trid);
    }
    
    foreach ($tours as $t) {
        $t->prizes = array();
        $query = "SELECT prize_id, type FROM prizes WHERE tour_id = $t->tour_id ORDER BY type ASC";
        $result = mysql_query($query);
        if ($result && mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                $t->prizes[$row['type']] = new Prize($row['prize_id']);
            }
        }
    }
    
    return $tours;
}


public static function getPrizesByTeam($tid)
{
    $prizes = array();
    
    $query = "SELECT prize_id FROM prizes WHERE team_id = $tid ORDER BY date DESC";

    $result = mysql_query($query);
    if ($result && mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_assoc($result)) {
            $prizes[] = new Prize($row['prize_id']);
        }
    }
    
    return $prizes;
}

public static function create($type, $tid, $trid, $title, $txt, $pic = false)
{
    if (!in_array($type, array_keys(Prize::getTypes())))
        return false;

    // Delete if already exists for type and tour.
    $query = "SELECT prize_id FROM prizes WHERE tour_id = $trid AND type = $type";
    $result = mysql_query($query);
    if ($result && mysql_num_rows($result) > 0) {
        $row = mysql_fetch_assoc($result);
        $pr = new Prize($row['prize_id']);
        $pr->delete();
    }

    // Create new.
    $query = "
            INSERT INTO prizes 
            (date, type, team_id, tour_id, title, txt) 
            VALUES 
            (NOW(), $type, $tid, $trid, '".mysql_real_escape_string($title)."', '".mysql_real_escape_string($txt)."')
            ";
    $result = mysql_query($query);
    $query = "SELECT MAX(prize_id) AS 'prize_id' FROM prizes;";
    $result = mysql_query($query);
    $row = mysql_fetch_assoc($result);  
    if ($pic) {
        Prize::savePic($pic, $row['prize_id']);
    }
    
    return true;
}

}

?>
