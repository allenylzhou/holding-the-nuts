<html>
<body>
<h1>Welcome to 304!</h1>

<h2>Function 1: Show Game History by User ID</h2>

<form method="POST" action="index.php">
	<label>User_Id:</label>
	<input type="text" name="userId" value="0" size="1">
	<input type="submit" value="Cash Games" name="cash">
	<input type="submit" value="Tournament Games" name="tournament">
</form>

<h2>Function 2: Create Game Session</h2>
...

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
		$TBS->LoadTemplate('game-history.htm');
		$TBS->MergeBlock('gameData', $gameData);
		$TBS->Show();
	}

?>
	
</body>
</html>