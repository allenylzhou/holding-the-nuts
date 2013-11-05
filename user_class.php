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

	public function __construct () {
		parent::__construct();
	}

	protected $ID;
	protected $username;
	protected $password;

	public function setProperties($properties) {
		foreach($properties as $key => $value) {
			$this->{$key} = $value;
			if($key == 'password'){						
				$Input=iconv('UTF-8','UTF-16LE',$value);
				$hash=bin2hex(mhash(MHASH_MD4,$Input));
				$this->{$key} = $hash;
			}
		}
	}

	public function save() {
		if (isset($this->ID)) {
			$this->update();
		} else {
			$this->insert();
		}
	}

	public function register() {
		try{
			$connection = Database::getConnection();
			$stid = oci_parse($connection, 'INSERT INTO USERS (USER_ID, USERNAME, PASSWORD) VALUES (USERS_SEQUENCE.nextval, :username, :password)');
			oci_bind_by_name($stid, ':username', $this->username, 20);
			oci_bind_by_name($stid, ':password', $this->password, 20);
			$return = @oci_execute($stid, OCI_NO_AUTO_COMMIT);
				
			if($return === false){ 
				$err = OCIError($stid)['code'];				
				switch ($err) {
					case 1:
						throw new Exception("This username has already been claimed.");
						break;
					default:
						throw new Exception("An unknown error has occured");
						break;
				}
			}
			else{
				oci_commit($connection);
			}	
		}
		catch (Exception $exception) {
			throw $exception;
		}
		finally {
			if($connection != null){
				Database::closeConnection($connection);	
			}
		}
	}
}

?>