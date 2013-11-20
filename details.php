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

		header('Location: ./index.php?action=sessions');
	}

	if (array_key_exists('delete', $_POST)) {
		$game->delete();
		header('Location: ./index.php?action=sessions');
	}

	// Display stuff
	$details = $game->getAttributes();
	$locations = Location::loadLocationsByUserId($user->getUserId());
	$backing = $game->loadBacking();

	$TBS = new clsTinyButStrong;
	$TBS->LoadTemplate('views/templates/app-container.html');
	$TBS->MergeBlock('details', $details);
	$TBS->MergeBlock('locations', $locations);
	$TBS->Show();

} else {
	header('Location: ./index.php?action=login');
}

?>
