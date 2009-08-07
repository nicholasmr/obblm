<?php

/*
 *  Copyright (c) Grégory Romé <email protected> 2009. All Rights Reserved.
 *  Author(s): Frederic Morel, Grégory Romé
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

require_once 'modules/cyanide/lib_cyanide.php';

// Registered module main function.
function main()
{
	global $settings;
	if ($settings['cyanide_enabled'])
	{
		uploadpage();
	}
}

function uploadpage()
{
	global $settings;
	$tourlist = "";
	$roundlist = "";
	$match_type = array(
	RT_FINAL => 'Final',
	RT_3RD_PLAYOFF => '3rd play-off',
	RT_SEMI => 'Semi final',
	RT_QUARTER => 'Quarter final',
	RT_ROUND16 => 'Round of 16 match');

	if($settings['cyanide_public_league'])
	{
		$reverse_checkbox = "<p>In case of non scheduled match: reverse Home/Away? <input type='checkbox' name='reverse' value='1'></p>";
	}else { $reverse_checkbox = ""; }

	if ( isset($_FILES['userfile']) )
	{
		if(!$_FILES['userfile']['tmp_name'])
		{
			Print "<h2>Don't forget the file!</h2>";
			exit(-1);
		}

		parse_results($_FILES['userfile']['tmp_name']);
	}
	else
	{
		foreach (Tour::getTours() as $t)
		{
			if ($t->type == TT_FFA && !$t->locked)
			{
				$tourlist .= "<option value='$t->tour_id'>$t->name</option>\n";
			}
		}


		foreach ( $match_type as $r => $d)
		{
			$roundlist .= "<option value='$r'>$d</option>\n";
		}

		$pure_rounds = array();
		for ($i=1;$i<30;$i++)
		{
			$pure_rounds[$i] = "Round #$i match";
		}

		foreach ($pure_rounds as $r => $d)
		{
			$roundlist .= "<option value='$r'>$d</option>\n";
		}

		Print "<br/><br/>
		<!-- The data encoding type, enctype, MUST be specified as below -->
		<form enctype='multipart/form-data' action='handler.php?type=cyanide_match_import' method='POST'>
		<!-- MAX_FILE_SIZE must precede the file input field -->
		<input type='hidden' name='MAX_FILE_SIZE' value='60000' />
		<!-- Name of input element determines name in $_FILES array -->
		<h2>Send Cyanide Match Report</h2>
		<p>Match Report File: <input name='userfile' type='file' /> (My documents\BloodBowl\MatchReport.sqlite)</p>
		<p>Tournament:
		<select name='ffatours'>
			<optgroup label='Existing FFA'>
			{$tourlist}
			</optgroup>
		</select></p>
		<p>Match type:
		<select name='roundnb'>
			<optgroup label='Round Number'>
			{$roundlist}
			</optgroup>
		</select></p>
		{$reverse_checkbox}
		<br><input type='submit' value='Send File' />
		</form>";
	}
}

function parse_results($sqlitefile) {
	global $coach;

	$matchparsed = CyanideMatch::parse_file( $sqlitefile );

	if ( checkCoach ( $matchparsed["hometeam"] ) ||
	checkCoach ( $matchparsed["awayteam"] ) ||
	($coach->ring < RING_COACH) )  // Commisioners can add match.
	{
		report ( $matchparsed );
	}
	else
	{
		Print "<h2>You are not either a coach involved in this match or a commisioner!</h2>";
		Print "<p>Home team:".$matchparsed["hometeam"]."<br>";
		Print "Away team:".$matchparsed["awayteam"]."</p>";

		exit (-1);
	}

}

function report ( $matchparsed ) {
	global $settings;

	$conn = mysql_up();

	$matchfields = addMatch ( $matchparsed );

	if( isset($_POST['reverse']) )
	{
		$team2_players = $matchparsed['homeplayers'];
		$team1_players = $matchparsed['awayplayers'];
	}
	else
	{
		$team1_players = $matchparsed['homeplayers'];
		$team2_players = $matchparsed['awayplayers'];
	}

	matchEntry ( $matchfields['hometeam_id'], $matchfields['match_id'],
	$team1_players );

	matchEntry ( $matchfields['awayteam_id'], $matchfields['match_id'],
	$team2_players );

	$match = new Match( $matchfields['match_id'] );

	if( $settings['cyanide_public_league'] )
	{
		// Private league match need updates
		$match->setLocked(true);
	}

	Print "<h2>Successfully uploaded report</h2>";

	//HttpResponse::redirect("index.php?section=fixturelist&match_id=".$match_id,HTTP_REDIRECT_POST);

}

function addMatch ( $matchparsed ) {
	global $settings;
	$match_id = '';

	$tour_id = $_POST['ffatours'];
	$round =  $_POST['roundnb'];

	if ( !checkHash ( $matchparsed['hash'] ) )
	{
		Print "The unique match identifier already exists.";
		exit(-1);
	}

	$hometeam_id = checkTeam ( $matchparsed['hometeam']);
	if ( !$hometeam_id )
	{
		Print "<h2>The team {$matchparsed['hometeam']} in the report does not exist on this site.</h2>";
		exit(-1);
	}

	$awayteam_id = checkTeam ( $matchparsed['awayteam']);
	if ( !$awayteam_id )
	{
		Print "<h2>The team {$matchparsed['hometeam']} in the report does not exist on this site.</h2>";
		exit(-1);
	}

	if ( $settings['cyanide_schedule'] )
	{
		$match_id = getschMatch( $hometeam_id, $awayteam_id );
		if($match_id)
		{
			print "<h4>Match scheduled found.</h4>";
		}

	}

	/* Allow to find the reverse match */
	if (!$match_id && $settings['cyanide_allow_reverse'])
	{
		$match_id = getschMatch( $awayteam_id, $hometeam_id);
		if ($match_id)
		{
			print "<h4>Reverse match scheduled found.</h4>";
			$revUpdate = true;
		}
	}

	$team1 = $hometeam_id;
	$team2 = $awayteam_id;
	$team1_ffactor = $matchparsed['homeff'];
	$team2_ffactor = $matchparsed['awayff'];
	$team1_fame = $matchparsed['homefame'];
	$team2_fame = $matchparsed['awayfame'];
	$team1_income = $matchparsed['homewinnings'];
	$team2_income = $matchparsed['awaywinnings'];
	$team1_score = $matchparsed['homescore'];
	$team2_score = $matchparsed['awayscore'];
	$team1_tcas = $matchparsed['home_cas'];
	$team2_tcas = $matchparsed['away_cas'];

	if ( !$match_id  )
	{
		if( $settings['cyanide_schedule'] === 'strict' )
		{
			Print "<h2>Strict mode: the match must be scheduled first.</h2>";
			exit (-1);
		}

		if( isset($_POST['reverse']) )
		{
			$team2 = $hometeam_id;
			$team1 = $awayteam_id;
			$team2 = $hometeam_id;
			$team1 = $awayteam_id;
			$team2_ffactor = $matchparsed['homeff'];
			$team1_ffactor = $matchparsed['awayff'];
			$team2_fame = $matchparsed['homefame'];
			$team1_fame = $matchparsed['awayfame'];
			$team2_income = $matchparsed['homewinnings'];
			$team1_income = $matchparsed['awaywinnings'];
			$team2_score = $matchparsed['homescore'];
			$team1_score = $matchparsed['awayscore'];
			$team2_tcas = $matchparsed['home_cas'];
			$team1_tcas = $matchparsed['away_cas'];
		}

		Print "<h3>Creating match.</h3>";
		$input = array(
			"team1_id" => $team1,
			"team2_id" => $team2,
			"round" => $round,
			"f_tour_id" => $tour_id,
			"hash" => $matchparsed['hash'] );

		$match_id =	CyanideMatch::create( $input );
	}

	unset( $input );

	if ( $match_id < 1 )
	{
		Print "<h2>There was an error uploading the report.</h2>";
		exit (-1);
	}

	$match = new CyanideMatch($match_id);
	$match->setHashCyanide($matchparsed['hash'], $match_id);
	$coach_id = $_SESSION['coach_id'];

	$team_home = new Team( $team1 );
	$tv_home = $team_home->value;
	$team_away = new Team( $team2 );
	$tv_away = $team_away->value;

	$input = array(
		"submitter_id" => $coach_id,
		"stadium" => $team1,
		"gate" => $matchparsed['gate'],
		"fans" => $matchparsed['fans'],
		"ffactor1" => $team1_ffactor,
		"ffactor2" => $team2_ffactor,
		"fame1" => $team1_fame,
		"fame2" => $team2_fame,
		"income1" => $team1_income,
		"income2" => $team2_income,
		"team1_score" => $team1_score,
		"team2_score" => $team2_score,
		"smp1" => 0,
		"smp2" => 0,
		"tcas1" => $team1_tcas,
		"tcas2" => $team2_tcas,
		"tv1" => $tv_home,
		"tv2" => $tv_away,
		"comment" => "" );

	$match->update( $input );

	$matchfields = array(
		"tour_id" => $tour_id,
		"hometeam_id" => $team1,
		"awayteam_id" => $team2,
		"match_id" => $match_id );

	return $matchfields;

}

