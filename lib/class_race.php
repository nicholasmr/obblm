<?php

class Race
{
	/***************
	 * Properties 
	 ***************/
	public $race    = '';
	public $name    = ''; // = $this->race, used for conventional reasons.
	public $race_id = 0;

	/***************
	 * Methods 
	 ***************/
	function __construct($race_id) {
		global $raceididx;
		$this->race_id = $race_id;
		$this->race = $this->name = $raceididx[$this->race_id];
		$this->setStats(false,false,false);
	}

	public function setStats($node, $node_id, $set_avg = false) {
		foreach (Stats::getAllStats(T_OBJ_RACE, $this->race_id, $node, $node_id, $set_avg) as $key => $val)
			$this->$key = $val;
	}

	public function getGoods($double_RRs = false) {
		/**
		 * Returns buyable stuff for this race.
		 **/
		global $DEA, $rules, $racesNoApothecary, $lng;
		$rr_price = $DEA[$this->race]['other']['rr_cost'] * (($double_RRs) ? 2 : 1);
		$apoth = !in_array($this->race_id, $racesNoApothecary);
		return array(
				// MySQL column names as keys
				'apothecary'    => array('cost' => $rules['cost_apothecary'],   'max' => ($apoth ? 1 : 0),              'item' => $lng->GetTrn('common/apothecary')),
				'rerolls'       => array('cost' => $rr_price,                   'max' => $rules['max_rerolls'],         'item' => $lng->GetTrn('common/reroll')),
				'ff_bought'     => array('cost' => $rules['cost_fan_factor'],   'max' => $rules['max_fan_factor'],      'item' => $lng->GetTrn('matches/report/ff')),
				'ass_coaches'   => array('cost' => $rules['cost_ass_coaches'],  'max' => $rules['max_ass_coaches'],     'item' => $lng->GetTrn('common/ass_coach')),
				'cheerleaders'  => array('cost' => $rules['cost_cheerleaders'], 'max' => $rules['max_cheerleaders'],    'item' => $lng->GetTrn('common/cheerleader')),
		);
	}

	public static function exists($id) {
		global $raceididx;
		return in_array($id, array_keys($raceididx));
	}
}