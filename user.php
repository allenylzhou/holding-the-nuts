<?php

include 'error_reporting.php';
include_once 'tbs_class.php';
include 'user_class.php';
session_start();
 
$template = "views/templates/user-profile.html";
$error = array();

if (!isset($_SESSION['USER'])){
	header('Location: ./login.php?redirect=1');
}

$user = $_SESSION['USER'];
if (   !array_key_exists('username', $_POST) 
	&& !array_key_exists('password', $_POST)
	&& !array_key_exists('email', $_POST)) {
}
else {
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
		$user->save2(true);
		$error[] =  'Done';
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
