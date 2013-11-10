<?php

define('USERNAME', 'ora_u4e7');
define('PASSWORD', 'a71174098');
define('DATABASE', 'ug');

class DataType {
	const NUMBER = 0;
	const DATE = 1;
	const VARCHAR = 2;
}

class DatabaseException extends Exception {

	protected $code;
	protected $message;
	protected $statement;

	public function __construct($message, $code, Exception $previous = NULL, $statement = NULL) {
		parent::__construct($code, $message);
		$this->code = $code;
		$this->message = $message;
		$this->statement = $statement;
	}

	public function getStatement() {return $this->statement;}
}

class Database {

	protected static $tableKey;
	protected static $tableAttributes;

	protected function __construct() {}

	// This function converts COLUMN_NAME into propertyName
	private function camelize($s)
	{
	    return lcfirst(implode('', explode(' ', ucwords(implode(' ', explode('_', strtolower($s)))))));
	}

	// This function converts propertyName into COLUMN_NAME
	private function underscore($s)
	{
	    $words = array();
	    $split = preg_split('/([A-Z])/', ucfirst($s), -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
	    for ($i = 0; $i < count($split); $i += 2) {
	        $words[] = strtolower($split[$i] . $split[$i+1]);
	    }
	    return strtoupper(implode('_', $words));
	}

	protected function start(){
		return oci_connect(constant('USERNAME'), constant('PASSWORD'), constant('DATABASE'));
	}

	protected function end($c){
		oci_close($c);
	}

	protected function insert() {
		try {
			// Start database connection
			$connection = $this->start();

			$keyAttributes = static::$tableKey;
			$tableAttributes = static::$tableAttributes;

			foreach ($tableAttributes as $table => $attributes) {

				$columns = array();
				$values = array();
				$returns = array();
				$bindings = array();

				foreach ($keyAttributes as $name => $domain) {

					if (is_null($this->{$name})) {
						if (isset($domain['default'])) {
							// If attribute is null, set it to its default value
							$this->{$name} = $domain['default'];
						} else if (isset($domain['sequence'])) {
							// If attribute is null, set it to a sequence
							$sequence = $domain['sequence'];
						}
					}

					// Add attribute column
					$columns[] = $this->underscore($name);

					$placeholder = ":bv" . count($bindings);
					if (isset($sequence)) {
						// Add attribute sequence
						$values[] = "$sequence.NEXTVAL";
						unset($sequence);
					} else {
						// Add attribute value (substituted by a binding variable placeholder)
						switch ($domain['type']) {
							case DataType::DATE:
								$values[] = "TO_DATE($placeholder, 'yyyy/mm/dd hh24:mi:ss')";
								break;
							default:
								$values[] = $placeholder;
								break;
						}
					}

					// Add attribute column and value (substituted by a binding variable placeholder)
					$returns[$this->underscore($name)] = $placeholder;

					$bindings[$placeholder] = $name;
				}

				foreach ($attributes as $name => $domain) {

					if (is_null($this->{$name}) && isset($domain['default'])) {
						// If attribute is null, set it to its default value
						$this->{$name} = $domain['default'];
					}

					// Add attribute column
					$columns[] = $this->underscore($name);
					// Add attribute value (substituted by a binding variable placeholder)
					$placeholder = ":bv" . count($bindings);
					switch ($domain['type']) {
						case DataType::DATE:
							$values[] = "TO_DATE($placeholder, 'yyyy/mm/dd hh24:mi:ss')";
							break;
						default:
							$values[] = $placeholder;
							break;
					}
					$bindings[$placeholder] = $name;
				}

				// Implode attribute columns and values into comma separated strings
				$columns = implode(',', $columns);
				$values = implode(',', $values);
				$returnColumns = implode(',', array_keys($returns));
				$returnValues = implode(',', array_values($returns));

				// Prepare SQL statement
				$sqlString = "INSERT INTO $table ($columns) VALUES ($values) RETURNING $returnColumns INTO $returnValues";
				$sqlStatement = oci_parse($connection, $sqlString);

				// Perform SQL injection (substitute binding variable placeholders with attribute values)
				foreach ($bindings as $placeholder => $name) {
					oci_bind_by_name($sqlStatement, $placeholder, $this->{$name});
				}

				// Execute SQL statement
				if (oci_execute($sqlStatement, OCI_NO_AUTO_COMMIT)) {
					oci_commit($connection);
				} else {
					$error = oci_error($sqlStatement);	
					throw new DatabaseException($error['code'], $error['message'], NULL, $error['sqltext']);
				}

			}
		} 
		catch (Exception $exception) {
			throw $exception;
		}

		if (isset($connection)) {
			// End database connection
			$this->end($connection);
		}
	}

	protected function update() {
		try {
			// Start database connection
			$connection = $this->start();

			$keyAttributes = static::$tableKey;
			$tableAttributes = static::$tableAttributes;

			foreach ($tableAttributes as $table => $attributes) {

				$sets = array();
				$wheres = array();
				$bindings = array();


				foreach ($keyAttributes as $name => $domain) {
					// Add WHERE condition (values substituted by a binding variable placeholder)
					$columnname = $this->underscore($name);
					$placeholder = ":bv" . count($bindings);
					switch ($domain['type']) {
						case DataType::DATE:
							$wheres[] = "$columnname=TO_DATE($placeholder, 'yyyy/mm/dd hh24:mi:ss')";
							break;
						default:
							$wheres[] = "$columnname=$placeholder";
							break;
					}
					$bindings[$placeholder] = $name;
				}

				foreach ($attributes as $name => $domain) {
					// Add SET assignment (values substituted by a binding variable placeholder)
					$columnname = $this->underscore($name);
					$placeholder = ":bv" . count($bindings);
					switch ($domain['type']) {
						case DataType::DATE:
							$sets[] = "$columnname=TO_DATE($placeholder, 'yyyy/mm/dd hh24:mi:ss')";
							break;
						default:
							$sets[] = "$columnname=$placeholder";
							break;
					}
					$bindings[$placeholder] = $name;
				}


				// Implode assignments and conditions into comma separated strings
				$sets = implode(',', $sets);
				$wheres = implode(',', $wheres);

				// Prepare SQL statement
				$sqlString = "UPDATE $table SET $sets WHERE $wheres";
				$sqlStatement = oci_parse($connection, $sqlString);

				// Perform SQL injection (substitute binding variable placeholders with attribute values)
				foreach ($bindings as $placeholder => $name) {
					oci_bind_by_name($sqlStatement, $placeholder, $this->{$name});
				}

				// Execute SQL statement
				if (oci_execute($sqlStatement, OCI_NO_AUTO_COMMIT)) {
					oci_commit($connection);
				} else {
					$error = oci_error($sqlStatement);	
					throw new DatabaseException($error['code'], $error['message'], NULL, $error['sqltext']);
				}
			}
		} catch (Exception $exception) {
			throw $exception;
		}

		if (isset($connection)) {
			// End database connection
			$this->end($connection);
		}
	}

	protected function delete() {
		try {
			// Start database connection
			$connection = $this->start();

			$keyAttributes = static::$tableKey;
			$tableAttributes = array_reverse(static::$tableAttributes);

			foreach ($tableAttributes as $table => $attributes) {

				$wheres = array();
				$bindings = array();

				foreach ($keyAttributes as $name => $domain) {

					// Add WHERE condition (values substituted by a binding variable placeholder)
					$columnname = $this->underscore($name);
					$placeholder = ":bv" . count($bindings);
					switch ($domain['type']) {
						case DataType::DATE:
							$wheres[] = "$columnname=TO_DATE($placeholder, 'yyyy/mm/dd hh24:mi:ss')";
							break;
						default:
							$wheres[] = "$columnname=$placeholder";
							break;
					}
					$bindings[$placeholder] = $name;
				}

				// Implode where conditions into comma separated string
				$wheres = implode(',', $wheres);

				$sqlString = "DELETE FROM $table WHERE $wheres";
				$sqlStatement = oci_parse($connection, $sqlString);

				// Perform SQL injection (substitute binding variable placeholders with attribute values)
				foreach($bindings as $placeholder => $value) {
					oci_bind_by_name($sqlStatement, $placeholder, $bindings[$placeholder]);
				}

				// Execute SQL statement
				if (oci_execute($sqlStatement, OCI_NO_AUTO_COMMIT)) {
					oci_commit($connection);
				} else {
					$error = oci_error($sqlStatement);	
					throw new DatabaseException($error['code'], $error['message'], NULL, $error['sqltext']);
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

	// lcfirst(implode('', explode(' ', ucwords(implode(' ', explode('_', strtolower($key)))))))

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

			$wheres[] = $joins[0]['variable'] . "." . static::$tableKey . "=" . $joins[1]['variable'] . "." . static::$tableKey . " ";
			//$wheres[] = $joins[0]['variable'] . "." . static::$tableKey . "=" . $this->id . " ";	
			$wheres[] = $joins[0]['variable'] . "." . "USER_ID=" . $this->userId;	

			$newjoins = implode(',', $newjoins);
			$wheres = implode('AND ', $wheres);

			// Prepare SQL statement
			$sqlString = "SELECT AVG($column) FROM $newjoins WHERE $wheres";
			$sqlStatement = oci_parse($connection, $sqlString);
			
			echo "<br>";
			echo $sqlString;
			echo "<br>";
			echo $sqlStatement;

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
}

?>
