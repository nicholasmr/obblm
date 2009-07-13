<?php

class CyanideMatchReport
{
	/***************
	 * Properties 
	 ***************/
	 
	// MySQL stored information
	public $spectators = 0;
	
	public $home_team_name = "";
	public $home_team_score = 0;
	public $home_team_cash_earned = 0;
	public $home_team_ff_variation = 0;
	public $home_team_inflicted_cas = 0;
	private $home_team_ff = 0;
	private $home_team_value = 0;
	public $home_team_fans = 0;
	
	public $away_team_name = "";
	public $away_team_score = 0;
	public $away_team_cash_earned = 0;
	public $away_team_ff_variation = 0;
	public $away_team_inflicted_cas = 0;
	private $away_team_ff = 0;
	private $away_team_value = 0;
	public $away_team_fans = 0;
	
	// ['name', 'mvp', 'cp', 'td', 'int', 'cas', 'ki', 'inj']
	public $home_team_player_data = array();
	public $away_team_player_data = array();
	
	
	 
	/***************
	 * Methods 
	 ***************/
	
	function __construct($sqlite_file_path) {
		
		$match_report_content = file_get_contents($sqlite_file_path);
		$match_report_crc = crc32($match_report_content);
		
		// use the crc of the match report as a seed so that reuploading the match report does not change results
		srand($match_report_crc);
		
		$match_report_db = new PDO("sqlite:" . $sqlite_file_path);
		
		// team names, scores, casulties
		$query = "SELECT A.strName AS Away_strName, Away_iScore, Away_Inflicted_iCasualties, Away_Inflicted_iDead, H.strName AS Home_strName, Home_iScore, Home_Inflicted_iCasualties, Home_Inflicted_iDead, iSpectators FROM Calendar, Away_Team_Listing A, Home_Team_Listing H";
		if (!($result = $match_report_db->query($query)))
			return false;
		
		if (!($array = $result->fetchAll()) || sizeof($array) !=1 )
			return false;

		$row = $array[0];
		
		$this->away_team_name = $row['Away_strName'];
		$this->away_team_score = $row['Away_iScore'];
		$this->away_team_inflicted_cas = $row['Away_Inflicted_iCasualties'] + $row['Away_Inflicted_iDead'];
		$this->home_team_name = $row['Home_strName'];
		$this->home_team_score = $row['Home_iScore'];
		$this->home_team_inflicted_cas = $row['Home_Inflicted_iCasualties'] + $row['Home_Inflicted_iDead'];
		$this->spectators = $row['iSpectators'];
		
		// team ff, values
		$query = "SELECT A.iPopularity AS Away_ff, A.iValue AS Away_value, H.iPopularity AS Home_ff, H.iValue AS Home_value FROM Away_Team_Listing A, Home_Team_Listing H;";
		if (!($result = $match_report_db->query($query)))
			return false;
		
		if (!($array = $result->fetchAll()) || sizeof($array) !=1 )
			return false;
			
		$row = $array[0];
		$this->away_team_ff = $row['Away_ff'];
		$this->away_team_value = $row['Away_value'];
		$this->home_team_ff = $row['Home_ff'];
		$this->home_team_value = $row['Home_value'];
		
		// Away players
		$query = "SELECT strName AS name, iMVP AS mvp, Inflicted_iPasses AS cp, Inflicted_iTouchdowns AS td, Inflicted_iInterceptions AS int, Inflicted_iCasualties AS cas, Inflicted_iDead AS ki, idPlayer_Casualty_Types AS inj_type, Sustained_iDead As dead FROM Away_Statistics_Players S JOIN Away_Player_Listing L ON S.idPlayer_Listing=L.ID LEFT JOIN Away_Player_Casualties C ON S.idPlayer_Listing=C.idPlayer_Listing";
		if (!($result = $match_report_db->query($query)))
			return false;
		
		if (!($array = $result->fetchAll()))
			return false;
		
		foreach ($array as $row) {
			/*
			 * Set injury type
			 */
			
			$tmp_inj = NONE;
			
			if ($row['inj_type']) {
				if ($row['inj_type'] > 1 && $row['inj_type'] < 10) {
					$tmp_inj = MNG;
				}
				else if ($row['inj_type'] == 10 || $row['inj_type'] == 11) {
					$tmp_inj = NI;
				}
				else if ($row['inj_type'] == 12 || $row['inj_type'] == 13) {
					$tmp_inj = MA;
				}
				else if ($row['inj_type'] == 14 || $row['inj_type'] == 15) {
					$tmp_inj = AV;
				}
				else if ($row['inj_type'] == 16) {
					$tmp_inj = AG;
				}
				else if ($row['inj_type'] == 17) {
					$tmp_inj = ST;
				}
				else if ($row['inj_type'] == 18) {
					$tmp_inj = DEAD;
				}
			}
			
			if ($row['dead'] == 1)
				$tmp_inj = DEAD;
			
			$row['inj'] = $tmp_inj;
			
			array_push($this->away_team_player_data, $row);
		}
		
		// Home players
		$query = "SELECT strName AS name, iMVP AS mvp, Inflicted_iPasses AS cp, Inflicted_iTouchdowns AS td, Inflicted_iInterceptions AS int, Inflicted_iCasualties AS cas, Inflicted_iDead AS ki, idPlayer_Casualty_Types AS inj_type, Sustained_iDead As dead FROM Home_Statistics_Players S JOIN Home_Player_Listing L ON S.idPlayer_Listing=L.ID LEFT JOIN Home_Player_Casualties C ON S.idPlayer_Listing=C.idPlayer_Listing";
		if (!($result = $match_report_db->query($query)))
			return false;
		
		if (!($array = $result->fetchAll()))
			return false;
		
		foreach ($array as $row) {
			$tmp_inj = NONE;
			
			if ($row['inj_type']) {
				if ($row['inj_type'] > 1 && $row['inj_type'] < 10) {
					$tmp_inj = MNG;
				}
				else if ($row['inj_type'] == 10 || $row['inj_type'] == 11) {
					$tmp_inj = NI;
				}
				else if ($row['inj_type'] == 12 || $row['inj_type'] == 13) {
					$tmp_inj = MA;
				}
				else if ($row['inj_type'] == 14 || $row['inj_type'] == 15) {
					$tmp_inj = AV;
				}
				else if ($row['inj_type'] == 16) {
					$tmp_inj = AG;
				}
				else if ($row['inj_type'] == 17) {
					$tmp_inj = ST;
				}
				else if ($row['inj_type'] == 18) {
					$tmp_inj = DEAD;
				}
			}
			
			if ($row['dead'] == 1)
				$tmp_inj = DEAD;
			
			$row['inj'] = $tmp_inj;
			
			array_push($this->home_team_player_data, $row);
		}
		
		$home_team_fans = (rand(2,12) + $this->home_team_ff) * 1000; // (2D6 + ff) * 1000
		$away_team_fans = (rand(2,12) + $this->away_team_ff) * 1000; // (2D6 + ff) * 1000
		
		if ($home_team_fans <= $away_team_fans)
			$home_team_fame = 0;
		else if ($home_team_fans >= 2*$away_team_fans)
			$home_team_fame = 2;
		else
			$home_team_fame = 1;
		
		if ($away_team_fans <= $home_team_fans)
			$away_team_fame = 0;
		else if ($away_team_fans >= 2*$home_team_fans)
			$away_team_fame = 2;
		else
			$away_team_fame = 1;
		
		// ff and money variations
		$home_cash_earned = (rand(1,6) + $home_team_fame) * 10; // D6 + fame
		$away_cash_earned = (rand(1,6) + $away_team_fame) * 10; // D6 + fame
		
		if ($this->home_team_score > $this->away_team_score) {
			// home team won
			$home_cash_earned += 10;
			
			$home_ff_roll = rand(3,18); // 3D6
			$away_ff_roll = rand(2,12); // 2D6
			
			if ($home_ff_roll > $this->home_team_ff)
				$this->home_team_ff_variation = 1;
			
			if ($away_ff_roll < $this->away_team_ff)
				$this->away_team_ff_variation = -1;
		}
		else if ($this->home_team_score == $this->away_team_score) {
			// draw
			
			$home_ff_roll = rand(2,12); // 2D6
			$away_ff_roll = rand(2,12); // 2D6
			
			if ($home_ff_roll > $this->home_team_ff)
				$this->home_team_ff_variation = 1;
			else if ($home_ff_roll < $this->home_team_ff)
				$this->home_team_ff_variation = -1;
				
			if ($away_ff_roll > $this->away_team_ff)
				$this->away_team_ff_variation = 1;
			else if ($away_ff_roll < $this->away_team_ff)
				$this->away_team_ff_variation = -1;
		}
		else {
			// away team won
			$away_cash_earned += 10;
			
			$home_ff_roll = rand(2,12); // 2D6
			$away_ff_roll = rand(3,18); // 3D6
			
			if ($home_ff_roll < $this->home_team_ff)
				$this->home_team_ff_variation = -1;
				
			if ($away_ff_roll > $this->away_team_ff)
				$this->away_team_ff_variation = 1;
		}
		
		// home team spiralling expenses
		if ($this->home_team_value > 1750 && $this->home_team_value <= 1890) {
			$home_cash_earned -= 10;
		}
		else if ($this->home_team_value > 1890 && $this->home_team_value <= 2040) {
			$home_cash_earned -= 20;
		}
		else if ($this->home_team_value > 2040 && $this->home_team_value <= 2190) {
			$home_cash_earned -= 30;
		}
		else if ($this->home_team_value > 2190 && $this->home_team_value <= 2340) {
			$home_cash_earned -= 40;
		}
		else if ($this->home_team_value > 2340 && $this->home_team_value <= 2490) {
			$home_cash_earned -= 50;
		}
		else if ($this->home_team_value > 2490 && $this->home_team_value <= 2640) {
			$home_cash_earned -= 60;
		}
		else if ($this->home_team_value > 2640 && $this->home_team_value <= 2790) {
			$home_cash_earned -= 70;
		}
		else if ($this->home_team_value > 2790) {
			$home_cash_earned -= 80;
		}
		
		if ($home_cash_earned < 0)
			$home_cash_earned = 0;
		
		
		// away team spiralling expenses
		if ($this->away_team_value > 1750 && $this->away_team_value <= 1890) {
			$away_cash_earned -= 10;
		}
		else if ($this->away_team_value > 1890 && $this->away_team_value <= 2040) {
			$away_cash_earned -= 20;
		}
		else if ($this->away_team_value > 2040 && $this->away_team_value <= 2190) {
			$away_cash_earned -= 30;
		}
		else if ($this->away_team_value > 2190 && $this->away_team_value <= 2340) {
			$away_cash_earned -= 40;
		}
		else if ($this->away_team_value > 2340 && $this->away_team_value <= 2490) {
			$away_cash_earned -= 50;
		}
		else if ($this->away_team_value > 2490 && $this->away_team_value <= 2640) {
			$away_cash_earned -= 60;
		}
		else if ($this->away_team_value > 2640 && $this->away_team_value <= 2790) {
			$away_cash_earned -= 70;
		}
		else if ($this->away_team_value > 2790) {
			$away_cash_earned -= 80;
		}
		
		if ($away_cash_earned < 0)
			$away_cash_earned = 0;
		
		$this->home_team_fans = $home_team_fans;
		$this->away_team_fans = $away_team_fans;
		$this->home_team_cash_earned = $home_cash_earned;
		$this->away_team_cash_earned = $away_cash_earned;
	}
	
