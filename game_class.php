<?php

include 'database_class.php';
	
abstract class Game extends Database {

	protected static $tableName = 'GAME';

	// This maps table columns to model properties
	protected static $tableModelMap = array(
		'GS_ID' => 'gsId',
		'USER_ID' => 'userId',
		'START_DATE' => 'startDate',
		'END_DATE' => 'endDate',
		'AMOUNT_IN' => 'amountIn',
		'AMOUNT_OUT' => 'amountOut',
		'LOCATION_NAME' => 'locationName'
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

	abstract protected function setProperties($properties);
	abstract protected function save();

}

class CashGame extends Game {

	protected static $tableName = 'GAME_CASH';

	//This maps table columns to model properties
	protected static $tableModelMap = array(
		'GS_ID' => 'gsId',
		'BIG_BLIND' => 'bigBlind',
		'SMALL_BLIND' => 'smallBlind'
	);

	public function __construct () {
		parent::__construct();
	}

	private $bigBlind;
	private $smallBlind;

	public function setProperties($properties) {
		foreach($properties as $key => $value) {
			$this->{$key} = $value;
		}
	}

	public function save() {
		// INSERT a GAME instance
		$gameAttributes = array();
		foreach(parent::$tableModelMap as $column => $property) {
			$gameAttributes[$column] = $this->{$property};
		}
		$this->insert($gameAttributes, parent::$tableName);

		// INSERT a GAME_CASH instance
		$cashGameAttributes = array();
		foreach(self::$tableModelMap as $column => $property) {
			$cashGameAttributes[$column] = $this->{$property};
		}
		$this->insert($cashGameAttributes);
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

	protected static $tableName = 'GAME_TOURNAMENT';

	//This maps table columns to model properties
	protected static $tableModelMap = array(
		'GS_ID' => 'gsId',
		'PLACED_FINISHED' => 'placedFinished'
	);

	public function __construct () {
		parent::__construct();
	}

	private $placedFinished;

	public function setProperties($properties) {
		foreach($properties as $key => $value) {
			$this->{$key} = $value;
		}
	}

	public function save() {
		// INSERT a GAME instance
		$this->insert(array_map(function($propertyName) {
			return $this->{$propertyName};
		}, parent::$tableModelMap), parent::$tableName);

		// INSERT a GAME_CASH instance
		$this->insert(array_map(function($propertyName) {
			return $this->{$propertyName};
		}, self::$tableModelMap));
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