<?php

include 'database_class.php';
	
abstract class Game extends Database {

	// This maps model properties to database
	protected static $tableSchemas = array(
		'GAME' => array(
			'ID' => 'GS_ID',
			'userId' => 'USER_ID',
			'startDate' => 'START_DATE',
			'endDate' => 'END_DATE',
			'amountIn' => 'AMOUNT_IN',
			'amountOut' => 'AMOUNT_OUT',
			'locationName' => 'LOCATION_NAME'
		)
	);

	protected static $tableSequencer = 'GAME_SEQUENCE';

	protected function __construct () {
		parent::__construct();
	}

	protected $ID;
	protected $userId;
	protected $startDate;
	protected $endDate;
	protected $amountIn;
	protected $amountOut;
	protected $locationName;

	abstract protected function setProperties($properties);
	abstract protected function save();

}

class CashGame extends Game {

	protected static $tableSchemas = array(
		'GAME_CASH' => array(
			'ID' => 'GS_ID',
			'bigBlind' => 'BIG_BLIND',
			'smallBlind' => 'SMALL_BLIND'
		)
	);

	public function __construct () {
		parent::__construct();
		// This is the order in which table instances are inserted to satisfy integrity constraint
		static::$tableSchemas = array_merge(parent::$tableSchemas, self::$tableSchemas);
	}

	protected $bigBlind;
	protected $smallBlind;

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

	public static function loadSavedGames($userId) {

		if ($connection = oci_connect("ora_u4e7", "a71174098", "ug")) {
			$sqlString = 'SELECT *
				FROM Game G, Game_Cash C
				WHERE G.gs_id = C.gs_id AND G.user_id = (:userId)';

			$sqlStatement = oci_parse($connection, $sqlString);
			oci_bind_by_name($sqlStatement, ':userId', $userId);

			oci_execute($sqlStatement);

			$returnData = array();
			while ($row = oci_fetch_array($sqlStatement)) {

				array_push($returnData, $row);
			}
		  	OCILogoff($connection);

		} else {
		  $err = OCIError();
		  echo "Oracle Connect Error " . $err['message'];
		}

		return $returnData;
	}

}

class TournamentGame extends Game {

	protected static $tableSchemas = array(
		'GAME_TOURNAMENT' => array(
			'ID' => 'GS_ID',
			'placeFinished' => 'PLACED_FINISHED'
		)
	);

	public function __construct () {
		parent::__construct();
		// This is the order in which table instances are inserted to satisfy integrity constraint
		static::$tableSchemas = array_merge(parent::$tableSchemas, self::$tableSchemas);
	}

	protected $placeFinished;

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

	public static function loadSavedGames($userId) {

		if ($connection = oci_connect("ora_u4e7", "a71174098", "ug")) {
			$sqlString = 'SELECT *
				FROM Game G, Game_Tournament T
				WHERE G.gs_id = T.gs_id AND G.user_id = (:userId)';

			$sqlStatement = oci_parse($connection, $sqlString);
			oci_bind_by_name($sqlStatement, ':userId', $userId);

			oci_execute($sqlStatement);

			$returnData = array();
			while ($row = oci_fetch_array($sqlStatement)) {

				array_push($returnData, $row);
			}
		  	OCILogoff($connection);

		} else {
		  $err = OCIError();
		  echo "Oracle Connect Error " . $err['message'];
		}

		return $returnData;
	}
}

?>