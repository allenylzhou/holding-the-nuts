<?php

$template = 'views/templates/cash-sessions.html';

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
