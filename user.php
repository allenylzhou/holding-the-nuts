<?php

$template = "views/templates/user-profile.html";

$error = array();
if (!isset($_SESSION['USER'])){
	header('Location: ./login.php?redirect=1');
}

$user = $_SESSION['USER'];
var_dump($user);
if (   !array_key_exists('username', $_POST) 
	&& !array_key_exists('password', $_POST)
	&& !array_key_exists('email', $_POST)) {
}
else {
	$un = $user->getUsername();
	$pw = $user->getPassword();
	$e = $user->getEmail();
	try {
		$user = $_SESSION['USER'];
		$username = $_POST['username'];                                        
		$password = $_POST['password'];
		$email = $_POST['email'];
		
		$user->setUsername($username);
		if($password != ''){
			$user->setPassword(User::hash($_POST['password']));
		}
		$user->setEmail($email);
		$user->store();
		$error[] =  'Done';
	}
	catch (DataBaseException $exception) {
		$user->setUsername($un);
		$user->setPassword($pw);
		$user->setEmail($e);
		
		switch ($exception->getCode()) {
			case 1:
				$m = 'Your information is no longer unique.';
				break;
			case 2290:
				$m = "Your email is illegal.";
				break;
			default:
				$m = "Something bad happened.";
		}
		$error[] = $m;
	}
	catch (Exception $exception) {
		$user->setUsername($un);
		$user->setPassword($pw);
		$user->setEmail($e);
	
		$error[] =  $exception->getMessage();
	}
}
$username = $user->getUsername();
$email = $user->getEmail();
$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('views/templates/app-container.html');
$TBS->MergeField('username', $username);
$TBS->MergeField('email', $email);
$TBS->MergeBlock('messages', $error);
$TBS->Show();

?>
