<?php

include_once 'database_class.php';
	
class User extends Database {

	protected static $tableKey = array(
		'userId' => array('type' => DataType::NUMBER, 'sequence' => 'USERS_SEQUENCE')
	);

	protected static $tableAttributes = array(
		'USERS' => array(
			'username' => array('type' => DataType::VARCHAR),
			'password' => array('type' => DataType::VARCHAR),
			'email' => array('type' => DataType::VARCHAR)
		)
	);

	protected $userId;
	protected $username;
	protected $password;
	protected $email;

	public function __construct ($key = array(), $select = false) {
		parent::__construct();

		foreach ($key as $name => $value) {
			if (array_key_exists($name, static::$tableKey)) {
				$this->{$name} = $value;
			}
		}

		if ($select) {
			$this->setAttributes($this->select());
		}
	}

	public function getUserId() { return $this->userId; }
	public function getUsername() { return $this->username; }
	public function getEmail() { return $this->email; }

	public function setAttributes($attributes) {
		foreach($attributes as $name => $value) {
			if (!array_key_exists($name, static::$tableKey)) {
				$this->{$name} = $value;
			}
		}
	}	
	
	public function login() {
		try{
			$connection = Database::start();
			$sqlString = 'SELECT USER_ID, USERNAME, PASSWORD 
						FROM USERS
						WHERE USERNAME = :username 
						and PASSWORD = :password';
			$stid = oci_parse($connection, $sqlString);
			oci_bind_by_name($stid, ':username', $this->username, 20);
			oci_bind_by_name($stid, ':password', $this->password, 20);
			oci_execute($stid);

			while ($row = oci_fetch_array($stid)) {
				$val = $row['USER_ID'];
				if($val != null){
					$this->userId = $val;
					$val = $row['USERNAME'];
					$this->username = $val;
					$val = $row['PASSWORD'];
					$this->password = $val;
					
					session_start();
					$_SESSION['USER'] = $this;
					session_write_close();
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
	
	public static function findUserId($username){
		try{
			$connection = Database::start();
			$sqlString = 'SELECT USER_ID FROM USERS WHERE USERNAME = :username';
			$stid = oci_parse($connection, $sqlString);
			oci_bind_by_name($stid, ':username', $username, 20);
			oci_execute($stid);
			
			while ($row = oci_fetch_array($stid)) {
				$userId = $row['USER_ID'];
				return $userId;
			}
			
			throw new Exception('No user found');
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
	
	// TODO
	// WEIRD EMAIL LOGIC IN CASE BACKERS DON'T EXIST
	public function addBacker($backerId) {
		try{
			$connection = Database::start();
			$sqlString = 'INSERT INTO HORSE_BACKERS (HORSE, BACKER) VALUES (:userId, :backerId);';
			$stid = oci_parse($connection, $sqlString);
			oci_bind_by_name($stid, ':userId', $this->userId, 20);
			oci_bind_by_name($stid, ':backerId', $backerId, 20);
			oci_execute($stid);
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
	
	// SELECT AND PROJECT
	public function getBackers() {
		$backers = array();   
		try{
			$connection = Database::start();
			$sqlString = 'SELECT BACKER as myBackers
						FROM HORSE_BACKERS
						WHERE HORSE = :user_id';
			$stid = oci_parse($connection, $sqlString);
			oci_bind_by_name($stid, ':user_id', $this->userId, 20);
			
			oci_define_by_name($stid, 'myBackers', $backerId);
			oci_execute($stid);
			
			while (oci_fetch($stid)) {
				if($userid != null){
					$backers[] = $backerId;
				}
			}
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
		return $backers;
	}
	
	// DIVISION
	public function getUsersWithSameBackers() {
		$otherUsers = array();   
		try{
			$connection = Database::start();
			$sqlString = 'select distinct(hb.horse) as otherUser
							from horse_backers hb
							where not exists (select distinct(hb1.backer)
											from horse_backers hb1
											where hb1.horse = :userId
											minus
											select distinct(hb2.backer) 
											from horse_backers hb2
											where hb2.horse = hb.horse)';
			$stid = oci_parse($connection, $sqlString);
			oci_bind_by_name($stid, ':userId', $this->userId, 20);
			
			oci_define_by_name($stid, 'otherUser', $otherUser);
			oci_execute($stid);
			
			while (oci_fetch($stid)) {
				if($otherUser != null){
					$otherUsers[] = $otherUser;
				}
			}
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
		return $otherUsers;
	}
	
	
	public static function hash($p){
		$input=iconv('UTF-8','UTF-16LE',$p);
        return bin2hex(mhash(MHASH_MD4,$input));
	}

	public function getTotalProfit() {
		$amountOut = Game::sum('amountOut', array('userId' => $this->userId));
		$amountIn = Game::sum('amountIn', array('userId' => $this->userId));
		return $amountOut - $amountIn;
	}
}

?>
