<?php

include 'tbs_class.php';
include 'user_class.php';
include 'backing_class.php';
include 'location_class.php';

$guest = new User(array('userId'=>0), true);
//echo $guest->getUsername();

$guest->login();

if (isset($_SESSION['USER'])) {
	$user = $_SESSION['USER'];

	$locations = Location::loadLocationsByUserId($user->getUserId());
	$backers = BackingAgreement::loadBackersByHorseId($user->getUserId());

	//print("<pre>".print_r($backers, true)."</pre>");

	$TBS = new clsTinyButStrong;
	$TBS->LoadTemplate('views/templates/new-session.html');
	$TBS->MergeBlock('locations', $locations);
	$TBS->MergeBlock('backers', $backers);
	$TBS->Show();

} else {
	//header('Location: ./register.php');
}

?>