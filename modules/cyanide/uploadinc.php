<?php

/*
 *  Copyright (c) Grégory Romé <email protected> 2009. All Rights Reserved.
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

// Registered module main function.
function cyanide_load() {
	global $settings;
	if ($settings['cyanide_enabled']) {
		uploadpage();
	}
}

function uploadpage() {
	global $settings;
	$tourlist = "";
	$roundlist = "";

	if (isset($_FILES['userfile'])) {
		parse_results($_FILES['userfile']['tmp_name']);
	}else
	{
		foreach (Tour::getTours() as $t)
		if ($t->type == TT_FFA && !$t->locked)
		$tourlist .= "<option value='$t->tour_id'>$t->name</option>\n";

		for ($index = 0; $index < 16; $index++) {
			$roundlist .= "<option value='$index'>Round #$index</option>\n";
		}

		Print "<br/><br/>
		<!-- The data encoding type, enctype, MUST be specified as below -->
		<form enctype='multipart/form-data' action='handler.php?type=cyanide' method='POST'>
		<!-- MAX_FILE_SIZE must precede the file input field -->
		<input type='hidden' name='MAX_FILE_SIZE' value='60000' />
		<!-- Name of input element determines name in $_FILES array -->
		Send this file: <input name='userfile' type='file' />
			<select name='ffatours'>
			<optgroup label='Existing FFA'>
			{$tourlist}
			</optgroup>
			</select>
			<select name='roundnb'>
			<optgroup label='Round Number'>
			{$roundlist}
			</optgroup>
			</select>
		<input type='submit' value='Send File' />
		</form>";
	}
}

function parse_results($sqlitefile) {

	$matchparsed = CyanideMatch::parse_file( $sqlitefile );

	/*if ( checkCoach ( $matchparsed["hometeam"] ) ||
			checkCoach ( $matchparsed["awayteam"] || true ) )
	{*/
		report ( $matchparsed );
	/*}
	else
	{
		Print "<br><h2>You are not a coach involved in this match!</h2>";
		Print "Home team:".$matchparsed["hometeam"]."<br>";
		Print "Away team:".$matchparsed["awayteam"]."<br>";

		exit (-1);
	}*/

}

function report ( $matchparsed ) {
	global $settings;

	$conn = mysql_up();

	$matchfields = addMatch ( $matchparsed );

	matchEntry ( $matchfields['hometeam_id'], $matchfields['match_id'],
					$matchparsed['homeplayers'] );

	matchEntry ( $matchfields['awayteam_id'], $matchfields['match_id'],
					$matchparsed['awayplayers'] );

	$match = new Match( $matchfields['match_id'] );

	if( $settings['cyanide_public_league'] ) {
		// Private league match need updates
		$match->setLocked(true);
	}

	Print "<h4>Successfully uploaded report</h4>";

}

function addMatch ( $matchparsed ) {

	$tour_id = $_POST['ffatours'];

	if ( !checkHash ( $matchparsed['hash'] ) )
	{
		Print "The unique match identifier already exists.";
		exit(-1);
	}

	$hometeam_id = checkTeam ( $matchparsed['hometeam']);
	if ( !$hometeam_id )
	{
		Print "<h4>The team {$matchparsed['hometeam']} in the report does not exist on this site.</h4>";
		exit(-1);
	}

	$awayteam_id = checkTeam ( $matchparsed['awayteam']);
	if ( !$awayteam_id )
	{
		Print "<h4>The team {$matchparsed['hometeam']} in the report does not exist on this site.</h4>";
		exit(-1);
	}

	global $settings;
	$match_id = '';
	$revUpdate = false;

	if ( $settings['cyanide_schedule'] ) {
		$match_id = getschMatch( $hometeam_id, $awayteam_id );
	}

	if (!$match_id) {
		$match_id = getschMatchRev( $hometeam_id, $awayteam_id );
		if ($match_id) $revUpdate = true;
	}

	if ( !$match_id && $settings['cyanide_schedule'] !== 'strict' ) {
		Print "<h4>Creating match.</h4>";
		$match_id = CyanideMatch::create( $input = array("team1_id" => $hometeam_id, "team2_id" => $awayteam_id, "round" => 1, "f_tour_id" => $tour_id, "hash" => $matchparsed['hash'] ) );
	}

	unset( $input );

	if ( $match_id < 1 )
	{
		Print "<h4>There was an error uploading the report.</h4>";
		exit (-1);
	}

	$match = new CyanideMatch($match_id);
	$match->setHashCyanide($matchparsed['hash'], $match_id);
	$coach_id = $_SESSION['coach_id'];
	$team_home = new Team( $hometeam_id );
	$tv_home = $team_home->value;
	$team_away = new Team( $awayteam_id );
	$tv_away = $team_away->value;

	if (!$revUpdate) {
		$input = array(
			"submitter_id" => $coach_id,
			"stadium" => $hometeam_id,
			"gate" => $matchparsed['gate'],
			"fans" => $matchparsed['fans'],
			"ffactor1" => $matchparsed['homeff'],
			"ffactor2" => $matchparsed['awayff'],
			"fame1" => $matchparsed['homefame'],
			"fame2" => $matchparsed['awayfame'],
			"income1" => $matchparsed['homewinnings'],
			"income2" => $matchparsed['awaywinnings'],
			"team1_score" => $matchparsed['homescore'],
			"team2_score" => $matchparsed['awayscore'],
			"smp1" => 0,
			"smp2" => 0,
			"tcas1" => $matchparsed['home_cas'],
			"tcas2" => $matchparsed['away_cas'],
			"tv1" => $tv_home,
			"tv2" => $tv_away,
			"comment" => "" );

		$match->update( $input );
	} else {
		$input = array(
			"submitter_id" => $coach_id,
			"stadium" => $hometeam_id,
			"gate" => $matchparsed['gate'],
			"fans" => $matchparsed['fans'],
			"ffactor2" => $matchparsed['homeff'],
			"ffactor1" => $matchparsed['awayff'],
			"fame2" => $matchparsed['homefame'],
			"fame1" => $matchparsed['awayfame'],
			"income2" => $matchparsed['homewinnings'],
			"income1" => $matchparsed['awaywinnings'],
			"team2_score" => $matchparsed['homescore'],
			"team1_score" => $matchparsed['awayscore'],
			"smp1" => 0,
			"smp2" => 0,
			"tcas1" => $matchparsed['home_cas'],
			"tcas2" => $matchparsed['away_cas'],
			"tv2" => $tv_home,
			"tv1" => $tv_away,
			"comment" => "" );
		$match->update( $input );
	}

	$matchfields = array( "tour_id" => $tour_id, "hometeam_id" => $hometeam_id, "awayteam_id" => $awayteam_id, "match_id" => $match_id ); # homecoach_id awaycoach_id

	return $matchfields;

}

