<?php

include 'tbs_class.php';
include 'user_class.php';
include 'game_class.php';
include 'backing_class.php';
include 'location_class.php';

$template = 'views/templates/cash-sessions.html';

$guest = new User(array('userId'=>0), true);
//echo $guest->getUsername();

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
	//header('Location: ./register.php');
}



if (array_key_exists('create', $_POST)) {

	switch($_POST['gsType']) {
		case 'cash':
			$newGame = new CashGame(array('gsId' => 60), true);
			break;
		case 'tournament':
			$newGame = new TournamentGame;
			break;
		default:
			break;
	}

	
	$newGame->setAttributes(array(
		'userId' => 0,
		//'startDate' => $_POST['startDate'],
		//'endDate' => $_POST['endDate'],
		'amountIn' => $_POST['amountIn'],
		'amountOut' => $_POST['amountOut']
	));
	
	$newGame->save();
	
	//print("<pre>" . print_r($result, true) . "</pre>");
}

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

?>