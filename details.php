<?php

$template = 'views/templates/session-details.html';

if (isset($_SESSION['USER'])) {
	$user = $_SESSION['USER'];
	$gameSessionId = $_GET['gsId'];
	$game = new CashGame(array('gsId'=>$gameSessionId), true);

	// Handle form
	if (array_key_exists('submit', $_POST)) {

		if(empty($_POST['locationName'])) {
			$locationName = $_POST['newLocationName'];
			$newLocation = new Location(array('userId' => $user->getUserId(), 'name' => $locationName));
			$newLocation->save();
		} else {
			$locationName = $_POST['locationName'];
		}	

		$game->setAttributes(array(
			'userId' => $user->getUserId(),
			'startDate' => $_POST['startDate'],
			'endDate' => $_POST['endDate'],
			'amountIn' => $_POST['amountIn'],
			'amountOut' => $_POST['amountOut'],
			'locationName' => $locationName
		));
		$game->save2(true);

		// if(!empty($_POST['newBackerName'])) {
		// 	//Insert or load new user
		// 	$newBacker = $user;


		// 	//Insert new backing agreement
		// 	$newBackingAgreement = new BackingAgreement;
		// 	$newBackingAgreement->setAttributes(array('horseId' => $user->getUserId(), 'backerId' => $newBacker->getUserId(), 'percentOfWin' => $_POST['newBackerPercentage']));
		// 	$newBackingAgreement->save();

		// 	$backingAgreementId = $newBackingAgreement->getBaId();

		// } else {
		// 	$backingAgreementId = $_POST['backingAgreementId'];
		// }
		
		// if ($backingAgreementId >= 0) {
		// 	$newBacking = new Backing(array('baId' => $backingAgreementId, 'gsId' => $newGame->getGsId()));
		// 	$newBacking->save();
		// }
		header('Location: ./sessions.php');
	}

	if (array_key_exists('delete', $_POST)) {
		$game->delete();
		header('Location: ./sessions.php');
	}

	// Display stuff
	$details = $game->getAttributes();
	$locations = Location::loadLocationsByUserId($user->getUserId());
	$backers = BackingAgreement::loadBackingAgreementsByHorseId($user->getUserId());

	$TBS = new clsTinyButStrong;
	$TBS->LoadTemplate('views/templates/app-container.html');
	$TBS->MergeBlock('details', $details);
	$TBS->MergeBlock('locations', $locations);
	$TBS->MergeBlock('backers', $backers);
	$TBS->Show();

} else {
	header('Location: ./login.php?redirect=1');
}

?>
