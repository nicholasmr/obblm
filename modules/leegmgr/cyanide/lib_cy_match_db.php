<?php
/*
 *  Copyright (c) Ryan Williams <email protected> 2010. All Rights Reserved.
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


//class tester
	
//	$file = sys_get_temp_dir().'MatchReport.sqlite';
//	$cymatch = new cy_match_db($file);
//

class cy_match_db {
	var $_db_read;
	var $file;
	var $error;
	var $db_status;
	var $db_engine;
	var $homeid;
	var $awayid;
	private $sql;
	 public $winner = '';
     public $concession = false;
     public $gate = 0;
     public $hash = '';
     public $hometeam = '';
        public $homescore = 0; //set
        public $homewinnings = 0;
        public $homeff = 0;
        public $homefame = 0;
        public $hometransferedGold = 0;
        public $homeplayers;
        public $tv_home = 0;
        public $homefans = 0;
     public $awayteam = '';
        public $awayscore = 0; // set
        public $awaywinnings = 0;
        public $awayff = 0;
        public $awayfame = 0;
        public $awaytransferedGold = 0;
        public $awayplayers;
        public $awayfans = 0;
        public $tv_away = 0;

    public $hometeam_id = 0;
    public $awayteam_id = 0;
    public $match_id = 0;
	
	
	function __construct($file) {
		$this->file = $file;
		$this->db_engine = 2; //PDO = 2 SQLite3 = 1
		//echo $file;
		if(file_exists($this->file)) {
			//open the DB
			$this->db_status = $this->load_match_db($this->db_engine);
			//set home team
			$this->set_home_team();
			//set away team
			$this->set_away_team();
			//Set team id's
			$this->set_team_ids();
			//set home score
			$this->set_home_score();
			//set away score
			$this->set_away_score();
			//set winner
			$this->set_winner();
			//set concession true false
			$this->set_concession();
			//set home ff and TV
			$this->set_home_tvff();
			//set away ff and TV
			$this->set_away_tvff();
			//Fans and Fame
			$this->set_home_fans();
			$this->set_away_fans();
			// Set home Fame
			$this->set_home_fame();
			//set away fame
			$this->set_away_fame();
			//set home winningd();
			$this->set_home_winnings();
			//set away winnings
			$this->set_away_winnings();
			//set gate
			// set new fan factor home;
			$this->set_home_ff_new();
			// set new fan factor away;
			$this->set_away_ff_new();
			// set home players array
			$this->set_players('Home');
			// set away players array
			$this->set_players('Away'); 
			// set away players array
			$this->set_gate();
			//close db
			$this->close_match_db($this->db_engine);
		} else {
			$this->error = "Unable to locate file in ".$this->file;
		}
		//echo $this->error;
		//echo $this->db_status;
	}
	private function load_match_db($db_type){
		if ($db_type == 1) {// use SQLite3 class
			$this->_db_read = new SQLite3($this->file);
		}
		if ($db_type == 2) {// use PDO class
			//echo $this->file_loc.$this->team['name'].'.db';
			$this->_db_read = new PDO('sqlite:'.$this->file);
		}
	}
	private function close_match_db($db_type) {
		if($db_type == 1) {
			$this->_db_read->close();
		}
		if($db_type == 2){
			$this->_db_read = null;
		}
	}
	//private function query_db() {
	//	return $this->_db_read->query($this->sql);
	//}
	private function set_team_ids() {
		$this->set_sql(11);
		foreach ($this->_db_read->query($this->sql) as $row) {
			$this->homeid = $row['idTeam_Listing_Home'];
			$this->awayid = $row['idTeam_Listing_Away'];
    	}
	}
	private function set_home_team() {
		$this->set_sql(3);
		foreach ($this->_db_read->query($this->sql) as $row) {
			$this->hometeam = $row['strName'];
    	}
	}
	private function set_away_team() {
		$this->set_sql(4);
		foreach ($this->_db_read->query($this->sql) as $row) {
			$this->awayteam = $row['strName'];
    	}
	}
	private function set_home_score() {
		$this->set_sql(1);
		foreach ($this->_db_read->query($this->sql) as $row) {
			$this->homescore = $row['Home_iScore'];
    	}
		
	}
	private function set_away_score() {
		$this->set_sql(2);
		foreach ($this->_db_read->query($this->sql) as $row) {
			$this->awayscore = $row['Away_iScore'];
    	}
		
	}
	private function set_winner() {
		if($this->homescore > $this->awayteam) {
			//winer is home
			$this->winner = $this->hometeam;
		} elseif($this->awayscore > $this->homescore) {
			$this->winner = $this->awayteam;
		} elseif ($this->awayscore == $this->homescore) {
			//Tie
			$this->winner = '';
		} else {
			$this->error = "There has been a problem match 
			winner could not be set";
		}
		
	}
	private function set_concession() {
		//get home_team_mvp
		$this->set_sql(5);
		foreach ($this->_db_read->query($this->sql) as $row) {
			$result = $row['count'];
			//if 0 or 2 set concession true
			if($result == 0 or $result == 2) {
				$this->concession = true;
			} else {
				$this->concession = false;
			}
    	}
		
	}
	private function set_gate(){
		//$this->set_sql(6);
		//foreach ($this->_db_read->query($this->sql) as $row) {
		//	$this->gate = $row['iSpectators'];
		//}
		$this->gate = $this->awayfans + $this->homefans;
		
	}
	private function set_home_tvff() {
		$this->set_sql(7);
		foreach ($this->_db_read->query($this->sql) as $row) {
			$this->homeff = $row['iPopularity'];
			$this->tv_home = $row['iValue'];
		}
	}
	private function set_away_tvff() {
		$this->set_sql(8);
		foreach ($this->_db_read->query($this->sql) as $row) {
			$this->awayff = $row['iPopularity'];
			$this->tv_away = $row['iValue'];
		}
	}
	private function set_home_fans() {
		$d1 = rand(1,6);
		$d2 = rand(1,6);
		$this->homefans = ($d1 + $d2 + $this->homeff)*1000;
		
	}
    private function set_away_fans() {
		$d1 = rand(1,6);
		$d2 = rand(1,6);
		$this->awayfans = ($d1 + $d2 + $this->awayff)*1000;
		
	}
	private function set_home_fame() {
		if($this->homefans <= $this->awayfans) {
			$this->homefame = 0;
		} elseif ($this->homefans > (2 * $this->awayfans)) {
			$this->homefame = 2;
		} elseif ($this->homefans > $this->awayfans) {
			$this->homefame = 1;
		} else {
			$this->error = "There has been a error gathering home team fame. This error should not occrue";
		}
	}
	private function set_away_fame() {
		if($this->awayfans <= $this->homefans) {
			$this->awayfame = 0;
		} elseif ($this->awayfans > (2 * $this->homefans)) {
			$this->awayfame = 2;
		} elseif ($this->awayfans > $this->homefans) {
			$this->awayfame = 1;
		} else {
			$this->error = "There has been a error gathering home team fame. This error should not occrue";
		}
	}
	private function set_home_winnings() {
		$d1 = rand(1,6);
		$cash = ($d1 + $this->homefame) *10000;
		if( ($this->winner == $this->hometeam) OR $this->winner == '') {
			$cash = $chash + 10000;
		}
		$this->homewinnings = $cash;
	}
	private function set_away_winnings() {
		$d1 = rand(1,6);
		$cash = ($d1 + $this->awayfame) *10000;
		if( ($this->winner == $this->awayteam) OR $this->winner == '') {
			$cash = $cash + 10000;
		}
		$this->awaywinnings = $cash;
	}
	private function set_home_ff_new() {
		$cff = $this->homeff;
		$d1  = rand(1,6);
		$d2  = rand(1,6);
		$win = false;
		if($this->winner == $this->hometeam) {
			$d3 = rand(1,6);
			$win = true;
		} else {
			$d3 = 0;
		}
		$rr = $d1 + $d2 +$d3 ;
		if ($win == true) {
			if($rr > $cff) {
				$this->homeff = $cff + 1;
			}
		} else {
			if($rr < $cff) {
				$this->homeff = $cff - 1;
			}
		}
	}
	private function set_away_ff_new() {
		$cff = $this->awayff;
		$d1  = rand(1,6);
		$d2  = rand(1,6);
		$win = false;
		if($this->winner == $this->awayteam) {
			$d3 = rand(1,6);
			$win = true;
		} else {
			$d3 = 0;
		}
		$rr = $d1 + $d2 +$d3 ;
		if ($win == true) {
			if($rr > $cff) {
				$this->awayff = $cff + 1;
			}
		} else {
			if($rr < $cff) {
				$this->awayff = $cff - 1;
			}
		}
	}
	private function set_players($t) {
		if ($t == 'Home') {
			$this->set_sql(9);
			
			$players = '';
			foreach ($this->_db_read->query($this->sql) as $row) {
				$players[$row['iNumber']]['ID'] = $row['ID'];
				$players[$row['iNumber']]['nr'] = $row['iNumber'];
				$players[$row['iNumber']]['name'] = $row['strName'];
				if($row['bStar'] >= 1) {
					$players[$row['iNumber']]['star'] = true;
				} else {
					$players[$row['iNumber']]['star'] = false;
				}
				$players[$row['iNumber']]['merc'] = false;
				$players[$row['iNumber']]['ir1_d1'] = rand(1,6);
				$players[$row['iNumber']]['ir1_d2'] = rand(1,6);
				$players[$row['iNumber']]['ir2_d1'] = rand(1,6);
				$players[$row['iNumber']]['ir2_d2'] = rand(1,6);
				$players[$row['iNumber']]['ir3_d1'] = rand(1,6);
				$players[$row['iNumber']]['ir3_d2'] = rand(1,6);
				
				//get player stats
				$this->sql = "Select * from Home_Statistics_Players where idPlayer_Listing = ".$players[$row['iNumber']]['ID']." Limit 1";
				$stats = '';
				foreach ($this->_db_read->query($this->sql) as $stats) {
					$players[$row['iNumber']]['mvp'] = $stats['iMVP'];
					$players[$row['iNumber']]['cp']  = $stats['Inflicted_iPasses'];
					$players[$row['iNumber']]['td']  = $stats['Inflicted_iTouchdowns'];
					$players[$row['iNumber']]['intcpt'] = $stats['Inflicted_iInterceptions'];
					$players[$row['iNumber']]['bh'] = $stats['Inflicted_iCasualties'];
					$players[$row['iNumber']]['si'] = $stats['Inflicted_iInjuries'];
					$players[$row['iNumber']]['ki'] = $stats['Inflicted_iDead'];
				}
				$this->sql = "Select idPlayer_Casualty_Types from Home_Player_Casualties where idPlayer_Listing =".$players[$row['iNumber']]['ID'];
				$cas = "";
				foreach ($this->_db_read->query($this->sql) as $cas) {
						$players[$row['iNumber']]['inj'] = $cas['idPlayer_Casualty_Types'];
				}
				if(isset($cas['idPlayer_Casualty_Types'])){
					$players[$row['iNumber']]['inj'] = $cas['idPlayer_Casualty_Types'];
					$players[$row['iNumber']]['inj'] = $this->set_inj($players[$row['iNumber']]['inj']);
				} else {
					$players[$row['iNumber']]['inj'] = NONE;
				}
			}
			$this->homeplayers = $players;
		} elseif ($t == 'Away') {
			$this->set_sql(10);
			$players = '';
			foreach ($this->_db_read->query($this->sql) as $row) {
				$players[$row['iNumber']]['ID'] = $row['ID'];
				$players[$row['iNumber']]['nr'] = $row['iNumber'];
				$players[$row['iNumber']]['name'] = $row['strName'];
				if($row['bStar'] >= 1) {
					$players[$row['iNumber']]['star'] = true;
				} else {
					$players[$row['iNumber']]['star'] = false;
				}
				$players[$row['iNumber']]['merc'] = false;
				$players[$row['iNumber']]['ir1_d1'] = rand(1,6);
				$players[$row['iNumber']]['ir1_d2'] = rand(1,6);
				$players[$row['iNumber']]['ir2_d1'] = rand(1,6);
				$players[$row['iNumber']]['ir2_d2'] = rand(1,6);
				$players[$row['iNumber']]['ir3_d1'] = rand(1,6);
				$players[$row['iNumber']]['ir3_d2'] = rand(1,6);
				
				//get player stats
				$this->sql = "Select * from Away_Statistics_Players where idPlayer_Listing = ".$players[$row['iNumber']]['ID']." Limit 1";
				$stats = '';
				foreach ($this->_db_read->query($this->sql) as $stats) {
					$players[$row['iNumber']]['mvp'] = $stats['iMVP'];
					$players[$row['iNumber']]['cp']  = $stats['Inflicted_iPasses'];
					$players[$row['iNumber']]['td']  = $stats['Inflicted_iTouchdowns'];
					$players[$row['iNumber']]['intcpt'] = $stats['Inflicted_iInterceptions'];
					$players[$row['iNumber']]['bh'] = $stats['Inflicted_iCasualties'];
					$players[$row['iNumber']]['si'] = $stats['Inflicted_iInjuries'];
					$players[$row['iNumber']]['ki'] = $stats['Inflicted_iDead'];
				}
				$this->sql = "Select idPlayer_Casualty_Types from Away_Player_Casualties where idPlayer_Listing =".$players[$row['iNumber']]['ID'];
			
				$cas = "";
				foreach ($this->_db_read->query($this->sql) as $cas) {
						$players[$row['iNumber']]['inj'] = $cas['idPlayer_Casualty_Types'];
				}
				if(isset($cas['idPlayer_Casualty_Types'])){
			
					$players[$row['iNumber']]['inj'] = $cas['idPlayer_Casualty_Types'];
					$players[$row['iNumber']]['inj'] = $this->set_inj($players[$row['iNumber']]['inj']);
				} else {
					$players[$row['iNumber']]['inj'] = NONE;
				}
			}
			$this->awayplayers = $players;
		}
	}
	private function set_inj($id) {
		 switch ( $id ) {
		 	case 1:
		 		$out = NONE;
		 		break;
		 	case 2: 
		 		$out = MNG;
		 		break;
		 	case 3: 
		 		$out = MNG;
		 		break;
		 	case 4: 
		 		$out = MNG;
		 		break;
		 	case 5: 
		 		$out = MNG;
		 		break;
		 	case 6: 
		 		$out = MNG;
		 		break;
		 	case 7: 
		 		$out = MNG;
		 		break;
		 	case 8: 
		 		$out = MNG;
		 		break;
		 	case 9: 
		 		$out = MNG;
		 		break;
		 	case 10: 
		 		$out = NI;
		 		break;
		 	case 11: 
		 		$out = NI;
		 		break;
		 	case 12:
		 		$out = MA;
		 		break;
		 	case 13:
		 		$out = MA;
		 		break;
		 	case 14:
		 		$out = AV;
		 		break;
		 	case 15:
		 		$out = AV;
		 		break;
		 	case 16:
		 		$out = AG;
		 		break;
		 	case 17:
		 		$out = ST;
		 		break;
		 	case 18:
		 		$out = DEAD;
		 		break;
			default:
                $out = NONE;
                break;
		 		
		 		
		 }
		 return $out;
		
	}
	private function set_sql($id){
		switch($id) {
			case 1:
				$this->sql = "Select Home_iScore from Calendar where ID = 1 Limit 1"; //get homescore
				break;
			case 2:
				$this->sql = "Select Away_iScore from Calendar where ID = 1 Limit 1"; //get awayscore
				break;
			case 3:
				$this->sql = "Select strName from Home_Team_Listing Where ID = 1 Limit 1"; // get home team name
				break;
			case 4:
				$this->sql = "Select strName from Away_Team_Listing Where ID = 1 Limit 1"; // get home team name
				break;
			case 5:
				$this->sql = "SELECT count(iMVP) as 'count' from Home_Statistics_Players where iMVP > 0"; // concession check
				break;
			case 6:
				$this->sql = "SELECT iSpectators from Calendar where ID = 1 Limit 1"; // get gate
				break;
			case 7:
				$this->sql = "SELECT iValue, iPopularity from Home_Team_Listing where ID = 1 Limit 1"; // get gate
				break;
			case 8:
				$this->sql = "SELECT iValue, iPopularity from Away_Team_Listing where ID = 1 Limit 1"; // get gate
				break;
			case 9;
				$this->sql = "SELECT * from Home_Player_Listing where idTeam_Listing = ".$this->homeid."";
				break;
			case 10;
				$this->sql = "SELECT * from Away_Player_Listing where idTeam_Listing = ".$this->awayid."";
				break;
			case 11; 
				$this->sql ="SELECT idTeam_Listing_Home,idTeam_Listing_Away From Calendar where ID = 1 Limit 1";
				break;
		}
	}

}

?>