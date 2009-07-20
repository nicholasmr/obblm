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

/* 
 *  Generic class for handling the "texts" table.
 */

class _Text
{
    /***************
     * Properties 
     ***************/

    // MySQL stored information    
    public $txt_id = 0;
    public $type   = 0;
    public $f_id   = 0 ;
    public $date   = '';
    public $txt2   = '';
    public $txt    = '';
    
    /***************
     * Methods 
     ***************/

    function __construct($txt_id) 
    {
        $result = mysql_query("SELECT * FROM texts WHERE txt_id = $txt_id");
        if ($result && mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                foreach ($row as $key => $val) {
                    $this->$key = $val;
                }
            }
        }
        
#        $this->txt = preg_replace('/\r/', '<br>', $this->txt);
    }
    
    public function delete()
    {
        return (mysql_query("DELETE FROM texts WHERE txt_id = $this->txt_id"));
    }
    
    public function edit($txt, $txt2, $f_id = false, $type = false)
    {
        if (mysql_query("UPDATE texts SET 
                        txt2 = '".mysql_real_escape_string($txt2)."', 
                        txt = '".mysql_real_escape_string($txt)."' 
                        ".(($f_id) ? ", f_id = $f_id " : '')." 
                        ".(($type) ? ", type = $type " : '')." 
                        WHERE txt_id = $this->txt_id")) {
            $this->txt  = $txt;
            $this->txt2 = $txt2;
            return true;
        }
        else
            return false;
    }
    
    /***************
     * Statics
     ***************/
    
    public static function create($f_id, $type, $txt, $txt2)
    {
        return (mysql_query("
                INSERT INTO texts 
                (f_id, txt2, txt, date, type) 
                VALUES 
                ($f_id, '".mysql_real_escape_string($txt2)."', '".mysql_real_escape_string($txt)."', NOW(), $type)
                "));
    }
}

/* 
 *  Handles messages for the messages board.
 */

class Message extends _Text
{
    /***************
     * Properties 
     ***************/

    public $msg_id      = 0;
    public $f_coach_id  = 0 ;
    public $date_posted = '';
    public $title       = '';
    public $message     = '';
    
    /***************
     * Methods 
     ***************/

    function __construct($msg_id) 
    {
        parent::__construct($msg_id);
        
        $this->msg_id       = $this->txt_id;        
        $this->f_coach_id   = $this->f_id;
        $this->date_posted  = $this->date;
        $this->title        = $this->txt2;
        $this->message      = $this->txt;
        
        unset($this->txt2);
        unset($this->txt);
    }

    public function edit($new_title, $new_msg, $f_coach_id = false) 
    {
        return (parent::edit($new_msg, $new_title, $f_coach_id, T_TEXT_MSG) && ($this->title = $this->txt2) && ($this->message = $this->txt));
    }

    /***************
     * Statics
     ***************/

    public static function getMessages($n = false) 
    {
        $m = array();

        $result = mysql_query("SELECT txt_id FROM texts WHERE type = ".T_TEXT_MSG." ORDER BY date DESC" . (($n) ? " LIMIT $n" : ''));
        if ($result && mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                array_push($m, new Message($row['txt_id']));
            }
        }
        
        return $m;
    }

    public static function create($input) 
    {
        return parent::create($input['f_coach_id'], T_TEXT_MSG, $input['msg'], $input['title']);
    }
}

/* 
 *  Handles entries in the hall of fame.
 */

class HOF extends _Text
{
    /***************
     * Properties 
     ***************/

    public $hof_id      = 0;
    public $player_id   = 0;
    public $title       = '';
    public $about       = '';

    /***************
     * Methods 
     ***************/    

    function __construct($hof_id) 
    {
        parent::__construct($hof_id);
        
        $this->hof_id       = $this->txt_id;        
        $this->player_id    = $this->f_id;
        $this->title        = $this->txt2;
        $this->about        = $this->txt;
        
        unset($this->txt2);
        unset($this->txt);
    }
    
    public function edit($title, $about) 
    {
        return parent::edit($about, $title, false, false);
    }
    
    /***************
     * Statics
     ***************/
    
    public static function getHOF($n = false)
    {
        $HOF = array();

        $result = mysql_query("SELECT txt_id, f_id FROM texts WHERE type = ".T_TEXT_HOF." ORDER BY date DESC" . (($n) ? " LIMIT $n" : ''));
        if ($result && mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                array_push($HOF, array('hof' => new HOF($row['txt_id']), 'player' => new Player($row['f_id'])));
            }
        }
        
        return $HOF;
    }
    
    public static function create($player_id, $title, $about)
    {
        return parent::create($player_id, T_TEXT_HOF, $about, $title);
    }
}

