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
		parent::__construct($message, $code);
		$this->code = $code;
		$this->message = $message;
		$this->statement = $statement;
	}

	public function getStatement() {return $this->statement;}
}

class Database {

	protected function __construct() {}

	// This function converts COLUMN_NAME into propertyName
	protected static function camelize($s)
	{
	    return lcfirst(implode('', explode(' ', ucwords(implode(' ', explode('_', strtolower($s)))))));
	}

	// This function converts propertyName into COLUMN_NAME
	protected static function underscore($s)
	{
	    $words = array();
	    $split = preg_split('/([A-Z])/', ucfirst($s), -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
	    for ($i = 0; $i < count($split); $i += 2) {
	        $words[] = strtolower($split[$i] . $split[$i+1]);
	    }
	    return strtoupper(implode('_', $words));
	}

	protected static function start(){
		return oci_connect(constant('USERNAME'), constant('PASSWORD'), constant('DATABASE'));
	}

	protected static function end($c){
		oci_close($c);
	}

	protected static function aggregate($o, $c, $q = array()) {
		$result = 0;
		try {
			// Start database connection
			$connection = static::start();

			// WHERE conditions must be specified
			if (!empty($q)) {

				$wheres = array();
				$bindings = array();

				$keyAttributes = static::$tableKey;
				$tableAttributes = static::$tableAttributes;

				foreach($tableAttributes as $table => $attributes) {
					if (array_key_exists($c, $attributes)) {

						// Add aggregate operation on column name
						$operation = strtoupper($o);
						$columnname = static::underscore($c);
						$select = "$operation($columnname)";

						foreach ($attributes as $name => $domain) {
							if (isset($q[$name])) {		
								// Add WHERE conditions (values substituted by a binding variable placeholder)
								$columnname = static::underscore($name);
								$placeholder = ":bv" . count($bindings);
								switch ($domain['type']) {
									case DataType::DATE:
										$wheres[] = "$columnname=TO_DATE($placeholder, 'yyyy/mm/dd hh24:mi:ss')";
										break;
									default:
										$wheres[] = "$columnname=$placeholder";
										break;
								}
								$bindings[$placeholder] = $q[$name];
							}
						}

						$wheres = implode(' AND ', $wheres);

						$sqlString = "SELECT $select FROM $table WHERE $wheres";
						$sqlStatement = oci_parse($connection, $sqlString);

						// Perform SQL injection (substitute binding variable placeholders with attribute values)
						foreach ($bindings as $placeholder => $value) {
							oci_bind_by_name($sqlStatement, $placeholder, $bindings[$placeholder]);
						}

						// Execute SQL statement
						if (oci_execute($sqlStatement)) {
							$result = oci_fetch_assoc($sqlStatement);
							$result = array_shift($result);
						} else {
							$error = oci_error($sqlStatement);	
							throw new DatabaseException($error['message'], $error['code'], NULL, $error['sqltext']);
						}
					}
				}
			}
		} catch (Exception $exception) {
			throw $exception;
		}

		if (isset($connection)) {
			// End database connection
			static::end($connection);
		}

		return $result;
	}

	// TODO: change this to a static function
	protected function select($q = array()) {
		$results = array();
		try {
			// Start database connection
			$connection = static::start();

			if (empty($q)) {
				// If no query is provided, select self
				$keyAttributes = static::$tableKey;
				$tableAttributes = static::$tableAttributes;

				foreach ($tableAttributes as $table => $attributes) {

					$selects = array();
					$wheres = array();
					$bindings = array();

					foreach ($keyAttributes as $name => $domain) {
						// Add SELECT attributes
						$columnname = static::underscore($name);
						$selects[] = $columnname;

						// Add WHERE conditions (values substituted by a binding variable placeholder)
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

					$selects = implode(',', $selects);
					$wheres = implode(' AND ', $wheres);

					$sqlString = "SELECT $selects FROM $table WHERE $wheres";
					$sqlStatement = oci_parse($connection, $sqlString);

					// Perform SQL injection (substitute binding variable placeholders with attribute values)
					foreach ($bindings as $placeholder => $name) {
						oci_bind_by_name($sqlStatement, $placeholder, $this->{$name});
					}

					// Execute SQL statement
					if (oci_execute($sqlStatement)) {
						foreach (oci_fetch_assoc($sqlStatement) as $key => $value) {
							$results[static::camelize($key)] = $value;
						}
					} else {
						$error = oci_error($sqlStatement);	
						throw new DatabaseException($error['message'], $error['code'], NULL, $error['sqltext']);
					}
				}
			} else {
				// TODO: Implement custom selects
			}
		} catch (Exception $exception) {
			throw $exception;
		}

		if (isset($connection)) {
			// End database connection
			static::end($connection);
		}
		return $results;
	}

	protected function insert() {
		try {
			// Start database connection
			$connection = static::start();

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
					$columns[] = static::underscore($name);

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
					$returns[static::underscore($name)] = $placeholder;

					$bindings[$placeholder] = $name;
				}

				foreach ($attributes as $name => $domain) {

					if (is_null($this->{$name}) && isset($domain['default'])) {
						// If attribute is null, set it to its default value
						$this->{$name} = $domain['default'];
					}

					// Add attribute column
					$columns[] = static::underscore($name);
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
					throw new DatabaseException($error['message'], $error['code'], NULL, $error['sqltext']);
				}

			}
		} 
		catch (Exception $exception) {
			throw $exception;
		}

		if (isset($connection)) {
			// End database connection
			static::end($connection);
		}
	}

