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
	
	foreach (Tour::getTours() as $t)
	if ($t->type == TT_FFA && !$t->locked)
	$tourlist .= "<option value='$t->tour_id'>$t->name</option>\n";
	
    if ($settings['cyanide_debug']) {
        Print "tour_id = ".$t->tour_id."<br>";
    }

	Print "
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
	<input type='submit' value='Send File' />
	</form>";

}

?>
