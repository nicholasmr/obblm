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
 
include("jpgraph/jpgraph.php");
include("jpgraph/jpgraph_bar.php");
include("jpgraph/jpgraph_pie.php");

// SG stands for Stats Graphs.
define('SG_OFFSET_SIZE',   100);
define('SG_OFFSET_TEAM',   0*SG_OFFSET_SIZE);
define('SG_OFFSET_COACH',  1*SG_OFFSET_SIZE);
define('SG_OFFSET_PLAYER', 2*SG_OFFSET_SIZE);
define('SG_OFFSET_LEAGUE', 3*SG_OFFSET_SIZE); // Overall league stats.

define('SG_CAS',        0); // Total
define('SG_BHSIKI',     1); // Multi bar bh, si and ki history.
define('SG_CPTDINT',    2); // Multi bar cp, td and int history.
define('SG_WLD',        3); // Multi bar won, lost and draw history.
 
define('SG_MULTIBAR_HIST_LENGTH', 6); // Number of months to show history from.
 
$sg_types = array(
    SG_CAS      => 'Current CAS distribution',
    SG_BHSIKI   => 'Last '.SG_MULTIBAR_HIST_LENGTH.' months casualty distribution',
    SG_CPTDINT  => 'Last '.SG_MULTIBAR_HIST_LENGTH.' months Cp, Td and Int distribution',
    SG_WLD      => 'Last '.SG_MULTIBAR_HIST_LENGTH.' months won, lost and draw distribution',
);
 
class SGraph
{
    public static function make($type, $id)
    {
        $o = $where = $graph = null;
        
        if     ($type < SG_OFFSET_TEAM+SG_OFFSET_SIZE)   {$t = new Team($_GET['id']);}
        elseif ($type < SG_OFFSET_COACH+SG_OFFSET_SIZE)  {$c = new Coach($_GET['id']);}
        elseif ($type < SG_OFFSET_PLAYER+SG_OFFSET_SIZE) {$p = new Player($_GET['id']);}

        switch ($_GET['gtype']) 
        {
            
            /********************
             *  Casualties
             ********************/
             
            case SG_OFFSET_PLAYER+SG_CAS: if (!is_object($o)) {$o = $p;}
            case SG_OFFSET_COACH+SG_CAS:  if (!is_object($o)) {$o = $c;}
            case SG_OFFSET_TEAM+SG_CAS:   if (!is_object($o)) {$o = $t;}
                
                $data = array("BH ($o->bh)" => $o->bh, "SI ($o->si)" => $o->si, "Ki ($o->ki)" => $o->ki);
                $graph = new PieGraph(500,400,"auto");
                $graph->SetShadow();
                $graph->title->Set('Casualties by '.$o->name);
                $graph->title->SetFont(FF_FONT1,FS_BOLD);
                $p1 = new PiePlot(array_values($data));
                $p1->SetLegends(array_keys($data));
                $p1->SetCenter(0.4);
                $graph->Add($p1);
                $graph->Stroke();
                
                break;
            
            /********************
             *  BH, SI and Ki
             ********************/
            
            case SG_OFFSET_PLAYER+SG_BHSIKI: if (!isset($where)) {$o = $p; $where = "f_player_id = $o->player_id";}
            case SG_OFFSET_COACH+SG_BHSIKI:  if (!isset($where)) {$o = $c; $where = "f_coach_id = $o->coach_id";}
            case SG_OFFSET_TEAM+SG_BHSIKI:   if (!isset($where)) {$o = $t; $where = "f_team_id = $o->team_id";}
                
                $queries = array();
                foreach (range(0, SG_MULTIBAR_HIST_LENGTH) as $i) {
                    $range = "(
                        (YEAR(date_played) = YEAR(SUBDATE(DATE(NOW()), INTERVAL $i MONTH))) 
                        AND 
                        (MONTH(date_played) = MONTH(SUBDATE(DATE(NOW()), INTERVAL $i MONTH)))
                    )";
                    # m$i = minus/negative $i months from present month.
                    array_push($queries, "SUM(IF($range, bh, 0)) AS 'bh_m$i'"); 
                    array_push($queries, "SUM(IF($range, si, 0)) AS 'si_m$i'"); 
                    array_push($queries, "SUM(IF($range, ki, 0)) AS 'ki_m$i'"); 
                    array_push($queries, "YEAR(SUBDATE(DATE(NOW()), INTERVAL $i MONTH)) AS 'yr_m$i'");
                    array_push($queries, "MONTH(SUBDATE(DATE(NOW()), INTERVAL $i MONTH)) AS 'mn_m$i'");
                }

                $query  = "SELECT ".implode(', ', $queries)." FROM matches, match_data WHERE f_match_id = match_id AND $where";
                $result = mysql_query($query);
                $row    = mysql_fetch_assoc($result);
                
                $lengends = array('bh' => 'green', 'si' => 'red', 'ki' => 'blue');
                $datasets = array();
                $labels   = array();
                
                foreach (array_keys($lengends) as $key) {
                    $ds = array();
                    foreach (range(0, SG_MULTIBAR_HIST_LENGTH) as $i) {
                        $ds[] = $row["${key}_m$i"];
                    }
                    $datasets[$key] = $ds;
                }
                foreach (range(0, SG_MULTIBAR_HIST_LENGTH) as $i) {
                    $labels[] = $row["yr_m$i"].'/'.$row["mn_m$i"];
                }
               
                SGraph::mbars($datasets, $labels, $lengends, "BH, SI and Ki history of $o->name", "Months", "Amount");
                
                break;

            /********************
             *  CP, TD and Int
             ********************/
            
            case SG_OFFSET_PLAYER+SG_CPTDINT: if (!isset($where)) {$o = $p; $where = "f_player_id = $o->player_id";}
            case SG_OFFSET_COACH+SG_CPTDINT:  if (!isset($where)) {$o = $c; $where = "f_coach_id = $o->coach_id";}
            case SG_OFFSET_TEAM+SG_CPTDINT:   if (!isset($where)) {$o = $t; $where = "f_team_id = $o->team_id";}
                
                $queries = array();
                foreach (range(0, SG_MULTIBAR_HIST_LENGTH) as $i) {
                    $range = "(
                        (YEAR(date_played) = YEAR(SUBDATE(DATE(NOW()), INTERVAL $i MONTH))) 
                        AND 
                        (MONTH(date_played) = MONTH(SUBDATE(DATE(NOW()), INTERVAL $i MONTH)))
                    )";
                    # m$i = minus/negative $i months from present month.
                    array_push($queries, "SUM(IF($range, cp, 0))     AS 'cp_m$i'"); 
                    array_push($queries, "SUM(IF($range, td, 0))     AS 'td_m$i'"); 
                    array_push($queries, "SUM(IF($range, intcpt, 0)) AS 'int_m$i'"); 
                    array_push($queries, "YEAR(SUBDATE(DATE(NOW()), INTERVAL $i MONTH)) AS 'yr_m$i'");
                    array_push($queries, "MONTH(SUBDATE(DATE(NOW()), INTERVAL $i MONTH)) AS 'mn_m$i'");
                }

