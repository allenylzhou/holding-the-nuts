<?php

class Database {

private static $USERNAME = 'ora_u4e7'; // 'project'; 
private static $PASSWORD = 'a71174098'; //'project';
private static $CONNECTSTRING = 'ug'; //'localhost:1521'; 

protected static $tableSchemas;
protected static $tableSequencer;
protected static $tableKey;

protected function __construct () {}

public static function getConnection(){
	return OCILogon(self::$USERNAME, self::$PASSWORD, self::$CONNECTSTRING);
}

public static function closeConnection($c){
	OCILogoff($c);
}

public function insert() {
	if ($connection = oci_connect("ora_u4e7", "a71174098", "ug")) {
		foreach(static::$tableSchemas as $name => $attributes) {

			$columns = array();
			$fields = array();
			$bindings = array();

			// Create placeholder for key
			$columns[] = static::$tableKey; 
			if (isset($this->id)) {
				$fields[] = ":id";
			} else {
				$fields[] = static::$tableSequencer . ".nextval";
			}

			// Create placeholders for other fields
			foreach($attributes as $property => $column) {
				$columns[] = $column;

				$placeholder = ":bv" . count($bindings);
				if (strtotime($this->{$property})) {
					// Wrap date and time placeholder
					$fields[] = "TO_DATE($placeholder, 'yyyy/mm/dd hh24:mi:ss')";
				} else {
					$fields[] = $placeholder;
				}
				$bindings[$placeholder] = $this->{$property};
			}

			// Comma separate column names and field placeholders
			$columns = implode(',', $columns);
			$fields = implode(',', $fields);

			// Prepare SQL statement
			$sqlString = "INSERT INTO $name ($columns) VALUES ($fields) RETURNING " . static::$tableKey . " INTO :id";
			$sqlStatement = oci_parse($connection, $sqlString);

			// Make bindings
			oci_bind_by_name($sqlStatement, ":id", $this->id);
			foreach($bindings as $placeholder => $value) {
				oci_bind_by_name($sqlStatement, $placeholder, $bindings[$placeholder]);
			}

			//echo $sqlString;
			//print("<pre>".print_r($bindings, true)."</pre>");

			if(oci_execute($sqlStatement)) {
				echo "Successfully inserted into $name table: ID = $this->id.<br/>";
			} else {

			}
		}
		OCILogoff($connection);
	} else {
		$err = OCIError();
		echo "Oracle Connect Error " . $err['message'];
	}
}

public function update() {
	if ($connection = oci_connect("ora_u4e7", "a71174098", "ug")) {

		foreach(static::$tableSchemas as $name => $properties) {

			$sets = array();

			// Create bind variable placeholders for each property
			$bindings = array();
			foreach($properties as $field => $column) {

				if ($field == "ID") {
					$where = "$column=".$this->{$field};
				} else {
					$placeholder = ":bv" . count($bindings);
					$sets[] = "$column = $placeholder";	
					$bindings[$placeholder] = $this->{$field};
				}
			}

			$sets = implode(',', $sets);

			// Prepare SQL statement
			$sqlString = "UPDATE $name SET $sets WHERE $where";
			$sqlStatement = oci_parse($connection, $sqlString);

			// Make bindings
			foreach($bindings as $placeholder => $value) {
				oci_bind_by_name($sqlStatement, $placeholder, $bindings[$placeholder]);
			}

			if(oci_execute($sqlStatement)) {
				echo "Successfully updated $name table: ID = $this->ID.<br/>";
			}
		}

		OCILogoff($connection);

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