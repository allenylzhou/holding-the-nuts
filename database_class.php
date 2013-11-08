<?php

define('USERNAME', 'ora_u4e7');
define('PASSWORD', 'a71174098');
define('DATABASE', 'ug');

class Database {

// TODO: use constants
private static $USERNAME = 'ora_u4e7'; // 'project'; 
private static $PASSWORD = 'a71174098'; //'project';
private static $CONNECTSTRING = 'ug'; //'localhost:1521'; 

protected static $tableSchemas;
protected static $tableSequencer;
protected static $tableKey;

protected function __construct () {}

// TODO: use start() instead
public static function getConnection(){
	return OCILogon(self::$USERNAME, self::$PASSWORD, self::$CONNECTSTRING);
}

// TODO: use end($c) instead
public static function closeConnection($c){
	OCILogoff($c);
}

protected function start(){
	return oci_connect(constant('USERNAME'), constant('PASSWORD'), constant('DATABASE'));
}

protected function end($c){
	oci_close($c);
}

protected function insert() {
	try {
		$connection = $this->start();
		foreach(static::$tableSchemas as $name => $attributes) {

			$columns = array();
			$fields = array();
			$bindings = array();

			// Create placeholder for id
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

			// Execute SQL statement
			if (oci_execute($sqlStatement, OCI_NO_AUTO_COMMIT)) {
				oci_commit($connection);
				echo "$name INSERT SUCCESS; ID = $this->id<br/>";
			} else {
				$err = OCIError($sqlStatement)['code'];				
				switch ($err) {
					default:
						throw new Exception("An unknown error has occured.");
						break;
				}
			}
		}

	} 
	catch (Exception $exception) {
		throw $exception;
	}

	if (isset($connection)) {
		$this->end($connection);
	}
}

protected function update() {
	try {
		$connection = $this->start();
		foreach(static::$tableSchemas as $name => $attributes) {

			$sets = array();
			$bindings = array();

			// Create placeholders for other fields
			foreach($attributes as $property => $column) {

				$placeholder = ":bv" . count($bindings);
				if (strtotime($this->{$property})) {
					// Wrap date and time placeholder
					$sets[] = "$column = TO_DATE($placeholder, 'yyyy/mm/dd hh24:mi:ss')";
				} else {
					$sets[] = "$column = $placeholder";	
				}
				$bindings[$placeholder] = $this->{$property};
			}

			$sets = implode(',', $sets);

			// Prepare SQL statement
			$where = static::$tableKey . "=" . $this->id;
			$sqlString = "UPDATE $name SET $sets WHERE $where";
			$sqlStatement = oci_parse($connection, $sqlString);

			// Make bindings
			foreach($bindings as $placeholder => $value) {
				oci_bind_by_name($sqlStatement, $placeholder, $bindings[$placeholder]);
			}

			// Execute SQL statement
			if (oci_execute($sqlStatement, OCI_NO_AUTO_COMMIT)) {
				oci_commit($connection);
				echo "$name UPDATE SUCCESS; ID = $this->id<br/>";
			} else {
				$err = OCIError($sqlStatement)['code'];				
				switch ($err) {
					default:
						throw new Exception("An unknown error has occured.");
						break;
				}
			}
		}
	} catch (Exception $exception) {
		throw $exception;
	}

	if (isset($connection)) {
		$this->end($connection);
	}
}

protected function delete() {
	try {
		$connection = $this->start();
		foreach(array_reverse(static::$tableSchemas) as $name => $attributes) {
			// Prepare SQL statement
			$where = static::$tableKey . "=" . $this->id;
			$sqlString = "DELETE FROM $name WHERE $where";
			$sqlStatement = oci_parse($connection, $sqlString);

			// Execute SQL statement
			if (oci_execute($sqlStatement, OCI_NO_AUTO_COMMIT)) {
				oci_commit($connection);
				echo "$name DELETE SUCCESS; ID = $this->id<br/>";
			} else {
				$err = OCIError($sqlStatement)['code'];				
				switch ($err) {
					default:
						throw new Exception("An unknown error has occured.");
						break;
				}
			}
		}
	} catch (Exception $exception) {
		throw $exception;
	}

	if (isset($connection)) {
		$this->end($connection);
	}
}

}

?>