<?php

/*
 *  Copyright (c) William Leonard <email protected> 2009. All Rights Reserved.
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

// Registered module main function.
function botocsxml_load() 
{

$noninjplayercount = 0;
$team = new Team ( $_GET["teamid"] );

$apothecary = $team->apothecary;

	if ( $apothecary == "1" ) {
		$apothecary = "true";
	}
	if ( $apothecary == "0" ) {
		$apothecary = "false";
	}

$players = $team->getPlayers();

if ( !checkJourneymen ( $players ) ) die('');

Print "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<?xml-stylesheet type=\"text/xsl\" href=\"/modules/leegmgr/team.xsl\"?>
<team>
    <name>{$team->name}</name>
    <race>{$team->race}</race>
    <coach>{$team->coach_name}</coach>
    <rerolls>{$team->rerolls}</rerolls>
    <fanfactor>{$team->fan_factor}</fanfactor>
    <assistants>{$team->ass_coaches}</assistants>
    <cheerleaders>{$team->cheerleaders}</cheerleaders>
    <apothecary>{$apothecary}</apothecary>
    <treasury>{$team->treasury}</treasury>
    <players>\n";

foreach ( $players as $p )
{

	if ( !$p->is_dead && !$p->is_sold )
	{
	$skills = $p->getSkillsStr();
	$a_skills = explode(', ', $skills);

		Print "        <player number=\"{$p->nr}\">
            <name>{$p->name}</name>
            <position>{$p->pos}</position>
            <ma>{$p->ma}</ma>
            <st>{$p->st}</st>
            <ag>{$p->ag}</ag>
            <av>{$p->av}</av>
            <skills>\n";

				$i = 0;

				while ( $i < count( $a_skills ) && strlen( $a_skills[0] ) > 0 )
				{

					Print "                <skill>".$a_skills[$i]."</skill>\n";
					$i++;

				}
	$injured = "false";
	if ( $p->is_mng ) $injured = "true";
			Print "            </skills>
            <spp>{$p->spp}</spp>
            <nigglings>{$p->inj_ni}</nigglings>
            <injured>$injured</injured>
            <value>{$p->value}</value>
        </player>\n";
	}

}

Print "    </players>
</team>\n";
}

function checkJourneymen ( $players )
{

	foreach ( $players as $p )
	{
		global $noninjplayercount;
		if ( !$p->is_dead && !$p->is_sold && $p->is_journeyman && $p->spp > 0 )
		{

			Print "You have a journeyman with star players points.  Please hire or fire him.";
			return false;

		}

		if ( !$p->is_dead && !$p->is_sold && !$p->is_mng ) $noninjplayercount ++;

	}
	
	$jm = 0;
	if ( isset( $_GET["jm"] ) ) $jm = $_GET["jm"];
	global $noninjplayercount;

	if ( !$jm && $noninjplayercount < 11 )
	{

		Print "You may have forgotten to hire a journeyman for the next match.  Please hire a player as a journeyman or hire a player.<br>";
		$rosterjm = curPageURL();
		Print "If you want to ignore this error, use the following for your roster:<br><b>{$rosterjm}</b>";
		return false;

	}

	return true;

}

function curPageURL() {

	$pageURL = 'http';
	$pageURL .= "://";
	$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	$pageURL .= "&jm=1";
	return $pageURL;

}


?>
