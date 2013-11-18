

<?php

include_once 'database_class.php';

class Statistics {

	public static function getAverageCashBuyIn($userId){
		$val;
		try{
			$connection = Database::start();
				$sqlString = 'SELECT AVG(AMOUNT_IN) as AMOUNT
				FROM Game G, Game_Cash C
				Where G.gs_id = C.gs_id And G.user_id = (:userID)';
			$sqlStatement = oci_parse($connection, $sqlString);
			oci_bind_by_name($sqlStatement, ':userId', $userId);
			oci_execute($sqlStatement);

			while($row = oci_fetch_array($sqlStatement)){
				$val = $row['AMOUNT'];
			}
		}
		catch (Exception $exception) {
			if($connection != null){
				Database::end($connection);	
			}
			throw $exception;
		}
		if($connection != null){
			Database::end($connection);	
		}
		return $val;
	}
	
	/* may or may not keep
	public function getBestPerformingDayOfWeek($userId) {
		$dayOfWeek = array();
		try{
			$connection = Database::start();
			$sqlString = "WITH USER_WINNING_ON_DAY AS (
							  SELECT to_char(to_date(START_DATE, 'DD/MM/YYYY'), 'DAY') AS DAY_OF_WEEK,
                                     AVG(AMOUNT_OUT-AMOUNT_IN) as WINNINGS
							  FROM   GAME
							  WHERE  USER_ID = :userId
							  GROUP BY to_char(to_date(START_DATE, 'DD/MM/YYYY'), 'DAY'))
							SELECT DAY_OF_WEEK, WINNINGS
							FROM   USER_WINNING_ON_DAY
							WHERE  WINNINGS >= ALL (SELECT MAX(A.WINNINGS)
													FROM USER_WINNING_ON_DAY A)";
			$stid = oci_parse($connection, $sqlString);
			oci_bind_by_name($stid, ':userId', $userId, 20);
			
			oci_define_by_name($stid, 'DAY_OF_WEEK', $dayOfWeek);
			oci_define_by_name($stid, 'WINNINGS', $winnings);
			oci_execute($stid);
			
			while (oci_fetch($stid)) {
				$dayOfWeek[((string) $dayOfWeek)] = $winnings; 
			}
		}
		catch (Exception $exception) {
			if($connection != null){
				Database::end($connection);	
			}
			throw $exception;
		}
		if($connection != null){
			Database::end($connection);	
		}
		return $dayOfWeek;
	}
	*/
	
	// NESTED AGGREGATE
	public static function getBestPerformingDay($userId) {
		try{
			$connection = Database::start();
			$sqlString = "WITH USER_WINNING_ON_DAY AS (
							  SELECT TRUNC(START_DATE) AS GAME_DAY,
                                     AVG(AMOUNT_OUT-AMOUNT_IN) as WINNINGS
							  FROM   GAME
							  WHERE  USER_ID = :userId
							  GROUP BY TRUNC(START_DATE))
							SELECT GAME_DAY, WINNINGS
							FROM   USER_WINNING_ON_DAY
							WHERE  WINNINGS >= ALL (SELECT MAX(A.WINNINGS)
													FROM USER_WINNING_ON_DAY A)";
			$stid = oci_parse($connection, $sqlString);
			oci_bind_by_name($stid, ':userId', $userId, 20);
			oci_execute($stid);
			
			$day = array();
			while ($row = oci_fetch_array($stid)) {
				$val = $row['GAME_DAY'];
				array_push($day, $val);
			}
		}
		catch (Exception $exception) {
			if($connection != null){
				Database::end($connection);	
			}
			throw $exception;
		}
		if($connection != null){
			Database::end($connection);	
		}
		return $day;
	}

}

?>
