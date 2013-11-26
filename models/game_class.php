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

	public function getAttributes() {
		return get_object_vars($this);
	}

	public function setAttributes($attributes) {
		foreach($attributes as $name => $value) {
			if (!array_key_exists($name, static::$tableKey)) {
				$this->{$name} = $value;
			}
		}
	}

	public function loadBacking() {
		$results = array();
		$connection = static::start();

		$sqlString = 'SELECT *
				FROM BACKING_AGREEMENT BA, BACKING B, GAME G, USERS U
				WHERE  BA.HORSE_ID = (:userId)
				AND BA.ba_Id = B.ba_Id AND B.GS_ID = G.GS_ID AND BA.BACKER_ID = U.USER_ID
				ORDER BY BA.BACKER_ID ASC';

		$sqlStatement = oci_parse($connection, $sqlString);
		$userId = $this->getUserId();
		oci_bind_by_name($sqlStatement, ':userId', $userId);

		if(@oci_execute($sqlStatement)) {
			$results = oci_fetch_assoc($sqlStatement);
		}
		static::end($connection);

		return $results;
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
			$this->setAttributes($this->load());
		}
	}

	public function getBigBlind() { return $this->bigBlind; }
	public function getSmallBlind() { return $this->smallBlind; }
/*
	public static function loadActiveGames($userId) {
		return static::loadGames($userId, 'GAME_CASH', true);
	}

	public static function loadFinishedGames($userId) {
		return static::loadGames($userId, 'GAME_CASH');
	}*/
	
	public static function loadFinishedGames($userId) {
		$results = array();
		$connection = static::start();

		$where = "P.GS_ID = C.GS_ID AND P.USER_ID = (:userId)";
		if (false) {
			$where .= " AND P.END_DATE IS NULL";
		}

		$sqlString = "SELECT 
					P.GS_ID, 
					START_DATE, 
					LOCATION_NAME, 
					(TO_DATE(TO_CHAR(END_DATE,'yyyy-mm-dd hh24:mi:ss'), 'yyyy-mm-dd hh24:mi:ss') - TO_DATE(TO_CHAR(START_DATE,'yyyy-mm-dd hh24:mi:ss'), 'yyyy-mm-dd hh24:mi:ss')) * 24 * 60 AS DURATION,
					AMOUNT_OUT - AMOUNT_IN AS PROFIT, 
					BIG_BLIND, SMALL_BLIND
				FROM GAME P, GAME_CASH C
				WHERE $where
				ORDER BY P.GS_ID ASC";

		$sqlStatement = oci_parse($connection, $sqlString);
		oci_bind_by_name($sqlStatement, ':userId', $userId);

		if(@oci_execute($sqlStatement)) {
			while ($row = oci_fetch_assoc($sqlStatement)) {
				array_push($results, $row);
			}
		}
		static::end($connection);

		return $results;
	}		
}

class TournamentGame extends Game {

	protected static $tableAttributes = array(
		'GAME_TOURNAMENT' => array(
			'placedFinished' => array('type' => DataType::NUMBER)
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
			$this->setAttributes($this->load());
		}
	}

	public function getPlacedFinished() { return $this->placedFinished; }
/*
	public static function loadActiveGames($userId) {
		return static::loadGames($userId, 'GAME_TOURNAMENT', true);
	}

	public static function loadFinishedGames($userId) {
		return static::loadGames($userId, 'GAME_TOURNAMENT');
	}
	*/
	public static function loadFinishedGames($userId) {
		$results = array();
		$connection = static::start();

		$where = "P.GS_ID = C.GS_ID AND P.USER_ID = (:userId)";
		if (false) {
			$where .= " AND P.END_DATE IS NULL";
		}

		$sqlString = "SELECT 
					P.GS_ID, 
					START_DATE, 
					LOCATION_NAME, 
					(TO_DATE(TO_CHAR(END_DATE,'yyyy-mm-dd hh24:mi:ss'), 'yyyy-mm-dd hh24:mi:ss') - TO_DATE(TO_CHAR(START_DATE,'yyyy-mm-dd hh24:mi:ss'), 'yyyy-mm-dd hh24:mi:ss')) * 24 * 60 AS DURATION,
					AMOUNT_OUT - AMOUNT_IN AS PROFIT, 
					PLACED_FINISHED
				FROM GAME P, GAME_TOURNAMENT C
				WHERE $where
				ORDER BY P.GS_ID ASC";

		$sqlStatement = oci_parse($connection, $sqlString);
		oci_bind_by_name($sqlStatement, ':userId', $userId);

		if(@oci_execute($sqlStatement)) {
			while ($row = oci_fetch_assoc($sqlStatement)) {
				array_push($results, $row);
			}
		}
		static::end($connection);

		return $results;
	}		
}

?>
