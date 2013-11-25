<?php

$template = 'views/templates/session-create.html';

if (isset($_SESSION['USER'])) {
	$user = $_SESSION['USER'];
	$error = array();

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

		$newGame = new CashGame;
		$startDate = (!empty($_POST['startDate'])) ? date('Y-m-d H:i:s', strtotime($_POST['startDate'])) : "";
		$endDate = (!empty($_POST['endDate'])) ? date('Y-m-d H:i:s', strtotime($_POST['endDate'])) : "";
		$newGame->setAttributes(array(
			'userId' => $user->getUserId(),
			'startDate' => $startDate,
			'endDate' => $endDate,
			'amountIn' => $_POST['amountIn'],
			'amountOut' => $_POST['amountOut'],
			'bigBlind' => $_POST['bigBlind'],
			'smallBlind' => $_POST['smallBlind'],
			'locationName' => $locationName
		));
		
		try{
			$newGame->save();
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

		$backingAgreementId = $_POST['backingAgreementId'];
		if ($backingAgreementId >= 0) {
			$newBacking = new Backing(array('baId' => $backingAgreementId, 'gsId' => $newGame->getGsId()));
			try{
				$newBacking->save();
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
		}

		// TODO: add error messaging
		if (empty($error)) {
			header('Location: ./index.php?action=sessions');
		}
	}
	// Display the form
	$time = date('Y-m-d H:i:s');
	$locations = Location::loadLocationsByUserId($user->getUserId());
	$backers = BackingAgreement::loadBackingAgreementsByHorseId($user->getUserId());

	$TBS = new clsTinyButStrong;
	$TBS->LoadTemplate('views/templates/app-container.html');
	$TBS->MergeBlock('locations', $locations);
	$TBS->MergeBlock('messages', $error);
	$TBS->MergeBlock('backers', $backers);
	$TBS->Show();

} else {
	header('Location: ./index.php?action=login');
}

?>