function matchEntry ( $team_id, $match_id, $teamPlayers )
{
	global $settings;
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
			if ( $p->nr == $player['nr'] && !$p->is_dead && !$p->is_sold )
			{
				$f_player_id = $p->player_id;
				break;
			}
		}

		if( !isset($f_player_id) ) {
			print "<h4>Warning: Player #".$player['nr']." of ".$team->name."does not exist in OBBLM</h4>";
			if( $settings['cyanide_allow_new_player'] )
			{
				print "<h4>Create Player #".$player['nr']." of ".$team->name.": ".cyanidedb_get_postion($player['type'])."</h4>";

				$new_player = array(
					'name' => $player['name'],
					'team_id'=> $team_id,
			 		'nr' => $player['nr'],
			 		'position' => $cyanide_player_type[$player['type']][1] ,
					'forceCreate' => true );
				$ret = Player::create($new_player, false);

				$f_player_id = $ret[1];
			} else { continue; }
		}

		$mvp = $player['mvp'];
		if ($mvp == NULL) { $mvp = 0; }

		$cp = $player['cp'];
		if ($cp == NULL) { $cp = 0; }

		$td = $player['td'];
		if ($td == NULL) { $td = 0; }

		$intcpt = $player['intcpt'];
		if ($intcpt == NULL) { $intcpt = 0; }

		$bh = $player['bh'][0];
		if ($bh == NULL) { $bh = 0; }

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
		if (  !$p->is_dead && !$p->is_sold )
		{
			$player = new Player ( $p->player_id );
			$p_matchdata = $player->getMatchData( $match_id );

			if ( !$p_matchdata['inj'] )
			{
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
							WHERE owned_by_coach_id = '%s' and name = '%s' ",
	mysql_real_escape_string($_SESSION['coach_id']),
	mysql_real_escape_string($team) );

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
			AND ( team1_id = $team_id1 )
			AND ( team2_id = $team_id2 )";

	$results = mysql_query($query);
	$rows = mysql_fetch_array($results);
	mysql_free_result($results);

	return $rows['match_id'];
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

function getObblmType($cyanide_type)
{

}
?>