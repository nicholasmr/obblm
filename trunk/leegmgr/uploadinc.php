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
 * Note: XXXXXX
 */

error_reporting(E_ALL); 

require_once('../header.php');
require_once('class_match_botocs.php');

uploadpage();

function uploadpage () {

	Print "
	<!-- The data encoding type, enctype, MUST be specified as below -->
	<form enctype='multipart/form-data' action={$_SERVER['PHP_SELF']} method='POST'>
	<!-- MAX_FILE_SIZE must precede the file input field -->
	<input type='hidden' name='MAX_FILE_SIZE' value='30000' />
	<!-- Name of input element determines name in $_FILES array -->
	Send this file: <input name='userfile' type='file' />
	<input type='submit' value='Send File' />
	</form>";

	if (isset($_FILES['userfile'])) {
		$uploaddir = '/var/www/uploads/';
		$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);

		if (strlen($_FILES['userfile']['tmp_name'])>3)
		{
			$zip = zip_open($_FILES['userfile']['tmp_name']);
			Print "<br>Retrieved a file.<br>";
		}

		if ($zip  &&  $_FILES['userfile']['type'] == "application/x-zip-compressed") {
			Print "<br>Retrieved a zip file.<br>";

			while ($zip_entry = zip_read($zip)) {

				if (strpos(zip_entry_name($zip_entry),".xml") > 1 ) {
					Print "<br>Reading XML file from the zip file.<br>";
					$xmlresults = zip_entry_read($zip_entry, 10240);
					zip_entry_close($zip_entry);
				}

			}

			zip_close($zip);

			if ( isset($xmlresults) ) {
				Print "<br>Parsing the XML.<br>";
				parse_results($xmlresults);
			}

			else {
				Print "<br>The zip file does not contain the results xml file.<br>";
			}

		}

		else {
			Print "<br>You must upload a zip file with the results in it.<br>";
		}

	}

}

function parse_results($xmlresults) {

	$results =  simplexml_load_string( $xmlresults );

	$gate = $results->team[0]->fans + $results->team[1]->fans;
	$hash = ""; #placeholder

	$hometeam = $results->team[0]->attributes()->name;
	$homescore = $results->team[0]->score;
	$homewinnings = $results->team[0]->winnings;
	$homeff = $results->team[0]->fanfactor;
	$homefame = $results->team[0]->fame;

	foreach ( $results->team[0]->players->player as $player )
	{

		$homeplayers[intval($player->attributes()->number)]['nr'] = intval($player->attributes()->number);
		$homeplayers[intval($player->attributes()->number)]['mvp'] = $player->mvp;
		$homeplayers[intval($player->attributes()->number)]['cp'] = $player->completion;
		$homeplayers[intval($player->attributes()->number)]['td'] = $player->touchdown;
		$homeplayers[intval($player->attributes()->number)]['intcpt'] = $player->interception;
		$homeplayers[intval($player->attributes()->number)]['bh'] = $player->casualties;
		$homeplayers[intval($player->attributes()->number)]['inj'] = $player->injuries->injury;
		$homeplayers[intval($player->attributes()->number)]['agn1'] = $player->injuries->injury[1];

	}

	$awayteam = $results->team[1]->attributes()->name;
	$awayscore = $results->team[1]->score;
	$awaywinnings = $results->team[1]->winnings;
	$awayff = $results->team[1]->fanfactor;
	$awayfame = $results->team[1]->fame;
	
	foreach ( $results->team[1]->players->player as $player )
	{

		$awayplayers[intval($player->attributes()->number)]['nr'] = intval($player->attributes()->number);
		$awayplayers[intval($player->attributes()->number)]['mvp'] = $player->mvp;
		$awayplayers[intval($player->attributes()->number)]['cp'] = $player->completion;
		$awayplayers[intval($player->attributes()->number)]['td'] = $player->touchdown;
		$awayplayers[intval($player->attributes()->number)]['intcpt'] = $player->interception;
		$awayplayers[intval($player->attributes()->number)]['bh'] = $player->casualties;
		$awayplayers[intval($player->attributes()->number)]['inj'] = $player->injuries->injury[0];
		$awayplayers[intval($player->attributes()->number)]['agn1'] = $player->injuries->injury[1];

	}


	$hash = XOREncrypt ( $hometeam.$gate.$homescore.$homewinnings, $awayteam.$gate.$awayscore.$awaywinnings);

	$matchparsed = array ( "homeplayers" => $homeplayers, "awayplayers" => $awayplayers, "gate" => $gate, "hometeam" => $hometeam, "homescore" => $homescore, "homewinnings" => $homewinnings, "homeff" => $homeff, "homefame" => $homefame, "awayteam" => $awayteam, "awayscore" => $awayscore, "awaywinnings" => $awaywinnings, "awayff" => $awayff, "awayfame" => $awayfame, "hash" => $hash );

	report ( $matchparsed );

}

