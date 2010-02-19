<?php

//examples
//comment this out for production

//End Example


class cy_team_db {
	var $_db_con;
	var $file_loc;
	var $team;
	var $players;
	var $playerskills;
	var $db_status;
	var $races;
	var $version;
	var $casualty;
	
	public function make_cy_roster($fileloc,$team,$players_arr,$team_array,$races_array,$p_skills_array,$casualty_array) {
		$this->file_loc  = $fileloc;
		$this->team 	 = $team_array;
		$this->players   = $players_arr;
		$this->playerskills = $p_skills_array;
		$this->races	 = $races_array;
		$this->version   = "1.1.3.2";
		$this->casualty  = $casualty_array;
		/*required data */
		/*****AI Positions Table****/
		require_once('sql/aipositions.php');
		/****AI_Tactics Table****/
		require_once('sql/ai_tactics.php');
		/****Equipment_Listing Table****/
		require_once('sql/equipment_listings.php');
		/****Equipment_Casualties Table****/
		require_once('sql/player_casualties.php');
		/****Player_Listing Table****/
		require_once('sql/player_listings.php');
		/****Player_Listing Table****/
		require_once('sql/player_skills.php');
		/****Player_types Table****/
		require_once('sql/player_types.php');
		/****Player_types_skill_doubles Table****/
		require_once('sql/player_types_skill_double.php');
		/****Player_types_skill_normal Table****/
		require_once('sql/player_types_skill_normal.php');
		/****Player_types_skills Table****/
		require_once('sql/player_types_skills.php');
		/****races Table****/
		require_once('sql/races.php');
		/****savegameinfo Table****/
		require_once('sql/savegameinfo.php');
		/****Statistics_Player Table****/
		require_once('sql/stats_player.php');
		/****Statistics_Season_Player Table****/
		require_once('sql/stats_season_player.php');
		/****Statistics_Season_Team Table****/
		require_once('sql/stats_season_team.php');
		/****Statistics_Team Table****/
		require_once('sql/stats_team.php');
		/****Team_Listing Table****/
		require_once('sql/team_listing.php');
		/****Team_Rankings Table****/
		require_once('sql/team_rankings.php');
		/*End required data */
		
		/**Build Team TB**/
		/* create data base */
		$this->create_team_db(2);
		
		/*create Tables */
		$this->begin_transaction();
		$this->create_table($_t_ai_positions);
		$this->create_table($_t_ai_tactics);
		$this->create_table($_t_equipment_listings);
		$this->create_table($_t_player_casualties);
		$this->create_table($_t_player_listings);
		$this->create_table($_t_player_skills);
		$this->create_table($_t_player_types);
		$this->create_table($_t_player_types_skill_double);
		$this->create_table($_t_player_types_skill_normal);
		$this->create_table($_t_player_types_skills);
		$this->create_table($_t_races);
		$this->create_table($_t_savegameinfo);
		$this->create_table($_t_statistics_player);
		$this->create_table($_t_statistics_season_players);
		$this->create_table($_t_statistics_season_teams);
		$this->create_table($_t_statistics_teams);
		$this->create_table($_t_team_listing);
		$this->create_table($_t_team_rankings);
		$this->commit();
		/*insert data */
		$this->begin_transaction();
		$this->insert_data($_dat_ai_positions);
		$this->insert_data($_dat_ai_tactics);
		$this->insert_player_listings();
		$this->insert_team_listings();
		$this->insert_team_rankings();
		$this->insert_stats_team();
		$this->insert_stats_season_team();
		$this->insert_races();	
		$this->insert_save_gameinfo();
		$this->commit();
		$this->begin_transaction();
		$this->insert_player_skills();
		$this->insert_player_types($_dat_player_types);
		$this->commit();
		$this->begin_transaction();
		$this->insert_player_types_skills($_dat_palyer_types_skills);
		$this->insert_equipment_listings($_dat_equip_listings);
		$this->insert_player_casualty();
		$this->insert_player_types_skill_double($_dat_skill_double);
		$this->insert_player_types_skill_normal($_dat_skill_normal);
		$this->commit();
		/*Close database*/
		$this->close_team_db(2);		
	}
	private function begin_transaction() {
		$sql = "BEGIN TRANSACTION";
		$this->db_status = $this->_db_con->exec($sql);
	}
	private function commit() {
		$sql = "COMMIT;";
		$this->db_status = $this->_db_con->exec($sql);
	}
	private function create_team_db($db_type){
		if ($db_type == 1) {// use SQLite3 class
			$this->_db_con = new SQLite3($this->file_loc.$this->team['name'].'.db');
		}
		if ($db_type == 2) {// use PDO class
			$this->_db_con = new PDO('sqlite:'.$this->file_loc.$this->team['name'].'.db');
		}
	}
	private function create_table($sql){
		$this->db_status = $this->_db_con->exec($sql);
	}
	private function insert_data($sql) {
		$this->db_status = $this->_db_con->exec($sql);
	}
	private function insert_team_listings(){
			$sql = "INSERT INTO 'Team_Listing' VALUES(".$this->team['ID'].",'".$this->team['name']."',".$this->team['race_id'].",'".$this->team['strLogo']."',".$this->team['iTeamColor'].",'".$this->team['moto']."','".$this->team['background']."',".$this->team['TV'].",".$this->team['fanfactor'].",".$this->team['gold'].",".$this->team['cheerleaders'].",".$this->team['balms'].",".$this->team['apothecary'].",".$this->team['reroll'] .",".$this->team['edited'].",".$this->team['idlistfilters'].",".$this->team['str_f_background'].",".$this->team['idstrlocalmoto'].",".$this->team['inextpurchas'].");";
			$this->db_status = $this->_db_con->exec($sql);
	}
	private function insert_team_rankings() {
			$sql = "INSERT INTO 'Team_Rankings' VALUES(".$this->team['rank']['id'].",".$this->team['ID'].",".$this->team['rank']['idRule_types'].",".$this->team['rank']['iseason'].",".$this->team['rank']['igroup'].",".$this->team['rank']['ipoints'].",".$this->team['rank']['idranking'].",".$this->team['rank']['wins'].",".$this->team['rank']['draws'].",".$this->team['rank']['loss'].",".$this->team['TV'].");";
			$this->db_status = $this->_db_con->exec($sql);
	}
	private function insert_stats_team() {
			$sql = "INSERT INTO 'Statistics_Teams' VALUES(1,".$this->team['ID'].",0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);";
			$this->db_status = $this->_db_con->exec($sql);
	}
	private function insert_stats_season_team() {
			$sql = "INSERT INTO 'Statistics_Season_Teams' VALUES(1,".$this->team['ID'].",1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);";
			$this->db_status = $this->_db_con->exec($sql);
	}
	private function insert_save_gameinfo() {
			$sql = "INSERT INTO 'SavedGameInfo' VALUES(1,'".$this->team['name']."','0','".$this->version."',".$this->team['ID'].",'".$this->team['name']."','".$this->team['strLogo']."',0,0,0,0,".$this->team['TV'].",0,0,0,0,0,'0',0,0,0,0,'0',0,0,'0',0,0);";
			$this->db_status = $this->_db_con->exec($sql);
	}
	private function insert_races() {
			$sql = "INSERT INTO 'Races' VALUES(".$this->races['id'].",'".$this->races['name']."',".$this->races['strlocal'].",".$this->races['strlocalinfo'].",'',".$this->races['reroll_price'].");";
			$this->db_status = $this->_db_con->exec($sql);
	}
	private function insert_player_listings(){
		$sql = '';
		foreach ($this->players as $i=> $player) {
			$sql .= "INSERT INTO 'Player_Listing' VALUES(".$player['ID'].",".$player['idPlayer_Names'].",'".$player['strName']."',".$player['idPlayer_Types'].",".$player['idTeam_Listing'].",".$player['idTeam_Listing_Previous'].",".$player['idRaces'].",".$player['iPlayerColor'].",".$player['iSkinScalePercent'].",".$player['iSkinMeshVariant'].",".$player['iSkinTextureVariant'].",'".$player['fAgeing real']."',".$player['iNumber'].",".$player['Characteristics_fMovementAllowance'].",".$player['Characteristics_fStrength'].",".$player['Characteristics_fAgility'].",".$player['Characteristics_fArmourValue'].",".$player['idPlayer_Levels'].",".$player['iExperience'].",".$player['idEquipment_Listing_Helmet'].",".$player['idEquipment_Listing_Pauldron'].",".$player['idEquipment_Listing_Gauntlet'].",".$player['idEquipment_Listing_Boot'].",".$player['Durability_iHelmet'].",".$player['Durability_iPauldron'].",".$player['Durability_iGauntlet'].",".$player['Durability_iBoot'].",".$player['iSalary'].",".$player['Contract_iDuration'].",".$player['Contract_iSeasonRemaining'].",".$player['idNegotiation_Condition_Types'].",".$player['Negotiation_iRemainingTries'].",".$player['Negotiation_iConditionDemand'].",".$player['iValue'].",".$player['iMatchSuspended'].",".$player['iNbLevelsUp'].",".$player['LevelUp_iRollResult'].",".$player['LevelUp_iRollResult2'].",".$player['LevelUp_bDouble'].",".$player['bGenerated'].",".$player['bStar'].",".$player['bEdited'].",".$player['bDead'].",'".$player['strLevelUp']."');";
			
		}
		$this->db_status = $this->_db_con->exec($sql);
	}
	private function insert_player_skills(){
			if(isset($this->playerskills)) {
				$sql = '';
				foreach($this->playerskills as $i => $skill) {
					$sql .= "INSERT INTO 'Player_Skills' VALUES(".$i.",".$skill['idPlayer'].",".$skill['idskill'].");";
				}
				$this->db_status = $this->_db_con->exec($sql);
			}
	}
	private function insert_player_types($sql){
				$this->db_status = $this->_db_con->exec($sql);
				$sql = "Delete from Player_Types where idRaces <> ".$this->races['id'].";";
				$this->db_status = $this->_db_con->exec($sql);
				// everyone can hire Morg
				$sql = "INSERT INTO player_types VALUES (53, 'AllStar_Orc_MorgNThorg', 0, 5, 0, 102474, 'Morg ''n'' Thorg', 50, 80, 50, 91.666, 430000, 1);";
				$this->db_status = $this->_db_con->exec($sql);
	}
	private function insert_player_types_skills($sql){
				$this->db_status = $this->_db_con->exec($sql);
				$sql = "Delete from Player_Type_skills where idPlayer_types not in (select distinct id from player_types);";
				$this->db_status = $this->_db_con->exec($sql);
	}
	private function insert_equipment_listings($sql){
				//echo "equipmetinsert:: ".$sql."<hr color=blue/>";
				$this->db_status = $this->_db_con->exec($sql);
				$sql = "Delete from Equipment_listing where idRaces <> ".$this->races['id'].";";
				$this->db_status = $this->_db_con->exec($sql);
	}
	
