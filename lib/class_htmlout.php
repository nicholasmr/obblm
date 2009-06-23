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

/*
 THIS FILE is used for HTML-helper routines.
 */

class HTMLOUT 
{

public static function standings($obj, $node, $node_id, $opts) 
{    
    /*
         Makes various kinds of standings tables.   
         $obj and $node types are STATS_* types.
     */
    
    $tblTitle = $tblSortRule = '';
    $objs = $fields = $extra = array();
    
    list($url, $GET_SS) = $opts;
    if (!$GET_SS) {$GET_SS = '';}
    else {$extra['GETsuffix'] = $GET_SS;} # GET Sorting Suffix

    // Common $obj type fields.
    $fields = array(
        'won'               => array('desc' => 'W'), 
        'lost'              => array('desc' => 'L'), 
        'draw'              => array('desc' => 'D'), 
        'played'            => array('desc' => 'GP'), 
        'win_percentage'    => array('desc' => 'WIN%'), 
        'row_won'           => array('desc' => 'SW'), 
        'row_lost'          => array('desc' => 'SL'), 
        'row_draw'          => array('desc' => 'SD'), 
        'score_team'        => array('desc' => 'GF'),
        'score_opponent'    => array('desc' => 'GA'),
        'won_tours'         => array('desc' => 'WT'), 
        'td'                => array('desc' => 'Td'), 
        'cp'                => array('desc' => 'Cp'), 
        'intcpt'            => array('desc' => 'Int'), 
        'cas'               => array('desc' => 'Cas'), 
        'bh'                => array('desc' => 'BH'), 
        'si'                => array('desc' => 'Si'), 
        'ki'                => array('desc' => 'Ki'), 
    );
    
    switch ($obj)
    {
        case STATS_PLAYER:
            
            break;
            
        case STATS_TEAM:
            
            break;
            
        case STATS_RACE:
            array_merge(array(
                              'race'  => array('desc' => 'Race', 'href' => array('link' => 'index.php?section=races', 'field' => 'race', 'value' => 'race_id')), 
                              'teams' => array('desc' => 'Teams'),
                              ), $fields);
            $extra['dashed'] = array('condField' => 'teams', 'fieldVal' => 0, 'noDashFields' => array('race'));
            
            $objs = Race::getRaces(true);
            foreach ($objs as $o) {
                $o->setStats(true);
            }
                
            break;
            
        case STATS_COACH:
            $tblTitle = 'Coaches standings';
            $tblSortRule = 'coach';
            $fields = array_merge(array(
                'name'      => array('desc' => 'Coach', 'href' => array('link' => 'index.php?section=coaches', 'field' => 'coach_id', 'value' => 'coach_id')),
                'teams_cnt' => array('desc' => 'Teams'), 
            ), $fields);        
            $objs = Coach::getCoaches();
            foreach ($objs as $o) {
                $o->setStats(false);
            }
            break;
    }
    
    
    sort_table(
       $tblTitle, 
       $url, 
       $objs, 
       $fields, 
       sort_rule($tblSortRule), 
       (isset($_GET["sort$GET_SS"])) ? array((($_GET["dir$GET_SS"] == 'a') ? '+' : '-') . $_GET["sort$GET_SS"]) : array(),
       $extra
    );
}
}

?>