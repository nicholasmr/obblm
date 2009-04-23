<?php

/*
 *  Copyright (c) Daniel Straalman <email is protected> 2008-2009. All Rights Reserved.
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
 * Note: XXXXXX
 */

require_once('../lib/game_data.php');
require_once('../settings.php');

mysql_connect($db_host, $db_user, $db_passwd) or die(mysql_error()); 
mysql_select_db($db_name) or die(mysql_error());

$team_id = $_GET["teamid"];

$querystring = "SELECT * FROM `teams` WHERE team_id=".$team_id;
$Teamdata = mysql_query($querystring) or die(mysql_error());
$Teamdata = mysql_fetch_array($Teamdata);
$TeamName = $Teamdata['name'];
$TeamRace = $Teamdata['race'];
$TeamCoach = $Teamdata['owned_by_coach_id'];
$apothecary = $Teamdata['apothecary'];

	if ( $apothecary == "1" ) {
		$apothecary = "Yes";
	}
	if ( $apothecary == "0" ) {
		$apothecary = "No";
	}

$rerollcost = $DEA[$TeamRace]['other']['RerollCost'];


$querystring = "SELECT name FROM `coaches` WHERE coach_id=".$TeamCoach;

$TeamCoach = mysql_query($querystring);
$TeamCoach = mysql_fetch_array($TeamCoach);
$TeamCoach = $TeamCoach['name'];

$querystring = "SELECT * FROM `players` WHERE owned_by_team_id=".$team_id." and date_sold is NULL";
$data = mysql_query($querystring) or die(mysql_error());


#Start adding default stats to achieved stats

#$info = mysql_fetch_array( $data );
#Print $DEA[$TeamRace]['players'][Acrobat]['ma']+$info['ach_ma'];

Print "<!-- JavaBBowl Options: +LRB -semi -final +EXPOFAB -HC -ot +TL +TX10 -->\n";
Print "<html><head><title>".$Teamdata['name']."</title></head>\n";
Print "<body bgcolor='#ffffff' text='#000000' link='#000000' vlink='#000000'>\n";
Print "<div style=\"text-align: center;\">\n";
Print "<span style=\"font-family: arial, sans-serif; text-align: center; font-size: x-large; font-weight: bold;\">".$Teamdata['name']."</span><br />\n";
Print "Race: ".$Teamdata['race']."<br />\n";
Print "Coached By: ".$TeamCoach."<br />\n";
Print "<TABLE align='center' border='1' bgcolor='#d7d7ff' bordercolorlight='f7f7ff' bordercolordark='9797bf'>\n";
Print "<TR bgcolor='#c6c6ff'><TH>#</TH><TH>Player Name</TH><TH>Position</TH><TH>Ma</TH><TH>St</TH><TH>Ag</TH><TH>Av</TH><TH>Player Skills</TH><TH>Inj</TH><TH>Cp</TH><TH>TD</TH><TH>Int</TH><TH>Cas</TH><TH>Mvp</TH><TH>SPP</TH><TH>Cost</TH></TR>\n";
$loop = 0;
while($info = mysql_fetch_array( $data )) 
{
$position = $info['position'];
$ma = $DEA[$TeamRace]['players'][$position]['ma']+$info['ach_ma'];
$st = $DEA[$TeamRace]['players'][$position]['st']+$info['ach_st'];
$ag = $DEA[$TeamRace]['players'][$position]['ag']+$info['ach_ag'];
$av = $DEA[$TeamRace]['players'][$position]['av']+$info['ach_av'];
$cost = $DEA[$TeamRace]['players'][$position]['cost'];
	#parse default skills
	$defaultskills = implode(", ",$DEA[$TeamRace]['players'][$position]['Def skills']);
	#end parse default skills

$sepnor=NULL;
$sepdob=NULL;

	if (strlen($info['ach_nor_skills'])>1){
		$sepnor=", ";
	}
	if (strlen($info['ach_dob_skills'])>1){
		$sepdob=", ";
	}
	$q_matchdata = "SELECT * FROM `match_data` WHERE `f_player_id` =".$info['player_id'];
	$r_matchdata = mysql_query($q_matchdata) or die(mysql_error());
	$i_injury = 0;
	$injury = "";
	while($playermatchdata = mysql_fetch_array( $r_matchdata ))
	{
		#$i_injury ++;
		$inj_comma = "";
		#if ($i_injury>1) $inj_comma = ", ";
		$playermatchinjury = "";
		switch ($playermatchdata['inj']) {

 			case 1:
		        	$playermatchinjury = "";
				$i_injury = "";
			        break;
			case 2:
				if ( strlen($injury) ) $inj_comma = ", ";
			        $playermatchinjury = "m";
				if ( $playermatchinjury == "m" ) {
				
					$q_mng = "SELECT f_match_id FROM `match_data` WHERE `f_player_id` = ".$playermatchdata['f_player_id']." ORDER BY f_match_id DESC";
					$r_mng = mysql_query($q_mng) or die(mysql_error());
					$a_mng = mysql_fetch_array( $r_mng );
					if ( $a_mng[f_match_id] > $playermatchdata[f_match_id] ) $playermatchinjury = "";

				}
				$injury = $injury.$inj_comma.$playermatchinjury;
			        break;
			case 3:
				if ( strlen($injury) ) $inj_comma = ", ";
			        $playermatchinjury = "n";
				$injury = $injury.$inj_comma.$playermatchinjury;
			        break;
	 		case 4:
				if ( strlen($injury) ) $inj_comma = ", ";
			        $playermatchinjury = "-ma";
				$injury = $injury.$inj_comma.$playermatchinjury;
			        break;
			case 5:
				if ( strlen($injury) ) $inj_comma = ", ";
			        $playermatchinjury = "-av";
				$injury = $injury.$inj_comma.$playermatchinjury;
			        break;
			case 6:
				if ( strlen($injury) ) $inj_comma = ", ";
			        $playermatchinjury = "-ag";
				$injury = $injury.$inj_comma.$playermatchinjury;
			        break;
			case 7:
				if ( strlen($injury) ) $inj_comma = ", ";
			        $playermatchinjury = "-st";
				$injury = $injury.$inj_comma.$playermatchinjury;
			        break;
			case 8:
				if ( strlen($injury) ) $inj_comma = ", ";
			        $playermatchinjury = "DEAD";
				$injury = $injury.$inj_comma.$playermatchinjury;
			        break;
		}
		switch ($playermatchdata['inj']) {
			case ( $playermatchdata['inj'] > 2 && $playermatchdata['inj'] < 8 ):
				#if ( $playermatchinjury == "m" ) {
				
					$q_n = "SELECT f_match_id FROM `match_data` WHERE `f_player_id` = ".$playermatchdata['f_player_id']." ORDER BY f_match_id DESC";
					$r_n = mysql_query($q_n) or die(mysql_error());
					$a_n1 = mysql_fetch_array( $r_n );
					#$a_n2 = mysql_fetch_array( $r_n );
					if ( $a_n1[f_match_id] == $playermatchdata[f_match_id] ) $injury = $injury.", m";

				#}
				break;
		}

	}

$skills = $defaultskills.$sepnor.$info['ach_nor_skills'].$sepdob.$info['ach_dob_skills'];
	if (strpos($injury, "DEAD")!="FALSE"){
		Print "<TR align='center'><TD ALIGN=LEFT>".$info['nr']."</TD><TD ALIGN=LEFT>".$info['name']."</TD><TD ALIGN=LEFT>".$info['position']."</TD><TD>".$ma."</TD><TD>".$st."</TD><TD>".$ag."</TD><TD>".$av."</TD><TD ALIGN=LEFT>".$skills."</TD><TD ALIGN=LEFT>".$injury."</TD><TD>"."0"."</TD><TD>"."0"."</TD><TD>"."0"."</TD><TD>"."0"."</TD><TD>"."0"."</TD><TD>"."0"."</TD><TD ALIGN=RIGHT>".$cost."</TD></TR>\n";
		$loop++;
	}
}

