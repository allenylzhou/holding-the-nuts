<?php

include "error_reporting.php";
include 'tbs_class.php';

include 'user_class.php';
include 'game_class.php';
include 'backing_class.php';
include 'location_class.php';

$template = 'views/templates/new-session.html';

session_start();
if (isset($_SESSION['USER'])) {
	$user = $_SESSION['USER'];

	if (array_key_exists('submit', $_POST)) {

		echo "inserting";

		if(empty($_POST['locationName'])) {
			$locationName = $_POST['newLocationName'];
			$newLocation = new Location(array('userId' => $user->getUserId(), 'name' => $locationName));
			$newLocation->save();
		} else {
			$locationName = $_POST['locationName'];
		}	

		$newGame = new CashGame;
		$newGame->setAttributes(array(
			'userId' => $user->getUserId(),
			'startDate' => $_POST['startDate'],
			'endDate' => $_POST['endDate'],
			'amountIn' => $_POST['amountIn'],
			'amountOut' => $_POST['amountOut'],
			'locationName' => $locationName
		));
		$newGame->save();

		if(!empty($_POST['newBackerName'])) {
			//Insert or load new user
			$newBacker = $user;


			//Insert new backing agreement
			$newBackingAgreement = new BackingAgreement;
			$newBackingAgreement->setAttributes(array('horseId' => $user->getUserId(), 'backerId' => $newBacker->getUserId(), 'percentOfWin' => $_POST['newBackerPercentage']));
			$newBackingAgreement->save();

			$backingAgreementId = $newBackingAgreement->getBaId();

		} else {
			$backingAgreementId = $_POST['backingAgreementId'];
		}
		
		if ($backingAgreementId >= 0) {
			$newBacking = new Backing(array('baId' => $backingAgreementId, 'gsId' => $newGame->getGsId()));
			$newBacking->save();
		}
	}

	/*
	if (array_key_exists('create_backing', $_POST)) {
		$newBacking = new Location(array('userId'=>0, 'name'=>'Edgewater'), true);

		$newBacking->setAttributes(array('favourite' => 100));

		//$newBacking->setAttributes(array(
			// 'horseId' => $_POST['horse'],
			// 'backerId' => $_POST['backer'],
			// 'flatFee' => 0,
			// 'percentOfWin' => $_POST['percentage'],
			// 'percentOfLoss' => $_POST['percentage'],
			// 'overrideAmount' => 0
			// ));
		$newBacking->save();	
	}
	*/

	// Display the form
	$locations = Location::loadLocationsByUserId($user->getUserId());
	$backers = BackingAgreement::loadBackersByHorseId($user->getUserId());
	//print("<pre>".print_r($backers, true)."</pre>");

	$TBS = new clsTinyButStrong;
	$TBS->LoadTemplate('views/templates/app-container.html');
	$TBS->MergeBlock('locations', $locations);
	$TBS->MergeBlock('backers', $backers);
	$TBS->Show();

} else {
	header('Location: ./login.php?redirect=1');
}

?>
