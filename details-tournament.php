<?php

$template = 'views/templates/session-tournament-details.html';

if (isset($_SESSION['USER'])) {
	$user = $_SESSION['USER'];
	$gameSessionId = $_GET['gsId'];
	$game = new TournamentGame(array('gsId'=>$gameSessionId), true);
	$error = array();

	// Handle form
	if (array_key_exists('submit', $_POST)) {

		if(!empty($_POST['locationName'])){
			$locationName = $_POST['locationName'];
		}
		else if(array_key_exists('newLocationName', $_POST) &&
			($_POST['locationName'] == null  ||$_POST['locationName'] == '' )&&
			$_POST['newLocationName'] != '') {
			$locationName = $_POST['newLocationName'];
			$newLocation = new Location(array('userId' => $user->getUserId(), 'name' => $locationName));
			$newLocation->save2(true);
		} else {
			$locationName = null;
		}	
		
		$startDate = (!empty($_POST['startDate'])) ? date('Y-m-d H:i:s', strtotime($_POST['startDate'])) : "";
		$endDate = (!empty($_POST['endDate'])) ? date('Y-m-d H:i:s', strtotime($_POST['endDate'])) : "";
		$game->setAttributes(array(
			'userId' => $user->getUserId(),
			'startDate' => $startDate,
			'endDate' => $endDate,
			'amountIn' => $_POST['amountIn'],
			'amountOut' => $_POST['amountOut'],
			'locationName' => $locationName,
			'placedFinished' => $_POST['placedFinished']
			
		));
		try{
			$game->save2(true);
		}
		catch (DatabaseException $exception) {
			switch ($exception->getErrorCode()) {
				case 2290:
					$error[] = "Your inputs were invalid";
				break;
				default:
					$error[] =  "An unknown error has occured";
				break;
			}
		}
		catch (Exception $exception) {
			$error[] = $exception->getMessage();
		}
		
		if (empty($error)) {
			header('Location: ./index.php?action=sessions');
		}
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
	$TBS->MergeBlock('messages', $error);
	$TBS->Show();

} else {
	header('Location: ./index.php?action=login');
}

