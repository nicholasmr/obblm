<?php

/*
 *  Copyright (c) Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2007-2009. All Rights Reserved.
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

function aasort(&$array, $args) {

    $sort_rule = ""; # Must be initialized in outer scope.
    foreach($args as $arg) {
        $order_field = substr($arg, 1, strlen($arg));
        foreach($array as $array_row) {
            $sort_array[$order_field][] = $array_row[$order_field];
        }
        $sort_rule .= '$sort_array["'.$order_field.'"], '.($arg[0] == "+" ? SORT_ASC : SORT_DESC).',';
    }
    eval ("array_multisort($sort_rule".' $array);');
}

// Sorts array of objects by common object properties. 
// Usage: objsort($obj_array, array('+X', '-Y')) ...to sort objects by X ascending followed by Y descending.
function objsort(&$obj_array, $fields)
{
    $idxs = count($fields)-1;   # Number of fields to sort by.
    $func = 'return ';          # Anonymous function used for sorting the object array.
    $parens = 0;                # Number of parentheses added to end of anonymous function.
    
    for ($i = 0; $i <= $idxs; $i++) {
        $field = substr($fields[$i], 1, strlen($fields[$i]));
        $sort_type = substr($fields[$i], 0, 1);
        $parens += ($i == $idxs ? 1 : 2);
        $func .= "\$a->$field " . ($sort_type == '+' ? '>' : '<') . " \$b->$field 
                    ? 1 
                    : (\$a->$field != \$b->$field 
                        ? -1 
                        : " . ($i == $idxs ? '0' : '(');
    }

    $func .= str_repeat(')', $parens) . ';';
    return usort($obj_array, create_function('$a, $b', $func));
}

// Returns what sort rule is to be used for different stats-table types.
function sort_rule($w) {

    # - Summable values from the MV tables must always be referenced by using the "mv_" prefix.
    # - Non-summable values (ie. steaks, win pct's etc.) are referenced by the "rg_" prefix.
    # - Static and dynamic properties (ie. name, treasury, skills etc.) need no prefix.
    
    $rules = array(
        T_OBJ_RACE   => array('-rg_win_pct', '+name'), // "All races"-table
        T_OBJ_COACH  => array('-rg_win_pct', '-wt_cnt', '-mv_cas', '+name'), // "All coaches"-table
        T_OBJ_TEAM   => array('-mv_won', '-mv_draw', '+mv_lost', '-mv_sdiff', '-mv_cas', '+name'), // "All teams"-table
        T_OBJ_PLAYER => array('-value', '-mv_td', '-mv_cas', '-mv_spp', '+name'), // "All players"-table
        T_OBJ_STAR   => array('-mv_played', '+name'), // Stars table.
        
        'match'     => array('-date_played'), // Games played tables.
        'player'    => array('+nr', '+name'), // For team roaster player list.
        'race_page' => array('+cost', '+position'), // Race's players table.
        'star_HH'   => array('-date_played'), // Stars hire history table.
    );

    return array_key_exists($w, $rules) ? $rules[$w] : array();
}


function rule_dict(array $sortRule) {
    
    /* Translates sort rules. */
    
    $sortRule = preg_replace('/(mv\_|rg\_)/', '', $sortRule);
    
    $ruleDict = array(
        'win_pct'     => 'WIN%',
        'date_played' => 'date played',
        'wt_cnt'      => 'WT',
        'sdiff'       => 'score diff.',
        'tdcas'       => '{td+cas}',
        'swon'        => 'SW',
        'slost'       => 'SL',
        'sdraw'       => 'SD',
    );
    
    foreach ($sortRule as &$r) {
        $idx = substr($r,1);
        if (array_key_exists($idx, $ruleDict)) {
            $r = substr($r,0,1).$ruleDict[$idx];
        }
    }
    
    return $sortRule;
}

function skillsTrans($str) {
    // Translates a commaseperated string of skill IDs to a comma (plus space) seperated skill name string.
    return implode(', ', array_map(create_function('$s', 'global $skillididx; return $skillididx[$s];'), is_array($str) ? $str : explode(',', $str)));
}

function racesTrans($str) {
    // Translates a commaseperated string of race IDs to a comma (plus space) seperated race name string.
    return implode(', ', array_map(create_function('$r', 'global $raceididx; return $raceididx[$r];'), is_array($str) ? $str : explode(',', $str)));
}

// Prints page title for main section pages.
function title($title) {
    echo "<h2>$title</h2>\n";
}

// Privileges error. Stop PHP interpreter and warn the user!
function fatal($err_msg) {
    die("<br><br><center><big><font color='red'><b>$err_msg</b></font></big></center><br>");
}

// Print a status message.
function status($status, $msg = '') {
    if ($status) { # Status == success
        echo "<div class=\"messageContainer green\">";
        echo "Request succeeded";
    } 
    else { # Status == failure
        echo "<div class=\"messageContainer red\">";
        echo "Request failed";
    }
    if ($msg) {echo " : $msg\n";}
    echo "</div>";
}

function textdate($mysqldate, $noTime = false) {
    return date("D M j Y".(($noTime) ? '' : ' G:i:s'), strtotime($mysqldate));
}

# Micro Time Stamp.
function MTS($str) {
    global $time_start;
    echo "\n<!-- $str: ".(microtime(true)-$time_start)." -->\n";
}

/* 
    From http://us3.php.net/manual/en/function.lcfirst.php 
*/
if (false === function_exists('lcfirst')):
    function lcfirst($str) { return (string)(strtolower(substr($str,0,1)).substr($str,1));}
endif; 
if (false === function_exists('ucfirst')):
    function ucfirst($str) { return (string)(strtoupper(substr($str,0,1)).substr($str,1));}
endif; 

// Returns HTML to show an icon with the result of a game
function matchresult_icon($result) {
    $types = array('W' => 'won', 'L' => 'lost', 'D' => 'draw');
    if (!array_key_exists($result, $types)) {$types[$result] = '';}
    return "<div class='match_icon ".$types[$result]."' title='".ucfirst($types[$result])."'></div>";
}

function fmtprint($str) {
    return preg_replace("/(\r\n|\n\r|\n|\r)/", '<br>', $str);
}

# URL compile constants
define('T_URL_PROFILE',   1);
define('T_URL_STANDINGS', 2);

function urlcompile($type, $obj, $obj_id, $node, $node_id, $extraGETs = array()) {
    return 'index.php?section=objhandler&amp;type='.$type.
        (($obj)     ? "&amp;obj=$obj" : '').
        (($obj_id)  ? "&amp;obj_id=$obj_id" : '').
        (($node)    ? "&amp;node=$node" : '').
        (($node_id) ? "&amp;node_id=$node_id" : '').
        implode("\n", array_map(create_function('$key,$val', 'return "$key=&amp;$val";'),array_keys($extraGETs),array_values($extraGETs)));
}

function inlineform($fields, $formName, $buttonText, $myFormElements = array()) {
    return "<form method='POST' name='$formName' style='display:inline; margin:0px;'>".
          implode("\n", array_map(create_function('$key,$val', 'return "<input type=\'hidden\' name=\'$key\' value=\'$val\'>";'),array_keys($fields),array_values($fields))).
          implode("\n", $myFormElements).
          "<a href='javascript:void(0);' onClick='document.$formName.submit();'>$buttonText</a></form>";
}
?>
