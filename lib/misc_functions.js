/*
 *  Copyright (c) Nicholas Mossor Rathmann <nicholas.rathmann@gmail.com> 2008-2009. All Rights Reserved.
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
 
var MERC_CNT = 1; // Used by add/rmstarmerc() routines.
 
/*************************
 *  General functions.
 *************************/
 
function IsNumeric(sText)
{
    var ValidChars = "0123456789";
    var IsNumber = true;
    var Char;
    
    for (i = 0; i < sText.length && IsNumber == true; i++) { 
        Char = sText.charAt(i); 
        if (ValidChars.indexOf(Char) == -1) {
            IsNumber = false;
        }
    }
    return IsNumber;
}

function numError(field)
{
    /* Tests for invalid (non-numeric) input in player attribute field, and resets field if so. */
    
    if (!IsNumeric(field.value)) {
        alert("Sorry. Only numeric values are allowed in the field you changed. Try again.");
        field.value = "";
    }
}

function scrollTop()
{
      window.scrollTo(0, 0);
}

function scrollBottom()
{
    if (document.body.scrollHeight) { 
      window.scrollTo(0, document.body.scrollHeight); 
    } 
    else if (screen.height) { // IE5 
      window.scrollTo(0, screen.height);
    }
}

/*************************
 *  These functions are used in the match report section.
 *************************/

function addStarMerc(table_id, id)
{
    /* 
        Adds a star or merc entry to a team's match report depending on table_id value (== 1 or 2).
    */
    
    var table  = document.getElementById('starsmercs_'+table_id);
    var rows   = table.rows.length;
    var fields = ['Position', 'Hiring cost', 'Additional skills', 'MVP', 'Cp', 'TD', 'Int', 'BH', 'SI', 'Ki', 'Remove'];

    /* Header. */
    if (rows == 0) {
        table.insertRow(0);
        for (i = 0; i < fields.length; i++) {
            var td   = document.createElement('td');
            var font = document.createElement('font'); 
            var txt  = document.createTextNode(fields[i]);
            font.style.fontStyle = 'italic';
            font.appendChild(txt);
            td.appendChild(font); 
            table.rows[0].appendChild(td);
        }
        rows++;
    }

    /* Player entry. */
    var x = new Array();

    // Merc?
    if (id == ID_MERCS) {
        idm = '_'+ID_MERCS+'_'+MERC_CNT;
        x[0] = 'Mercenary' + '<input type="hidden" id="team'+idm+'" name="team'+idm+'" value="'+table_id+'">';
        x[1] = 'depends on position';
        x[2] = '<input type="text" onChange="numError(this);" size="1" maxlength="2" value="0" id="skills'+idm+'" name="skills'+idm+'">';
        x[3] = '<INPUT TYPE="CHECKBOX" id="mvp'+idm+'" NAME="mvp'+idm+'">';
        x[4] = '<input type="text" onChange="numError(this);" size="1" maxlength="2" value="0" id="cp'+idm+'" name="cp'+idm+'">';
        x[5] = '<input type="text" onChange="numError(this);" size="1" maxlength="2" value="0" id="td'+idm+'" name="td'+idm+'">';
        x[6] = '<input type="text" onChange="numError(this);" size="1" maxlength="2" value="0" id="intcpt'+idm+'" name="intcpt'+idm+'">';
        x[7] = '<input type="text" onChange="numError(this);" size="1" maxlength="2" value="0" id="bh'+idm+'" name="bh'+idm+'">';
        x[8] = '<input type="text" onChange="numError(this);" size="1" maxlength="2" value="0" id="si'+idm+'" name="si'+idm+'">';
        x[9] = '<input type="text" onChange="numError(this);" size="1" maxlength="2" value="0" id="ki'+idm+'" name="ki'+idm+'">';
        x[10] = '<a href="javascript:void(0)" onclick="rmStarMerc('+ID_MERCS+','+MERC_CNT+');">Remove</a>';
        MERC_CNT++;
    }
    // Star?
    else {
        var table_id2 = (table_id == 1) ? 2 : 1;
        var stars     = document.getElementById('stars_'+table_id);
        var stars2    = document.getElementById('stars_'+table_id2); // The other stars table.
        var pos       = null;

        for (s in phpStars) {
            if (phpStars[s]['id'] == id) {
                pos = s;
            }
        }

        x[0] = pos + '<input type="hidden" id="team_'+id+'" name="team_'+id+'" value="'+table_id+'">';
        x[1] = phpStars[pos]['cost']/1000+'k';
        x[2] = 'N/A';
        x[3] = '<INPUT TYPE="CHECKBOX" id="mvp_'+id+'" NAME="mvp_'+id+'">';
        x[4] = '<input type="text" onChange="numError(this);" size="1" maxlength="2" id="cp_'+id+'" name="cp_'+id+'" value="0">';
        x[5] = '<input type="text" onChange="numError(this);" size="1" maxlength="2" id="td_'+id+'" name="td_'+id+'" value="0">';
        x[6] = '<input type="text" onChange="numError(this);" size="1" maxlength="2" id="intcpt_'+id+'" name="intcpt_'+id+'" value="0">';
        x[7] = '<input type="text" onChange="numError(this);" size="1" maxlength="2" id="bh_'+id+'" name="bh_'+id+'" value="0">';
        x[8] = '<input type="text" onChange="numError(this);" size="1" maxlength="2" id="si_'+id+'" name="si_'+id+'" value="0">';
        x[9] = '<input type="text" onChange="numError(this);" size="1" maxlength="2" id="ki_'+id+'" name="ki_'+id+'" value="0">';
        x[10] = '<a href="javascript:void(0)" onclick="rmStarMerc('+id+',0);">Remove</a>';
            
        // Remove star from add-list drop-downs.
        for (i = 0; i < stars.options.length; i++) { // could also be stars2
            if (stars.options[i].value == id) {
                stars.remove(i);
                stars2.remove(i);
                break;
            }
        }
        if (stars.length == 0) { // could also be stars2.
            document.getElementById('addStarsBtn_'+table_id).disabled = true;
            stars.disabled = true;
            document.getElementById('addStarsBtn_'+table_id2).disabled = true;
            stars2.disabled = true;
        }
    }
    
    // Add the table row.
    table.insertRow(rows);
    
    for (i = 0; i < x.length; i++) { 
        table.rows[rows].appendChild(document.createElement('td'));
        table.rows[rows].cells[i].innerHTML = x[i];
    }
    
    return (id == ID_MERCS) ? MERC_CNT-1 : 0;
}

