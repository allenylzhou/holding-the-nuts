<html>
<body>
<h1>Registration Form</h1>
<form method="POST" action="register.php">
	<p>Your username must be of at least length 3</p>
	<label>Username:</label>
	<input type="text" name="username">
	<label>Password:</label>
	<input type="password" name="password">
	<input type="submit" name="" value="Register Me" >
</form>

<?php

include 'user_class.php';
include 'tbs_class.php';
	
	if (!array_key_exists('username', $_POST) && !array_key_exists('password', $_POST)) {
		
	}
	else if (!array_key_exists('username', $_POST) || !array_key_exists('password', $_POST)) {
		echo 'Please fill in the registration form.';
	}
	else if($_POST['username'] == '' || $_POST['password'] == ''){
		echo 'Please fill in all of the form please';
	}
	else {
		try {
			$username = $_POST['username'];                                        
            $password = User::hash($_POST['password']);
			
			$user = new User;
			$user->setAttributes(array(
				'username' => $username,
				'password' => $password
			));
			
			//$user->register();
			$user->save();
			$user->login();
			echo 'Registration successful';
		}
		catch (ErrorCodeException $exception) {
			switch ($exception->getErrorCode()) {
				case 1:
					echo "This username has already been claimed.";
					break;
				case 2290:
					echo "Your username is invalid";
					break;
				default:
					echo "An unknown error has occured";
					break;
			}
		}
	}
	$TBS = new clsTinyButStrong;
	//$TBS->LoadTemplate('game-history.html');
	//$TBS->MergeBlock('gameData', $gameData);
	$TBS->Show();
?>


	
</body>
</html>