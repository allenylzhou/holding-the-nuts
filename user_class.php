<?php

include 'database_class.php';
	
class User extends Database {

	// This maps model properties to database
	protected static $tableSchemas = array(
		'GAME' => array(
			'ID' => 'USER_ID',
			'username' => 'USERNAME',
			'password' => 'PASSWORD'
		)
	);

	protected static $tableSequencer = 'USERS_SEQUENCE';

	protected function __construct () {
		parent::__construct();
	}

	protected $ID;
	protected $username;
	protected $password;

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