function rmStarMerc(id, nr)
{
    /* 
        Remove a star or merc entry from a team's match report. 
    */
    
    // NOTE: the "nr" argument is only used when mercs are to be removed.
    
    var str = 'team_'+id + ((id == ID_MERCS) ? '_'+nr : '');
    var ref = document.getElementById(str);
    
    // Look through each table for the star/merc and remove it.
    for (i = 1; i <= 2; i++) {
        var t = document.getElementById('starsmercs_'+i);
        for (j = 0; j < t.rows.length; j++) { 
            if (ref.parentNode == t.rows[j].cells[0]) {
                t.deleteRow(j);
                break;
            }
        }
    }
    
    /* Add star to drop-down menu again. */
        
    if (id != ID_MERCS) {
    
        var s1  = document.getElementById('stars_1');
        var s2  = document.getElementById('stars_2');
        var pos = ''; // Value of position field.

        for (s in phpStars) {
            if (phpStars[s]['id'] == id) {
                pos = s;
                break;
            }
        }

        // When re-adding stars to drop-down there will always be 1 or more stars in drop-down. Therefore we always re-enable the menu and button.
        document.getElementById('addStarsBtn_1').disabled = false;
        document.getElementById('addStarsBtn_2').disabled = false;
        s1.disabled = false;
        s2.disabled = false;
        
        try {
            s1.add(new Option(pos, id), null);
            s2.add(new Option(pos, id), null);
        }
        catch (e) { // IE
            s1.add(new Option(pos, id), 0);
            s2.add(new Option(pos, id), 0);
        }
    }
}

function existingStarMerc(table_id, id, mdat)
{
    /*
        Like addStarMerc(), but fills the added entry with field values from "mdat".
    */
    
    var nr    = addStarMerc(table_id, id);
    var table = document.getElementById('starsmercs_'+table_id);

    if (id == ID_MERCS) {
        id = id+'_'+nr;
    }

    for (d in mdat) {
        e = document.getElementById(d+'_'+id);
        if (d == 'mvp') {
            e.checked = (mdat[d]) ? true : false;
        }
        else {
            e.value = mdat[d];
        }
    }    
}

/*************************
 *  These functions are used in the administrator's section by the import feature.
 *************************/

function addPlayerEntry()
{
    /* Adds a new entry in the players list. */
    
    // The variable "players" is defined globally and is the number of player entries the form currently contains.
    players = ++document.importForm.players.value;
    
    // Player table element/object.
    t = document.getElementById('playerTable');

    // TDs content.
    var cont = new Array();
    cont[0] = players;
    cont[1] = '<input type="text" name="'+players+'name" size="15" maxlength="50">';
    cont[2] = '<select name="'+players+'position"></select>';
    cont[3] = '<select name="'+players+'status"></select>';
    cont[4] = '<input type="text" name="'+players+'stats" value="0/0/0/0/0/0/0" size="20" maxlength="20">';
    cont[5] = '<input type="text" name="'+players+'injs" value="0/0/0/0/0" size="15" maxlength="15">';

    // Add table TRs and TDs.
    t.insertRow(players);
    t.rows[players].setAttribute("id", "row"+players);
    for (i = 0; i < cont.length; i++) { 
        var newTD = document.createElement('td');
        newTD.innerHTML = cont[i];
        t.rows[players].appendChild(newTD);
    }

    document.importForm[players+'position'].options[0] = new Option('Please re-select team race', 0);
    document.importForm[players+'status'].options[0] = new Option('Ready', NONE);
    document.importForm[players+'status'].options[1] = new Option('MNG', MNG);
    document.importForm[players+'status'].options[2] = new Option('Dead', DEAD);

    // Update the player position list.
    chPos(players, document.importForm.race.value);
    
    // Scroll to page bottom.
    scrollBottom();
}

function chRace(race)
{
    /* Changes every player's position list to match positions from a new team race. */

    for (i = 1; i <= players; i++) { 
        chPos(i, race);
    }
}

function chPos(pos, race)
{
    /* Changes the position list of a single player to match the position types of the passed race. */
    
    document.importForm[pos+'position'].options.length = 0; // Reset player position select elements.
    var j = 0;
    for (var player in gd[race]['players']) {
        document.importForm[pos+'position'].options[j] = new Option(player, player);
        j++;
    }
}


/*************************
 *  Jquery shortcuts
 *************************/
 
function fadeIn(id)         { return $('#'+id).fadeIn('slow');}
function fadeOut(id)        { return $('#'+id).fadeOut('slow');}
function slideDown(id)      { return $('#'+id).slideDown('slow');}
function slideUp(id)        { return $('#'+id).slideUp('slow');}
function slideToggle(id)    { return $('#'+id).slideToggle("slow");}
function toggle(id)         { return $('#'+id).toggle("slow");}