/* 
 *  Handles text Descriptions for players (T_TEXT_PLAYER), teams (T_TEXT_TEAM) and coaches (T_TEXT_COACH).
 */

class TDesc extends _Text
{
    /***************
     * Properties 
     ***************/
     
     // From MySQL.
     public $type = 0;
     public $txt = '';

    /***************
     * Methods 
     ***************/

    function __construct($type, $f_id) 
    {
        $result = mysql_query("SELECT * FROM texts WHERE type = $type AND f_id = $f_id");
        if ($result && mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                foreach ($row as $key => $val) {
                    $this->$key = $val;
                }
            }
        }
        
        $this->type = $type;
        $this->f_id = $f_id;
    }

    public function save($txt)
    {
        return (empty($this->txt)) 
            ? parent::create($this->f_id, $this->type, $txt, false) 
            : parent::edit($txt, false, false, false);
    }
}

/* 
 *  Handles wanted players.
 */

class Wanted extends _Text
{
    /***************
     * Properties 
     ***************/

    public $wanted_id   = 0;
    public $player_id   = 0;
    public $why         = '';
    public $bounty      = '';

    /***************
     * Methods 
     ***************/    

    function __construct($wanted_id) 
    {
        parent::__construct($wanted_id);
        
        $this->wanted_id    = $this->txt_id;        
        $this->player_id    = $this->f_id;
        $this->bounty       = $this->txt2;
        $this->why          = $this->txt;
        
        unset($this->txt2);
        unset($this->txt);
    }
    
    public function edit($why, $bounty) 
    {
        return parent::edit($why, $bounty, false, false);
    }
    
    /***************
     * Statics
     ***************/
    
    public static function getWanted($n = false)
    {
        $w = array();

        $result = mysql_query("SELECT txt_id, f_id FROM texts WHERE type = ".T_TEXT_WANTED." ORDER BY date DESC" . (($n) ? " LIMIT $n" : ''));
        if ($result && mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                array_push($w, array('wanted' => new Wanted($row['txt_id']), 'player' => new Player($row['f_id'])));
            }
        }
        
        return $w;
    }
    
    public static function create($player_id, $why, $bounty)
    {
        return parent::create($player_id, T_TEXT_WANTED, $why, $bounty);
    }
}

/* 
 *  Handles match summaries.
 */

class MSMR extends _Text
{
    /*
        Please note: 
        
            The date field for MSMR is not used for anything and its contents is not reliable. 
            Instead use the date fields of the corresponding match object.
    */

    /***************
     * Properties 
     ***************/

    public $match_id = 0;
    public $exists = false; // Does this match already have a summary entry in the texts table?

    /***************
     * Methods 
     ***************/    

    function __construct($mid) 
    {
        $this->match_id = $mid;
        $query = "SELECT txt_id FROM texts WHERE f_id = $mid AND type = ".T_TEXT_MSMR;
        $result = mysql_query($query);
        if ($result && mysql_num_rows($result) > 0) {
            $this->exists = true;
            $row = mysql_fetch_assoc($result);
            parent::__construct($row['txt_id']);
        }
    }
    
    public function save($txt)
    {
        return (!$this->exists) 
            ? parent::create($this->match_id, T_TEXT_MSMR, $txt, false) 
            : parent::edit($txt, false, false, false);
    }
}

/* 
 *  Handles tournament descriptions.
 */

class TourDesc extends _Text
{
    /***************
     * Properties 
     ***************/

    public $tour_id = 0;

    /***************
     * Methods 
     ***************/    

    function __construct($tid) 
    {
        $this->tour_id = $tid;
        $query = "SELECT txt_id FROM texts WHERE f_id = $tid AND type = ".T_TEXT_TOUR;
        $result = mysql_query($query);
        $row = mysql_fetch_assoc($result);
        parent::__construct($row['txt_id']);
        
    }
    
    public function save($txt)
    {
        return (empty($this->txt)) 
            ? parent::create($this->tour_id, T_TEXT_TOUR, $txt, false) 
            : parent::edit($txt, false, false, false);
    }
}

/* 
 *  Handles guest book entries.
 */

class GuestBook extends _Text
{
    /***************
     * Properties 
     ***************/

    public $gb_id = 0;

    /***************
     * Methods 
     ***************/    

    function __construct($gb_id) 
    {
        parent::__construct($gb_id);
        $this->gb_id = $gb_id;
    }
    
