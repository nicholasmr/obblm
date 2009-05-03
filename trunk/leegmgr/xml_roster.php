<?php

/*
 *
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
 * Author William Leonard, 2009
 *
 * Note: XXXXX
 */

require_once('../header.php');

$conn = mysql_up();

$team = new Team ( $_GET["teamid"] );

$apothecary = $team->apothecary;

	if ( $apothecary == "1" ) {
		$apothecary = "true";
	}
	if ( $apothecary == "0" ) {
		$apothecary = "false";
	}

Print "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<?xml-stylesheet type=\"text/xsl\" href=\"team.xsl\"?>
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

$players = $team->getPlayers();

foreach ( $players as $p )
{

	if ( !$p->is_dead && !$p->is_sold )
	{
	$skills = $p->getSkillsStr();
	$a_skills = explode(', ', $skills);
	
		Print "        <player number=\"{$p->nr}\">
            <name>{$p->name}</name>
            <position>{$p->position}</position>
            <ma>{$p->ma}</ma>
            <st>{$p->st}</st>
            <ag>{$p->ag}</ag>
            <av>{$p->av}</av>
            <skills>\n";

				$i = 0;

				while ( $i < count( $a_skills ) )
				{

					Print "                <skill>".$a_skills[$i]."</skill>\n";
					$i++;

				}

			Print "            </skills>
            <value>{$p->value}</value>
        </player>\n";
	}

}

Print "    </players>
</team>\n";

?>