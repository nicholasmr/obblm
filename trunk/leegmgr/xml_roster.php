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
 * Note: XXXXX
 */
require_once('../lib/game_data.php');
require_once('../settings.php');

mysql_connect($db_host, $db_user, $db_passwd) or die(mysql_error()); 
mysql_select_db($db_name) or die(mysql_error());

$team_id = 1;
$team_id = $_GET["teamid"];

$querystring = "SELECT * FROM `teams` WHERE team_id=".$team_id;
$Teamdata = mysql_query($querystring) or die(mysql_error());
$Teamdata = mysql_fetch_array($Teamdata);
$TeamName = $Teamdata['name'];
$TeamRace = $Teamdata['race'];
$TeamCoach = $Teamdata['owned_by_coach_id'];
$apothecary = $Teamdata['apothecary'];

	if ( $apothecary == "1" ) {
		$apothecary = "true";
	}
	if ( $apothecary == "0" ) {
		$apothecary = "false";
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

Print "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
Print "<?xml-stylesheet type=\"text/xsl\" href=\"http://ate.stuntyleeg.com/leegmgr/team.xsl\"?>\n";
Print "<team>\n";
Print "<name>".$TeamName."</name>\n";
Print "<race>".$TeamRace."</race>\n";
Print "<coach>".$TeamCoach."</coach>\n";
Print "<rerolls>".$Teamdata[rerolls]."</rerolls>\n";
Print "<fanfactor>".$Teamdata[fan_factor]."</fanfactor>\n";
Print "<assistants>".$Teamdata[ass_coaches]."</assistants>\n";
Print "<cheerleaders>".$Teamdata[cheerleaders]."</cheerleaders>\n";
Print "<apothecary>".$apothecary."</apothecary>\n";
Print "<treasury>".$Teamdata[treasury]."</treasury>\n";
Print "<players>\n";
$loop = 0;
while($info = mysql_fetch_array( $data )) 
{
#$loop = $loop + 1;
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
	$skills_r = explode ( ', ', $skills);

	if (strpos($injury, "DEAD")!="FALSE"){

		Print "<player number=\"".$info['nr']."\">\n";
		Print "<name>".$info['name']."</name>\n";
		Print "<position>".$info['position']."</position>\n";
		Print "<ma>".$ma."</ma>\n";
		Print "<st>".$st."</st>\n";
		Print "<ag>".$ag."</ag>\n";
		Print "<av>".$av."</av>\n";
#$skills
		Print "<skills>\n";
		$s_i = 0;
		while ( $s_i < count($skills_r) && strlen($skills_r[$s_i]) > 0 )
		{
			Print "<skill>".$skills_r[$s_i]."</skill>";
			$s_i = $s_i + 1;
		}
		Print "</skills>\n";
#$injury
		Print "<value>".$cost."</value>\n";
		Print "</player>\n";

		$loop++;
	}
}

Print "</players>\n";
Print "</team>\n";


?>