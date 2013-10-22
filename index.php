<html>
<body>
<h1>It works!</h1>

<form method="POST" action="index.php">
	<p>
		<label>Time In</label>
		<input type="text" name="timeIn" size="6">
	</p>
	<p>
		<label>Buy In</label>
		<input type="text" name="buyIn" size="6">
	</p>
	<p>
		<label>Time Out</label>
		<input type="text" name="timeOut" size="6">
	</p>
	<p>
		<label>Buy Out</label>
		<input type="text" name="buyOut" size="6">
	</p>
	<p><input type="submit" value="Record" name="record"></p>
</form>

<?php

	if (array_key_exists('record', $_POST)) {
		echo "Recorded!";
	}
?>
	
</body>
</html>