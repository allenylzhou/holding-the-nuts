<?php

$template = "views/templates/user-profile.html";

$error = array();
if (!isset($_SESSION['USER'])){
	header('Location: ./index.php?action=login');
}

$user = $_SESSION['USER'];
if (array_key_exists('password', $_POST)) {
	try {
		$hash = User::hash($_POST['password']);
		
		if($hash == $user->getPassword()){
			$user->delete();
			$_SESSION['USER'] = null;
		}
		else{
			$error[] = "That's not your password.";
		}
		if (empty($error)) {
			header('Location: ./index.php');
		}
	}
	catch (DataBaseException $exception) {		
		switch ($exception->getCode()) {
			case 2292:
				$error[]  = "Your have an existing backing.";
				break;
			default:
				$error[]  = "Something bad happened.";
		}
	}
	catch (Exception $exception) {	
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