function report ( $matchparsed ) {

	$conn = mysql_up();

	$matchfields = addMatch ( $matchparsed );

	matchEntry ( $matchfields['hometeam_id'], $matchfields['match_id'], $matchparsed['homeplayers'] );

	matchEntry ( $matchfields['awayteam_id'], $matchfields['match_id'], $matchparsed['awayplayers'] );

	$match = new Match( $matchfields['match_id'] );

	$match->toggleLock();

	Print "<br>Successfully uploaded report<br>";

}

function addMatch ( $matchparsed ) {

	$tour_id = 1; #get from settings later or find from scheduled matches.

	if ( !checkHash ( $matchparsed['hash'] ) )
	{
		Print "The unique match identifier already exists.";
		exit(-1);
	}

	$hometeam_id=checkTeam ( $matchparsed['hometeam']);
	if ( !$hometeam_id )
	{
		Print "<br>The team {$matchparsed['hometeam']} in the report does not exist on this site.<br>";
		exit(-1);
	}

	$awayteam_id=checkTeam ( $matchparsed['awayteam']);
	if ( !$awayteam_id )
	{
		Print "<br>The team {$matchparsed['hometeam']} in the report does not exist on this site.<br>";
		exit(-1);
	}

	$match_id = Match_BOTOCS::create( $input = array("team1_id" => $hometeam_id, "team2_id" => $awayteam_id, "round" => 255, "f_tour_id" => 1, "hash" => $matchparsed['hash'] ) );
	unset( $input );

	$match = new Match_BOTOCS($match_id);
	$match->setBOTOCSHash($matchparsed['hash']);
	$match->update( $input = array("submitter_id" => 1, "stadium" => $hometeam_id, "gate" => $matchparsed['gate'], "fans" => 0, "ffactor1" => $matchparsed['homeff'], "ffactor2" => $matchparsed['awayff'], "fame1" => $matchparsed['homefame'], "fame2" => $matchparsed['awayfame'], "income1" => $matchparsed['homewinnings'], "income2" => $matchparsed['awaywinnings'], "team1_score" => $matchparsed['homescore'], "team2_score" => $matchparsed['awayscore'], "smp1" => 0, "smp2" => 0, "tcas1" => 0, "tcas2" => 0, "tv1" => 0, "tv2" => 0, "comment" => "" ) );
	$matchfields = array( "tour_id" => $tour_id, "hometeam_id" => $hometeam_id, "awayteam_id" => $awayteam_id, "match_id" => $match_id ); # homecoach_id awaycoach_id
	return $matchfields;

}

