<html>
<body>
<h1>Welcome to 304!</h1>

<h2>Function 1: Show Game History by User ID</h2>

<form method="POST" action="index.php">
	<input type="submit" value="See Cash Games" name="cash">
	<input type="submit" value="See Tournament Games" name="tournament">
	<input type="hidden" name="userId" value="0"><br/>
</form>

<h2>Function 2: Create Game Session</h2>
<form method="POST" action="index.php">
	<label>GS_TYPE:</label>
	<select name="gsType">
		<option value="cash">Cash</option>
		<option value="tournament">Tournament</option>
	</select><br/>

	<label>START_TIME:</label>
	<input type="date" name="startDate"><br/>

	<label>END_TIME:</label>
	<input type="date" name="endDate"><br/>

	<label>AMOUNT_IN:</label>
	<input type="text" name="amountIn" value="0" size="1"><br/>

	<label>AMOUNT_OUT:</label>
	<input type="text" name="amountOut" value="0" size="1"><br/>

	<input type="submit" value="Create" name="create">
</form>

<?php

include 'tbs_class.php';
include 'game_class.php';

	if (array_key_exists('cash', $_POST) || array_key_exists('tournament', $_POST)) {

		$gameType = array('cash' => isset($_POST['cash']), 'tournament' => isset($_POST['tournament']));

		if($gameType['cash']) {
			$gameData = CashGame::loadSavedGames($_POST['userId']);
		} else {
			$gameData = TournamentGame::loadSavedGames($_POST['userId']);
		}

		$TBS = new clsTinyButStrong;
		$TBS->LoadTemplate('game-history.html');
		$TBS->MergeBlock('gameData', $gameData);
		$TBS->Show();
	}

	if (array_key_exists('create', $_POST)) {

		switch($_POST['gsType']) {
			case 'cash':
				$newGame = new CashGame;
				break;
			case 'tournament':
				$newGame = new TournamentGame;
				break;
			default:
				break;
		}

		$newGame->setProperties(array(
			'userId' => 0,
			// 'startDate' => $_POST['startDate'],
			// 'endDate' => $_POST['endDate'],
			'amountIn' => $_POST['amountIn'],
			'amountOut' => $_POST['amountOut']
		));

		$newGame->save();
	}

?>
	
</body>
</html>