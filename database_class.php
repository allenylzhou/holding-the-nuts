<?php

abstract class Database {

private static $USERNAME = 'project'; // "ora_u4e7"
private static $PASSWORD = 'project'; // "a71174098"
private static $CONNECTSTRING = 'localhost:1521'; // "ug";

protected static $tableName;
protected static $tableModelMap;

protected function __construct () {}

public static function getConnection(){
	return OCILogon(self::$USERNAME, self::$PASSWORD, self::$CONNECTSTRING);
}

public static function closeConnection($c){
	OCILogoff($c);
}

public static function insert($attributes, $overrideTableName = null) {
	if ($connection = oci_connect("ora_u4e7", "a71174098", "ug")) {

		if (isset($overrideTableName)) {
			$tableName = $overrideTableName;
		} else {
			$tableName = static::$tableName;
		}

		// Comma separated attributes names
		$attributeNames = implode(',', array_keys($attributes));

		// Comma separated attributes values
		$attributeValues = implode(',', array_map(function($value) {
			return (is_null($value)) ? "NULL" : $value;
		}, array_values($attributes)));

		// TODO: Find a way to bind $attributeValues to protect against SQL injection
		// TODO: Bind sequencer id to return variable
		$sqlString = "INSERT INTO $tableName ($attributeNames) VALUES ($attributeValues)";
		$sqlStatement = oci_parse($connection, $sqlString);

		oci_execute($sqlStatement);

		echo "Successfully inserted into $tableName.<br/>";

		OCILogoff($connection);
	} else {
		$err = OCIError();
		echo "Oracle Connect Error " . $err['message'];
	}
}

public static function update() {
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