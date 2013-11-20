<?php

$template = "views/templates/player-backings.html";

$error = array();
	
if (isset($_SESSION['USER'])) {
	$user = $_SESSION['USER'];
	
	if(isset($_POST['backerToAdd'])){
		try{
			$user->addBacker(User::findUserId($_POST['backerToAdd']));
			echo 'aaaa';
		}
		catch(DataBaseException $e){
			switch ($e->getCode()) {
			case 1:
				$m = 'You already have this backer added.';
				break;
			case 2290:
				$m = "I think you just tried to add yourself.";
				break;
			default:
				$m = "Something bad happened.";
			}
			$error[] = $m;
		}
		catch(Exception $e){
			$error[] = $e->getMessage();
		}
	}
	
	$backingAgreement = BackingAgreement::loadBackingAgreementsByHorseId($user->getUserId());
	$backers = BackingAgreement::loadBackersByHorseId($user->getUserId());
	$backings = BackingAgreement::loadBackingsByHorseId($user->getUserId());
	$sameBackers = $user->getUsersWithSameBackers();

	$backerList = array();
	foreach ($backers as $v) {
		$key = $v['BACKER_ID'];
		$val = $v['USERNAME'];
		$backerList[$key] = $val;
	}
	$TBS = new clsTinyButStrong;
	$TBS->LoadTemplate('views/templates/app-container.html');
	$TBS-> MergeBlock('backers', $backers);
	$TBS-> MergeBlock('backerList', $backerList);
	$TBS-> MergeBlock('backingAgreement', $backingAgreement);
	$TBS-> MergeBlock('backings', $backings);
	$TBS-> MergeBlock('sameBackers', $sameBackers);
	$TBS->MergeBlock('messages', $error);
	$TBS->Show();
	
	
} else {
	header('Location: ./index.php?action=login');
}


?>


