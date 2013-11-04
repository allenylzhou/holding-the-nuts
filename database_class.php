<?php

class Database {

private static $USERNAME = 'project'; // "ora_u4e7"
private static $PASSWORD = 'project'; // "a71174098"
private static $CONNECTSTRING = 'localhost:1521'; // "ug";

protected static $tableSchemas;
protected static $tableSequencer;

protected function __construct () {}

public static function getConnection(){
	return OCILogon(self::$USERNAME, self::$PASSWORD, self::$CONNECTSTRING);
}

public static function closeConnection($c){
	OCILogoff($c);
}

public function insert() {
	if ($connection = oci_connect("ora_u4e7", "a71174098", "ug")) {

		foreach(static::$tableSchemas as $name => $properties) {

			$columns = array();
			$fields = array();

			// Create bind variable placeholders for each property
			$bindings = array();
			foreach($properties as $field => $column) {
				$columns[] = $column;
				if ($field == "ID" && is_null($this->{$field})) {
					$fields[] = static::$tableSequencer . ".nextval";
				} else {
					$placeholder = ":bv" . count($bindings);
					$fields[] = $placeholder;
					$bindings[$placeholder] = $this->{$field};
				}
			}

			$columns = implode(',', $columns);
			$fields = implode(',', $fields);
			$returnColumn = $properties['ID'];

			// Prepare SQL statement
			$sqlString = "INSERT INTO $name ($columns) VALUES ($fields) RETURNING $returnColumn INTO :rv";
			$sqlStatement = oci_parse($connection, $sqlString);

			// Make bindings
			oci_bind_by_name($sqlStatement, ':rv', $this->ID);
			foreach($bindings as $placeholder => $value) {
				oci_bind_by_name($sqlStatement, $placeholder, $bindings[$placeholder]);
			}

			if(oci_execute($sqlStatement)) {
				echo "Successfully inserted into $name: ID = $this->ID.<br/>";
			}
		}

		OCILogoff($connection);

	} else {
		$err = OCIError();
		echo "Oracle Connect Error " . $err['message'];
	}
}

public function update() {
	echo $this->amountOut;
	if ($c=OCILogon("ora_u4e7", "a71174098", "ug")) {
		echo "Successfully connected to Oracle.\n";
		OCILogoff($c);
	} else {
		$err = OCIError();
		echo "Oracle Connect Error " . $err['message'];
	}
}

public function delete() {
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