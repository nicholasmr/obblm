<?php

/*
 *  Copyright (c) William Leonard <email protected> 2009. All Rights Reserved.
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

class XML_BOTOCS implements ModuleInterface
{
    /***************
     * Properties 
     ***************/

    public $noninjplayercount = 0;
    public $exist_journeyman = false;
    public $jm = 0;
    //team
    public $roster = '';
    public $team_id = 0;
    public $name = '';
    public $race = '';
    public $coach_name = '';
    public $rerolls = 0;
    public $fan_factor = 0;
    public $ass_coaches = 0;
    public $cheerleaders = 0;
    public $apothecary = "false";
    public $treasury = 0;
    //players
    public $players;


    /***************
     * Methods 
     ***************/

    function __construct($team_id, $jm) {

        $this->team_id = $team_id;
        $this->jm = $jm;

        $team = new Team ( $_GET["teamid"] );
            $this->players = $team->getPlayers();
            $this->name = $team->name;
            $this->race = $team->race;
            $this->coach_name = $team->coach_name;
            $this->rerolls = $team->rerolls;
            $this->fan_factor = $team->fan_factor;
            $this->ass_coaches = $team->ass_coaches;
            $this->cheerleaders = $team->cheerleaders;
            $this->apothecary = $team->apothecary;
                $this->apothecary = ( $this->apothecary == "1" ) ? "true" : "false";
            $this->treasury = $team->treasury;
            
        if ( !$this->checkJourneymen() ) return false;

        $this->name = $team->name;
        $this->coach_name = $team->coach_name;
        $this->createRoster();

    }

    function createRoster() {

        $this->roster .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $this->roster .= "<?xml-stylesheet type=\"text/xsl\" href=\"/modules/leegmgr/team.xsl\"?>\n";
        $this->roster .= "<team>\n";
        $this->roster .= "    <name>".htmlspecialchars($this->name, ENT_NOQUOTES, 'UTF-8')."</name>\n";
        $this->roster .= "    <race>".htmlspecialchars($this->race, ENT_NOQUOTES, 'UTF-8')."</race>\n";
        $this->roster .= "    <coach>".htmlspecialchars($this->coach_name, ENT_NOQUOTES, 'UTF-8')."</coach>\n";
        $this->roster .= "    <rerolls>".$this->rerolls."</rerolls>\n";
        $this->roster .= "    <fanfactor>".$this->fan_factor."</fanfactor>\n";
        $this->roster .= "    <assistants>".$this->ass_coaches."</assistants>\n";
        $this->roster .= "    <cheerleaders>".$this->cheerleaders."</cheerleaders>\n";
        $this->roster .= "    <apothecary>".$this->apothecary."</apothecary>\n";
        $this->roster .= "    <treasury>".$this->treasury."</treasury>\n";
        $this->roster .= "    <players>\n";

        foreach ( $this->players as $p )
        {

            if ( !$p->is_dead && !$p->is_sold )
            {
                $skills = $p->getSkillsStr();
                $a_skills = explode(', ', $skills);
                $this->roster .= "        <player number=\"".$p->nr."\">\n";
                $this->roster .= "            <name>".htmlspecialchars($p->name, ENT_NOQUOTES, 'UTF-8')."</name>\n";
                $this->roster .= "            <position>".htmlspecialchars($p->pos, ENT_NOQUOTES, 'UTF-8')."</position>\n";
                $this->roster .= "            <ma>".$p->ma."</ma>\n";
                $this->roster .= "            <st>".$p->st."</st>\n";
                $this->roster .= "            <ag>".$p->ag."</ag>\n";
                $this->roster .= "            <av>".$p->av."</av>\n";
                $this->roster .= "            <skills>\n";

                $i = 0;

                    while ( $i < count( $a_skills ) && strlen( $a_skills[0] ) > 0 )
                    {

                        if ( strpos($a_skills[$i], "Ball & Chain") !== FALSE ) $a_skills[$i] = "Ball and Chain";
                        if ( strpos($a_skills[$i], "Nurgle's Rot") !== FALSE ) $a_skills[$i] = "Nurgles Rot";
                        if ( strpos($a_skills[$i], "Claw/Claws") !== FALSE ) $a_skills[$i] = "Claws";
                        if ( strpos($a_skills[$i], "*") ) $a_skills[$i] = str_replace("*","",$a_skills[$i]);
				$this->roster .= "                <skill>".htmlspecialchars($a_skills[$i], ENT_NOQUOTES, 'UTF-8')."</skill>\n";
                        $i++;
                    }
                $injured = ( $p->is_mng ) ? "true" : "false";
                $this->roster .= "            </skills>\n";
                $this->roster .= "            <spp>".$p->spp."</spp>\n";
                $this->roster .= "            <nigglings>".$p->inj_ni."</nigglings>\n";
                $this->roster .= "            <injured>".$injured."</injured>\n";
                $this->roster .= "            <value>".$p->value."</value>\n";
                $this->roster .= "        </player>\n";
            }

        }

        $this->roster .= "    </players>\n";
        $this->roster .= "</team>";

    }

    function checkJourneymen()
    {

        foreach ( $this->players as $p )
        {
            if ( !$p->is_dead && !$p->is_sold && $p->is_journeyman && $p->spp > 0 )
            {
                Print "You have a journeyman with star players points.  Please hire or fire him.";
                return false;
            }
            if ( !$p->is_dead && !$p->is_sold && !$p->is_mng ) $this->noninjplayercount ++;
            if ( !$p->is_dead && !$p->is_sold && $p->is_journeyman )
            {
                $this->exist_journeyman = true;
            }
        }
	
        if ( !$this->jm && $this->noninjplayercount < 11 )
        {
            Print "You may have forgotten to hire a journeyman for the next match.  Please hire a player as a journeyman or hire a player.<br>";
            $rosterjm = $this->curPageURL();
            Print "If you want to ignore this error, use the following for your roster:<br><b>{$rosterjm}</b>";
            return false;
        }

        if ( $this->exist_journeyman && $this->noninjplayercount > 11 )
        {
            Print "You have a journeyman and more than 11 players that can participate in the next match.";
            return false;
        }

        if ( !$p->is_dead && !$p->is_sold && $p->nr == 100 )
        {
            Print "Player number 100 is reserved for newly raised zombies.  Please renumber the player.";
            return false;
        }

        return true;

    }

    function curPageURL() {

        $pageURL = 'http';
        $pageURL .= "://";
        $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        $pageURL .= "&jm=1";
        return $pageURL;

    }

    private static function random_team($coach_id) {

        $teams = array();
        
        $result = mysql_query( "SELECT team_id FROM teams WHERE owned_by_coach_id = $coach_id and name LIKE '%[P]' ORDER BY name ASC" );
        if ($result && mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                $teams[] = $row['team_id'];
            }
        }
        if ( count($teams) < 5 ) return false;
       $key = array_rand($teams);

       return $teams[$key];

    }
    
    /*
     * Module interface
     */ 

    public static function main($argv) {
        
        // Module registered main function.
        global $settings;
        if ( !$settings['leegmgr_enabled'] ) die ("LeegMgr is currently disabled.");

        if ( isset($_GET["coachid"]) )
        {
            $_GET["teamid"] = self::random_team($_GET["coachid"]);
        }
    
        if ( isset($_GET["teamid"]) && $_GET["teamid"] )
        {
            $team_id = $_GET["teamid"];
            if ( isset($_GET["jm"]) ) $jm = $_GET["jm"];
            else $jm = 0;
            $roster = new XML_BOTOCS( $team_id, $jm );
            Print $roster->roster;
        }
        else
        {
            Print "<html><body>";
            Print "A valid team id was not found";
            Print "</body></html>";
        }

    }
    
    public static function getModuleAttributes()
    {
        return array(
            'author'     => 'William Leonard',
            'moduleName' => 'BOTOCS team XML export',
            'date'       => '2009',
            'setCanvas'  => false,
        );
    }

    public static function getModuleTables()
    {
        return array();
    }

    public static function getModuleUpgradeSQL()
    {
        return array();
    }
    
    public static function triggerHandler($type, $argv){}
    
}

?>