while($loop < 16){
$loop = $loop + 1;
Print "<TR align='center'><TD ALIGN=LEFT>"."&nbsp"."</TD><TD ALIGN=LEFT>"."&nbsp"."</TD><TD ALIGN=LEFT>"."&nbsp"."</TD><TD>"."&nbsp"."</TD><TD>"."&nbsp"."</TD><TD>"."&nbsp"."</TD><TD>"."&nbsp"."</TD><TD ALIGN=LEFT>"."&nbsp"."</TD><TD ALIGN=LEFT>"."&nbsp"."</TD><TD>"."&nbsp"."</TD><TD>"."&nbsp"."</TD><TD>"."&nbsp"."</TD><TD>"."&nbsp"."</TD><TD>"."&nbsp"."</TD><TD>"."&nbsp"."</TD><TD ALIGN=RIGHT>"."&nbsp"."</TD></TR>\n";
}

Print "<TR><TD colspan='16'>"."&nbsp"."</TD></TR>\n";
Print "<TR align='center'><TD align='right' bgcolor='#c6c6ff' colspan='2'>Team Name:</TD><TD colspan='5'>".$Teamdata['name']."</TD><TD align='right' bgcolor='c6c6ff' colspan='2'>Re-Rolls:</TD><TD colspan='7'>".$Teamdata['rerolls']."</TD></TR>\n";
Print "<TR align='center'><TD align='right' bgcolor='#c6c6ff' colspan='2'>Race:</TD><TD colspan='5'>".$Teamdata['race']."</TD><TD align='right' bgcolor='c6c6ff' colspan='2'>Fan Factor:</TD><TD colspan='7'>".$Teamdata['fan_factor']."</TD></TR>\n";
Print "<TR align='center'><TD align='right' bgcolor='#c6c6ff' colspan='2'>Team Rating:</TD><TD colspan='5'>100</TD><TD align='right' bgcolor='c6c6ff' colspan='2'>Assistant Coaches:</TD><TD colspan='7'>".$Teamdata['ass_coaches']."</TD></TR>\n";
Print "<TR align='center'><TD align='right' bgcolor='#c6c6ff' colspan='2'>Treasury:</TD><TD colspan='5'>".$Teamdata['treasury']."</TD><TD align='right' bgcolor='c6c6ff' colspan='2'>Cheerleaders:</TD><TD colspan='7'>".$Teamdata['cheerleaders']."</TD></TR>\n";
Print "<TR align='center'><TD align='right' bgcolor='#c6c6ff' colspan='2'>Coach:</TD><TD colspan='5'>".$TeamCoach."</TD><TD align='right' bgcolor='c6c6ff' colspan='2'>Apothecary:</TD><TD colspan='7'>".$apothecary."</TD></TR>\n";
Print "<TR align='center'><TD align='right' bgcolor='#c6c6ff' colspan='2'>&nbsp;</TD><TD colspan='5'>&nbsp;</TD><TD align='right' bgcolor='c6c6ff' colspan='2'>Team Wizard:</TD><TD colspan='7'>No</TD></TR>\n";
Print "</table>\n";
Print "</body></html>\n";


?>