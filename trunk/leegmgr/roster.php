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

Print "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
Print "<?xml-stylesheet type=\"text/xsl\" href=\"http://ate.stuntyleeg.com/leegmgr/team.xsl\"?>\n";
Print "<team>\n";
Print "<name>".$team->name."</name>\n";
Print "<race>".$team->race."</race>\n";
Print "<coach>".$team->coach_name."</coach>\n";
Print "<rerolls>".$team->rerolls."</rerolls>\n";
Print "<fanfactor>".$team->fan_factor."</fanfactor>\n";
Print "<assistants>".$team->ass_coaches."</assistants>\n";
Print "<cheerleaders>".$team->cheerleaders."</cheerleaders>\n";
Print "<apothecary>".$apothecary."</apothecary>\n";
Print "<treasury>".$team->treasury."</treasury>\n";
Print "<players>\n";

$players = $team->getPlayers();

foreach ( $players as $p )
{

	$player = new Player( $p->player_id );
	$skills = $player->getSkillsStr();
	$a_skills = explode(', ', $skills);
	
		Print "<player number=\"".$player->nr."\">\n";
		Print "<name>".$player->name."</name>\n";
		Print "<position>".$player->position."</position>\n";
		Print "<ma>".$ma = $player->ma."</ma>\n";
		Print "<st>".$player->st."</st>\n";
		Print "<ag>".$player->ag."</ag>\n";
		Print "<av>".$player->av."</av>\n";
			Print "<skills>\n";

				$i = 0;

				while ( $i < count( $a_skills ) )
				{

					Print "<skill>".$a_skills[$i]."</skill>\n";
					$i++;

				}

			Print "</skills>\n";
		Print "<value>".$player->value."</value>\n";
		Print "</player>\n";

}

Print "</players>\n";
Print "</team>\n";

?>