function matchEntry ( $team_id, $match_id, $teamPlayers ) {

	$match = new Match( $match_id );

	$team = new Team( $team_id );
	$players = $team->getPlayers();

	foreach ( $teamPlayers as $player )
	{
		foreach ( $players as $p  )
		{
			if ( $p->nr == $player['nr'] && !$p->is_dead && !$p->is_sold ) {
				$f_player_id = $p->player_id;
				break;
			}
		}
		
		$mvp = $player['mvp'];
		if ($mvp == NULL) $mvp = 0;
		$cp = $player['cp'];
		if ($cp == NULL) $cp = 0;
		$td = $player['td'];
		if ($td == NULL) $td = 0;
		$intcpt = $player['intcpt'];
		if ($intcpt == NULL) $intcpt = 0;
		$bh = $player['bh'][0];
		if ($bh == NULL) $bh = 0;
		#$si = $players[$i]
		#$ki = $players[$i]

		$inj = switchInjury ( $player['inj'] );

		$agn1 = switchInjury ( $player['agn1'] );
		if ( $agn1 > $inj ) list($inj, $agn1) = array($agn1, $inj);
		if ( $agn1 == 8 || $agn1 == 2 ) $agn1 = 1;

		$match->entry( $input = array ( "player_id" => $f_player_id, "mvp" => $mvp, "cp" => $cp, "td" => $td, "intcpt" => $intcpt, "bh" => $bh, "si" => 0, "ki" => 0, "inj" => $inj, "agn1" => $agn1, "agn2" => 1 ) );

	}
	##ADD EMPTY RESULTS FOR PLAYERS WITHOUT RESULTS MAINLY FOR MNG

	foreach ( $players as $p  )
	{
		if (  !$p->is_dead && !$p->is_sold ) {
			$player = new Player ( $p->player_id );
			$p_matchdata = $player->getMatchData( $match_id );
			if ( !$p_matchdata['inj'] ) {
				$match->entry( $input = array ( "player_id" => $p->player_id, "mvp" => 0, "cp" => 0,"td" => 0,"intcpt" => 0,"bh" => 0,"si" => 0,"ki" => 0, "inj" => 1, "agn1" => 1, "agn2" => 1  ) );
			}
		}
	}	

}

function checkCoach ( $hometeam ) {

	if ( !mysql_fetch_array( mysql_query( "SELECT `owned_by_coach_id` FROM `teams` WHERE `owned_by_coach_id` = ".$_SESSION['coach_id']." and `name` = \"".$hometeam."\"" ) ) )
	{
		return 0;
	}

	return 1;

}

function checkHash ( $hash ) {

	$query = "SELECT hash_botocs FROM matches WHERE hash_botocs = \"".$hash."\"";
	$hashresults = mysql_query($query);
	$hashresults = mysql_fetch_array($hashresults);
	$hashresults = $hashresults['hash_botocs'];


	if ( $hashresults == $hash ) {
		Print "<br>Unique match id already exists: <b>".$hash."<br>";
		return false;
	}

	return true;

}

function checkTeam ( $teamname ) {

	$query = "SELECT team_id FROM teams WHERE name = \"".$teamname."\"";
	$team_id = mysql_query($query);
	if (!$team_id) {
		return false;
	}
	$team_id = mysql_fetch_array($team_id);
	$team_id = $team_id['team_id'];
	return $team_id;

}

function switchInjury ( $inj ) {

	switch ( $inj ) {
		case NULL:
			$injeffect = 1;
			break;
		case "Miss Next Game":
			$injeffect = 2;
			break;
		case "Niggling Injury":
			$injeffect = 3;
			break;
		case "-1 MA":
			$injeffect = 4;
			break;
		case "-1 AV":
			$injeffect = 5;
			break;
		case "-1 AG":
			$injeffect = 6;
			break;
		case "-1 ST":
			$injeffect = 7;
			break;
		case "Dead":
			$injeffect = 8;
			break;
		default:
			$injeffect = 1;
			break;
		}

	return $injeffect;

}

function XOREncryption($InputString, $KeyPhrase){
 
	$KeyPhraseLength = strlen($KeyPhrase);
 
	#Loop trough input string
	for ($i = 0; $i < strlen($InputString); $i++){
		#Get key phrase character position
		$rPos = $i % $KeyPhraseLength;

		#Magic happens here:
		$r = ord($InputString[$i]) ^ ord($KeyPhrase[$rPos]);
		#Replace characters
		$InputString[$i] = chr($r);
	}
	return $InputString;

}
 
function XOREncrypt($InputString, $KeyPhrase){

	$diff = strlen($InputString) - strlen($KeyPhrase);

	while ( $diff > 0 )
	{
		$KeyPhrase = $KeyPhrase." ";
		$diff = $diff - 1;
	}
	while ( $diff < 0 )
	{
		$InputString = $InputString." ";
		$diff = $diff + 1;
	}
	$InputString = XOREncryption($InputString, $KeyPhrase);
	$InputString = base64_encode($InputString);
	return $InputString;
}

?>