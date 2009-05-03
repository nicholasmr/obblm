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
		$apothecary = "Yes";
	}
	if ( $apothecary == "0" ) {
		$apothecary = "No";
	}

Print "<!-- JavaBBowl Options: +LRB -semi -final +EXPOFAB +ot  -->
<html><head><title>{$team->name}</title></head>
<body bgcolor=\"#ffffff\" text=\"#000000\" link=\"#000000\" vlink=\"#000000\">
<div style=\"text-align: center;\">
<span style=\"font-family: arial, sans-serif; text-align: center; font-size: x-large; font-weight: bold;\">{$team->name}</span><br />
Race: {$team->race}<br />
Coached By: {$team->coach_name}<br />
<TABLE align=\"center\" border=\"1\" bgcolor=\"#d7d7ff\" bordercolorlight=\"#f7f7ff\" bordercolordark=\"#9797bf\">
<TR bgcolor=\"#c6c6ff\"><TH>#</TH><TH>Player Name</TH><TH>Position</TH><TH>Ma</TH><TH>St</TH><TH>Ag</TH><TH>Av</TH><TH>Player Skills</TH><TH>Inj</TH><TH>Cp</TH><TH>TD</TH><TH>Int</TH><TH>Cas</TH><TH>Mvp</TH><TH>SPP</TH><TH>Cost</TH></TR>";


$players = $team->getPlayers();
$loop = 0;
foreach ( $players as $p )
{

	if ( !$p->is_dead && !$p->is_sold )
	{
		#$player = new Player( $p->player_id );
		Print "<TR align=\"center\"><TD ALIGN=LEFT>{$p->nr}</TD><TD ALIGN=LEFT>{$p->name}</TD><TD ALIGN=LEFT>{$p->position}</TD><TD>{$p->ma}</TD><TD>{$p->st}</TD><TD>{$p->ag}</TD><TD>{$p->av}</TD><TD ALIGN=LEFT>{$p->getSkillsStr()}</TD><TD ALIGN=LEFT>{$p->getInjsStr()}</TD><TD>0</TD><TD>0</TD><TD>0</TD><TD>0</TD><TD>0</TD><TD>0</TD><TD ALIGN=RIGHT>{$p->value}</TD></TR>";
		$loop ++;
	}

}
while($loop < 16){
$loop ++;
Print "<TR align='center'><TD ALIGN=LEFT> </TD><TD ALIGN=LEFT> </TD><TD ALIGN=LEFT> </TD><TD> </TD><TD> </TD><TD> </TD><TD> </TD><TD ALIGN=LEFT> </TD><TD ALIGN=LEFT> </TD><TD> </TD><TD> </TD><TD> </TD><TD> </TD><TD> </TD><TD> </TD><TD ALIGN=RIGHT> </TD></TR>\n";
}

Print "<TR><TD colspan=\"16\">&nbsp;</TD></TR>
<TR align=\"center\"><TD align=\"right\" bgcolor=\"#c6c6ff\" colspan=\"2\">Team Name:</TD><TD colspan=\"5\">{$team->name}</TD><TD align=\"right\" bgcolor=\"c6c6ff\" colspan=\"2\">Re-Rolls:</TD><TD colspan=\"7\">{$team->rerolls}</TD></TR>
<TR align=\"center\"><TD align=\"right\" bgcolor=\"#c6c6ff\" colspan=\"2\">Race:</TD><TD colspan=\"5\">{$team->race}</TD><TD align=\"right\" bgcolor=\"c6c6ff\" colspan=\"2\">Fan Factor:</TD><TD colspan=\"7\">{$team->fan_factor}</TD></TR>
<TR align=\"center\"><TD align=\"right\" bgcolor=\"#c6c6ff\" colspan=\"2\">Team Rating:</TD><TD colspan=\"5\">100</TD><TD align=\"right\" bgcolor=\"c6c6ff\" colspan=\"2\">Assistant Coaches:</TD><TD colspan=\"7\">{$team->ass_coaches}</TD></TR>
<TR align=\"center\"><TD align=\"right\" bgcolor=\"#c6c6ff\" colspan=\"2\">Treasury:</TD><TD colspan=\"5\">{$team->treasury}</TD><TD align=\"right\" bgcolor=\"c6c6ff\" colspan=\"2\">Cheerleaders:</TD><TD colspan=\"7\">{$team->cheerleaders}</TD></TR>
<TR align=\"center\"><TD align=\"right\" bgcolor=\"#c6c6ff\" colspan=\"2\">Coach:</TD><TD colspan=\"5\">{$team->coach_name}</TD><TD align=\"right\" bgcolor=\"c6c6ff\" colspan=\"2\">Apothecary:</TD><TD colspan=\"7\">{$apothecary}</TD></TR>
<TR align=\"center\"><TD align=\"right\" bgcolor=\"#c6c6ff\" colspan=\"2\">&nbsp;</TD><TD colspan=\"5\">&nbsp;</TD><TD align=\"right\" bgcolor=\"c6c6ff\" colspan=\"2\">Team Wizard:</TD><TD colspan=\"7\">No</TD></TR>
</table>
</body></html>\n";

Print_r ($p);

?>