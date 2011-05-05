<?php
/*
 *  Copyright (c) Ian Williams <email is protected> 2011. All Rights Reserved.
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
/*
	Using the league prefences league and global administrators can change the touranments displayed on the homepage dynamically.
	Within the settings_xxx.php file the ID for each box should be set to 'prime' or 'second' to pick up the tournaments selected as primary and secondary.
    In addition the primary tournament will be selected by default on the league tables page.
*/

class LeaguePref implements ModuleInterface
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
        'moduleName' => 'LeaguePref',
        'date'       => '2011', # For example '2009'.
        'setCanvas'  => true, # If true, whenever your main() is run through Module::run() your code's output will be "sandwiched" into the standard HTML frame.
    );
}

/*
 *  This function returns the MySQL table definitions for the tables required by the module. If no tables are used array() should be returned.
 */
public static function getModuleTables()
{
    global $CT_cols;

	return array(
        # Table name => column definitions
        'league_prefs' => array(
			'f_lid'       => $CT_cols[T_NODE_LEAGUE].' NOT NULL PRIMARY KEY ',
	        'prime_tid'   => $CT_cols[T_NODE_TOURNAMENT],
	        'second_tid'  => $CT_cols[T_NODE_TOURNAMENT],
        ),
    );
}

public static function getModuleUpgradeSQL()
{
    return array();
}

public static function triggerHandler($type, $argv){
}

/***************
 * OPTIONAL subdivision of module code into class methods.
 *
 * These work as in ordinary classes with the exception that you really should (but are strictly required to) only interact with the class through static methods.
 ***************/

/***************
 * Properties
 ***************/
public $lid      = 0;
public $l_name    = '';
public $p_tour   = 0;
public $s_tour = 0;
public $existing = false;

function __construct($lid, $name, $ptid, $stid, $existing = false) {
	$this->lid = $lid;
	$this->l_name = $name;
	$this->p_tour = $ptid;
	$this->s_tour = $stid;
	$this->existing = $existing;
}

/* Gets the preferences for all leagues */
public static function loadLeaguesPreferences() {
	$preferences = array();
    $result = mysql_query("SELECT lid, name, prime_tid, second_tid FROM leagues LEFT OUTER JOIN league_prefs on lid=f_lid");
    if ($result && mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_assoc($result)) {
            array_push($preferences, new LeaguePref($row['lid'],$row['name'],$row['prime_tid'],$row['second_tid'], true));
        }
    }
    return $preferences;
}

/* Gets the preferences for the current league */
public static function getLeaguePreferences() {

	global $settings, $coach, $leagues;

    $sel_lid = (is_object($coach) && isset($coach->settings['home_lid']) && in_array($coach->settings['home_lid'], array_keys($leagues))) ? $coach->settings['home_lid'] : $settings['default_visitor_league'];

	$result = mysql_query("SELECT lid, name, prime_tid, second_tid FROM leagues LEFT OUTER JOIN league_prefs on lid=f_lid WHERE lid=$sel_lid");
    if ($result && mysql_num_rows($result) > 0) {
        while ($row = mysql_fetch_assoc($result)) {
            return new LeaguePref($row['lid'],$row['name'],$row['prime_tid'],$row['second_tid'], true);
        }
    } else {
		return new LeaguePref($sel_lid,$leagues['lname'],0,0,false);
	}
}

function validate() {
	return $this->p_tour != $this->s_tour;
}

function save() {
	if ($this->existing) {
     	return mysql_query("UPDATE league_prefs SET prime_tid=$this->p_tour, second_tid=$this->s_tour WHERE f_lid=$this->lid");
	} else {
     	return mysql_query("INSERT INTO league_prefs (f_lid, prime_tid, second_tid) VALUE ($this->lid, $this->p_tour, $this->s_tour)");
	}
}

public static function showLeaguePreferences() {
    global $lng, $tours, $coach, $leagues;

    title($lng->getTrn('name', 'LeaguePref'));

	self::handleActions();

	// short cuts to text lookups
	$primeTitle = $lng->getTrn('primeTitle', 'LeaguePref');
	$primeHelp = $lng->getTrn('primeHelp', 'LeaguePref');
	$secondTitle = $lng->getTrn('secondTitle', 'LeaguePref');
	$secondHelp = $lng->getTrn('secondHelp', 'LeaguePref');
	$submitText = $lng->getTrn('submitText', 'LeaguePref');
	$submitTitle = $lng->getTrn('submitTitle', 'LeaguePref');

	$rTours = array_reverse($tours, true);
	$l_prefs = self::loadLeaguesPreferences();
	foreach($l_prefs as $l_pref) {
		// check this coach is allowed to administer this league
		$canEdit = is_object($coach) && $coach->isNodeCommish(T_NODE_LEAGUE, $l_pref->lid) ? "" : "DISABLED";
echo<<< EOQ
	<div class='boxWide'>
		<h3 class='boxTitle4'>$l_pref->l_name</h3>
		<div class='boxConf'>
			<form method="POST">
				<span title="$primeHelp">$primeTitle: <select name="p_tour">
EOQ;
		foreach ($rTours as $trid => $desc) {
			echo "<option value='$trid'" . ($trid==$l_pref->p_tour ? 'SELECTED' : ''). " $canEdit >$desc[tname]</option>\n";
		}
echo<<< EOQ
				</select></span>
				<br /><span title="$secondHelp">$secondTitle: <select name="s_tour">
EOQ;
		foreach ($rTours as $trid => $desc) {
			echo "<option value='$trid'" . ($trid==$l_pref->s_tour ? 'SELECTED' : '').  " $canEdit >$desc[tname]</option>\n";
		}
echo<<< EOQ
				</select></span>
				<input type="hidden" name="lid" value="$l_pref->lid" />
				<input type="hidden" name="existing" value="$l_pref->existing" />
				<br /><input type="submit" name="action" $canEdit title="$submitTitle" value="$submitText" style="position:relative; right:-200px;">
			</form>
		</div>
	</div>
EOQ;
	echo "<div class='boxWide'>";
	HTMLOUT::helpBox($lng->getTrn('help', 'LeaguePref'), '');
	echo "</div>";

	}
}

public static function handleActions() {
    global $lng, $coach;

    if (isset($_POST['action'])) {
    	if (is_object($coach) && $coach->isNodeCommish(T_NODE_LEAGUE, $_POST['lid'])) {
			$l_pref = new LeaguePref($_POST['lid'],"",$_POST['p_tour'],$_POST['s_tour'],$_POST['existing']);
			if($l_pref->validate()) {
				if($l_pref->save()) {
					echo "<div class='boxWide'>";
					HTMLOUT::helpBox($lng->getTrn('saved', 'LeaguePref'), '');
					echo "</div>";
				} else {
					echo "<div class='boxWide'>";
					HTMLOUT::helpBox($lng->getTrn('failedSave', 'LeaguePref'), '', 'errorBox');
					echo "</div>";
				}
			} else {
				echo "<div class='boxWide'>";
				HTMLOUT::helpBox($lng->getTrn('failedValidate', 'LeaguePref'), '', 'errorBox');
				echo "</div>";
			}
		} else {
			echo "<div class='boxWide'>";
			HTMLOUT::helpBox($lng->getTrn('failedSecurity', 'LeaguePref'), '', 'errorBox');
			echo "</div>";
		}
    }
}



}
?>
