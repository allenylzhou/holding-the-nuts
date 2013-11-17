<?php

include 'error_reporting.php';
include 'tbs_class.php';

include 'user_class.php';

$template = "views/templates/player-backings.html";

// TODO: remove this later
$guest = new User(array('userId'=>0), true);
$guest->login();

if (isset($_SESSION['USER'])) {
	$TBS = new clsTinyButStrong;
	$TBS->LoadTemplate('views/templates/app-container.html');
	$TBS->Show();
} else {
	header('Location: ./login.php?redirect=1');
}

?>