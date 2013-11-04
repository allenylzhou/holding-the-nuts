<html>
<body>

<form method="POST" action="register.php">
	<label>Username:</label>
	<input type="text" name="username" value="">
	<label>Password:</label>
	<input type="register" name="password" value="" >
	<input type="submit" name="" value="Register Me" >
</form>

<?php

include 'database_class.php';
include 'tbs_class.php';
	if (array_key_exists('username', $_POST) && array_key_exists('password', $_POST)) {
		$connection;
		try {
			$username = $_POST['username'];
			$password = $_POST['password'];
			
			$Input=iconv('UTF-8','UTF-16LE',$password);
			$hash=bin2hex(mhash(MHASH_MD4,$Input));
			
			$connection = Database::getConnection();
			$stid = oci_parse($connection, 'INSERT INTO USERS (USER_ID, USERNAME, PASSWORD) VALUES (USERS_SEQUENCE.nextval, :username, :password)');
			oci_bind_by_name($stid, ':username', $username, 20);
			oci_bind_by_name($stid, ':password', $hash, 20);
			$return = @oci_execute($stid, OCI_NO_AUTO_COMMIT);
			
			if($return === false){ 
				$err = OCIError($stid)['code'];		

				// throw errors				
				switch ($err) {
					case 1:
						echo "This username has already been claimed.";
						break;
					default:
						echo "An unknown error has occured";
						break;
				}
			}
			else{
				echo "Registration has been succesful!";
				oci_commit($connection);
			}
			
		}
		catch (Exception $exception) {
		}
		finally {
			if($connection != null){
				Database::closeConnection($connection);	
			}
		}
	}
	else{
		
	}
	$TBS = new clsTinyButStrong;
	//$TBS->LoadTemplate('game-history.html');
	//$TBS->MergeBlock('gameData', $gameData);
	$TBS->Show();
?>


	
</body>
</html>