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

class UPLOAD_BOTOCS
{
    /***************
     * Properties 
     ***************/

    public $userfile = array();
    public $xmlresults = '';
    public $error = '';
    public $tour_id = 0;
    public $coach_id = '';

    //Parsed values
    public $gate = 0;
    public $hash = '';
    public $hometeam = '';
        public $homescore = 0;
        public $homewinnings = 0;
        public $homeff = 0;
        public $homefame = 0;
        public $homeplayers;
    public $awayteam = '';
        public $awayscore = 0;
        public $awaywinnings = 0;
        public $awayff = 0;
        public $awayfame = 0;
        public $awayplayers;

    public $hometeam_id = 0;
    public $awayteam_id = 0;
    public $match_id = 0;



    function __construct($userfile, $tour_id, $coach_id) {

        $status = true;
        libxml_use_internal_errors(true);
        $this->userfile = $userfile;
        $this->tour_id = $tour_id;
        $this->coach_id = $coach_id;

        if ( !$this->processFile() ) return false;
        if ( !$this->valXML() ) return false;
        if ( !$this->parse_results() ) return false;
        if ( !$this->checkCoach ( $this->hometeam ) && !$this->checkCoach ( $this->awayteam ) )
        {
            $this->error = "You must be the owner of one of the teams in the report to upload a match.";
            return false;
        }

        $conn = mysql_up();
        if ( !$this->addMatch () ) return false;
        if ( !$this->matchEntry ( $this->hometeam_id, $this->homeplayers ) ) return false;
        if ( !$this->matchEntry ( $this->awayteam_id, $this->awayplayers ) ) return false;

        $match = new Match( $this->match_id );
        $match->setLocked(true);

        return true;

    }

    function parse_results() {

        $results =  simplexml_load_string( $this->xmlresults );

        $this->gate = $results->team[0]->fans + $results->team[1]->fans;

        $this->hometeam = $results->team[0]->attributes()->name;
        $this->homescore = $results->team[0]->score;
        $this->homewinnings = $results->team[0]->winnings;
        $this->homeff = $results->team[0]->fanfactor;
        $this->homefame = $results->team[0]->fame;

        foreach ( $results->team[0]->players->player as $player )
        {
            $this->homeplayers[intval($player->attributes()->number)]['nr'] = $player->attributes()->number;
            $this->homeplayers[intval($player->attributes()->number)]['name'] = $player->attributes()->name;
            $this->homeplayers[intval($player->attributes()->number)]['star'] = $player->attributes()->starPlayer;
            $this->homeplayers[intval($player->attributes()->number)]['merc'] = $player->attributes()->mercenary;
            $this->homeplayers[intval($player->attributes()->number)]['mvp'] = $player->mvp;
            $this->homeplayers[intval($player->attributes()->number)]['cp'] = $player->completion;
            $this->homeplayers[intval($player->attributes()->number)]['td'] = $player->touchdown;
            $this->homeplayers[intval($player->attributes()->number)]['intcpt'] = $player->interception;
            $this->homeplayers[intval($player->attributes()->number)]['bh'] = $player->casualties;
            $this->homeplayers[intval($player->attributes()->number)]['inj'] = $player->injuries->injury;
            $this->homeplayers[intval($player->attributes()->number)]['agn1'] = $player->injuries->injury[1];

        }

        $this->awayteam = $results->team[1]->attributes()->name;
        $this->awayscore = $results->team[1]->score;
        $this->awaywinnings = $results->team[1]->winnings;
        $this->awayff = $results->team[1]->fanfactor;
        $this->awayfame = $results->team[1]->fame;

        foreach ( $results->team[1]->players->player as $player )
        {

            $this->awayplayers[intval($player->attributes()->number)]['nr'] = $player->attributes()->number;
            $this->awayplayers[intval($player->attributes()->number)]['name'] = $player->attributes()->name;
            $this->awayplayers[intval($player->attributes()->number)]['star'] = $player->attributes()->starPlayer;
            $this->awayplayers[intval($player->attributes()->number)]['merc'] = $player->attributes()->mercenary;
            $this->awayplayers[intval($player->attributes()->number)]['mvp'] = $player->mvp;
            $this->awayplayers[intval($player->attributes()->number)]['cp'] = $player->completion;
            $this->awayplayers[intval($player->attributes()->number)]['td'] = $player->touchdown;
            $this->awayplayers[intval($player->attributes()->number)]['intcpt'] = $player->interception;
            $this->awayplayers[intval($player->attributes()->number)]['bh'] = $player->casualties;
            $this->awayplayers[intval($player->attributes()->number)]['inj'] = $player->injuries->injury[0];
            $this->awayplayers[intval($player->attributes()->number)]['agn1'] = $player->injuries->injury[1];

        }

        $this->hash = md5 ( $this->xmlresults );

        return true;

    }

