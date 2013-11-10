<?php

include_once 'database_class.php';
	
abstract class Game extends Database {

	protected static $tableKey = array(
		'gsId' => array('type' => DataType::NUMBER, 'sequence' => 'GAME_SEQUENCE')
	);

	// This maps model properties to database
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

	public function getProperties() {
		return get_object_vars($this);
	}

	public function setProperties($properties) {
		unset($properties['id']);
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

	public function erase() {
		$this->delete();
	}

	public function getAverageBuyIn() {
		return $this->getAverage('AMOUNT_IN');
		
	}
	public function getAverageBuyOut(){
		return $this->getAverage('AMOUNT_OUT');
	}
}

class CashGame extends Game {

	protected static $tableAttributes = array(
		'GAME_CASH' => array(
			'bigBlind' => array('type' => DataType::NUMBER, 'default' => 0),
			'smallBlind' => array('type' => DataType::NUMBER, 'default' => 0)
		)
	);

	public function __construct ($id = null) {
		parent::__construct();
		// This is the order in which table instances are inserted to satisfy integrity constraint
		static::$tableAttributes = array_merge(parent::$tableAttributes, self::$tableAttributes);

		if (isset($id)) {
			$this->id = $id;
			$properties = $this->select();
			$this->setProperties($properties);
		}
	}

	protected $bigBlind;
	protected $smallBlind;

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
			'placeFinished' => 'PLACED_FINISHED'
		)
	);

	public function __construct ($id = null) {
		parent::__construct();
		// This is the order in which table instances are inserted to satisfy integrity constraint
		static::$tableAttributes = array_merge(parent::$tableAttributes, self::$tableAttributes);

		if (isset($id)) {
			$this->id = $id;
			$properties = $this->select();
			$this->setProperties($properties);
		}
	}

	protected $placeFinished;

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
