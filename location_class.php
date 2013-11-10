<?php

include 'database_class.php';
	
class Location extends Database {

	protected static $tableKey = array(
		'userId' => array('type' => DataType::NUMBER),
		'name' => array('type' => DataType::VARCHAR)
	);

	// This maps model properties to database
	protected static $tableAttributes = array(
		'LOCATION' => array(
			'favourite' => array('type' => DataType::NUMBER)
		)
	);

	protected function __construct () {
		parent::__construct();
	}

	protected $userId;
	protected $name;
	protected $favourite

	public function setProperties($properties) {
		foreach($properties as $key => $value) {
			$this->{$key} = $value;
		}
	}

	public function save() {
		if (isset($this->ID)) {
			$this->update();
		} else {
			$this->insert();
		}
	}

}

?>