    /***************
     * Statics
     ***************/
    
    public static function create($txt)
    {
        return parent::create(0, T_TEXT_GUEST, $txt, false);
    }
    
    public static function getBook()
    {
        $gb = array();
        
        $query = "SELECT txt_id FROM texts WHERE type = ".T_TEXT_GUEST." ORDER BY date DESC";
        $result = mysql_query($query);
        if ($result && mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                array_push($gb, new GuestBook($row['txt_id']));
            }
        }
        
        return $gb;
    }
}

/* 
 *  Handles the logging of actions made on the site by coaches.
 */

class SiteLog extends _Text
{
    /***************
     * Properties 
     ***************/

    public $log_id = 0;
    public $f_id = 0; // Submitter (coach).
    public $txt = '';

    /***************
     * Methods 
     ***************/    

    public function __construct($lid) 
    {
        $this->log_id = $lid;
        parent::__construct($lid);
    }

    /* 
        Parent has delete() implemented.
    */

    /***************
     * Statics
     ***************/
    
    public static function create($str, $f_id)
    {
        return parent::create($f_id, T_TEXT_LOG, $str, false);
    }
    
    public static function getLogs($monthsBack = false)
    {
        $logs = array();
        
        $query = "SELECT txt_id FROM texts WHERE type = ".T_TEXT_LOG.(($monthsBack) ? " AND date > SUBDATE(NOW(), INTERVAL $monthsBack MONTH) " : '')." ORDER BY date DESC";
        $result = mysql_query($query);
        if ($result && mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                array_push($logs, new SiteLog($row['txt_id']));
            }
        }
        
        return $logs;
    }
}

/* 
 *  Match summary comments
 */

class MSMRC extends _Text
{
    /***************
     * Properties 
     ***************/

    public $cid = 0; // Comment ID.
    public $mid = 0; // ID of match to which this summary comment belongs to.
    public $sid = 0; // Submitter's ID.
    public $sname = ''; // Submitter's name.
    public $txt = '';

    /***************
     * Methods 
     ***************/    

    function __construct($cid) 
    {
        parent::__construct($cid);
        $this->cid = $cid;
        $this->mid = $this->f_id;
        $this->sid = (int) $this->txt2; // NOTE: The submitter's ID is stored in a text field type!
        $this->sname = get_alt_col('coaches', 'coach_id', $this->sid, 'name');
        
        return true;
    }
    
    /***************
     * Statics
     ***************/
     
    public static function matchHasComments($mid)
    {
        $query = "SELECT COUNT(*) AS 'cnt' FROM texts WHERE f_id = $mid AND type = ".T_TEXT_MSMRC;
        $result = mysql_query($query);
        $row = mysql_fetch_assoc($result);
        return ((int) $row['cnt'] > 0);
    }
    
    public static function getComments($mid, $sort = '-')
    {
        $c = array();
        $query = "SELECT txt_id FROM texts WHERE f_id = $mid AND type = ".T_TEXT_MSMRC.' ORDER BY date '.(($sort == '-') ? 'DESC' : 'ASC');
        $result = mysql_query($query);
        if ($result && mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                array_push($c, new MSMRC($row['txt_id']));
            }
        }
        return $c;
    }
    
    public static function create($mid, $sid, $txt)
    {
        return parent::create($mid, T_TEXT_MSMRC, $txt, $sid);
    }
}

/* 
 *  Team news board.
 */

class TNews extends _Text
{
    /***************
     * Properties 
     ***************/

    public $news_id = 0;
    public $f_id = 0; // Submitter (team id).
    public $txt = '';

    /***************
     * Methods 
     ***************/    

    public function __construct($nid) 
    {
        $this->news_id = $nid;
        parent::__construct($nid);
    }

    public function edit($txt)
    {
        return parent::edit($txt, '', false, false);    
    }

    /* 
        Parent has delete() implemented.
    */

    /***************
     * Statics
     ***************/
    
    public static function create($str, $tid)
    {
        return parent::create($tid, T_TEXT_TNEWS, $str, false);
    }
    
    public static function getNews($tid = false, $n = false)
    {
        $news = array();
        
        $query = "SELECT txt_id FROM texts WHERE type = ".T_TEXT_TNEWS.(($tid) ? " AND f_id = $tid " : ''). " ORDER BY date DESC ".(($n) ? " LIMIT $n " : '');
        $result = mysql_query($query);
        if ($result && mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                array_push($news, new TNews($row['txt_id']));
            }
        }
        
        return $news;
    }
}

?>
