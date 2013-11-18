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

		$sqlString = "WITH OWES AS(
						SELECT DECODE(DS.HORSE_ID, U.USER_ID, DS.BACKER_ID, DS.HORSE_ID) AS OTHER_USER,
							   SUM(DECODE(DS.HORSE_ID, U.USER_ID, DS.OWED - DS.PAYED, DS.PAYED - DS.OWED)) AS OWED
						FROM V_DEBT_STATUS DS,USERS U
						WHERE (DS.HORSE_ID = U.USER_ID OR DS.BACKER_ID = U.USER_ID)
						AND U.USER_ID = :userId
						GROUP BY DECODE(DS.HORSE_ID, U.USER_ID, DS.BACKER_ID, DS.HORSE_ID)
					)
					SELECT u.user_id as BACKER_ID, u.username AS USERNAME, O.OWED AS OWED
					FROM HORSE_BACKERS HB, OWES O, users U
					WHERE HB.HORSE = :userId
					and u.user_id = hb.backer
					and HB.BACKER = O.OTHER_USER (+)";

		$sqlStatement = oci_parse($connection, $sqlString);
		oci_bind_by_name($sqlStatement, ':userId', $userId);

		if(oci_execute($sqlStatement)) {
			while ($row = oci_fetch_assoc($sqlStatement)) {
				$rowTemp = array();
				$rowTemp['BACKER_ID'] = $row['BACKER_ID'];
				$rowTemp['USERNAME'] = $row['USERNAME'];
				$rowTemp['OWED'] = $row['OWED'];
				$results[] = $rowTemp;
			}
		}
		static::end($connection);

		return $results;
	}
	
	public static function loadBackingAgreementsByHorseId($userId) {
		$results = array();
		$connection = static::start();

		$sqlString = 'SELECT BA.BA_ID, 
							BA.BACKER_ID, 
							U.USERNAME,
							FLAT_FEE, 
							PERCENT_OF_WIN, 
							PERCENT_OF_LOSS, 
							OVERRIDE_AMOUNT
						FROM BACKING_AGREEMENT BA, USERS U
						WHERE  BA.HORSE_ID = (:userId)
						AND U.USER_ID = BA.BACKER_ID
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
	
	public static function loadBackingsByHorseId($userId) {
			$results = array();
			$connection = static::start();

			$sqlString = 'SELECT *
					FROM BACKING_AGREEMENT BA, BACKING B
					WHERE  BA.HORSE_ID = (:userId)
					AND BA.ba_Id = B.ba_Id
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
