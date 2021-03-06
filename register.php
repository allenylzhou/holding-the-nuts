<?php

$template = "views/templates/user-authentication.html";

$error = array();
if (!array_key_exists('username', $_POST) 
	&& !array_key_exists('password', $_POST)
	&& !array_key_exists('email', $_POST)) {
	
}
else if (!array_key_exists('username', $_POST) 
		|| !array_key_exists('password', $_POST)
		|| !array_key_exists('email', $_POST)) {
	$error[] =  'Please fill in the registration form.';
}
else if($_POST['username'] == '' 
	|| $_POST['password'] == ''
	|| $_POST['email'] == ''){
	$error[] =  'Please fill in all of the form please';
}
else {
	try {
		$username = $_POST['username'];                                        
        $password = User::hash($_POST['password']);
		$email = $_POST['email'];
		
		$user = new User;
		$user->setAttributes(array(
			'username' => $username,
			'password' => $password,
			'email' => $email
		));
		
		$user->save();
		$user->login();
		$error[] = 'Registration successful';
	}
	catch (DatabaseException $exception) {
		switch ($exception->getErrorCode()) {
			case 1:
				$error[] =  "This username has already been claimed, or the email has already been claimed";
				break;
			case 2290:
				$error[] =  "Your username is invalid, or your email is invalid";
				break;
			default:
				$error[] =  "An unknown error has occured";
				break;
		}
	}
	catch (Exception $exception) {
		$error[] = $exception->getMessage();
	}
}

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('views/templates/app-container.html');
$TBS->MergeBlock('messages', $error);
$TBS->Show();

?>