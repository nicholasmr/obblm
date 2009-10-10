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
    
    $rule = array();
    
    switch ($w)
    {
        case 'streaks': // For streaks table.
            $rule = array('-row_won', '-row_draw', '+row_lost', '+name');
            break;
    
        case 'race_page': // Race's players table.
            $rule = array('+cost', '+position');
            break;
            
        case 'race': // "All races"-table
            $rule = array('-win_percentage', '+race');
            break;
            
        case 'match': // Games played tables.
            $rule = array('-date_played');
            break;
    
        case 'coach': // "All coaches"-table
            $rule = array('-win_percentage', '-won_tours', '-cas', '+name');
            break;
            
        case 'team': // Overall team standings.
            $rule = array('-won', '-draw', '+lost', '-score_diff', '-cas', '+name');
            break;
            
        case 'player': // For team roaster player list.
            $rule = array('+nr', '+name');
            break;
            
        case 'player_overall': // "All players"-table
            $rule = array('-value', '-td', '-cas', '-spp', '+name');
            break;
            
        case 'star': // Stars table.
            $rule = array('-played', '+name');
            break;
            
        case 'star_HH': // Stars hire history table.
            $rule = array('-date_played');
            break;
    }
    
    return $rule;
}


function rule_dict(array $rule) {
    
    /* Translates sort rules. */
    
    $d = array(
        'win_percentage'    => 'win percentage',
        'date_played'       => 'date played',
        'won_tours'         => 'won tours',
        'score_diff'        => 'score diff.',
        'tdcas'             => '{td+cas}',
        'row_won'           => 'won in row',
        'row_lost'          => 'lost in row',
        'row_draw'          => 'draw in row',
    );
    
    foreach ($rule as &$r) {
        $r = preg_replace('/_tour$/', '', $r);
        foreach ($d as $idx => $rpl) {
            $r = preg_replace("/$idx/", $rpl, $r);
        }
    }
    
    return $rule;
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
                if ($msg != ''){
                    echo " : $msg\n";
                }
            echo "</div>";
        } else { # Status == failure
                echo "<div class=\"messageContainer red\">";
                    echo "Request failed";
                if ($msg != ''){
                    echo " : $msg\n";
                }
            echo "</div>";
        }
        ?>
    <?php
}

function textdate($mysqldate, $noTime = false) {
    return date("D M j Y".(($noTime) ? '' : ' G:i:s'), strtotime($mysqldate));
}

// Returns HTML to show an icon with the result of a game
function matchresult_icon($result) {

    global $lng;

    $class = "";

    switch ($result){
        case "W":
            $class = "won";
            $title = $lng->getTrn('global/misc/gamewon');
            break;
        case "L":
            $class = "lost";
            $title = $lng->getTrn('global/misc/gamelost');
            break;
        case "D":
            $class = "draw";
            $title = $lng->getTrn('global/misc/gamedraw');
            break;
        default:
            $class = "";
            $title = $lng->getTrn('global/misc/gameunknown');
    }
    return "<div class='match_icon ". $class ."' title='". $title ."'></div>";
}

#function get_parent_node($node, $node_id, $parent_node) {
# Should be written soon.
#}

#function get_parent_obj($obj, $obj_id, $parent_obj) {
# Should be written soon.
#}

?>
