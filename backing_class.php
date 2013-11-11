<?php

include_once 'database_class.php';
	
class BackingAgreement extends Database {

	protected static $tableKey = array(
		'id' => array('type' => DataType::NUMBER, 'sequence' => 'BACKING_AGREEMENT_SEQUENCE')
	);

	protected static $tableAttributes = array(
		'BACKING_AGREEMENT' => array(
			'horseId' => array('type' => DataType::NUMBER),
			'backerId' => array('type' => DataType::NUMBER),
			'flatFee' => array('type' => DataType::NUMBER),
			'percentOfWin' => array('type' => DataType::NUMBER),
			'percentOfLoss' => array('type' => DataType::NUMBER),
			'overrideAmount' => array('type' => DataType::NUMBER)
		)
	);

	protected $id;
	protected $horseId;
	protected $backerId;
	protected $flatFee;
	protected $percentOfWin;
	protected $percentOfLoss;
	protected $overrideAmount;

	public function __construct ($key = array(), $select = false) {
		parent::__construct();

		foreach ($key as $name => $value) {
			if (array_key_exists($name, static::$tableKey)) {
				$this->{$name} = $value;
			}
		}

		if ($select) {
			$this->setAttributes($this->load());
		}
	}

	public function getId() { return $this->id; }
	public function getHorseId() { return $this->horseId; }
	public function getBackerId() { return $this->backerId; }
	public function getFlatFee() { return $this->flatFee; }
	public function getPercentOfWin() { return $this->percentOfWin; }
	public function getPercentOfLoss() { return $this->percentOfLoss; }
	public function getOverrideAmount() { return $this->overrideAmount; }

	public function setAttributes($attributes) {
		foreach($attributes as $name => $value) {
			if (!array_key_exists($name, static::$tableKey)) {
				$this->{$name} = $value;
			}
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

	protected static $tableKey = array(
		'id' => array('type' => DataType::NUMBER),
		'gsId' => array('type' => DataType::NUMBER)
	);

	protected static $tableAttributes = array(
		'BACKING' => array()
	);

	protected $id;
	protected $gsId;

	public function __construct ($key = array(), $select = false) {
		parent::__construct();

		foreach ($key as $name => $value) {
			if (array_key_exists($name, static::$tableKey)) {
				$this->{$name} = $value;
			}
		}

		if ($select) {
			$this->setAttributes($this->load());
		}
	}

	public function getId() { return $this->id; }
	public function getGsId() { return $this->gsId; }

	public function setAttributes($attributes) {
		foreach($attributes as $name => $value) {
			if (!array_key_exists($name, static::$tableKey)) {
				$this->{$name} = $value;
			}
		}
	}
}



?>
