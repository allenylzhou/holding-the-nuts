<?php
	error_reporting(E_ALL);
	ini_set('display_errors', 'On');

	// Configure TBS
	date_default_timezone_set('America/Vancouver');

	// Configure session
	session_save_path('./tmp');

	// Include TBS
	require_once 'third-party/tbs_class.php';

	// Include models
	require_once 'models/backing_class.php';
	require_once 'models/user_class.php';
	require_once 'models/game_class.php';
	require_once 'models/location_class.php';
	require_once 'models/payment_class.php';
	require_once 'models/statistics_class.php';
	require_once 'models/user_class.php';

	// Show view
	session_start();
	$view = (isset($_GET['action'])) ? $_GET['action'] : 'login';
	include "$view.php";

?>
