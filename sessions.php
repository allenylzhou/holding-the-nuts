<?php

include 'error_reporting.php';
include 'tbs_class.php';

include 'user_class.php';
include 'game_class.php';
include 'backing_class.php';
include 'location_class.php';

$template = 'views/templates/cash-sessions.html';

// TODO: remove this later
$guest = new User(array('userId'=>0), true);
$guest->login();

if (isset($_SESSION['USER'])) {
	$user = $_SESSION['USER'];

	//Show active games
	$activeGames = CashGame::loadActiveGames($user->getUserId());

	//Show finished games
	$finishedGames = CashGame::loadFinishedGames($user->getUserId());

	$TBS = new clsTinyButStrong;
	$TBS->LoadTemplate('views/templates/app-container.html');
	$TBS->MergeBlock('activeGames', $activeGames);
	$TBS->MergeBlock('finishedGames', $finishedGames);
	$TBS->Show();

} else {
	header('Location: ./login.php?redirect=1');
}

?>
