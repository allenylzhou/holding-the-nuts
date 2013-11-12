

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
	
	
	public function getTopWinnersOnDay($day) {
		$winners = array();
		try{
			$connection = Database::start();
			$sqlString = 'WITH USER_WINNING_ON_DAY AS (
							  SELECT USER_ID, SUM(AMOUNT_OUT-AMOUNT_IN) as WINNINGS
							  FROM   GAME
							  WHERE trunc(START_DATE) = TRUNC(:date)
							  GROUP BY USER_ID)
							SELECT U.USERNAME, UWD.WINNINGS
							FROM   USER_WINNING_ON_DAY UWD, USERS U
							WHERE  UWD.USER_ID = U.USER_ID
							and UWD.WINNINGS >= ALL (SELECT MAX(A.WINNINGS)
													FROM USER_WINNING_ON_DAY A)';
			$stid = oci_parse($connection, $sqlString);
			oci_bind_by_name($stid, ':date', $day, 20);
			
			oci_define_by_name($stid, 'USERNAME', $username);
			oci_define_by_name($stid, 'WINNINGS', $winnings);
			oci_execute($stid);
			
			while (oci_fetch($stid)) {
				$winners[((string) $username)] = $winnings; 
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
		return $winners;
	}
	



}

?>
