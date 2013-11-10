<?php

include_once 'database_class.php';
	
class BackingAgreement extends Database {

	// This maps model properties to database
	protected static $tableSchemas = array(
		'BACKING_AGREEMENT' => array(
			'horseId' => 'HORSE_ID',
			'backerId' => 'BACKER_ID',
			'flatFee' => 'FLAT_FEE',
			'percentOfWin' => 'PERCENT_OF_WIN',
			'percentOfLoss' => 'PERCENT_OF_LOSS',
			'overrideAmount' => 'OVERRIDE_AMOUNT'
		)
	);

	protected static $tableSequencer = 'BACKING_AGREEMENT_SEQUENCE';
	protected static $tableKey = 'ID';

	public function __construct () {
		parent::__construct();
	}

	protected $id;
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
		if (isset($this->id)) {
			$this->update();
		} else {
			$this->insert();
		}
	}
	

	public static function loadSavedBackings($horseId) {

		if ($connection = oci_connect("ora_u4e7", "a71174098", "ug")) {
			$sqlString = 'SELECT *
				FROM BACKING_AGREEMENT B
				WHERE  B.HORSE_ID = (:horseId)
				ORDER BY B.BACKER_ID ASC';

			$sqlStatement = oci_parse($connection, $sqlString);
			oci_bind_by_name($sqlStatement, ':horseId', $horseId);

			oci_execute($sqlStatement);

			$returnData = array();
			while ($row = oci_fetch_array($sqlStatement)) {

				array_push($returnData, $row);
			}
		  	OCILogoff($connection);

		} else {
		  //$err = OCIError();
		  //echo "Oracle Connect Error " . $err['message'];
		}

		return $returnData;
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
		if (isset($this->ID)) {
			$this->update();
		} else {
			$this->insert();
		}
	}
}



?>
