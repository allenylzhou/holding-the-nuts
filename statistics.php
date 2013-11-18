<?php

include 'error_reporting.php';
include_once 'tbs_class.php';

include 'game_class.php';
include 'user_class.php';
include 'statistics_class.php';

$template = "views/templates/player-statistics.html";

// TODO: remove this later
$guest = new User(array('userId'=>0), true);
$guest->login();

if (isset($_SESSION['USER'])) {

	$user = $_SESSION['USER'];

	$totalProfit = $user->getTotalProfit();
	
	$averageBuyin = Statistics::getAverageCashBuyIn($user->getUserId());
	$bestPerformingDay = Statistics::getBestPerformingDay($user->getUserId());

	$TBS = new clsTinyButStrong;
	$TBS->LoadTemplate('views/templates/app-container.html');
	$TBS->MergeBlock('bestPerformingDays', $bestPerformingDay);
	$TBS->Show();
} else {
	header('Location: ./login.php?redirect=1');
}

?>