function matchEntry ( $team_id, $match_id, $teamPlayers ) {

	$match = new Match( $match_id );

	$team = new Team( $team_id );
	$players = $team->getPlayers();

	foreach ( $teamPlayers as $player )
	{

		if ( $player['star'] == "true" )
		{
			global $stars;
			$stname = strval($player['name']);
			$f_player_id  = $stars[$stname]['id'];
			$player['inj'] = '';
		}

		if ( $player['merc'] == "true" ) continue;

		foreach ( $players as $p  )
		{
			if ( $p->nr == $player['nr'] && !$p->is_dead && !$p->is_sold ) {
				$f_player_id = $p->player_id;
				break;
			}
		}

		if( !isset($f_player_id) ) {
			print "<br>Warning: Player #".$player['nr']." of ".$team->name."does not exist in OBBLM";
			continue;
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

		$inj = switchInjury ( $player['inj'] );

		$agn1 = switchInjury ( $player['agn1'] );
		if ( $agn1 > $inj ) list($inj, $agn1) = array($agn1, $inj);
		if ( $agn1 == 8 || $agn1 == 2 ) $agn1 = 1;

		$input = array ("team_id" => $team_id,
						"player_id" => $f_player_id,
						"mvp" => $mvp,
						"cp" => $cp,
						"td" => $td,
						"intcpt" => $intcpt,
						"bh" => $bh,
						"si" => 0,
						"ki" => 0,
						"inj" => $inj,
						"agn1" => $agn1,
						"agn2" => 1 );

		$match->entry( $input );

	}
	##ADD EMPTY RESULTS FOR PLAYERS WITHOUT RESULTS MAINLY FOR MNG

	foreach ( $players as $p  )
	{
		if (  !$p->is_dead && !$p->is_sold ) {
			$player = new Player ( $p->player_id );
			$p_matchdata = $player->getMatchData( $match_id );
			if ( !$p_matchdata['inj'] ) {
				$input  = array (
					"team_id" => $team_id,
					"player_id" => $p->player_id,
					"mvp" => 0,
					"cp" => 0,
					"td" => 0,
					"intcpt" => 0,
					"bh" => 0,
					"si" => 0,
					"ki" => 0,
					"inj" => 1,
					"agn1" => 1,
					"agn2" => 1  );

				$match->entry( $input );
			}
		}
	}

}

function checkCoach ( $team ) {

	if ( !isset( $_SESSION['coach_id'] ) ) return false;

	$query = sprintf("SELECT owned_by_coach_id
						FROM teams
							WHERE owned_by_coach_id = '%s' and name = '%s' ", mysql_real_escape_string($_SESSION['coach_id']), mysql_real_escape_string($team) );

	if ( !mysql_fetch_array( mysql_query( $query ) ) ) {
		return false;
	}

	return true;

}

function checkHash ( $hash ) {

	$query = sprintf("
		SELECT hash_botocs
		FROM matches
		WHERE hash_botocs = '%s' ", mysql_real_escape_string($hash) );

	$results = mysql_query($query);

	if( mysql_num_rows( $results ) != 0 ) {
		Print "<h4>Unique match id already exists: ".$hash."</h4>";
		return false;
	}

	return true;
}

function checkTeam ( $teamname )
{
	$query = sprintf("SELECT team_id FROM teams WHERE name = '%s' ",
						mysql_real_escape_string($teamname) );

	$team_id = mysql_query($query);
	if (!$team_id) {
		return false;
	}

	$team_id = mysql_fetch_array($team_id);
	$team_id = $team_id['team_id'];

	return $team_id;
}

function getschMatch( $team_id1, $team_id2 ) {

	$query = "
		SELECT match_id
		FROM matches
		WHERE submitter_id IS NULL
			AND ( team1_id = $team_id1 ) AND  ( team2_id = $team_id2 )";

	$match_id = mysql_query($query);
	$match_id = mysql_fetch_array($match_id);
	$match_id = $match_id['match_id'];
	return $match_id;

}

function getschMatchRev( $team_id2, $team_id1 ) {

	$query = "SELECT match_id FROM matches WHERE submitter_id IS NULL AND ( team1_id = $team_id1 ) AND  ( team2_id = $team_id2 )";

	$match_id = mysql_query($query);
	$match_id = mysql_fetch_array($match_id);
	$match_id = $match_id['match_id'];
	return $match_id;

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


?>
