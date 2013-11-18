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
	$totalHoursPlayed = Statistics::getTotalHoursPlayed($user->getUserId());
	$totalMinutesPlayed = Statistics::getTotalMinutesPlayed($user->getUserId());
	
	$tempHours = array_values($totalHoursPlayed[0]);
	$tempMinutes = array_values($totalMinutesPlayed[0]);
	
	$totalHours = ($tempHours[0] * 60 + $tempMinutes[0]) / 60;
	$totalHours = number_format((float)$totalHours, 2, '.', '');
	
	
	$hourly = $totalProfit / $totalHours;
	$hourly = number_format((float)$hourly, 2, '.', '');
	
	$averageBuyin = Statistics::getAverageCashBuyIn($user->getUserId());
	$averageBuyin = number_format((float)$averageBuyin, 2, '.', '');
	$bestPerformingDay = Statistics::getBestPerformingDay($user->getUserId());
	$profitByMonths = Statistics::getProfitByMonth($user->getUserId());
	$profitByDay = Statistics::getProfitByDayOfWeek($user->getUserId());


	$TBS = new clsTinyButStrong;
	$TBS->LoadTemplate('views/templates/app-container.html');
	$TBS->MergeBlock('totalHours', $totalHoursPlayed);
	$TBS->MergeBlock('bestPerformingDays', $bestPerformingDay);
	$TBS->MergeBlock('profitByMonth', $profitByMonths );
	$TBS->MergeBlock('profitByDOW',$profitByDay);
	$TBS->Show();
} else {
	header('Location: ./login.php?redirect=1');
}

?>
