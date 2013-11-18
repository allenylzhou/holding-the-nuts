<?php

include_once 'database_class.php';
	
class BackingAgreement extends Database {

	protected static $tableKey = array(
		'baId' => array('type' => DataType::NUMBER, 'sequence' => 'BACKING_AGREEMENT_SEQUENCE')
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

	protected $baId;
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

	public function getId() { return $this->baId; }
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

	public static function loadBackersByHorseId($userId) {
		$results = array();
		$connection = static::start();

		$sqlString = "SELECT HB.BACKER AS BACKER_ID
				FROM HORSE_BACKERS HB, USERS U
				WHERE HB.HORSE = U.USER_ID AND U.USER_ID = (:userId)
				ORDER BY U.USERNAME ASC";

		$sqlStatement = oci_parse($connection, $sqlString);
		oci_bind_by_name($sqlStatement, ':userId', $userId);

		if(oci_execute($sqlStatement)) {
			while ($row = oci_fetch_assoc($sqlStatement)) {
				array_push($results, $row);
			}
		}
		static::end($connection);

		return $results;
	}
	
	public static function loadBackingsByHorseId($userId) {
		$results = array();
		$connection = static::start();

		$sqlString = 'SELECT *
			FROM BACKING_AGREEMENT BA, BACKING B
			WHERE  BA.HORSE_ID = (:userId)
			AND BA.baId = B.baId
			ORDER BY BA.BACKER_ID ASC';

		$sqlStatement = oci_parse($connection, $sqlString);
		oci_bind_by_name($sqlStatement, ':userId', $userId);

		if(oci_execute($sqlStatement)) {
			while ($row = oci_fetch_assoc($sqlStatement)) {
				array_push($results, $row);
			}
		}
		static::end($connection);

		return $results;
	}
	
	// new
	public static function getUsername($userId){ 
		$results = array();
		$connection = static::start();

		$sqlString = 'SELECT USERNAME
			FROM USERS U
			WHERE  U.USER_ID = (:userId)';

		$sqlStatement = oci_parse($connection, $sqlString);
		oci_bind_by_name($sqlStatement, ':userId', $userId);

		if(oci_execute($sqlStatement)) {
			while ($row = oci_fetch_assoc($sqlStatement)) {
				array_push($results, $row);
			}
		}
		static::end($connection);

		return $results[0]['USERNAME'];
	}

}

class Backing extends Database {

	protected static $tableKey = array(
		'baId' => array('type' => DataType::NUMBER),
		'gsId' => array('type' => DataType::NUMBER)
	);

	protected static $tableAttributes = array(
		'BACKING' => array()
	);

	protected $baId;
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

	public function getId() { return $this->baId; }
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
