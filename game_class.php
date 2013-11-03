<?php
	
abstract class Game {

	protected $id;
	protected $userId;
	protected $startDate;
	protected $endDate;
	protected $amountIn;
	protected $amountOut;
	protected $locationName;

	protected function getUserId() {return $this->userId;}
	protected function getStartDate() {return $this->startDate;}
	protected function getEndDate() {return $this->endDate;}
	protected function getAmountIn() {return $this->amountIn;}
	protected function getAmountOut() {return $this->amountOut;}
	protected function getLocationName() {return $this->locationName;}

	protected function setStartDate($v) {
		//TODO: Add input validation
		// if (isValid($v)) {
				$this->startDate = $v;
		// } else {
		//		throw new Exception;
		// }
	}

	protected function setEndDate($v) {
		//TODO: Add input validation
		$this->endDate = $v;
	}
	protected function setAmountIn($v) {
		//TODO: Add input validation
		$this->amountIn = $v;
	}
	protected function setAmountOut($v) {
		//TODO: Add input validation
		$this->amountOut = $v;
	}
	protected function setLocationName($v) {
		//TODO: Add input validation
		$this->locationName = $v;
	}

	protected function save() {
		print(get_object_vars($this));
	}

}

class CashGame extends Game {

	private $bigBlind;
	private $smallBlind;

	public function getBigBlind() {return $this->bigBlind;}
	public function getSmallBlind() {return $this->smallBlind;}

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

	private $placedFinished;

	public function getPlacedFinished() {return $this->placedFinished;}

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