

<?php

include 'database_class.php';

class Statistics extends Database{

	public static function getAverageCashBuyIn($userId){
		if ($connection = oci_connect("ora_u4e7", "a71174098", "ug")){
			$sqlString = 'SELECT AVG(AMOUNT_IN) as AMOUNT
				FROM Game G, Game_Cash C
				Where G.gs_id = C.gs_id And G.user_id = (:userID)';
		
			$sqlStatement = oci_parse($connection, $sqlString);
			oci_bind_by_name($sqlStatement, ':userId', $userId);
			s
			oci_execute($sqlStatement);
			
			$returnData = array();
			while($row = oci_fetch_array($sqlStatement)){
			
				array_push($returnData, $row);
			}
			OCILogoff($connection);
		}else{
			$err = OCIError();
			echo "Oracle Connect Error " . $err['message'];
			
		}
		return $returnData;
	}
	
	/* may or may not keep
	public function getBestPerformingDayOfWeek($userId) {
		$dayOfWeek = array();
		try{
			$connection = Database::start();
			$sqlString = "WITH USER_WINNING_ON_DAY AS (
							  SELECT TRUNC(START_DATE) - TRUNC(START_DATE, 'D') AS DAY_OF_WEEK,
                                     AVG(AMOUNT_OUT-AMOUNT_IN) as WINNINGS
							  FROM   GAME
							  WHERE  USER_ID = :userId
							  GROUP BY TRUNC(START_DATE) - TRUNC(START_DATE, 'D'))
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
	public function getBestPerformingDay($userId) {
		$day = array();
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
			
			oci_define_by_name($stid, 'GAME_DAY', $day);
			oci_define_by_name($stid, 'WINNINGS', $winnings);
			oci_execute($stid);
			
			while (oci_fetch($stid)) {
				$day[((string) $day)] = $winnings; 
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
