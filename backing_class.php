<?php

include 'database_class.php';
	
class BackingAgreement extends Database {

	// This maps model properties to database
	protected static $tableSchemas = array(
		'GAME' => array(
			'ID' => 'ID',
			'horseId' => 'HORSE_ID',
			'backerId' => 'BACKER_ID',
			'flatFee' => 'FLAT_FEE',
			'percentOfWin' => 'PERCENT_OF_WIN',
			'percentOfLoss' => 'PERCENT_OF_LOSS',
			'overrideAmount' => 'OVERRIDE_AMOUNT'
		)
	);

	protected static $tableSequencer = 'BACKING_AGREEMENT_SEQUENCE';

	protected function __construct () {
		parent::__construct();
	}

	protected $ID;
	protected $horseId;
	protected $backerId;
	protected $flatFee;
	protected $percentOfWin;
	protected $percentOfLoss;
	protected $overrideAmount;

	public function setProperties($properties) {
		foreach($properties as $key => $value) {
			$this->{$key} = $value;
		}
	}

	public function save() {
		if isset($this->ID) {
			$this->update();
		} else {
			$this->insert();
		}
	}

}

class Backing extends Database {

	protected static $tableSchemas = array(
		'GAME_CASH' => array(
			'ID' => 'ID',
			'gsId' => 'GS_ID'
		)
	);

	// TODO: Modify database_class.php to handle the case where there is no sequencer
	//protected static $tableSequencer = 'GAME_SEQUENCE';

	public function __construct () {
		parent::__construct();
	}

	protected $gsId;

	public function setProperties($properties) {
		foreach($properties as $key => $value) {
			$this->{$key} = $value;
		}
	}

	public function save() {
		if isset($this->ID) {
			$this->update();
		} else {
			$this->insert();
		}
	}
}


?>