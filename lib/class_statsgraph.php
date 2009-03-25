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
 
include('Image/Graph.php');

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
#    SG_CAS      => 'Current CAS distribution',
#    SG_BHSIKI   => 'Last '.SG_MULTIBAR_HIST_LENGTH.' months casualty distribution',
#    SG_CPTDINT  => 'Last '.SG_MULTIBAR_HIST_LENGTH.' months Cp, Td and Int distribution',
    SG_WLD      => 'Last '.SG_MULTIBAR_HIST_LENGTH.' months won, lost and draw distribution',
);
 
class SGraph
{
    public static function make($type, $id)
    {
        $o = null;
        
        if ($type < SG_OFFSET_TEAM+SG_OFFSET_SIZE) {
            $t = new Team($_GET['id']);
        }
        if ($type < SG_OFFSET_COACH+SG_OFFSET_SIZE) {
            $c = new Coach($_GET['id']);
        }
        if ($type < SG_OFFSET_PLAYER+SG_OFFSET_SIZE) {
            $p = new Player($_GET['id']);
        }

        // Create the graph
        $Graph =& Image_Graph::factory('graph', array(800, 500));
        $Font =& $Graph->addNew('font', 'Verdana');
        $Font->setSize(8);
        $Graph->setFont($Font);
        
        switch ($_GET['gtype']) 
        {
            
            // Casulties:
            case SG_OFFSET_PLAYER+SG_CAS:
                if (!is_object($o)) {$o = $p;}
            case SG_OFFSET_COACH+SG_CAS:
                if (!is_object($o)) {$o = $c;}
            case SG_OFFSET_TEAM+SG_CAS:
                if (!is_object($o)) {$o = $t;}
                
                // create the plotarea
                $Graph->add(
                    Image_Graph::vertical(
                        Image_Graph::factory('title', array('Casualties by '.$o->name, 12)),
                        Image_Graph::horizontal(
                            $Plotarea = Image_Graph::factory('plotarea'),
                            $Legend = Image_Graph::factory('legend'),
                            70
                        ),
                        5
                    )
                );

                $Legend->setPlotarea($Plotarea);
                        
                // create the 1st dataset
                $Dataset1 =& Image_Graph::factory('dataset');
                $Dataset1->addPoint("BH ($o->bh)", $o->bh);
                $Dataset1->addPoint("SI ($o->si)", $o->si);
                $Dataset1->addPoint("Ki ($o->ki)", $o->ki);
                $Plot =& $Plotarea->addNew('pie', array(&$Dataset1));
                $Plotarea->hideAxis();

                $Marker =& $Plot->addNew('Image_Graph_Marker_Value', IMAGE_GRAPH_PCT_Y_TOTAL);
                $PointingMarker =& $Plot->addNew('Image_Graph_Marker_Pointing_Angular', array(20, &$Marker));
                $Plot->setMarker($PointingMarker);    
                $Marker->setDataPreprocessor(Image_Graph::factory('Image_Graph_DataPreprocessor_Formatted', '%0.1f%%'));

                $Plot->Radius = 2;

                // set a standard fill style
                $FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
                $Plot->setFillStyle($FillArray);
                $FillArray->addColor('green@1');
                $FillArray->addColor('blue@1');
                $FillArray->addColor('red@1');

                $Plot->explode(5);
                $Plot->setStartingAngle(90);
                
                break;
            
            case SG_OFFSET_PLAYER+SG_WLD:
#                if (!is_object($o)) {$o = $t;}
            case SG_OFFSET_COACH+SG_WLD:
#                if (!is_object($o)) {$o = $t;}
            case SG_OFFSET_TEAM+SG_WLD:
                if (!is_object($o)) {$o = $t;}
                
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
                
                $Graph->add(
                    Image_Graph::vertical(
                        Image_Graph::factory('title', array("Won, lost and draw history", 12)),        
                        Image_Graph::vertical(
                            $Plotarea = Image_Graph::factory('plotarea'),
                            $Legend = Image_Graph::factory('legend'),
                            90
                        ),
                        5
                    )
                );
                $Legend->setPlotarea($Plotarea);
                    
                $Datasets = array();
                foreach (array('w' => 'won', 'l' => 'lost', 'd' => 'draw') as $idx => $desc) {
                    $ds =& Image_Graph::factory('dataset');
                    $ds->setName($desc);
                    foreach (range(0, SG_MULTIBAR_HIST_LENGTH) as $i) {
                        $ds->addPoint($row["yr_m$i"].'/'.$row["mn_m$i"],  $row["${idx}_m$i"]);
                    }
                    array_push($Datasets, $ds);
                }
                $Plot =& $Plotarea->addNew('bar', array($Datasets));

                $Plot->setLineColor('gray');
                $FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
                $FillArray->addColor('blue@1');
                $FillArray->addColor('red@1');
                $FillArray->addColor('green@1');
                $Plot->setFillStyle($FillArray);
                $Marker =& $Plot->addNew('Image_Graph_Marker_Value', IMAGE_GRAPH_VALUE_Y);
                $PointingMarker =& $Plot->addNew('Image_Graph_Marker_Pointing_Angular', array(20, &$Marker));
                $Plot->setMarker($PointingMarker);     
    
                break;
        }

        $Graph->done(); 
        
        return;
    }
}
 
 ?>
