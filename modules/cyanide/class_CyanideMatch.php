<?php

/*
 *  Copyright (c) Gr�gory Rom� <email protected> 2009. All Rights Reserved.
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

class CyanideMatch extends Match
{
	public $hash_cyanide = null;

	public function __construct($mid)
	{
		parent::__construct($mid);
		$this->hash_cyanide = get_alt_col('matches', 'match_id', $mid, 'hash_cyanide');
	}

	public function setHashCyanide($hash)
	{
		return mysql_query("UPDATE matches SET hash_cyanide = '".mysql_real_escape_string($hash)."' WHERE match_id = $this->match_id");
	}

	public static function create(array $input)
	{
		/* Like parent but returns match_id of created match */

		return (parent::create($input)
		&& ($result = mysql_query("Select last_insert_id() from matches"))
		&& mysql_num_rows($result) > 0
		&& (list($mid) = array_values(mysql_fetch_assoc($result)))
		&& $mid
		) ? $mid : false;
	}

	public static function parse_file($file)
	{
		$match_report_db = new PDO("sqlite:" . $file);
		$hash = md5($file);

		// team names, scores, casulties
		$query = "SELECT A.strName AS Away_strName,
						Away_iScore,
						Away_Inflicted_iCasualties,
						Away_Inflicted_iDead,
						Away_iCashEarned,
						H.strName AS Home_strName,
						Home_iScore,
						Home_Inflicted_iCasualties,
						Home_Inflicted_iDead,
						Home_iCashEarned
						iSpectators,
						FROM Calendar, Away_Team_Listing A, Home_Team_Listing H";

		if (!($result = $match_report_db->query($query))) {
			return false;
		}

		if (!($array = $result->fetchAll()) || sizeof($array) !=1 ) {
			return false;
		}

		$row = $array[0];


		$gate = $row['iSpectators']/1000;

		$hometeam  = $row['Home_strName'];
		$homescore = $row['Home_iScore'];
		$homewinnings = $row['Home_iCashEarned']; // 0 in public league ?

		$awayteam  = $row['Away_strName'];
		$awayscore = $row['Away_iScore'];
		$awaywinnings = $row['Away_iCashEarned']; // 0 in public league ?


		$away_team_inflicted_cas = $row['Away_Inflicted_iCasualties'] + $row['Away_Inflicted_iDead'];
		$home_team_inflicted_cas = $row['Home_Inflicted_iCasualties'] + $row['Home_Inflicted_iDead'];

		// team ff, values
		$query = "SELECT A.iPopularity AS Away_ff,
						A.iValue AS Away_value,
						H.iPopularity AS Home_ff,
						H.iValue AS Home_value
					FROM Away_Team_Listing A, Home_Team_Listing H;";

		if (!($result = $match_report_db->query($query))) {
			return false;
		}

		if (!($array = $result->fetchAll()) || sizeof($array) !=1 ) {
			return false;
		}

		$row = $array[0];
		$awayff = $row['Away_ff'];
		$homeff = $row['Home_ff'];

		$this->away_team_value = $row['Away_value'];
		$this->home_team_value = $row['Home_value'];

		// Away players
		$query = "SELECT L.iNumber As nr,
						strName AS name,
						iMVP AS mvp,
						Inflicted_iPasses AS cp,
						Inflicted_iTouchdowns AS td,
						Inflicted_iInterceptions AS intcpt,
						Inflicted_iCasualties AS cas,
						Inflicted_iDead AS ki,
						idPlayer_Casualty_Types AS inj_type,
						Sustained_iDead As dead
					FROM Away_Statistics_Players S
						JOIN Away_Player_Listing L ON S.idPlayer_Listing=L.ID
						LEFT JOIN Away_Player_Casualties C
							ON S.idPlayer_Listing=C.idPlayer_Listing";

		if (!($result = $match_report_db->query($query)))
			return false;

		if (!($array = $result->fetchAll()))
			return false;

		foreach ($array as $row) {
			$awayplayers[$row['nr']]['nr'] = $row['nr'];
			$awayplayers[$row['nr']]['name'] = $row['name'];
			//$awayplayers[$row['nr']]['star'] = $row['cp'];
			//$awayplayers[$row['nr']]['merc'] = $row['cp'];
			$awayplayers[$row['nr']]['mvp'] = $row['mvp'];
			$awayplayers[$row['nr']]['cp'] = $row['cp'];
			$awayplayers[$row['nr']]['td'] = $row['td'];
			$awayplayers[$row['nr']]['intcpt'] = $row['intcpt'];
			$awayplayers[$row['nr']]['bh'] = $row['cas'];
			$awayplayers[$row['nr']]['si'] = 0;
			$awayplayers[$row['nr']]['ki'] = $row['ki'];
			$awayplayers[$row['nr']]['inj'] = $this->getInjury($row['inj']);
			$awayplayers[$row['nr']]['agn1'] = NONE;
			$awayplayers[$row['nr']]['agn2'] = NONE;
		}

		// Home players
		$query = "SELECT L.iNumber As nr,
						strName AS name,
						iMVP AS mvp,
						Inflicted_iPasses AS cp,
						Inflicted_iTouchdowns AS td,
						Inflicted_iInterceptions AS intcpt,
						Inflicted_iCasualties AS cas,
						Inflicted_iDead AS ki,
						idPlayer_Casualty_Types AS inj_type,
						Sustained_iDead As dead
					FROM Home_Statistics_Players S
						JOIN Home_Player_Listing L ON S.idPlayer_Listing=L.ID
						LEFT JOIN Home_Player_Casualties C
							ON S.idPlayer_Listing=C.idPlayer_Listing";

		if (!($result = $match_report_db->query($query))) {
			return false;
		}

		if (!($array = $result->fetchAll())) {
			return false;
		}

		foreach ($array as $row) {
			$homeplayers[$row['nr']]['nr'] = $row['nr'];
			$homeplayers[$row['nr']]['name'] = $row['name'];
			//$homeplayers[$row['nr']]['star'] = $row['cp'];
			//$homeplayers[$row['nr']]['merc'] = $row['cp'];
			$homeplayers[$row['nr']]['mvp'] = $row['mvp'];
			$homeplayers[$row['nr']]['cp'] = $row['cp'];
			$homeplayers[$row['nr']]['td'] = $row['td'];
			$homeplayers[$row['nr']]['intcpt'] = $row['intcpt'];
			$homeplayers[$row['nr']]['bh'] = $row['cas'];
			$homeplayers[$row['nr']]['si'] = 0;
			$homeplayers[$row['nr']]['ki'] = $row['ki'];
			$homeplayers[$row['nr']]['inj'] = $this->getInjury($row['inj']);
			$homeplayers[$row['nr']]['agn1'] = NONE;
			$homeplayers[$row['nr']]['agn2'] = NONE;
		}

		if($settings['cyanide_public_league']) {

			$homefame = (rand(2,12) + $this->home_team_ff) * 1000; // (2D6 + ff) * 1000
			$awayfame = (rand(2,12) + $this->away_team_ff) * 1000; // (2D6 + ff) * 1000

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

			if ($home_cash_earned < 0) {
				$home_cash_earned = 0;
			}

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
			{
				$away_cash_earned = 0;
			}

			$homewinnings = $home_cash_earned;
			$awaywinnings = $away_cash_earned;
		}

		return array ( "homeplayers" => $homeplayers,
						"awayplayers" => $awayplayers,
						"gate" => $gate,
						"hometeam" => $hometeam,
						"homescore" => $homescore,
						"homewinnings" => $homewinnings,
						"homeff" => $homeff,
						"homefame" => $homefame,
						"awayteam" => $awayteam,
						"awayscore" => $awayscore,
						"awaywinnings" => $awaywinnings,
						"awayff" => $awayff,
						"awayfame" => $awayfame,
						"hash" => $hash );
	}

	private function getInjury($inj)
	{
		$tmp_inj = NONE;

		if ($inj) {
			if ($inj > 1 && $inj < 10) {
				$tmp_inj = MNG;
			}
			else if ($inj == 10 || $inj == 11) {
				$tmp_inj = NI;
			}
			else if ($inj == 12 || $inj == 13) {
				$tmp_inj = MA;
			}
			else if ($inj == 14 || $inj == 15) {
				$tmp_inj = AV;
			}
			else if ($inj == 16) {
				$tmp_inj = AG;
			}
			else if ($inj == 17) {
				$tmp_inj = ST;
			}
			else if ($inj == 18) {
				$tmp_inj = DEAD;
			}
		}

		if ($inj == 1)
			$tmp_inj = DEAD;

		return $tmp_inj;
	}
}

?>