	protected function update() {
		try {
			// Start database connection
			$connection = static::start();

			$keyAttributes = static::$tableKey;
			$tableAttributes = static::$tableAttributes;

			foreach ($tableAttributes as $table => $attributes) {

				$sets = array();
				$wheres = array();
				$bindings = array();

				foreach ($keyAttributes as $name => $domain) {
					// Add WHERE condition (values substituted by a binding variable placeholder)
					$columnname = static::underscore($name);
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
					if (isset($this->{$name})) {
						// Add SET assignment (values substituted by a binding variable placeholder)
						$columnname = static::underscore($name);
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
				}

				if (count($sets) > 0) {
					// Implode assignments and conditions into comma separated strings
					$sets = implode(',', $sets);
					$wheres = implode(' AND ', $wheres);

					// Prepare SQL statement
					$sqlString = "UPDATE $table SET $sets WHERE $wheres";
					$sqlStatement = oci_parse($connection, $sqlString);

					// Perform SQL injection (substitute binding variable placeholders with attribute values)
					foreach ($bindings as $placeholder => $name) {
						oci_bind_by_name($sqlStatement, $placeholder, $this->{$name});
					}

					//Execute SQL statement
					if (oci_execute($sqlStatement, OCI_NO_AUTO_COMMIT)) {
						oci_commit($connection);
					} else {
						$error = oci_error($sqlStatement);	
						throw new DatabaseException($error['message'], $error['code'], NULL, $error['sqltext']);
					}
				}
			}
		} catch (Exception $exception) {
			throw $exception;
		}

		if (isset($connection)) {
			// End database connection
			static::end($connection);
		}
	}

	public function delete() {
		try {
			// Start database connection
			$connection = static::start();

			$keyAttributes = static::$tableKey;
			$tableAttributes = array_reverse(static::$tableAttributes);

			foreach ($tableAttributes as $table => $attributes) {

				$wheres = array();
				$bindings = array();

				foreach ($keyAttributes as $name => $domain) {
					// Add WHERE condition (values substituted by a binding variable placeholder)
					$columnname = static::underscore($name);
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
				$wheres = implode(' AND ', $wheres);

				$sqlString = "DELETE FROM $table WHERE $wheres";
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
					throw new DatabaseException($error['message'], $error['code'], NULL, $error['sqltext']);
				}
			}
		} catch (Exception $exception) {
			throw $exception;
		}

		if (isset($connection)) {
			// End database connection
			static::end($connection);
		}
	}

	public static function sum($c, $q) {
		return static::aggregate('sum', $c, $q);
	}

	public static function average() {
		return static::aggregate('average', $c, $q);
	}

	public static function max() {
		return static::aggregate('max', $c, $q);
	}

	public static function min() {
		return static::aggregate('min', $c, $q);
	}	

	public static function count() {
		return static::aggregate('count', $c, $q);
	}

	public function load() {
		return $this->select();
	}

	public function save() {
		try {
			// Try insert
			$this->insert();
		} catch (Exception $exception) {		
			if($exception instanceof DatabaseException && $exception->getCode() == 1) {
				// If unique constraint is violated, try update
				try {
					$this->update();
				} catch (Exception $exception) {
					throw $exception;
				}
			} else {
				throw $exception;
			}
		}
	}

}

?>
