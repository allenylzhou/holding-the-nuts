

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



}

?>