                $query  = "SELECT ".implode(', ', $queries)." FROM matches, match_data WHERE f_match_id = match_id AND $where";
                $result = mysql_query($query);
                $row    = mysql_fetch_assoc($result);
                
                $lengends = array('cp' => 'green', 'td' => 'red', 'int' => 'blue');
                $datasets = array();
                $labels   = array();
                
                foreach (array_keys($lengends) as $key) {
                    $ds = array();
                    foreach (range(0, SG_MULTIBAR_HIST_LENGTH) as $i) {
                        $ds[] = $row["${key}_m$i"];
                    }
                    $datasets[$key] = $ds;
                }
                foreach (range(0, SG_MULTIBAR_HIST_LENGTH) as $i) {
                    $labels[] = $row["yr_m$i"].'/'.$row["mn_m$i"];
                }
               
                SGraph::mbars($datasets, $labels, $lengends, "CP, TD and Int history of $o->name", "Months", "Amount");
                
                break;

            /********************
             *  Won, lost and draw
             ********************/
            
            case SG_OFFSET_PLAYER+SG_WLD:   if (!isset($where)) {$o = $p; $where = "f_player_id = $o->team_id";}
            case SG_OFFSET_COACH+SG_WLD:    if (!isset($where)) {$o = $c; $where = "f_coach_id = $o->team_id";}
            case SG_OFFSET_TEAM+SG_WLD:     if (!isset($where)) {$o = $t; $where = "f_team_id = $o->team_id";}
                
                                
                $queries = array();
                foreach (range(0, SG_MULTIBAR_HIST_LENGTH) as $i) {
                    $range = "(
                        (YEAR(date_played) = YEAR(SUBDATE(DATE(NOW()), INTERVAL $i MONTH))) 
                        AND 
                        (MONTH(date_played) = MONTH(SUBDATE(DATE(NOW()), INTERVAL $i MONTH)))
                    )";
                    # m$i = minus/negative $i months from present month.
                    array_push($queries, "SUM(IF((team1_score > team2_score AND team1_id = $o->team_id OR team1_score < team2_score AND team2_id = $o->team_id) AND $range, 1, 0)) AS 'w_m$i'"); 
                    array_push($queries, "SUM(IF((team1_score < team2_score AND team1_id = $o->team_id OR team1_score > team2_score AND team2_id = $o->team_id) AND $range, 1, 0)) AS 'l_m$i'");
                    array_push($queries, "SUM(IF(team1_score = team2_score AND $range, 1, 0)) AS 'd_m$i'");
                    array_push($queries, "YEAR(SUBDATE(DATE(NOW()), INTERVAL $i MONTH)) AS 'yr_m$i'");
                    array_push($queries, "MONTH(SUBDATE(DATE(NOW()), INTERVAL $i MONTH)) AS 'mn_m$i'");
                }

                $query  = "SELECT ".implode(', ', $queries)." FROM matches WHERE team1_id = $o->team_id OR team2_id = $o->team_id";
                $result = mysql_query($query);
                $row    = mysql_fetch_assoc($result);
                
                /*
                    Plotting
                */
                
                $graph = new Graph(800,600,"auto");    
                $graph->SetScale("textint");
                $graph->SetShadow();
                $graph->img->SetMargin(40,30,20,40);
                
                // Load data
                $indicies = array('w' => 'Won', 'l' => 'Lost', 'd' => 'Draw', 'xlabels' => 'UNUSED');
                $dsets = array();
                foreach (array_keys($indicies) as $idx) {
                    $ds = array();
                    foreach (range(0, SG_MULTIBAR_HIST_LENGTH) as $i) {
                        $ds[] = ($idx == 'xlabels') ? ($row["yr_m$i"].'/'.$row["mn_m$i"])  : $row["${idx}_m$i"];
                    }
                    $dsets[$idx] = $ds;
                }
    
                $graph->xaxis->SetTickLabels($dsets['xlabels']);

                // Create the bar plots
                $bplots = array();
                foreach (array('w' => 'green', 'l' => 'red', 'd' => 'blue') as $idx => $color) {
                    $bplot = new BarPlot($dsets[$idx]);
                    $bplot->SetFillColor($color);
                    $bplot->value->Show();
                    $bplot->value->SetFormat('%d');
                    $bplot->SetValuePos('center');
                    $bplot->value->SetFont(FF_FONT1,FS_BOLD);
                    $bplot->value->SetColor('white');
                    $bplot->SetLegend($indicies[$idx]);
                    $bplots[] = $bplot;
                }

                // Create the grouped bar plot
                $gbplot = new GroupBarPlot($bplots);
                $graph->Add($gbplot);
                $graph->title->Set("Won, lost and draw history of $o->name");
                $graph->xaxis->title->Set("Months");
                $graph->yaxis->title->Set("Matches");
                $graph->Stroke();
                
                break;
        }

        return;
    }
    
    private static function mbars($datasets, $labels, $lengends, $title, $xlabel, $ylabel) 
    {
        /*
            Types:
                datasets: array('KEY1' => array(1,2,3,4),   'KEY2' => array(1,2,3,4),   ...)
                lengends: array('KEY1' => 'color1',         'KEY2' => 'color2',         ...)
                labels:   array('Jan', '02', 'whatever', ...)
        */
        
        // Ready graph object.
        $graph = new Graph(800,600,"auto");    
        $graph->SetScale("textint");
        $graph->SetShadow();
        $graph->img->SetMargin(40,30,20,40);
        $graph->xaxis->SetTickLabels($labels);

        // Create the bar plots
        $bplots = array();
        foreach ($lengends as $key => $color) {
            $bplot = new BarPlot($datasets[$key]);
            $bplot->SetFillColor($color);
            $bplot->value->Show();
            $bplot->value->SetFormat('%d');
            $bplot->value->SetFont(FF_FONT1,FS_BOLD);
            $bplot->value->SetColor('white');
            $bplot->SetValuePos('center');
            $bplot->SetLegend($key);
            $bplots[] = $bplot;
        }

        // Create the grouped bar plot
        $plot = new GroupBarPlot($bplots);
        $graph->Add($plot);
        $graph->title->Set($title);
        $graph->xaxis->title->Set($xlabel);
        $graph->yaxis->title->Set($ylabel);
        return $graph->Stroke();
    }
}
 
 ?>
