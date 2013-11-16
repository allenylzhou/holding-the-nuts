<?php

include 'error_reporting.php';
include 'tbs_class.php';

include 'user_class.php';

$template = "views/templates/player-backings.html";

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('views/templates/app-container.html');
$TBS->Show();

?>