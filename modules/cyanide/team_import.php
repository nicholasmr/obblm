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

	if ( isset($_FILES['userfile']) )
	{
		if(!$_FILES['userfile']['tmp_name'])
		{
			Print "<h2>Don't forget the file!</h2>";
			exit(-1);
		}

		$team = new CyanideTeam($_FILES['userfile'], $_POST['file_type']);

		//$team_id = $team->create();

		print "<h2>Not yet implemented</h2>";
	}
	else
	{

		Print "<br/><br/>
		<!-- The data encoding type, enctype, MUST be specified as below -->
		<form enctype='multipart/form-data' action='handler.php?type=cyanide_import_team' method='POST'>
		<!-- MAX_FILE_SIZE must precede the file input field -->
		<input type='hidden' name='MAX_FILE_SIZE' value='60000' />
		<!-- Name of input element determines name in $_FILES array -->
		<h2>Send Cyanide Team Database</h2>
		<p>Team File: <input name='userfile' type='file' /></p>
		<select name='file_type'>
			<option value='0'>Saved Team</option>
			<option value='1'>Match Report Home</option>
			<option value='2'>Match Report Away</option>
		</select>
		<br><input type='submit' value='Send File' />
		</form>";
	}
}
?>