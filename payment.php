<?php

include 'error_reporting.php';
include_once 'tbs_class.php';
include 'user_class.php';
include 'payment_class.php';
session_start();
 
$template = "views/templates/payment.html";
$error = array();

if (!isset($_SESSION['USER'])){
	header('Location: ./login.php?redirect=1');
}

if (   !array_key_exists('username', $_POST) 
	|| !array_key_exists('amount', $_POST)) {
}
else if ($_POST['amount']<=0){
	$error[] = 'Invalid amount';
}
else {
	try {
		$user = $_SESSION['USER'];
		$userId = $user->getUserId();
		
		$username = $_POST['username'];
		$amount = $_POST['amount'];
		$date = $_POST['date'];
		
		echo $date;
		if($date == null || $date == ''){
			$date = date ('Y-m-d');
		}
		
		$payment = new Payment;
		$payment->setAttributes(array(
			'payerId' => $userId,
			'payeeId' => User::findUserId($username),
			'paymentDate' => $date,
			'amount' => $amount
		));
		$payment->save();
	}
	catch (Exception $exception) {
		$error[] =  $exception->getMessage();
	}
}

$user = $_SESSION['USER'];
$to = Payment::getPaymentsTo($user->getUserId());
$from = Payment::getPaymentsFrom($user->getUserId());

$TBS = new clsTinyButStrong;
$TBS->LoadTemplate('views/templates/app-container.html');
$TBS-> MergeBlock('to', $to);
$TBS-> MergeBlock('from', $from);
$TBS->MergeBlock('messages', $error);
$TBS->Show();

?>
