<?php

include 'error_reporting.php';
include 'tbs_class.php';

include 'user_class.php';

$template = "views/templates/user-authentication.html";

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('views/templates/app-container.html');
$TBS->Show();

if (!array_key_exists('username', $_POST) && !array_key_exists('password', $_POST)) {
	
}
else if (!array_key_exists('username', $_POST) || !array_key_exists('password', $_POST)) {
	echo 'Please fill in the Login form.';
}
else if($_POST['username'] == '' || $_POST['password'] == ''){
	echo 'Please fill in all of the form please';
}
else {
	try {
		$username = $_POST['username'];                                        
        $password = User::hash($_POST['password']);
					
		$user = new User;
		$user->setProperties(array(
			'username' => $username,
			'password' => $password
		));
		
		$user->login();
		header('Location: register.php') ;

	}
	catch (Exception $exception) {
		echo $exception->getMessage();
	}
}

?>