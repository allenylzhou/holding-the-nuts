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

	public static function loadSavedGames($uid) {

		if ($connection = oci_connect("ora_u4e7", "a71174098", "ug")) {
			//echo "Successfully connected to Oracle.\n";

			$sql_text = 'SELECT *
				FROM Game G, Game_Cash C
				WHERE G.gs_id = C.gs_id AND G.user_id = (:userId)';

			$query = oci_parse($connection, $sql_text);
			oci_bind_by_name($query, ':userId', $uid);

			oci_execute($query);

			$results = array();
			while ($row = oci_fetch_array($query)) {

				array_push($results, $row);
			}
		  	OCILogoff($connection);

		} else {
		  $err = OCIError();
		  echo "Oracle Connect Error " . $err['message'];
		}

		return $results;
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
		static::$tableSchemas = array_merge(self::$tableSchemas, parent::$tableSchemas);
	}

	protected $placedFinished;

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

	public static function loadSavedGames($uid) {
		if ($connection = oci_connect("ora_u4e7", "a71174098", "ug")) {
			//echo "Successfully connected to Oracle.\n";

			$sql_text = 'SELECT *
				FROM Game G, Game_Tournament T
				WHERE G.gs_id = T.gs_id AND G.user_id = (:userId)';

			$query = oci_parse($connection, $sql_text);
			oci_bind_by_name($query, ':userId', $uid);

			oci_execute($query);

			$results = array();
			while ($row = oci_fetch_array($query)) {

				array_push($results, $row);
			}
		  	OCILogoff($connection);

		} else {
		  $err = OCIError();
		  echo "Oracle Connect Error " . $err['message'];
		}

		return $results;
	}
}

?>