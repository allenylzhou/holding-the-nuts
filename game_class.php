<?php

include_once 'database_class.php';
	
abstract class Game extends Database {

	protected static $tableKey = array(
		'gsId' => array('type' => DataType::NUMBER, 'sequence' => 'GAME_SEQUENCE')
	);

	protected static $tableAttributes = array(
		'GAME' => array(
			'userId' => array('type' => DataType::NUMBER),
			'startDate' => array('type' => DataType::DATE),
			'endDate' => array('type' => DataType::DATE),
			'amountIn' => array('type' => DataType::NUMBER, 'default' => 0),
			'amountOut' => array('type' => DataType::NUMBER, 'default' => 0),
			'locationName' => array('type' => DataType::VARCHAR)
		)
	);

	protected function __construct () {
		parent::__construct();
	}

	protected $gsId;
	protected $userId;
	protected $startDate;
	protected $endDate;
	protected $amountIn;
	protected $amountOut;
	protected $locationName;

	public function getGsId() { return $this->gsId; }
	public function getUserId() { return $this->userId; }
	public function getStartDate() { return $this->startDate; }
	public function getEndDate() { return $this->endDate; }
	public function getAmountIn() { return $this->amountIn; }
	public function getAmountOut() { return $this->amountOut; }
	public function getLocationName() { return $this->locationName; }

	public function setAttributes($attributes) {
		foreach($attributes as $name => $value) {
			if (!array_key_exists($name, static::$tableKey)) {
				$this->{$name} = $value;
			}
		}
	}

	public function erase() {
		$this->delete();
	}

	public function getAverageBuyIn($userId) {
		return $this->aggregate('avg', 'amountIn', array('userId' => $userId));
	}

	public function getAverageBuyOut($userId) {
		return $this->aggregate('avg', 'amountOut', array('userId' => $userId));
	}
}

class CashGame extends Game {

	protected static $tableAttributes = array(
		'GAME_CASH' => array(
			'bigBlind' => array('type' => DataType::NUMBER, 'default' => 0),
			'smallBlind' => array('type' => DataType::NUMBER, 'default' => 0)
		)
	);

	protected $bigBlind;
	protected $smallBlind;

	public function __construct ($key = array(), $select = false) {
		parent::__construct();
		// This is the order in which table instances are inserted to satisfy integrity constraint
		static::$tableAttributes = array_merge(parent::$tableAttributes, self::$tableAttributes);

		foreach ($key as $name => $value) {
			if (array_key_exists($name, static::$tableKey)) {
				$this->{$name} = $value;
			}
		}

		if ($select) {
			$this->setAttributes($this->select());
		}
	}

	public function getBigBlind() { return $this->bigBlind; }
	public function getSmallBlind() { return $this->smallBlind; }

	public static function loadSavedGames($userId) {

		if ($connection = oci_connect("ora_u4e7", "a71174098", "ug")) {
			$sqlString = 'SELECT *
				FROM Game G, Game_Cash C
				WHERE G.gs_id = C.gs_id AND G.user_id = (:userId)
				ORDER BY G.GS_ID ASC';
			$sqlStatement = oci_parse($connection, $sqlString);
			oci_bind_by_name($sqlStatement, ':userId', $userId);

			oci_execute($sqlStatement);

			//$returnData = oci_fetch_assoc($sqlStatement);
			
			$returnData = array();
			while ($row = oci_fetch_array($sqlStatement)) {

				array_push($returnData, $row);
			}
			
		  	OCILogoff($connection);

		} else {
		  //$err = OCIError();
		  //echo "Oracle Connect Error " . $err['message'];
		}
		//print("<pre>" . print_r($returnData, true) . "</pre>");
		return $returnData;
	}

}

class TournamentGame extends Game {

	protected static $tableAttributes = array(
		'GAME_TOURNAMENT' => array(
			'placeFinished' => array('type' => DataType::NUMBER)
		)
	);

	protected $placedFinished;

	public function __construct ($key = array(), $select = false) {
		parent::__construct();
		// This is the order in which table instances are inserted to satisfy integrity constraint
		static::$tableAttributes = array_merge(parent::$tableAttributes, self::$tableAttributes);

		foreach ($key as $name => $value) {
			if (array_key_exists($name, static::$tableKey)) {
				$this->{$name} = $value;
			}
		}

		if ($select) {
			$this->setAttributes($this->select());
		}
	}

	public function getPlacedFinished() { return $this->placeFinished; }



	public static function loadSavedGames($userId) {

		if ($connection = oci_connect("ora_u4e7", "a71174098", "ug")) {
			$sqlString = 'SELECT *
				FROM Game G, Game_Tournament T
				WHERE G.GS_ID = T.GS_ID AND G.USER_ID = (:userId)
				ORDER BY G.GS_ID ASC';

			$sqlStatement = oci_parse($connection, $sqlString);
			oci_bind_by_name($sqlStatement, ':userId', $userId);

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

?>