	private function insert_player_casualty(){
			if(isset($this->casualty)) {
				$sql =''; 
				foreach($this->casualty as $i => $cas) {
					$sql .= "INSERT INTO player_casualties VALUES (".$i.",".$cas['idPlayer'].",".$cas['idcasualty'].");";
				}
				$this->db_status = $this->_db_con->exec($sql);
			}
	}
	private function insert_player_types_skill_double($sql){
				$this->db_status = $this->_db_con->exec($sql);
				$sql = "Delete from Player_Type_Skill_Categories_Double where idPlayer_types not in (select distinct id from player_types);";
				$this->db_status = $this->_db_con->exec($sql);
	}
	private function insert_player_types_skill_normal($sql){
				$this->db_status = $this->_db_con->exec($sql);
				$sql = "Delete from player_type_skill_categories_normal where idPlayer_types not in (select distinct id from player_types);";
				$this->db_status = $this->_db_con->exec($sql);
	}	
	private function close_team_db($db_type) {
		if($db_type == 1) {
			$this->_db_con->close();
		}
		if($db_type == 2){
			$this->_db_con = null;
		}
	}
		
}	
	
class cyanide {
	var $team;
	var $players;
    var $race_id;
    var $race;
	var $player_skills;	
	var $skill_count;
	var $cas_count;	
	var $casualty;
		
