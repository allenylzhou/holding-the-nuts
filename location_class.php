<?php

include 'database_class.php';
	
class Location extends Database {

	// This maps model properties to database
	protected static $tableSchemas = array(
		'GAME' => array(
			'userId' => 'USER_ID',
			'name' => 'NAME'
		)
	);

	// TODO: Modify database_class.php to handle the case where there is no sequencer
	//protected static $tableSequencer = 'GAME_SEQUENCE';

	protected function __construct () {
		parent::__construct();
	}

	protected $ID;
	protected $userId;
	protected $name;

	public function setProperties($properties) {
		foreach($properties as $key => $value) {
			$this->{$key} = $value;
		}
	}

	public function save() {
		if isset($this->ID) {
			$this->update();
		} else {
			$this->insert();
		}
	}

}

?>