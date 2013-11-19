<?php

include 'error_reporting.php';
include_once 'tbs_class.php';
include 'backing_class.php';
include 'user_class.php';

session_start();

$template = "views/templates/player-backings.html";


$error = array();
	
if (isset($_SESSION['USER'])) {

	$user = $_SESSION['USER'];
	
	if($_POST['username'] != '' 
		&& $_POST['flatfee'] != ''
		&& $_POST['pow'] != ''
		&& $_POST['pol'] != ''
		&& $_POST['oa'] != ''){
		try{
			addBackingAgreement($user->getUserId(), $_POST['username'], $_POST['flatfee'], $_POST['pow'], $_POST['pol'], $_POST['oa']);
		}
		catch(DataBaseException $e){
			$error[] = $e->getMessage();
		}
	}
	
	$backingAgreement = BackingAgreement::loadBackingAgreementsByHorseId($user->getUserId());
	$backers = BackingAgreement::loadBackersByHorseId($user->getUserId());
	$backings = BackingAgreement::loadBackingsByHorseId($user->getUserId());
	$sameBackers = $user->getUsersWithSameBackers();

	
	$TBS = new clsTinyButStrong;
	$TBS->LoadTemplate('views/templates/app-container.html');
	$TBS-> MergeBlock('backers', $backers);
	$TBS-> MergeBlock('backingAgreement', $backingAgreement);
	$TBS-> MergeBlock('backings', $backings);
	$TBS-> MergeBlock('sameBackers', $sameBackers);
	$TBS->MergeBlock('messages', $error);
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
	catch (Exception $exception) {
		throw $exception;
	}
}



?>


