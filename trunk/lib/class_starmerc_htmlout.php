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

class Star_HTMLOUT extends Star
{

public function profile() 
{
    title($lng->getTrn('secs/stars/hh').' '.$this->name);
    echo '<center><a href="'.urlcompile(1,1).'">['.$lng->getTrn('global/misc/back').']</a></center><br><br>';
    HTMLOUT::starHireHistory(false, false, false, false, $this->star_id, array('url' => urlcompile(T_OBJ_STAR, $this->star_id)));
}

public static function standings()
{
    // All stars
    title($lng->getTrn('global/secLinks/stars'));
    echo $lng->getTrn('global/sortTbl/simul')."<br>\n";
    echo $lng->getTrn('global/sortTbl/spp')."<br><br>\n";
    HTMLOUT::standings(STATS_STAR, false, false, array('url' => 'index.php?section=stars'));
    $stars = Star::getStars(false,false,false,false);
    foreach ($stars as $s) {
        $s->skills = '<small>'.implode(', ', $s->skills).'</small>';
        $s->teams = '<small>'.implode(', ', $s->teams).'</small>';
        $s->name = preg_replace('/\s/', '&nbsp;', $s->name);
    }
    $fields = array(
        'name'   => array('desc' => 'Star', 'href' => array('link' => 'index.php?section=stars', 'field' => 'sid', 'value' => 'star_id')),
        'cost'   => array('desc' => 'Price', 'kilo' => true, 'suffix' => 'k'),
        'ma'     => array('desc' => 'Ma'),
        'st'     => array('desc' => 'St'),
        'ag'     => array('desc' => 'Ag'),
        'av'     => array('desc' => 'Av'),
        'teams'  => array('desc' => 'Teams', 'nosort' => true),
        'skills' => array('desc' => 'Skills', 'nosort' => true),
    );
    HTMLOUT::sort_table(
        '<a name="s2">'.$lng->getTrn('secs/stars/tblTitle2').'</a>',
        'index.php?section=stars',
        $stars,
        $fields,
        sort_rule('star'),
        (isset($_GET['sort'])) ? array((($_GET['dir'] == 'a') ? '+' : '-') . $_GET['sort']) : array(),
        array('anchor' => 's2')
    );
}

}
?>
