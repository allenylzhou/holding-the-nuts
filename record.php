<?php

$template = 'views/templates/session-create.html';

if (isset($_SESSION['USER'])) {
	$user = $_SESSION['USER'];

	if (array_key_exists('submit', $_POST)) {

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

		$backingAgreementId = $_POST['backingAgreementId'];
		if ($backingAgreementId >= 0) {
			$newBacking = new Backing(array('baId' => $backingAgreementId, 'gsId' => $newGame->getGsId()));
			$newBacking->save();
		}

		header('Location: ./index.php?action=sessions');
	}
	// Display the form
	$time = date('Y-m-d');
	$locations = Location::loadLocationsByUserId($user->getUserId());
	$backers = BackingAgreement::loadBackingAgreementsByHorseId($user->getUserId());

	$TBS = new clsTinyButStrong;
	$TBS->LoadTemplate('views/templates/app-container.html');
	$TBS->MergeBlock('locations', $locations);
	$TBS->MergeBlock('backers', $backers);
	$TBS->Show();

} else {
	header('Location: ./index.php?action=login');
}

?>