    function addMatch () {

        if ( !$this->checkHash ( $this->hash ) ) return false;

        $this->hometeam_id= $this->checkTeam ( $this->hometeam );
        $this->awayteam_id=$this->checkTeam ( $this->awayteam );
        if ( !$this->hometeam_id || !$this->awayteam_id )
        {
            $this->error = "One of the teams was not found on the site.";
            return false;
        }

        global $settings;

        $revUpdate = false;

        if ( $settings['leegmgr_schedule'] ) $this->match_id = $this->getschMatch();

        if (!$this->match_id) {
            $this->match_id = $this->getschMatchRev();
            if ($this->match_id) $revUpdate = true;
        }

        if ( $this->chkAltSchedule() && !$this->match_id )
        {
            $this->error = "One of the teams has another match scheduled.";
            return false;
        }

        if ( !$this->match_id && $settings['leegmgr_schedule'] !== 'strict' ) {
            $this->match_id = Match_BOTOCS::create( $input = array("team1_id" => $this->hometeam_id, "team2_id" => $this->awayteam_id, "round" => 1, "f_tour_id" => $this->tour_id, "hash" => $this->hash ) );
        }

        unset( $input );

        if ( $this->match_id < 1 ) return false;

        $match = new Match_BOTOCS($this->match_id);
        $match->setBOTOCSHash($this->hash);

        $team_home = new Team( $this->hometeam_id );
        $tv_home = $team_home->value;
        $team_away = new Team( $this->awayteam_id );
        $tv_away = $team_away->value;

        if (!$revUpdate) $match->update( $input = array("submitter_id" => $this->coach_id, "stadium" => $this->hometeam_id, "gate" => $this->gate, "fans" => 0, "ffactor1" => $this->homeff, "ffactor2" => $this->awayff, "fame1" => $this->homefame, "fame2" => $this->awayfame, "income1" => $this->homewinnings, "income2" => $this->awaywinnings, "team1_score" => $this->homescore, "team2_score" => $this->awayscore, "smp1" => 0, "smp2" => 0, "tcas1" => 0, "tcas2" => 0, "tv1" => $tv_home, "tv2" => $tv_away, "comment" => "" ) );
        else $match->update( $input = array("submitter_id" => $this->coach_id, "stadium" => $this->hometeam_id, "gate" => $this->gate, "fans" => 0, "ffactor2" => $this->homeff, "ffactor1" => $this->awayff, "fame2" => $this->homefame, "fame1" => $this->awayfame, "income2" => $this->homewinnings, "income1" => $this->awaywinnings, "team2_score" => $this->homescore, "team1_score" => $this->awayscore, "smp1" => 0, "smp2" => 0, "tcas1" => 0, "tcas2" => 0, "tv2" => $tv_home, "tv1" => $tv_away, "comment" => "" ) );

        return true;

    }

