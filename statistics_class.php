

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
	public static function getBestPerformingDays($userId) {
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
	
	// NESTED AGGREGATE
	public static function getWorstPerformingDays($userId) {
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
							WHERE  WINNINGS >= ALL (SELECT MIN(A.WINNINGS)
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

	public static function getProfitByMonth($userId){
		$months = array();		
		try{
			$connection = Database::start();
			$sqlString = "SELECT to_char(to_date(START_DATE, 'DD/MM/YYYY'), 'MONTH') as mon, extract(year from START_DATE) as year, SUM(AMOUNT_OUT - AMOUNT_IN) as am 
				 	FROM Game 
					where USER_ID = :userId
					group by extract(year from START_DATE), to_char(to_date(START_DATE, 'DD/MM/YYYY'), 'MONTH')
					order by 1, 2";
			$stid = oci_parse($connection, $sqlString);
			oci_bind_by_name($stid, ':userId', $userId, 20);

			if (oci_execute($stid)) {
				while ($row = oci_fetch_assoc($stid)) {
					array_push($months, $row);
				}
			}
		}
		catch (Exception $exception) {
			if($connection != null){
				Database::end($connection);	
			}
			throw $exception;
		}
		return $months;
	}

	public static function getTotalHoursPlayed($userId){
		try{
			$connection = Database::start();
			$sqlString = "select SUM(extract(hour from cast(END_DATE as timestamp)) - extract(hour from cast(START_DATE as timestamp))) as numhours
					from Game
					where USER_ID = :userId";
			$stid = oci_parse($connection, $sqlString);
			oci_bind_by_name($stid, ':userId', $userId, 20);
			oci_execute($stid);
			
			$hours = array();
			while ($row = oci_fetch_array($stid)) {
				array_push($hours, $row);
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
		return $hours;
	}
	
	public static function getTotalMinutesPlayed($userId){
		try{
			$connection = Database::start();
			$sqlString = "select SUM(extract(minute from cast(END_DATE as timestamp)) - extract(minute from cast(START_DATE as timestamp))) as numminutes
					from Game
					where USER_ID = :userId";
			$stid = oci_parse($connection, $sqlString);
			oci_bind_by_name($stid, ':userId', $userId, 20);
			oci_execute($stid);
			
			$minutes = array();
			while ($row = oci_fetch_array($stid)) {
				array_push($minutes, $row);
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
		return $minutes;

	}
	public static function getProfitByDayOfWeek($userId){
		$results = array();		
		try{
			$connection = Database::start();
			$sqlString = "select to_char(to_date(START_DATE, 'DD/MM/YYYY'), 'DAY') as day, SUM(AMOUNT_OUT - AMOUNT_IN) as amount
					from Game
					where USER_ID = :userId
					group by to_char(to_date(START_DATE, 'DD/MM/YYYY'), 'DAY')";
			$stid = oci_parse($connection, $sqlString);
			oci_bind_by_name($stid, ':userId', $userId, 20);

			if (oci_execute($stid)) {
				while ($row = oci_fetch_assoc($stid)) {
					array_push($results, $row);
				}
			}
		}
		catch (Exception $exception) {
			if($connection != null){
				Database::end($connection);	
			}
			throw $exception;
		}
		return $results;

	}

}

?>
