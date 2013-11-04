<?php

class Database {

	private static $USERNAME = 'project'; // "ora_u4e7"
	private static $PASSWORD = 'project'; // "a71174098"
	private static $CONNECTSTRING = 'localhost:1521'; // "ug";
	
	public static function getConnection(){
		return OCILogon(self::$USERNAME, self::$PASSWORD, self::$CONNECTSTRING);
	}
	
	public static function closeConnection($c){
		OCILogoff($c);
	}

	public static function insert($params) {
		if ($c=OCILogon("ora_u4e7", "a71174098", "ug")) {
		  echo "Successfully connected to Oracle.\n";
		  OCILogoff($c);
		} else {
		  $err = OCIError();
		  echo "Oracle Connect Error " . $err['message'];
		}
	}

	public static function update($params) {
		if ($c=OCILogon("ora_u4e7", "a71174098", "ug")) {
		  echo "Successfully connected to Oracle.\n";
		  OCILogoff($c);
		} else {
		  $err = OCIError();
		  echo "Oracle Connect Error " . $err['message'];
		}
	}

	public static function delete() {
		if ($c=OCILogon("ora_u4e7", "a71174098", "ug")) {
		  echo "Successfully connected to Oracle.\n";
		  OCILogoff($c);
		} else {
		  $err = OCIError();
		  echo "Oracle Connect Error " . $err['message'];
		}
	}
}

?>