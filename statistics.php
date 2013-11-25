<?php

$template = "views/templates/player-statistics.html";
$bestWorst = 'Best';
if (isset($_SESSION['USER'])) {

        $user = $_SESSION['USER'];

        $totalProfit = $user->getTotalProfit();
        $totalHoursPlayed = Statistics::getTotalHoursPlayed($user->getUserId());
        $totalMinutesPlayed = Statistics::getTotalMinutesPlayed($user->getUserId());
        
        $tempHours = array_values($totalHoursPlayed[0]);
        $tempMinutes = array_values($totalMinutesPlayed[0]);
        
        $totalHours = ($tempHours[0] * 60 + $tempMinutes[0]) / 60;
        $totalHours = number_format((float)$totalHours, 2, '.', '');
        //echo "total hours is ". $totalHours;

        if($totalHours == 0){
                $hourly = 0;
        }else
        {
                $hourly = $totalProfit / $totalHours;
                $hourly = number_format((float)$hourly, 2, '.', '');
        }
        
        $averageBuyin = Statistics::getAverageCashBuyIn($user->getUserId());
        $averageBuyin = number_format((float)$averageBuyin, 2, '.', '');
        $profitByMonths = Statistics::getProfitByMonth($user->getUserId());
        $profitByDay = Statistics::getProfitByDayOfWeek($user->getUserId());

        $bestWorst = 'Best';
        $getMin = false;
        if(isset($_POST['bestWorst']) && $_POST['bestWorst'] == 'worst'){
                $getMin = true;
                $bestWorst = 'Worst';
        }
        $bestWorstPerformingDays = Statistics::getPerformingDays($user->getUserId(), $getMin);

        $TBS = new clsTinyButStrong;
        $TBS->LoadTemplate('views/templates/app-container.html');
        $TBS->MergeField('bestWorst', $bestWorst);
        $TBS->MergeBlock('totalHours', $totalHoursPlayed);
        $TBS->MergeBlock('bestWorstPerformingDays', $bestWorstPerformingDays);
        $TBS->MergeBlock('profitByMonth', $profitByMonths );
        $TBS->MergeBlock('profitByDOW',$profitByDay);
        $TBS->Show();
} else {
        header('Location: ./index.php?action=login');
}

?>