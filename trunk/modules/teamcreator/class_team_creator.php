<?php
/*
    This file is a template for modules.

    Note: the two terms functions and methods are used loosely in this documentation. They mean the same thing.

    How to USE a module once it's written:
    ---------------------------------
        Firstly you will need to register it in the modules/modsheader.php file.
        The existing entries and comments should be enough to figure out how to do that.
        Now, let's say that your module (as an example) prints some kind of statistics containing box.
        What should you then write on the respective page in order to print the box?

            if (Module::isRegistered('MyModule')) {
                Module::run('MyModule', array());
            }

        The second argument passed to Module::run() is the $argv array passed on to main() (see below).
*/

class TeamCreator implements ModuleInterface
{

/***************
 * ModuleInterface requirements. These functions MUST be defined.
 ***************/

/*
 *  Basically you are free to design your main() function as you wish.
 *  If you are writing a simple module that merely echoes out some data, you may want to have main() doing all the work (i.e. place all your code here).
 *  If you on the other hand are writing a module which is divided into several routines, you may (and should) use the main() as a wrapper for calling the appropriate code.
 *
 *  The below main() example illustrates how main() COULD work as a wrapper, when the subdivision of code is done into functions in this SAME class.
 */
public static function main($argv) # argv = argument vector (array).
{
    /*
        Let $argv[0] be the name of the function we wish main() to call.
        Let the remaining contents of $argv be the arguments of that function, in the correct order.

        Please note only static functions are callable through main().
    */

    $func = array_shift($argv);
    return call_user_func_array(array(__CLASS__, $func), $argv);
}

/*
 *  This function returns information about the module and its author.
 */
public static function getModuleAttributes()
{
    return array(
        'author'     => 'DoubleSkulls',
        'moduleName' => 'TeamCreator',
        'date'       => '2011',
        'setCanvas'  => true, # If true, whenever your main() is run through Module::run() your code's output will be "sandwiched" into the standard HTML frame.
    );
}

/*
 *  This function returns the MySQL table definitions for the tables required by the module. If no tables are used array() should be returned.
 */
public static function getModuleTables()
{
    return array(
    );
}

public static function getModuleUpgradeSQL()
{
    return array(
    );
}

public static function triggerHandler($type, $argv){
    // Do stuff on trigger events.
    // $type may be any one of the T_TRIGGER_* types.
}

/***************
 * OPTIONAL subdivision of module code into class methods.
 *
 * These work as in ordinary classes with the exception that you really should (but are strictly required to) only interact with the class through static methods.
 ***************/

private $attribute = 'Default value';

public function __construct($arg1)
{
    $this->attribute = $arg1;
}

public function myMethod()
{
    return $this->attribute;
}

/* Generates an array containing all the info needed for each player, to be converted to Javascript */
public static function addPlayer($pos, $d) {
	$p = array();
	$p['position'] = $pos;
	$p['ma'] = $d['ma'];
	$p['st'] = $d['ag'];
	$p['ag'] = $d['ag'];
	$p['av'] = $d['av'];
	$p['skills'] = implode(', ', skillsTrans($d['def']));
	$p['N'] = isset($d['norm']) ? implode('',$d['norm']) : "";
	$p['D'] = isset($d['doub']) ? implode('',$d['doub']) : "";
	$p['cost'] = $d['cost'] / 1000;
	$p['id'] = isset($d['pos_id']) ? $d['pos_id'] : $d['id'];
	$p['ind'] = isset($d['pos_id']) ? 0 : 1;
	$p['max'] = isset($d['qty']) ? $d['qty'] : 1;
	return $p;
}

/* Generates an array containing all the info needed for each inducement or other team attribute */
public static function addTeamAttribute($name, $cost, $max) {
	$att = array();
	$att['name'] = $name;
	if ($max == -1) {
		$max = 16;
	}
	$att['max'] = $max;
	$att['cost'] = $cost;
	$att['ind'] = 0;
	return $att;
}

/* Generates an array containing all the info needed for the teams, to be converted to Javascript */
public static function getRaceArray() {
	global $raceididx, $DEA, $stars, $rules, $racesNoApothecary, $inducements;

 	$races = array();
	foreach ($raceididx as $rid => $rname) {
		$race = array();
		$race['name'] = $rname;
		$race['rid'] = $rid;
		$race['apoth'] = !in_array($rid, $racesNoApothecary);
		$race['players'] = array();
		$race['others'] = array();
		foreach ($DEA[$raceididx[$rid]]['players'] as $pos => $d) {
			$race['players'][] = self::addPlayer($pos, $d);
		}
		foreach($stars as $pos => $d) {
			if (in_array($rid, $d['races'])) {
				$race['players'][] = self::addPlayer($pos, $d);
			}
		}
		$race['player_count'] = sizeof($race['players']);

		if ($rules['max_rerolls'] <> 0) {
			$race['others'][] = self::addTeamAttribute('Rerolls',
								     				   $DEA[$raceididx[$rid]]['other']['rr_cost'] / 1000,
												       $rules['max_rerolls']);
		}

		if ($rules['max_fan_factor'] <> 0) {
			$race['others'][] = self::addTeamAttribute('Fan Factor',
								     				   10,
												       $rules['max_fan_factor']);
		}

		if ($rules['max_cheerleaders'] <> 0) {
			$race['others'][] = self::addTeamAttribute('Cheerleaders',
								     				   10,
												       $rules['max_cheerleaders']);
		}

		if ($rules['max_ass_coaches'] <> 0) {
			$race['others'][] = self::addTeamAttribute('Ass Coaches',
								     				   10,
												       $rules['max_ass_coaches']);
		}

		if ($race['apoth']) {
			$race['others'][] = self::addTeamAttribute('Apothecary',
								     				   50,
												       1);
		}

		foreach($inducements as $name => $d) {
			$inducement = array();
			$inducement['name'] = $name;
			$inducement['max'] = $d['max'];
			if($race['apoth'] && $name == 'Igor') {
				continue;
			} else if (!$race['apoth'] && $name == 'Wandering Apothecaries') {
				continue;
			}
			if (in_array($rid, $d['reduced_cost_races'])) {
				$inducement['cost'] = $d['reduced_cost'] / 1000;
			} else {
				$inducement['cost'] = $d['cost'] / 1000;
			}
			$inducement['ind'] = 1;
			$race['others'][] = $inducement;
		}
		$race['other_count'] = sizeof($race['others']);

		$races[] = $race;
	}
	return $races;
}

public static function teamCreator()
{
	global $lng, $coach, $raceididx, $leagues, $rules, $divisions;
    title($lng->getTrn('name', 'TeamCreator'));
    // Show new team form.
	$easyconvert = new array_to_js();
	$races = self::getRaceArray();
	@$easyconvert->add_array($races, 'races');
	echo $easyconvert->output_all();
	$maxPlayers = $rules['max_team_players'];
echo<<< EOQ
	<script type="text/javascript">

	function setIdVisible(element, value) {
		document.getElementById(element).style.visibility=value;
	}

	function setIdHtml(element, value) {
		document.getElementById(element).innerHTML=value;
	}

	function addCellToRow(rowObj, cellValue, colspan) {
		var cell = rowObj.insertCell(rowObj.cells.length);
		if (colspan > 1) {
        	cell.colSpan=colspan;
        	cell.align="right";
        	cell.innerHTML = cellValue + "&nbsp;&nbsp;";;
        } else {
        	cell.innerHTML = cellValue;
        }
		return cell;
	}

	function makeInput(type, id, value) {
		return "<input type=\"" + type + "\" id=\"" + id + "\" name=\"" + id + "\" value=\"" + value+ "\" />";
	}

	function updateQty(id, type, newQty) {
		var race = races[document.getElementById("rid").value];
		if (type == 'p') {
			var players = race['players'];
		} else {
			var players = race['others'];
		}
		var player = players[id];
		var divS = "sub" + type + id;
		var newCost = player["cost"] * newQty;
		document.getElementById(divS).innerText= newCost;
		updateTotal();
	}

	function makeSelect(id, type, max) {
		var str = "<select id=\"qty" + type + id + "\" name=\"qty" + type + id + "\" onchange=\"updateQty(" + id + ", '" + type + "', this.options[this.selectedIndex].value)\">";
		for (var i = 0; i <= max; i++) {
			str += "<option>" + i + "</option>";
		}
		str += "</select>";
		return str;
	}

	function changeInduce(check) {
		var oldInduce = document.getElementById('oldInduce');
		if (check != new Boolean(oldInduce.checked)) {
			oldInduce.checked = check;
			var race = races[document.getElementById("rid").value];
			var pCounts = new Array();
			var oCounts = new Array();
			for (var i=0; i < race["player_count"]; i++) {
				pCounts[i] = getValue('qtyp' + i);
			}
			for (i=0; i < race["other_count"]; i++) {
				oCounts[i] = getValue('qtyo' + i);
			}
			changeRace(getValue("rid"));
			for (var i=0; i < race["player_count"]; i++) {
				if (pCounts[i] > 0) {
					try {
						setIndex('qtyp' + i, pCounts[i]);
						updateQty(i, 'p', pCounts[i]);
					} catch (err) {
					}
				}
			}
			for (i=0; i < race["other_count"]; i++) {
				if (oCounts[i] > 0) {
					try {
						setIndex('qtyo' + i, oCounts[i]);
						updateQty(i, 'o', oCounts[i]);
					} catch (err) {
					}
				}
			}
		}
	}

	function changeRace(raceId) {
		var oFormObject = document.forms('form_team');
		var race = races[raceId];
		var players = race["players"];
		var others = race["others"];
		var i;
		var rowIdx;
		var table = document.getElementById('teamTable');
		var induce = document.getElementById('induce').checked;
		while (table.rows.length > 1) {
			table.deleteRow(1);
		}
		document.getElementById("pcnt").innerText=0;
		document.getElementById("total").innerText=0;

		rowIdx = 0;
		for (i = 0; i < race["player_count"]; i++) {
			var player = players[i];
			if (!induce && player['ind']) {
				continue;
			}
			rowIdx++;
			var row = table.insertRow(rowIdx);
			addCellToRow(row, player["position"], 1);
			addCellToRow(row, player["ma"], 1);
			addCellToRow(row, player["st"], 1);
			addCellToRow(row, player["ag"], 1);
			addCellToRow(row, player["av"], 1);
			addCellToRow(row, player["skills"], 1);
			addCellToRow(row, player["N"], 1);
			addCellToRow(row, player["D"], 1);
			addCellToRow(row, player["cost"], 1);
			addCellToRow(row, makeInput('hidden', 'pid' + rowIdx, player["id"]) + makeSelect(i, 'p', player["max"]), 1);
			addCellToRow(row, "<div id=\"subp" + i + "\"></div>", 1);
		}
		for (i = 0; i < race["other_count"]; i++) {
			var other = others[i];
			if (!induce && other['ind']) {
				continue;
			}
			rowIdx++;
			var row = table.insertRow(rowIdx);
			addCellToRow(row, other["name"], 8);
			addCellToRow(row, other["cost"], 1);
			addCellToRow(row, makeInput('hidden', 'oid' + i, other["name"]) + makeSelect(i, 'o', other["max"]), 1);
			addCellToRow(row, "<div id=\"subo" + i + "\"></div>", 1);
		}
	}

	function createTeam() {
		var oForm = document.forms('form_team');
		oForm.elements['subtype'].value = 'createTeam';
		var name = oForm.elements['name'].value;
		var pCount = oForm.elements['pCnt'].value;
		var total = oForm.elements['cost'].value;
		var submit = true;

		if (submit && name.length == 0) {
			alert('You must enter a team name');
			submit = false;
		}

		if(submit && oForm.elements['rid'].value == -1) {
			alert('You must select a race');
			submit = false;
		}

		if (submit && pCount > 16) {
			alert('You have ' + pCount + ' players, and should have no more than 16.');
			submit = false;
		}

		if (submit && pCount < 11) {
			submit = confirm('You only have ' + pCount + ' players, and should have at least 11. Create anyway?');
		}

		if (submit && total > 1000) {
			submit = confirm('You have spent more than $' + total + 'k. Create anyway?');
		}

		if (submit) {
			oFormObject.submit();
		}
	}

	function setIndex(elem, value) {
		document.getElementById(elem).selectedIndex = value;
	}

	function getValue(elemId) {
		try {
			return document.getElementById(elemId).value;
		} catch (err) {
			return "";
		}
	}

	function getText(elemId) {
		try {
			return document.getElementById(elemId).innerText;
		} catch (err) {
			return "";
		}
	}

	function updateTotal() {
		var race = races[getValue("rid")];
		var playerCount = race['player_count'];

		var pCount = 0;
		var total = 0;
		var subTot = 0;

		for (var i=0; i < playerCount; i++) {
			pCount += new Number(getValue('qtyp' + i));
			subTot = getText('subp' + i);
			if (!isNaN(subTot)) {
				total +=  new Number(subTot);
			}
		}
		document.getElementById("pcnt").innerText=pCount;
		for (var i=0; i < race['other_count']; i++) {
			subTot = getText('subo' + i);
			if (!isNaN(subTot)) {
				total +=  new Number(subTot);
			}
		}
		document.getElementById("total").innerText=total + "k";
	}

	</script>
	<form method="POST" id="form_team">
	<div class='boxWide'>
		<table class="common"><tr><td>
		<b>Race</b>: <select id="rid" name="rid" onchange="changeRace(this.options[this.selectedIndex].value)">
EOQ;
		echo "<option value='-1'>-select-</option>";
		$selectedRid = isset($_POST['rid']) ? $_POST['rid'] : -1;

		$i = 0;
        foreach ($raceididx as $rname) {
            echo "<option value='$i'>$rname</option>";
            $i++;
		}
echo<<< EOQ
	    </select></td>
	    <td align="right" id="indTxt">Inducements:</td><td><input type="checkbox" id="induce" onChange="changeInduce(this.checked)" /><input type="hidden" id="oldInduce" value="false" /></td>
	    <td align="right"><b># Players</b>:</td><td><div id="pcnt"></div></td><td align="right"><b>Total</b>:</td><td><div id="total"></div></td></tr></table>
	</div>
	<div class="boxWide">
	<table class="common" id="teamTable">
		<tr class="commonhead">
			<th>Position</th>
			<th>MA</th>
			<th>ST</th>
			<th>AG</th>
			<th>Av</th>
			<th>Skills</th>
			<th>Norm</th>
			<th>Doub</th>
			<th>$</th>
			<th>Qty</th>
			<th>Tot.</th>
		</tr>
	</table>
	</div>
	</form>
EOQ;
}
}