	/*
	 * Reverses home team and away team
	 */
	public function reverseHomeAndAway () {
		
		$tmp_team_name = $this->home_team_name;
		$tmp_team_score = $this->home_team_score;
		$tmp_team_cash_earned = $this->home_team_cash_earned;
		$tmp_team_ff_variation = $this->home_team_ff_variation;
		$tmp_team_inflicted_cas = $this->home_team_inflicted_cas;
		$tmp_team_ff = $this->home_team_ff;
		$tmp_team_value = $this->home_team_value;
		$tmp_team_fans = $this->home_team_fans;
		$tmp_team_player_data = $this->home_team_player_data;
		
		$this->home_team_name = $this->away_team_name;
		$this->home_team_score = $this->away_team_score;
		$this->home_team_cash_earned = $this->away_team_cash_earned;
		$this->home_team_ff_variation = $this->away_team_ff_variation;
		$this->home_team_inflicted_cas = $this->away_team_inflicted_cas;
		$this->home_team_ff = $this->away_team_ff;
		$this->home_team_value = $this->away_team_value;
		$this->home_team_fans = $this->away_team_fans;
		$this->home_team_player_data = $this->away_team_player_data;
		
		$this->away_team_name = $tmp_team_name;
		$this->away_team_score = $tmp_team_score;
		$this->away_team_cash_earned = $tmp_team_cash_earned;
		$this->away_team_ff_variation = $tmp_team_ff_variation;
		$this->away_team_inflicted_cas = $tmp_team_inflicted_cas;
		$this->away_team_ff = $tmp_team_ff;
		$this->away_team_value = $tmp_team_value;
		$this->away_team_fans = $tmp_team_fans;
		$this->away_team_player_data = $tmp_team_player_data;
	}
	
}
?>
