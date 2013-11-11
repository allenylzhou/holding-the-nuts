<?php

include_once 'database_class.php';
	
class Location extends Database {

	protected static $tableKey = array(
		'userId' => array('type' => DataType::NUMBER),
		'name' => array('type' => DataType::VARCHAR)
	);

	protected static $tableAttributes = array(
		'LOCATION' => array(
			'favourite' => array('type' => DataType::NUMBER)
		)
	);

	protected $userId;
	protected $name;
	protected $favourite;

	public function __construct ($key = array(), $select = false) {
		parent::__construct();

		foreach ($key as $name => $value) {
			if (array_key_exists($name, static::$tableKey)) {
				$this->{$name} = $value;
			}
		}

		if ($select) {
			$this->setAttributes($this->load());
		}
	}

	public function getUserId() { return $this->userId; }
	public function getName() { return $this->name; }
	public function getFavourite() { return $this->favourite; }

	public function setAttributes($attributes) {
		foreach($attributes as $name => $value) {
			if (!array_key_exists($name, static::$tableKey)) {
				$this->{$name} = $value;
			}
		}
	}
}

?>