    function matchEntry ( $team_id, $teamPlayers ) {

        $addZombie = false;
        $match = new Match( $this->match_id );

        $team = new Team( $team_id );
        $players = $team->getPlayers();

        foreach ( $teamPlayers as $player )
        {
            if ( $player['nr'] == 100 ) $addZombie = true;  //Must add zombie last so that dead players can be reported first.
            else $addZombie = false;
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
            #$si = $players[$i]
            #$ki = $players[$i]

            $inj = $this->switchInjury ( $player['inj'] );

            $agn1 = $this->switchInjury ( $player['agn1'] );
            if ( $agn1 > $inj ) list($inj, $agn1) = array($agn1, $inj);
            if ( $agn1 == 8 || $agn1 == 2 ) $agn1 = 1;

            if ( !$addZombie )
            $match->entry( $input = array ( "team_id" => $team_id, "player_id" => $f_player_id, "mvp" => $mvp, "cp" => $cp, "td" => $td, "intcpt" => $intcpt, "bh" => $bh, "si" => 0, "ki" => 0, "inj" => $inj, "agn1" => $agn1, "agn2" => 1 ) );
            else
            {
                    $delta = Player::price( array('race' => $team->race, 'position' => "Zombie") );
                    $team->dtreasury($delta);
                    $zombie_added = Player::create(array( 'nr' => $player['nr'], 'position' => "Zombie", 'team_id' => $team_id, 'name' => $player['name']) );
                    if ( !$zombie_added[0] ) $team->dtreasury(-$delta);
                    else
                    {
                        $input = array ( "team_id" => $team_id, "player_id" => $zombie_added[1], "mvp" => $mvp, "cp" => $cp, "td" => $td, "intcpt" => $intcpt, "bh" => $bh, "si" => 0, "ki" => 0, "inj" => $inj, "agn1" => $agn1, "agn2" => 1 );
                    }
            }

        }

        ##ADD EMPTY RESULTS FOR PLAYERS WITHOUT RESULTS MAINLY FOR MNG

        foreach ( $players as $p  )
        {
            if (  !$p->is_dead && !$p->is_sold ) {
                $player = new Player ( $p->player_id );
                $p_matchdata = $player->getMatchData( $this->match_id );
                if ( !$p_matchdata['inj'] ) {
                    $match->entry( $input = array ( "team_id" => $team_id, "player_id" => $p->player_id, "mvp" => 0, "cp" => 0,"td" => 0,"intcpt" => 0,"bh" => 0,"si" => 0,"ki" => 0, "inj" => 1, "agn1" => 1, "agn2" => 1  ) );
                }
            }
        }

        return true;

    }

    function checkCoach ( $team ) {

        $query = sprintf("SELECT owned_by_coach_id FROM teams WHERE owned_by_coach_id = '%s' and name = '%s' ", mysql_real_escape_string($this->coach_id), mysql_real_escape_string($team) );

        if ( !mysql_fetch_array( mysql_query( $query ) ) )
        {
            return false;
        }

        return true;

    }

    function checkHash () {

        $query = sprintf("SELECT hash_botocs FROM matches WHERE hash_botocs = '%s' ", mysql_real_escape_string($this->hash) );
        $hashresults = mysql_query($query);
        $hashresults = mysql_fetch_array($hashresults);
        $hashresults = $hashresults['hash_botocs'];

        if ( $hashresults == $this->hash ) {
            $this->error = "Unique match id already exists: ".$this->hash;
            return false;
        }

        return true;

    }

    function checkTeam ( $teamname ) {

        $query = sprintf("SELECT team_id FROM teams WHERE name = '%s' ", mysql_real_escape_string($teamname) );
        $team_id = mysql_query($query);

        if (!$team_id) return false;

        $team_id = mysql_fetch_array($team_id);
        $team_id = $team_id['team_id'];

        return $team_id;

    }

    function getschMatch() {

        $team_id1 = $this->hometeam_id;
        $team_id2 = $this->awayteam_id;

        $query = "SELECT match_id FROM matches WHERE submitter_id IS NULL AND ( team1_id = $team_id1 ) AND  ( team2_id = $team_id2 )";

        $match_id = mysql_query($query);
        $match_id = mysql_fetch_array($match_id);
        $match_id = $match_id['match_id'];

        return $match_id;

    }

    function getschMatchRev() {

        $team_id2 = $this->hometeam_id;
        $team_id1 = $this->awayteam_id;

        $query = "SELECT match_id FROM matches WHERE submitter_id IS NULL AND ( team1_id = $team_id1 ) AND  ( team2_id = $team_id2 )";

        $match_id = mysql_query($query);
        $match_id = mysql_fetch_array($match_id);
        $match_id = $match_id['match_id'];

        return $match_id;

    }

    function chkAltSchedule() {

        $team_id1 = $this->hometeam_id;
        $team_id2 = $this->awayteam_id;

        $query = "SELECT match_id FROM matches WHERE submitter_id IS NULL AND ( ( team1_id = $team_id1 ) OR  ( team1_id = $team_id2 ) OR  ( team2_id = $team_id1 ) OR ( team2_id = $team_id2 ) )";

        $match_id = mysql_query($query);
        $match_id = mysql_fetch_array($match_id);
        $match_id = $match_id['match_id'];

        return $status = ($match_id > 0) ? true : false;

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

    function libxml_display_error($error)
    {
        $return = "<br/>\n";
        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $return .= "<b>Warning $error->code</b>: ";
                break;
            case LIBXML_ERR_ERROR:
                $return .= "<b>Error $error->code</b>: ";
                break;
            case LIBXML_ERR_FATAL:
                $return .= "<b>Fatal Error $error->code</b>: ";
                break;
        }

        $return .= trim($error->message);
        if ($error->file) $return .= " in <b>$error->file</b>";

        $return .= " on line <b>$error->line</b>\n";

        return $return;
    }

