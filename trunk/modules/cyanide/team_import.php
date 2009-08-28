<?php

/*
 *  Copyright (c) Gregory Romé <email protected> 2009. All Rights Reserved.
 *  Author(s): Frederic Morel, Gregory Romé
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

function main()
{
	global $settings;

	if ($settings['cyanide_enabled'])
	{
		team_upload_page();
	}
}

function team_upload_page()
{
	global $settings;
	global $coach;

	if(!isset($coach)) {exit -1;}

	$coach_id = $coach->coach_id;

	if ( isset($_POST['send']) )
	{
		if(!$_FILES['userfile']['tmp_name'])
		{
			Print "<h2>Don't forget the file!</h2>";
			exit(-1);
		}

		$team = new CyanideTeam($_FILES['userfile']['tmp_name'], $_POST['file_type']);
		if(!$team)
		{
			Print "<h2>ERROR: Impossible to parse the team</h2>";
			exit(-1);
		}
		
		if(!isset($_POST['is_new']) || ($_POST['is_new'] !== 'on'))
		{
			$team->is_new = false;
		}
		
		$team_id = $team->create();

		$msg="<h2>Error!</h2>";

		if($team_id)
		{
			if($team->populate())
			{
				$msg = "<h2>Team Imported!</h2>";
			}
		}

		if($team->has_err)
		{
			foreach ($team->err as $key => $errs)
			{
				print "<p>".$key."<br>";
				foreach($errs as $err)
				{
					print $err." ";
				}
				print "</p>";
			}

			print $msg;
		}
	}
	else
	{
		if( isset($_POST['check']) && $_FILES['userfile']['tmp_name'] )
		{

			$team = new CyanideTeam($_FILES['userfile']['tmp_name'], $_POST['file_type']);
			if(!$team->info['coach_id'])
			{
				$msg = "<h3>The '".$team->info['name']."' is unknown!</h3>";
			}
			else
			{
				$team_ = new Team($team->id);
				print $team_->fan_factor;
				$team_coach = new Coach($team->info['coach_id']);
				$msg = "<h3>This file contains the team: <b>".$team->info['name']." of ".$team_coach->name."</b>!</h3>";
			}
		} else { $msg = ""; }

		$selected[0] = "";
		$selected[1] = "";
		$selected[2] = "";
		if(isset($_POST['file_type']))
		{
			$selected[$_POST['file_type']]="selected";
		}

		$coach_selection = "";
		if($coach->ring !== RING_COACH)
		{
			$coaches = Coach::getCoaches();
			objsort($coaches, array('+name'));

			$coach_selection="
            	<b>Coach:</b>
            	<select name='coach'>";

			foreach ($coaches as $c)
			{
				$coach_selection .= "<option value='$c->coach_id' ".(($coach_id == $c->coach_id) ? 'SELECTED' : '').">$c->name </option>\n";
			}

			$coach_selection .= "</select><br>";
		}

		Print "<br/><br/>
		<!-- The data encoding type, enctype, MUST be specified as below -->
		<form enctype='multipart/form-data' action='handler.php?type=cyanide_team_import' method='POST'>
		<!-- MAX_FILE_SIZE must precede the file input field -->
		<input type='hidden' name='MAX_FILE_SIZE' value='60000' />
		<!-- Name of input element determines name in $_FILES array -->
		<h2>Send Cyanide Team Database</h2>
		{$msg}
		{$coach_selection}
		<p><b>Team File</b>: <input name='userfile' type='file'/></p>
		<p><b>File type</b>:
		<select name='file_type' selected='2'>
			<option value='0' $selected[0] >Saved Team</option>
			<option value='1' $selected[1] >Match Report Home</option>
			<option value='2' $selected[2] >Match Report Away</option>
		</select> 
        Is this team is new? <input type='checkbox' checked name='is_new'></p>
		<input type='submit' value='Check' name='check'/>
		<br><input type='submit' value='Send File' name='send'/>
		</form>";
	}
}
?>