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
public $f_id     = 0;  // ID of object to whom this prize is given.
public $ac_id    = 0;  // A free to use associative ID for the prize (fx. the ID of the tour in which the prize was won by a, say team).
public $type     = 0;  // Type of object to whom this prize is given.
public $date     = '';
public $title    = '';
public $txt      = '';

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
    
    return true;
}

public function delete()
{
    return (mysql_query("DELETE FROM prizes WHERE prize_id = $this->prize_id"));
}

public function edit($type, $f_id, $ac_id, $title, $txt)
{
    if (mysql_query("UPDATE prizes SET 
                    title = '".mysql_real_escape_string($title)."', 
                    txt = '".mysql_real_escape_string($txt)."',
                    f_id = $f_id,
                    ac_id = $ac_id,
                    type = $type 
                    WHERE prize_id = $this->prize_id")) {
        $this->txt   = $txt;
        $this->title = $title;
        $this->f_id  = $f_id;
        $this->ac_id = $ac_id;
        $this->type  = $type;
        return true;
    }
    else
        return false;
}

public function saveStadiumPic($name) {
    return save_pic($name, IMG_PRIZES, $this->prize_id);
}

public function getPic() {
    return get_pic(IMG_PRIZES, $this->prize_id);
}

/***************
 * Statics
 ***************/

public static function getPrizes($type)
{
    $prizes = array();
    $query = "SELECT prize_id FROM prizes WHERE type = $type ORDER BY date DESC";
    if ($result && mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_assoc($result)) {
            $prizes[] = new Prize($row['prize_id']);
        }
    }
    
    return $prizes;
}

public static function create($type, $f_id, $ac_id, $title, $txt)
{
    if (!in_array($type, array(PRIZE_PLAYER, PRIZE_TEAM, PRIZE_COACH)))
        return false;

    return (mysql_query("
            INSERT INTO prizes 
            (date, type, f_id, ac_id, title, txt) 
            VALUES 
            (NOW(), $type, $f_id, $ac_id, '".mysql_real_escape_string($title)."', '".mysql_real_escape_string($txt)."')
            "));
}

}

?>
