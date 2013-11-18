<?php

include 'error_reporting.php';
include_once 'tbs_class.php';
include 'backing_class.php';
include 'user_class.php';

$template = "views/templates/player-backings.html";

// TODO: remove this later
$guest = new User(array('userId'=>0), true);
$guest->login();

if (isset($_SESSION['USER'])) {

	$user = $_SESSION['USER'];
	
	if($_POST['username'] != '' 
		&& $_POST['flatfee'] != ''
		&& $_POST['pow'] != ''
		&& $_POST['pol'] != ''
		&& $_POST['oa'] != ''){
		addBackingAgreement($user->getUserId(), $_POST['username'], $_POST['flatfee'], $_POST['pow'], $_POST['pol'], $_POST['oa']);
	}
	
	$backingAgreement = BackingAgreement::loadBackingAgreementsByHorseId($user->getUserId());
	$backers = BackingAgreement::loadBackersByHorseId($user->getUserId());
	$backings = BackingAgreement::loadBackingsByHorseId($user->getUserId());

	
	$TBS = new clsTinyButStrong;
	$TBS->LoadTemplate('views/templates/app-container.html');
	$TBS-> MergeBlock('backers', $backers);
	$TBS-> MergeBlock('backingAgreement', $backingAgreement);
	$TBS-> MergeBlock('backings', $backings);
	$TBS->Show();
	
	
} else {
	header('Location: ./login.php?redirect=1');
}

function addBackingAgreement($horseId, $username, $flatFee, $pow, $pol, $oa){
	try {
		$backingAgreement = new BackingAgreement();
		
		if($oa == ''){
			$oa = null;
		}
		
		$backingAgreement->setAttributes(array(
			'horseId' => $horseId,
			'backerId' => User::findUserId($username),
			'flatFee' => $flatFee,
			'percentOfWin' => $pow,
			'percentOfLoss' => $pol,
			'overrideAmount' =>$oa
		));		
		
		$backingAgreement->save();
	}
	catch (DatabaseException $exception) {
		echo $exception->getMessage();
		switch ($exception->getErrorCode()) {
		/*	case 1:
				$error[] =  "This username has already been claimed, or the email has already been claimed";
				break;
			case 2290:
				$error[] =  "Your username is invalid, or your email is invalid";
				break;
*/			default:
				$error[] =  "An unknown error has occured";
				break;
		}
	}
	catch (Exception $exception) {
		$error[] = $exception->getMessage();
	}
}



?>


