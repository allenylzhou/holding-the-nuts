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

	protected static function loadGames($userId, $gameType, $active = false) {
		$results = array();
		$connection = static::start();

		$where = "P.GS_ID = C.GS_ID AND P.USER_ID = (:userId)";
		if ($active) {
			$where .= " AND P.END_DATE IS NULL";
		}

		$sqlString = "SELECT *
				FROM GAME P, $gameType C
				WHERE $where
				ORDER BY P.GS_ID ASC";

		$sqlStatement = oci_parse($connection, $sqlString);
		oci_bind_by_name($sqlStatement, ':userId', $userId);

		echo $sqlString;

		if(oci_execute($sqlStatement)) {
			while ($row = oci_fetch_assoc($sqlStatement)) {
				array_push($results, $row);
			}
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

	public static function loadActiveGames($userId) {
		return static::loadGames($userId, 'GAME_CASH', true);
	}

	public static function loadFinishedGames() {
		return static::loadGames($userId, 'GAME_CASH');
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
			$this->setAttributes($this->load());
		}
	}

	public function getPlacedFinished() { return $this->placeFinished; }

	public static function loadActiveGames($userId) {
		return static::loadGames($userId, 'GAME_TOURNAMENT', true);
	}

	public static function loadFinishedGames($userId) {
		return static::loadGames($userId, 'GAME_TOURNAMENT');
	}
}

?>
