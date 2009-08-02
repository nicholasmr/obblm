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

require('header.php'); // Includes and constants.

if (!isset($_GET['type']))
    fatal("Sorry. Don't know what to do. Please specify 'type' via GET.");
    
switch ($_GET['type'])
{        
    /* PDF-roster */
    case 'roster':
        Module::run('pdfroster', array());
        break;

    /* RSS feed */
    case 'rss':
        Module::run('rss', array());
        break;
        
    /* Visual stats */
    case 'graph':
        Module::run('statsgraph', array($_GET['gtype'], $_GET['id'], false));
        break;
        
    /* Inducements */
    case 'inducements':
        Module::run('inducements', array());
        break;

    /* BOTOCS match import */
    case 'leegmgr':
        Module::run('leegmgr', array());
        break;

    /* Team BOTOCS XML export */
    case 'botocsxml':
        Module::run('botocsxml', array());
        break;

    /* Team XML export */
    case 'xmlexport':
        Module::run('teamxmlexport', array($_GET['tid']));
        break;
        
    /* Mem. matches */
   	case 'memmatches':
   		Module::run('memmatches', array());
   		break;

    /* Comparison */
   	case 'comparison':
   		Module::run('comparison', array());
   		break;
   		
    /* Register */
   	case 'registration':
   		Module::run('registration', array());
   		break;
   		
    /* Match gallery */
    case 'mg':
    
        if (!isset($_GET['mid']) || !is_numeric($_GET['mid']) || !is_object($m = new Match($_GET['mid']))) {
            fatal("Sorry, invalid match ID.");
        }
        $curPic = (isset($_GET['pic'])) ? (int) $_GET['pic']-1 : 0;
        $pics = $m->getPics();
        if (empty($pics)) {
            fatal("Sorry. The requested match has no uploaded pictures.");
        }
        echo "<b>Photos from match: <i>$m->team1_name</i> $m->team1_score - $m->team2_score <i>$m->team2_name</i></b><br><br>\n";
        echo "<center>\n";
        $i = 1;
        foreach ($pics as $p) {
            echo "<a href='handler.php?type=mg&amp;mid=$_GET[mid]&amp;pic=$i'>[$i]</a>&nbsp;&nbsp;";
            $i++;
        }
        echo "</center>\n";
        echo "<br><br>\n";
        echo "<img src='".$pics[$curPic]."'>\n";
                
        break;

    default:
        fatal("Sorry. I don't know what the type '$_GET[type]' means.\n");
}

mysql_close($conn); 

?>
