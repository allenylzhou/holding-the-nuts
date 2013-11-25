<?php

$template = 'views/templates/cash-sessions.html';

if (isset($_SESSION['USER'])) {

	$user = $_SESSION['USER'];

	//Show active games
	//$activeCGames = CashGame::loadActiveGames($user->getUserId());

	//Show finished games
	$finishedCGames = CashGame::loadFinishedGames($user->getUserId());
	$finishedTGames = TournamentGame::loadFinishedGames($user->getUserId());
	

	$TBS = new clsTinyButStrong;
	$TBS->LoadTemplate('views/templates/app-container.html');
	//$TBS->MergeBlock('activeCGames', $activeCGames);
	$TBS->MergeBlock('finishedCGames', $finishedCGames);
	$TBS->MergeBlock('finishedTGames', $finishedTGames);
	$TBS->Show();
	

} else {
	header('Location: ./index.php?action=login');
}

?>
