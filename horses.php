<?php

$template = "views/templates/player-horses.html";

$error = array();
	
if (isset($_SESSION['USER'])) {

	$user = $_SESSION['USER'];
	
	$getEmailInstead = false;
	$usernameEmail = 'Username';
	if(isset($_POST['usernameEmail']) && $_POST['usernameEmail'] == 'email'){
		$getEmailInstead = true;
		$usernameEmail = 'Email';
	}
	$owesAtLeast = 0;
	if(isset($_POST['owesAtLeast'])){
		$owesAtLeast = $_POST['owesAtLeast'];
	}
	$horses = BackingAgreement::loadHorsesByBackerId($user->getUserId(), $getEmailInstead, $owesAtLeast);

	$TBS = new clsTinyButStrong;
	$TBS->LoadTemplate('views/templates/app-container.html');
	$TBS->MergeBlock('horses', $horses);
	$TBS->MergeField('usernameEmail', $usernameEmail);
	$TBS->MergeBlock('messages', $error);
	$TBS->Show();
	
	
} else {
	header('Location: ./login.php?redirect=1');
}

?>


