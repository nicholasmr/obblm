<?php

/*
 *  Copyright (c) Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2008. All Rights Reserved.
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

session_start();
error_reporting(E_ALL);

require('header.php'); // Includes and constants.
require('lib/class_statsgraph.php');

$conn = mysql_up(false);

if (!isset($_GET['type'])) {
    fatal("Sorry. Don't know what to do. Please specify 'type' via GET.");
}
    
switch ($_GET['type'])
{        
    /***************
     *  GD-bracket
     ***************/
    case 'gdbracket':
        if (!isset($_GET['tour_id']) || !is_numeric($_GET['tour_id']) || !is_object($t = new Tour($_GET['tour_id']))) {
            fatal("Sorry, invalid tournament ID.");
        }

        // Make the K.O. bracket ready.
        $t->update();

        if (get_class($t->koObj) != 'KnockoutGD') {
            fatal("Sorry. GD-lib support is required to draw tournament bracket.");
        }

        // Create team ID <--> Name translation
        $dictionary = array();
        $query = "SELECT team_id, name FROM teams";
        $result = mysql_query($query);
        while ($row = mysql_fetch_assoc($result))
            $dictionary[$row['team_id']] = $row['name'];

        // Install translation.
        $t->koObj->renameCompets($dictionary);

        // Draw image.
        $im = $t->koObj->getImage($settings['site_name']);
        header('Content-type: image/png');
        imagepng($im);
        imagedestroy($im);
        break;

    /***************
     *  PDF-roster
     ***************/
    case 'roster':
        if (class_exists('FPDF') && class_exists('BB_PDF')) {
            fpdf_roster();
        }
        else {
            fatal("Sorry. FPDF support is required for this feature to work.");        
        }
        break;

    /***************
     *  RSS feed
     ***************/        
    case 'rss':
        global $settings;
        $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : ""; 
        $matches = array();
        preg_match('/(\w*)/', strtolower($_SERVER["SERVER_PROTOCOL"]), $matches); 
        $protocol = $matches[0].$s;
        $rss = new OBBLMRssWriter(
            $settings['site_name'].' feed', 
            $protocol."://".$_SERVER['SERVER_NAME'].dirname($_SERVER['REQUEST_URI']), 
            'Blood bowl league RSS feed',
            'en-EN', 
            explode(',', RSS_FEEDS)
        );
        echo $rss->generateNewsRssFeed();
        break;
        
    /***************
     *  Match gallery
     ***************/
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

    /***************
     *  Visual stats
     ***************/
    case 'graph':
        SGraph::make($_GET['gtype'], $_GET['id'], false);
                
        break;
        
    /***************
     *  Inducements
     ***************/
    case 'inducements':
        {
        include('inducements.php'); // Daniel's try-out page.
        }
        break;

    /***************
     *  Team XML export
     ***************/
    case 'xmlexport':
        $t = new Team($_GET['tid']);
        echo $t->xmlExport();
        break;

    /***************
     *	BOTOCS match import
     ***************/
	case 'leegmgr':
		{
	    require_once ('leegmgr/uploadinc.php');
		}
	    break;

    default:
        fatal("Sorry. I don't know what the type '$_GET[type]' means.\n");
}

mysql_close($conn); 

?>
