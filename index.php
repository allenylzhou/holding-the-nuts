<html>
<body>
<h1>Welcome to 304!</h1>



<h2>Function 1: Show Game History by User ID</h2>

<form method="POST" action="index.php">
	<input type="submit" value="See Cash Games" name="cash">
	<input type="submit" value="See Tournament Games" name="tournament">
	<input type="submit" value="See Backing Agreements" name="backingagreement">
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

<h2> Function 3: Create a Backing</h2>
<form method="POST" action="index.php">
	<label>HORSE ID: </label>
	<input type="text" name="horse" value="" size="18"><br/>
	<label>BACKER ID: </label>
	<input type="text" name="backer" value="" size="18"><br/>
	<label>BACKING_PERCENTAGE: </label>
	<input type="text" name="percentage" value="" size="5"><br/>
	
	<input type="submit" value="Create Backing" name="create_backing">
</form>

<?php

include 'tbs_class.php';
include 'game_class.php';
include 'backing_class.php';
include 'location_class.php';
/*
	if (array_key_exists('cash', $_POST) || array_key_exists('tournament', $_POST)
						|| array_key_exists('backingagreement', $_POST)){

		$gameType = array('cash' => isset($_POST['cash']), 
				'tournament' => isset($_POST['tournament']),
				'backingagreement' => isset($_POST['backingagreement']));
				

		if($gameType['cash']) {
			$gameData = CashGame::loadSavedGames($_POST['userId']);
		} else {
			if($gameType['tournament']{
				$gameData = TournamentGame::loadSavedGames($_POST['userId']);
			} else {
				$gameData = Backing_class::loadSavedBackings($_POST['userId']);
			}
		}

		$TBS = new clsTinyButStrong;
		$TBS->LoadTemplate('game-history.html');
		$TBS->MergeBlock('gameData', $gameData);
		$TBS->Show();
		
		
	}
	
*/
		if (array_key_exists('cash', $_POST) || array_key_exists('tournament', $_POST)){

		$gameType = array('cash' => isset($_POST['cash']), 
				'tournament' => isset($_POST['tournament']));
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
				$newGame = new CashGame(array('gsId' => 60), true);
				break;
			case 'tournament':
				$newGame = new TournamentGame;
				break;
			default:
				break;
		}

		/*
		$newGame->setProperties(array(
			'userId' => 0,
			//'startDate' => $_POST['startDate'],
			//'endDate' => $_POST['endDate'],
			'amountIn' => $_POST['amountIn'],
			'amountOut' => $_POST['amountOut']
		));
		*/

		$result = $newGame->getAverageBuyOut(0);
		echo 'AVG BUY OUT: ' . $result;
		
		
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

	//$oldGame = new CashGame(0);
	//$result = $oldGame->getAverageBuyOut();
	//print("<pre>" . print_r($result, true) . "</pre>");
	//print("<pre>" . print_r($oldGame->getProperties(), true) . "</pre>");

?>
	
</body>
</html>
