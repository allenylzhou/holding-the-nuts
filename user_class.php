<?php

include 'database_class.php';
	
class User extends Database {

	// This maps model properties to database
	protected static $tableSchemas = array(
		'USERS' => array(
			'username' => 'USERNAME',
			'password' => 'PASSWORD'
		)
	);

	protected static $tableSequencer = 'USERS_SEQUENCE';
	protected static $tableKey = 'USER_ID';

	public function __construct ($id = null) {
		parent::__construct();

		if (isset($id)) {
			$this->id = $id;
			$properties = $this->select();
			$this->setProperties($properties);
		}
	}

	protected $id;
	protected $username;
	protected $password;

	public function getProperties() {
		return get_object_vars($this);
	}

	public function setProperties($properties) {
		unset($properties['id']);
		foreach($properties as $key => $value) {
			$this->{$key} = $value;
			// if($key == 'password'){						
			// 	$Input=iconv('UTF-8','UTF-16LE',$value);
			// 	$hash=bin2hex(mhash(MHASH_MD4,$Input));
			// 	$this->{$key} = $hash;
			// }
		}
	}

	public function save() {
		if (isset($this->id)) {
			$this->update();
		} else {
			$this->insert();
		}
	}

	
	public function erase() {
		$this->delete();
	}
	
	
	public function login() {
		try{
			$connection = Database::start();
			$sqlString = 'SELECT * 
						FROM USERS
						WHERE USERNAME = :username 
						and PASSWORD = :password';
			$stid = oci_parse($connection, $sqlString);
			oci_bind_by_name($stid, ':username', $this->username, 20);
			oci_bind_by_name($stid, ':password', $this->password, 20);
			
			oci_define_by_name($stid, 'USER_ID', $userid);
			oci_define_by_name($stid, 'USERNAME', $username);
			oci_define_by_name($stid, 'PASSWORD', $passwordHash);
			oci_execute($stid);

			while (oci_fetch($stid)) {
				if($userid != null){
					$this->id = $userid;
					$this->username = $username;
					$this->password = $passwordHash;
					return;
				}
			}
			throw new Exception('Improper credentials supplied');
		}
		catch (Exception $exception) {
			if($connection != null){
				Database::end($connection);	
			}
			throw $exception;
		}
		if($connection != null){
			Database::end($connection);	
		}
		
	}
	
	public static function hash($p){
		$input=iconv('UTF-8','UTF-16LE',$p);
        return bin2hex(mhash(MHASH_MD4,$input));
	}
}

?>