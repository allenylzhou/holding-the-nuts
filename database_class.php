<?php

define('USERNAME', 'ora_u4e7');
define('PASSWORD', 'a71174098');
define('DATABASE', 'ug');

class Database {

protected static $tableSchemas;
protected static $tableSequencer;
protected static $tableKey;

protected function __construct () {}


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
				$test = OCIError($sqlStatement);
				$err = $test['code'];	
				$this->handleError($err);
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
				$test = OCIError($sqlStatement);
				$err = $test['code'];	
				$this->handleError($err);
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
				$test = OCIError($sqlStatement);
				$err = $test['code'];	
				$this->handleError($err);
			}
		}
	} catch (Exception $exception) {
		throw $exception;
	}

	if (isset($connection)) {
		$this->end($connection);
	}
}

protected function select() {
	try {
		$connection = $this->start();
		$properties = array();
		foreach(static::$tableSchemas as $name => $attributes) {

			$selects = array();
			foreach($attributes as $property => $column) {
				$selects[] = "$column";
			}
			$selects = implode(',', $selects);

			// Prepare SQL statement
			$where = static::$tableKey . "=" . $this->id;
			$sqlString = "SELECT $selects FROM $name WHERE $where";
			$sqlStatement = oci_parse($connection, $sqlString);

			// Execute SQL statement
			if (oci_execute($sqlStatement)) {
				$properties = array_merge($properties, oci_fetch_assoc($sqlStatement));
			} else {
				$test = OCIError($sqlStatement);
				$err = $test['code'];	
				$this->handleError($err);
			}
		}

		$keys = array();
		foreach($properties as $key => $value) {
			// Transforms an under_scored_string to a camelCasedOne
			$keys[] = lcfirst(implode('', explode(' ', ucwords(implode(' ', explode('_', strtolower($key)))))));
		}
		$properties = array_combine($keys, array_values($properties));
		return $properties;
	} catch (Exception $exception) {
		throw $exception;
	}

	if (isset($connection)) {
		$this->end($connection);
	}
}

protected function getAverage($column) {
	try {
		$connection = $this->start();

		$joins = array();
		$wheres = array();

		foreach(static::$tableSchemas as $name => $attributes) {
			$joins[] = array('name' => $name, 'variable' => "v" . count($joins));
		}
		
		$newjoins = array();
		foreach ($joins as $join) {
			$newjoins[] = $join['name'] . " " . $join['variable'];
		}

		$wheres[] = $joins[0]['variable'] . "." . static::$tableKey . "=" . $joins[1]['variable'] . "." . static::$tableKey;
		$wheres[] = $joins[0]['variable'] . "." . static::$tableKey . "=" . $this->id;	

		$newjoins = implode(',', $newjoins);
		$wheres = implode('AND', $wheres);

		// Prepare SQL statement
		$sqlString = "SELECT AVG($column) FROM $newjoins WHERE $wheres";
		$sqlStatement = oci_parse($connection, $sqlString);

		// Execute SQL statement
		if (oci_execute($sqlStatement)) {
			$result = oci_fetch_assoc($sqlStatement);
		} else {
			$test = OCIError($sqlStatement);
			$err = $test['code'];	
			$this->handleError($err);
		}

		return $result;


	} catch (Exception $exception) {
		throw $exception;
	}

	if (isset($connection)) {
		$this->end($connection);
	}


}

protected function getSum($column) {
	try {
		$connection = $this->start();

		$joins = array();
		$wheres = array();

		foreach(static::$tableSchemas as $name => $attributes) {
			$joins[] = array('name' => $name, 'variable' => "v" . count($joins));
		}
		
		$newjoins = array();
		foreach ($joins as $join) {
			$newjoins[] = $join['name'] . " " . $join['variable'];
		}

		$wheres[] = $joins[0]['variable'] . "." . static::$tableKey . "=" . $joins[1]['variable'] . "." . static::$tableKey;
		$wheres[] = $joins[0]['variable'] . "." . static::$tableKey . "=" . $this->id;	

		$newjoins = implode(',', $newjoins);
		$wheres = implode('AND', $wheres);

		// Prepare SQL statement
		$sqlString = "SELECT SUM($column) FROM $newjoins WHERE $wheres";
		$sqlStatement = oci_parse($connection, $sqlString);

		// Execute SQL statement
		if (oci_execute($sqlStatement)) {
			$result = oci_fetch_assoc($sqlStatement);
		} else {
			$test = OCIError($sqlStatement);
			$err = $test['code'];	
			$this->handleError($err);
		}

		return $result;


	} catch (Exception $exception) {
		throw $exception;
	}

	if (isset($connection)) {
		$this->end($connection);
	}


}
// if we're moving most of the sql queries to be auto generated, then we need generalized errors to be hand
private function handleError($err){

	switch ($err) {
		case 1:
			// Unique constraint violated
			throw new ErrorCodeException("Unique constraint violated", 1);
			break;
		case 2290:
			// Check constraint violated
			throw new ErrorCodeException("Check constraint violated", 2290);
			break;
		default:
			throw new ErrorCodeException("An unknown error has occured.", null);
			break;
	}

}

}

class ErrorCodeException extends Exception { 
	private $errorCode;
	public function __construct($m, $c){
		$this->message = $m;
		$this->errorCode = $c;
	}
	public function getErrorCode(){return $this->errorCode;}
}

?>
