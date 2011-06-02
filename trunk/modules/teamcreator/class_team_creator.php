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

public static function teamCreator()
{
	global $lng, $coach, $raceididx, $leagues, $divisions, $DEA, $stars, $racesNoApothecary, $inducements;
    title($lng->getTrn('name', 'TeamCreator'));
    // Show new team form.
    ?>
	<script type="text/javascript">
	function changeRace() {
		var oFormObject = document.forms('form_team');
		oFormObject.elements['subtype'].value = 'racechange';
		if(oFormObject.elements['rid'].value > -1) {
			oFormObject.submit();
		}
	}

	function change(nextElem) {
		nextElem.focus();
		nextElem.select();
	}
	</script>
	    <form method="POST" id="form_team">
        <div class='boxBody'>
<?php
		if (isset($coach)) {
?>
		<?php echo $lng->getTrn('common/name');?><br><input type="text" name="name" size="20" maxlength="50">
		<br><br><?php echo $lng->getTrn('common/league').'/'.$lng->getTrn('common/division');?><br>
		<select name="lid_did">
			<?php
			foreach ($leagues = Coach::allowedNodeAccess(Coach::NODE_STRUCT__TREE, $coach->coach_id, array(T_NODE_LEAGUE => array('tie_teams' => 'tie_teams'))) as $lid => $lstruct) {
				if ($lstruct['desc']['tie_teams']) {
					echo "<OPTGROUP LABEL='".$lng->getTrn('common/league').": ".$lstruct['desc']['lname']."'>\n";
					foreach ($lstruct as $did => $dstruct) {
						if ($did != 'desc') {
							echo "<option value='$lid,$did'>".$lng->getTrn('common/division').": ".$dstruct['desc']['dname']."</option>";
						}
					}
					echo "</OPTGROUP>\n";
				}
				else {
					echo "<option value='$lid'>".$lng->getTrn('common/league').": ".$lstruct['desc']['lname']."</option>";
				}
			}
			?>
		</select>
		<br><br>
<?php
		}
?>
	    <b><?php echo $lng->getTrn('common/race');?></b>: <select id="selectRace" name="rid" onchange="changeRace()">
<?php
		echo "<option value='-1'>-select-</option>";
		$selectedRid = isset($_POST['rid']) ? $_POST['rid'] : -1;

        foreach ($raceididx as $rid => $rname) {

            echo "<option value='$rid'";
            if ($selectedRid == $rid) { echo " SELECTED "; }
            echo ">$rname</option>";
		}
        ?>
	    </select>
		<input type='hidden' id='subtype' name='subtype' value=''>
        </div>
<?php
		if ($selectedRid > -1) {
			$race = $DEA[$raceididx[$selectedRid]];
			$apoth = !in_array($selectedRid, $racesNoApothecary);
			$other = (object) $race['other'];
			$other->rr_cost = $other->rr_cost / 1000;
			foreach ($race['players'] as $player => $d) {
				$p = (object) array_merge(array('position' => $player), $d);
				$p->skills = implode(', ', skillsTrans($p->def));
				$p->N = implode('',$p->norm);
				$p->D = implode('',$p->doub);
				$p->cost = $p->cost / 1000;
				$p->id = $p->pos_id;
				$players[] = $p;
			}
			foreach($stars as $player => $d) {
				if (in_array($selectedRid, $d['races'])) {
					$p = (object) array_merge(array('position' => $player), $d);
					$p->skills = implode(', ', skillsTrans($p->def));
					$p->N = '';
					$p->D = '';
					$p->qty = 1;
					$p->cost = $p->cost / 1000;
					$players[] = $p;
				}
			}

			$lastPlayerIdx = 2 + sizeof($players);
echo<<< EOQ
	<script>
		function updateTotal() {
			var oForm = document.forms('form_team');
			var elem;
			var elemId;
			var pCount = 0;
			var total = 0;
			var pQtyPatt =new RegExp("pQty");
			var pQtyCostPatt =new RegExp("QtyCost");

			try {
				for (var i=0; oForm.length; i++) {
					elem = oForm.elements[i];
					if(elem.nodeName == "INPUT") {
						elemId = elem.id;
						if (pQtyCostPatt.test(elemId)) {
							total += new Number(elem.value);
						} else if (pQtyPatt.test(elemId)) {
							pCount += new Number(elem.value);
						}
					}
				}
			} catch (err) {
				// alert("Error " + err);
			}
			if (pCount > 16) {
				alert('You are only allowed 16 players maximum, and have ' + pCount);
			}
			oForm.elements['pCnt'].value = pCount;
			oForm.elements['cost'].value = total;
		}


		function updateSub(position, maxQtyElem, costElem, qtyStrElem, subElem) {
			var num = qtyStrElem.value;
			var cost = costElem.value;
			var maxQty = maxQtyElem.value;
			if (isNaN(num)) {
				alert('This value needs to be a number');
			} else {
				if (num > maxQty) {
					alert('You are not allowed more than ' + maxQty + ' ' + position);
				}
				subElem.value = num * cost;
				updateTotal();
			}
		}

		function updateTeamAtt(attributeName, maxQty, cost, qtyStrElem, subElem) {
			var num = new Number(qtyStrElem.value);
			if ('Nan' == num) {
				alert('Not a number');
			} else if (num > maxQty) {
				alert('You are not allowed more than ' + maxQty + ' ' + attributeName);
			} else {
				subElem.value = num * cost;
				updateTotal();
			}
		}
	</script>

	<table class="common">
		<tr class="commonhead">
			<th>Position</th>
			<th>MA</th>
			<th>ST</th>
			<th>AG</th>
			<th>Av</th>
			<th>Skills</th>
			<th>Norm</th>
			<th>Doub</th>
			<th>Max</th>
			<th>$</th>
			<th>Qty</th>
			<th>Cost</th>
		</tr>
EOQ;
			$idx = 0;
			foreach($players as $player) {
				$idx++;
				if ($idx == sizeof($players)) {
					$next = "this.form.rrs";
				} else {
					$next = "this.form.pQty" . ($idx + 1);
				}


echo<<< EOQ
		<tr>
			<td>$player->position</td>
			<td>$player->ma</td>
			<td>$player->st</td>
			<td>$player->ag</td>
			<td>$player->av</td>
			<td>$player->skills</td>
			<td>$player->N</td>
			<td>$player->D</td>
			<td>$player->qty</td>
			<td>$player->cost</td>
			<input type='hidden' id='pId$idx' name='pId$idx' value='$player->id' />
			<input type='hidden' id='pCost$idx' name='pCost$idx' value='$player->cost' />
			<input type='hidden' id='pMax$idx' name='pMax$idx' value='$player->qty' />
			<td><input type='text' id='pQty$idx' name='pQty$idx' value='0' size='2' maxsize="2" onBlur="updateSub('$player->position', this.form.pMax$idx, this.form.pCost$idx, this.form.pQty$idx, this.form.pQtyCost$idx);" /></td>
			<td><input type='text' id='pQtyCost$idx' name='pQtyCost$idx' value='0' size='3'  maxsize="3" onFocus="change($next);"/></td>
		</tr>
EOQ;
			}
			$idx++;
			$next = $idx + 1;
echo<<< EOQ
		<tr>
			<td colspan="8" align="right">Rerolls&nbsp;&nbsp;</td>
			<td>8</td>
			<td>$other->rr_cost</td>
			<td><input type='text' id='rrs' name='rrs' value='0' size='2' onBlur="updateTeamAtt('Rerolls', 8, $other->rr_cost, this.form.rrs, this.form.rrsQtyCost);" /></td>
			<td><input type='text' id='rrsQtyCost' name='rrsQtyCost' value='0' size='3' onFocus="change(this.form.fans);" /></td>
		</tr>
		<tr>
			<td colspan="8" align="right">Fan Factor&nbsp;&nbsp;</td>
			<td>9</td>
			<td>10</td>
			<td><input type='text' id='fans' name='fans' value='0' size='2' onBlur="updateTeamAtt('Fan Factor', 9, 10, this.form.fans, this.form.fansQtyCost);"  /></td>
			<td><input type='text' id='fansQtyCost' name='fansQtyCost' value='0' size='3'  onFocus="change(this.form.cl);"/></td>
		</tr>
		<tr>
			<td colspan="8" align="right">Cheerleaders&nbsp;&nbsp;</td>
			<td>n/a</td>
			<td>10</td>
			<td><input type='text' id='cl' name='cl' value='0' size='2' onBlur="updateTeamAtt('Cheerleaders', 100, 10, this.form.cl, this.form.clQtyCost);"  /></td>
			<td><input type='text' id='clQtyCost' name='clQtyCost' value='0' size='3'  onFocus="change(this.form.ac);"/></td>
		</tr>
EOQ;
		 if($apoth) {
		 	$next = "this.form.apo";
		 } else  {
		 	$next = "this.form.indQ1";
		 }
echo<<< EOQ
		<tr>
			<td colspan="8" align="right">Assistant Coaches&nbsp;&nbsp;</td>
			<td>n/a</td>
			<td>10</td>
			<td><input type='text' id='ac' name='ac' value='0' size='2' onBlur="updateTeamAtt('Assistant Coaches', 100, 10, this.form.ac, this.form.acQtyCost);"  /></td>
			<td><input type='text' id='acQtyCost' name='acQtyCost' value='0' size='3'  onFocus="change($next);" /></td>
		</tr>
EOQ;
		 if($apoth) {
echo<<< EOQ
		<tr>
			<td colspan="8" align="right">Apothecary&nbsp;&nbsp;</td>
			<td>1</td>
			<td>50</td>
			<td><input type='text' id='apo' name='apo' value='0' size='2'  onBlur="updateTeamAtt('Apothecary', 1, 50, this.form.apo, this.form.apoQtyCost);"  /></td>
			<td><input type='text' id='apoQtyCost' name='apoQtyCost' value='0' size='3'  onFocus="change(this.form.indQ1);" /></td>
		</tr>
EOQ;
		 }
		 $idx = 0;
		 foreach($inducements as $name => $d) {
			$inducement = (object) array_merge(array('name' => $name), $d);
			if($apoth && $inducement->name == 'Igor') {
				continue;
			} else if (!$apoth && $inducement->name == 'Wandering Apothecaries') {
				continue;
			}
			if (in_array($selectedRid, $inducement->reduced_cost_races)) {
				$inducement->cost = $inducement->reduced_cost / 1000;
			} else {
				$inducement->cost = $inducement->cost / 1000;
			}
			$idx++;
			if($idx == (sizeof($inducements) -1)) {
				$next = "this.form.pQty1";
			} else  {
				$next = "this.form.indQ" . (1 + $idx);
			}


echo<<< EOQ
		<tr>
			<td colspan="8" align="right">$inducement->name&nbsp;&nbsp;</td>
			<td>$inducement->max</td>
			<td>$inducement->cost</td>
			<input type='hidden' id='ind$idx' name='ind$idx' value='$inducement->name' />
			<td><input type='text' id='indQ$idx' name='indQ$idx' value='0' size='2' onBlur="updateTeamAtt('$inducement->name', $inducement->max, $inducement->cost, indQ$idx, this.form.indQtyCost$idx);"  /></td>
			<td><input type='text' id='indQtyCost$idx' name='$idx' value='0' size='3'  onFocus="change($next);" /></td>
		</tr>
EOQ;
		 }
echo<<< EOQ
		<tr>
			<td colspan="8" align="right"># Players&nbsp;&nbsp;</td>
			<td colspan="2"><input type='text' id='pCnt' name='pCnt' value='0' size='2'  onFocus="change(this.form.pQty1);" /></td>
			<td align="right"><b>Total<b></td>
			<td><input type='text' id='cost' name='cost' value='0' size='4'  onFocus="change(this.form.pQty1);" /></td>
		</tr>
EOQ;
		}
?>
	</table>
	</form>
    <?php
}

}
?>