    function libxml_display_errors() {
        $errors = libxml_get_errors();

        foreach ($errors as $error) {
            $this->error .= $this->libxml_display_error($error);
        }

        libxml_clear_errors();
    }

    // Enable user error handling
    #libxml_use_internal_errors(true);

    function valXML() {

        $tmpfname = tempnam("/tmp", "XML");

        $handle = fopen($tmpfname, "w");
        fwrite($handle, $this->xmlresults);
        fclose($handle);

        $xml = new DOMDocument();
        $xml->load($tmpfname); 

        if (!$xml->schemaValidate('modules/leegmgr/botocsreport.xsd')) {
            unlink($tmpfname);
            $this->error = "DOMDocument::schemaValidate() Generated Errors!";
            $this->libxml_display_errors();
            return false;
        }

        return true;
    }

    private static function form() {
        
        /**
         * Creates an upload form.
         *
         * 
         **/


        $tourlist = "";
        foreach (Tour::getTours() as $t)
            if ($t->type == TT_FFA && !$t->locked) $tourlist .= "<option value='$t->tour_id'>$t->name</option>\n";

        $form = "
            <!-- The data encoding type, enctype, MUST be specified as below -->
            <form enctype='multipart/form-data' action='handler.php?type=leegmgr' method='POST'>
                <!-- MAX_FILE_SIZE must precede the file input field -->
                <input type='hidden' name='MAX_FILE_SIZE' value='100000' />
                <!-- Name of input element determines name in $_FILES array -->
                Send this file: <input name='userfile' type='file' />
                <select name='ffatours'>
                    <optgroup label='Existing FFA'>
                        {$tourlist}
                    </optgroup>
                </select>
                <input type='submit' value='Send File' />
            </form>
        ";

        return $form;
    }
    
    private static function submitForm($userfile, $tour_id, $coach_id ) {

        $upload = new UPLOAD_BOTOCS($userfile, $tour_id, $coach_id);
        if ( !$upload->error )
        {
            Print "Upload was successful.";
            unset($upload);
        }
        else
        {
            Print "<br><b>Error: {$upload->error}</b><br>";
            unset($upload);
            unset($_FILES['userfile']);
            UPLOAD_BOTOCS::main();
        }

    }

    function processFile() {

            $status = true;
            $uploaddir = '/var/www/uploads/';
            $uploadfile = $uploaddir . basename($this->userfile['name']);

            if (strlen($this->userfile['tmp_name'])>3) $zip = zip_open($this->userfile['tmp_name']);

            if ( $zip  &&
                          ( $this->userfile['type'] == "application/x-zip-compressed" ||
                            $this->userfile['type'] == "application/octet-stream"     ||
                            $this->userfile['type'] == "application/zip"              ||
                            $this->userfile['type'] == "application/x-zip"
                          )
                                                                                         )
            {
                while ($zip_entry = zip_read($zip))
                {
                    if (strpos(zip_entry_name($zip_entry),"report.xml") !== false )
                    {
                        $this->xmlresults = zip_entry_read($zip_entry, 100000);
                        zip_entry_close($zip_entry);
                    }
                }
                zip_close($zip);

                if ( !isset($this->xmlresults) )
                {
                    $this->error = "The zip file does not contain the results xml file.";
                    $status = false;
                }
            }

            else
            {
                $this->error = "You must upload a zip file with the results in it.";
                $status = false;
            }

        return $status;

    }

    public static function main() {
        
        // Module registered main function.
        global $settings;
        if ( !$settings['leegmgr_enabled'] ) die ("LeegMgr is currently disabled.");
    
        if ( isset($_FILES['userfile']) && isset($_SESSION['coach_id']) )
        {
            $userfile = $_FILES['userfile'];
            $tour_id = $_POST['ffatours'];
            $coach_id = $_SESSION['coach_id'];
            self::submitForm($userfile, $tour_id, $coach_id);
        }
        else
        {
            Print "<html><body>";
            Print UPLOAD_BOTOCS::form();
            Print "</body></html>";
        }

    }

}

?>