	public function set_team_id($int) {
		$this->team['ID'] = $int;
	} 
	public function set_team_name($str) {
		$this->team['name'] = $str;
	}
	public function set_team_race_id($int) {
		$this->team['race_id'] = $int;
	}
	public function set_team_logo($str) {
		$this->team['strLogo'] = $str;
	}
	public function set_team_color($int) {
		$this->team['iTeamColor'] = $int;
	}
	public function set_team_moto($str) {
		$this->team['moto'] = $str;
	}
	public function set_team_background($str){
		$this->team['background'] = $str;
	}
	public function set_team_value($int) {
		$this->team['TV'] = $int;
	}
	public function set_team_fanfactor($int) {
		$this->team['fanfactor'] = $int;
	}
	public function set_team_gold($int) {
		$this->team['gold'] = $int;
	}
    public function set_team_cheerleaders($int) {
		$this->team['cheerleaders'] = $int;
	}
	public function set_team_constants(){
		$this->team['balms'] = 0;
		$this->team['edited'] = 0;
		$this->team['idlistfilters'] = 2;
		$this->team['str_f_background'] = 0;
		$this->team['idstrlocalmoto'] = 0;
		$this->team['inextpurchas'] = 0;
	}
	public function set_team_apothecary($int) {
		$this->team['apothecary'] = $int;
	}
	public function set_team_rerolls($int) {
		$this->team['reroll'] = $int;
	}
	public function set_team_rank_constants(){
		$this->team['rank']['id'] = 4;
		$this->team['rank']['idRule_types'] = 2;
		$this->team['rank']['iseason'] = 1;
		$this->team['rank']['igroup'] = 1;
		$this->team['rank']['ipoints'] = 0;
		$this->team['rank']['idranking'] = 0;
		$this->team['rank']['wins'] = 0;
		$this->team['rank']['draws'] = 0;
		$this->team['rank']['loss'] = 0;	
	}	
	public function set_reroll_price($str) {
		$reroll['Human'] 	 = 	50000;
	 	$reroll['Dwarf']     = 	40000;
	 	$reroll['Skaven']    = 	60000;
	 	$reroll['Orc']       = 	60000;
	 	$reroll['Lizardman'] = 	60000;
	 	$reroll['Goblin']    = 	60000;
	 	$reroll['WoodElf']   = 	50000;
	 	$reroll['Chaos']     = 	60000;
	 	$reroll['Wood Elf']  = 	50000;
	 	$reroll['DarkElf']   = 	50000;
	 	$reroll['Dark Elf']  = 	50000;
	 	$this->race['reroll_price'] = $reroll[$str];
	}
	public function convert_player_type($str) {
		
		$player_type_map['0']['Morg \'N\' Thorg']=53;
		$player_type_map['1']['Lineman']=1;
		$player_type_map['1']['Catcher']=2;
		$player_type_map['1']['Thrower']=3;
		$player_type_map['1']['Blitzer']=4;
		$player_type_map['1']['Ogre']=5;
		$player_type_map['1']['Griff Oberwald']=37;
		$player_type_map['2']['Grim Ironjaw']=38;
		$player_type_map['2']['Deathroller']=10;
		$player_type_map['2']['Troll Slayer']=9;
		$player_type_map['2']['Blocker']=6;
		$player_type_map['2']['Blitzer']=8;
		$player_type_map['2']['Runner']=7;
		$player_type_map['3']['Rat Ogre']=20;
		$player_type_map['3']['Stormvermin']=19;
		$player_type_map['3']['Blitzer']=19;
		$player_type_map['3']['Gutter Runner']=18;
		$player_type_map['3']['Thrower']=17;
		$player_type_map['3']['Lineman']=16;
		$player_type_map['3']['Headsplitter']=39;
		$player_type_map['4']['Varag Ghoul-Chewer']=43;
		$player_type_map['4']['Troll']=26;
		$player_type_map['4']['Blitzer']=25;
		$player_type_map['4']['Black Orc']=24;
		$player_type_map['4']['Thrower']=23;
		$player_type_map['4']['Goblin']=22;
		$player_type_map['4']['Lineman']=21;
		$player_type_map['5']['Slibli']=42;
		$player_type_map['5']['Kroxigor']=29;
		$player_type_map['5']['Saurus']=28;
		$player_type_map['5']['Skink']=27;
		$player_type_map['6']['Troll']=44;
		$player_type_map['6']['Pogoer']=45;
		$player_type_map['6']['Fanatic']=46;
		$player_type_map['6']['Ripper']=41;
		$player_type_map['6']['Looney']=31;
		$player_type_map['6']['Goblin']=30;
		$player_type_map['7']['Lineman']=11;
		$player_type_map['7']['Catcher']=12;
		$player_type_map['7']['Thrower']=13;
		$player_type_map['7']['Jordell Freshbreeze']=40;
		$player_type_map['7']['Wardancer']=14;
		$player_type_map['7']['Treeman']=15;
		$player_type_map['8']['Beastman']=32;
		$player_type_map['8']['Chaos Warrior']=33;
		$player_type_map['8']['Minotaur']=34;
		$player_type_map['8']['Grashnak Blackhoof']=36;
		$player_type_map['9']['Runner']=48;
		$player_type_map['9']['Assassin']=49;
		$player_type_map['9']['Blitzer']=50;
		$player_type_map['9']['WitchElf']=51;
		$player_type_map['9']['Horkon Heartripper']=52;
		$player_type_map['9']['Lineman']=47;
		
		$out = $player_type_map[$this->race['id']][$str];
		if(!isset($out)) {
			$out = $player_type_map[$this->race['id']]['Lineman'];
		}
		return $out;
	}
	public function convert_race_id($type,$str) {
		// type = cyid for the cyinide id
		//        obbid for obblm id
		// str  = Race Name string from client
		
		$race_map['Chaos']['cyid']  = 8;
		$race_map['Chaos']['cyname']  = "Chaos";
		$race_map['Chaos']['obbid'] = 1;
		$race_map['Chaos']['strlocal'] = 43;
		$race_map['Chaos']['strlocalinfo'] = 340;
		$race_map['Dark Elf']['cyid']  = 9;
		$race_map['Dark Elf']['cyname']  = "DarkElf";
		$race_map['Dark Elf']['obbid'] = 3;
		$race_map['Dark Elf']['strlocal'] = 102099;
		$race_map['Dark Elf']['strlocalinfo'] = 1034;
		$race_map['DarkElf']['obbid'] = 3;
		$race_map['DarkElf']['cyid']  = 9;
		$race_map['Dwarf']['obbid'] = 4;
		$race_map['Dwarf']['cyid']  = 2;
		$race_map['Dwarf']['cyname']  = "Dwarf";
		$race_map['Dwarf']['strlocal'] = 37;
		$race_map['Dwarf']['strlocalinfo'] = 334;
		$race_map['Goblin']['obbid'] = 6;
		$race_map['Goblin']['cyid']  = 6;
		$race_map['Goblin']['cyname']  = "Goblin";
		$race_map['Goblin']['strlocal'] = 41;
		$race_map['Goblin']['strlocalinfo'] = 338;
		$race_map['Human']['obbid'] = 9;
		$race_map['Human']['cyid']  = 1;
		$race_map['Human']['cyname']  = "Human";
		$race_map['Human']['strlocal'] = 36;
		$race_map['Human']['strlocalinfo'] = 33;
		$race_map['Lizardman']['obbid'] = 11;
		$race_map['Lizardman']['cyid']  = 5;
		$race_map['Lizardman']['cyname']  = "Lizardman";
		$race_map['Lizardman']['strlocal'] = 40;
		$race_map['Lizardman']['strlocalinfo'] = 337;
		$race_map['Orc']['obbid'] = 12;
		$race_map['Orc']['cyid']  = 4;
		$race_map['Orc']['cyname']  = "Orc";
		$race_map['Orc']['strlocal'] = 39;
		$race_map['Orc']['strlocalinfo'] = 336;
		$race_map['Skaven']['obbid'] = 19;
		$race_map['Skaven']['cyid']  = 3;
		$race_map['Skaven']['cyname']  = "Skaven";
		$race_map['Skaven']['strlocal'] = 38;
		$race_map['Skaven']['strlocalinfo'] = 335;
		$race_map['Wood Elf']['obbid'] = 20;
		$race_map['Wood Elf']['cyid']  = 7;
		$race_map['Wood Elf']['cyname']  = "WoodElf";
		$race_map['Wood Elf']['strlocal'] = 42;
		$race_map['Wood Elf']['strlocalinfo'] = 339;
		$race_map['WoodElf']['obbid'] = 20;
		$race_map['WoodElf']['cyid']  = 7;
		
		$this->race['id'] = $race_map[$str][$type];
		$this->race['name'] = $race_map[$str]['cyname'];
		$this->race['strlocal'] = $race_map[$str]['strlocal'];
		$this->race['strlocalinfo'] = $race_map[$str]['strlocalinfo'];
	}
	public function set_player_skills($id,$str) {
		//id = player_id
		//str = obblm Skill name String not id
		if(!isset($this->skill_count)) {
			$this->skill_count = 1;
		}
		$skills_map['Strip Ball']=1;
		$skills_map['+1 St']= 2;
		$skills_map['+1 Ag']= 3;
		$skills_map['+1 Ma']= 4;
		$skills_map['+1 Av']= 5;
		$skills_map['Catch']=6;
		$skills_map['Dodge']=7;
		$skills_map['Sprint']=8;
		$skills_map['Pass Block']=9;
		$skills_map['Foul Appearance']=10;
		$skills_map['Leap']=11;
		$skills_map['Extra Arms']=12;
		$skills_map['Mighty Blow']=13;
		$skills_map['Leader']=14;
		$skills_map['Horns']=15;
		$skills_map['Two Heads']=16;
		$skills_map['Stand Firm']=17;
		$skills_map['Always Hungry']=18;
		$skills_map['Regeneration']=19;
		$skills_map['Take Root']=20;
		$skills_map['Accurate']=21;
		$skills_map['Break Tackle']=22;
		$skills_map['Sneaky Git']=23;
		$skills_map['Chainsaw']=25;
		$skills_map['Dauntless']=26;
		$skills_map['Dirty Player']=27;
		$skills_map['Diving Catch']=28;
		$skills_map['Dump-Off']=29;
		$skills_map['Block']=30;
		$skills_map['Bone-Head']=31;
		$skills_map['Very Long Legs']=32;
		$skills_map['Disturbing Presence']=33;
		$skills_map['Diving Tackle']=34;
		$skills_map['Fend']=35;
		$skills_map['Frenzy']=36;
		$skills_map['Grab']=37;
		$skills_map['Guard']=38;
		$skills_map['Hail Mary Pass']=39;
		$skills_map['Juggernaut']=40;
		$skills_map['Jump Up']=41;
		$skills_map['Loner']=44;
		$skills_map['Nerves of Steel']=45;
		$skills_map['No Hands']=46;
		$skills_map['Pass']=47;
		$skills_map['Piling On']=48;
		$skills_map['Prehensile Tail']=49;
		$skills_map['Pro']=50;
		$skills_map['Really Stupid']=51;
		$skills_map['Right Stuff']=52;
		$skills_map['Safe Throw']=53;
		$skills_map['Secret Weapon']=54;
		$skills_map['Shadowing']=55;
		$skills_map['Side Step']=56;
		$skills_map['Tackle']=57;
		$skills_map['Strong Arm']=58;
		$skills_map['Stunty']=59;
		$skills_map['Sure Feet']=60;
		$skills_map['Sure Hands']=61;
		$skills_map['Thick Skull']=63;
		$skills_map['Throw Team-Mate']=64;
		$skills_map['Wild Animal']=67;
		$skills_map['Wrestle']=68;
		$skills_map['Tentacles']=69;
		$skills_map['Multiple Block']=70;
		$skills_map['Kick']=71;
		$skills_map['Kick-Off Return']=72;
		$skills_map['Big Hand']=74;
		$skills_map['Claw/Claws'] = 75;
		$skills_map['Ball & Chain']=76;
		$skills_map['Stab']=77;	
		
		$this->player_skills[$this->skill_count]['idPlayer']= $id; 
		$this->player_skills[$this->skill_count]['idskill']= $skills_map[$str];
		$this->skill_count ++;
	}
	public function set_player_casualty($id,$str){
		if(!isset($this->cas_count)) {
			$this->cas_count = 1;
		}
		$casualties['Badly Hurt']['desc'] ='No long term effect';
		$casualties['Badly Hurt']['id']   =1;
		
		$casualties['Broken Ribs']['desc'] ='Miss next game';
		$casualties['Broken Ribs']['id'] =2;
		
		$casualties['Groin Strain']['desc'] ='Miss next game';
		$casualties['Groin Strain']['id'] =3;
		
		$casualties['Gouged Eye']['desc'] ='Miss next game';
		$casualties['Gouged Eye']['id'] =4;
		
		$casualties['Broken Jaw']['desc'] ='Miss next game';
		$casualties['Broken Jaw']['id'] =5;
		
		$casualties['Fractured Arm']['desc'] ='Miss next game';
		$casualties['Fractured Arm']['id'] =6;
		
		$casualties['Fractured Leg']['desc'] ='Miss next game';
		$casualties['Fractured Leg']['id'] =7;
		
		$casualties['Smashed Hand']['desc'] ='Miss next game';
		$casualties['Smashed Hand']['id'] =8;
		
		$casualties['Pinched Nerve']['desc'] ='Miss next game';
		$casualties['Pinched Nerve']['id'] =9;
		
		$casualties['Damaged Back']['desc'] ='Niggling injury, adds 1 to any subsequent injury r...';
		$casualties['Damaged Back']['id'] =10;
		
		$casualties['Smashed Knee']['desc'] ='Niggling injury, adds 1 to any subsequent injury r...';
		$casualties['Smashed Knee']['id'] =11;
		
		$casualties['Ni']['desc'] ='Niggling injury, adds 1 to any subsequent injury r...';
		$casualties['Ni']['id'] =11;
		
		$casualties['Smashed Hip']['desc'] ='Loses 1 point in Movement Allowance';
		$casualties['Smashed Hip']['id'] =12;
		
		$casualties['Smashed Ankle']['desc'] ='Loses 1 point in Movement Allowance';
		$casualties['Smashed Ankle']['id'] =13;
		
		$casualties['Serious Concussion']['desc'] ='Loses 1 point in Armour Value';
		$casualties['Serious Concussion']['id'] =14;
		
		$casualties['Fractured Skull']['desc'] ='Loses 1 point in Armour Value';
		$casualties['Fractured Skull']['id'] =15;
		
		$casualties['Broken Neck']['desc'] ='Loses 1 point in Agility';
		$casualties['Broken Neck']['id'] =16;
		
		$casualties['Smashed Collar Bone']['desc'] ='Loses 1 point in Strength';
		$casualties['Smashed Collar Bone']['id'] =17;
		
		$casualties['Dead']['desc'] ='Dead';
		$casualties['Dead']['id'] = 18;
		
		
		$this->casualty[$this->cas_count]['idPlayer']= $id; 
		$this->casualty[$this->cas_count]['idcasualty']= $casualties[$str]['id'];
		$this->cas_count ++;
	}
	
	
	public function convert_ma($int) {
		return $int * 8.333;

	}
	public function convert_st($int) {
		$out = ($int*10)+20;
		return $out;
	}
	public function convert_ag($int) {
		return $int * 16.333;
	}
	public function convert_av($int) {
		switch($int){
			case 0:
				$out = 0.000;
				break;
			case 1: 
				$out = 0.990;
				break;
			case 2: 
				$out = 2.768;
			case 3:
				$out = 8.324;
				break;
			case 4:
			 	$out = 16.657;
			 	break;
			case 5:
				$out = 27.768;
				break;
			case 6:
				$out = 41.657;
				break;
			case 7:
				$out = 58.324;
				break;
			case 8:
				$out = 72.213;
				break;
			case 9: 
				$out = 83.324;
				break;
			case 10:
				$out = 91.657;
				break;
			case 11:
				$out = 97.213;
				break;
			case 12: 
				$out = 99.990;
				break;
		}
		return $out;
	}
	public function add_player_to_array($id,$strName,$idPlayer_Types,$idTeam_Listing,$idRaces,$iSkinTextureVariant,$fAgeing_real,$iNumber,$MV,$ST,$AG,$AV,$idPlayer_Levels,$iExperience,$iSalary,$iValue) {
		$data = array(
			"ID" => $id,
			"idPlayer_Names" => 0, 
			"strName" => $strName, 
			"idPlayer_Types" => $idPlayer_Types,
			"idTeam_Listing" => $idTeam_Listing,
			"idTeam_Listing_Previous" => 0,
			"idRaces" => $idRaces,
			"iPlayerColor" => 0,"iSkinScalePercent" => 0,
			"iSkinMeshVariant" => 0,
			"iSkinTextureVariant" => $iSkinTextureVariant, //not sure what this does but it varies will have to play with it set 0 for now;
			"fAgeing real" => $fAgeing_real,
			"iNumber" => $iNumber, // this is the players number int 1-32
			"Characteristics_fMovementAllowance" => $MV, //must be run through function get_cy_mv()
			"Characteristics_fStrength" => $ST, // must be run through function get_cy_mv()
			"Characteristics_fAgility" => $AG, // must be run through function get_cy_mv()
			"Characteristics_fArmourValue" => $AV, // must be run through function get_cy_AV()
			"idPlayer_Levels" => $idPlayer_Levels,
		    "iExperience" => $iExperience, //spp 
			"idEquipment_Listing_Helmet" => 70, //not sure what to do with the equipment related stuff.
			"idEquipment_Listing_Pauldron" => 71, // still trying to figure out the details and if these have to be set.
			"idEquipment_Listing_Gauntlet" => 72, 
			"idEquipment_Listing_Boot" => 73, 
			"Durability_iHelmet" => 0,  
			"Durability_iPauldron" => 0, 
			"Durability_iGauntlet" => 0,
			"Durability_iBoot" => 0,  //end equipment
			"iSalary" => $iSalary, //Example base lineman salary for DarkElf = 70,000k this has to do with TV and expenses
			"Contract_iDuration" => 0,
			"Contract_iSeasonRemaining" => 0,
			"idNegotiation_Condition_Types" => 0,
			"Negotiation_iRemainingTries" => 0, 
			"Negotiation_iConditionDemand" => 0, 
			"iValue" => $iValue, // may need to tinker here... I think this is total player value in k so 120,000 = 120 
			"iMatchSuspended" => 0, //think we can use this to show injured players and not have them playable for the match.. would have to set thier injuries in the casualty table leave 0 for now 
			"iNbLevelsUp" => 0, // Related to in game level up NA for obblm since level is handled web side 
			"LevelUp_iRollResult" => 0,// Related to in game level up NA for obblm since level is handled web side 
			"LevelUp_iRollResult2" => 0, // Related to in game level up NA for obblm since level is handled web side  
			"LevelUp_bDouble" => 0, // Related to in game level up NA for obblm since level is handled web side  
			"bGenerated" => 0, // Related to in game level up NA for obblm since level is handled web side  
			"bStar" => 0, // Related to in game level up NA for obblm since level is handled web side 
			"bEdited" => 0, // Checked for in game editor.. not useable in some modes if true 
			"bDead" => 0, // true false,, not needed since we handle death web side 
			"strLevelUp" => '0'// Related to in game level up NA for obblm since level is handled web side 
		);
		$this->players[$id] = $data;
	}
}

?>
