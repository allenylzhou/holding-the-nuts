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
	
	$backingAgreement = BackingAgreement::loadBackersByHorseId($user->getUserId());
	//var_dump($backingAgreement);
	$backers = array();
	foreach ($backingAgreement as $value) {
		$backers[] = array('name' => BackingAgreement::getUsername($value['BACKER_ID']));
	}
	
	//var_dump($backers);
	
	$TBS = new clsTinyButStrong;
	$TBS->LoadTemplate('views/templates/app-container.html');
	$TBS-> MergeBlock('backers', $backers);
	$TBS-> MergeBlock('backingAgreement', $backingAgreement);
	$TBS->Show();
	
	
} else {
	header('Location: ./login.php?redirect=1');
}



?>


