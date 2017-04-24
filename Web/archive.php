<?php
	include "MISC_functions.php";
	if (!isset($_GET["date"])||empty($_GET["date"]) || !checkDateFormat($date))
		$date = "None";
	else
		$date = $_GET["date"];
	if ($date=="None" || !isset($_GET["time"]) || empty($_GET["time"]))
		$time = "None";
	else
		$time = intval($_GET["time"]);
	$menu = ($date=="None");
?>


<!DOCTYPE HTML>
<html>
<head>
	<title>Live sound</title>
	<link rel="icon" href="icon.png">
	<link rel="stylesheet" href="css/style.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<script src="script/canvasjs.min.js"></script>
	<script src="script/chart.js"></script>
</head>
<body>
	<h1 class="title">ARCHIVE SOUND</h1>
	<a href="https://bunnies.hopto.org">Come to our homepage.</a>
	<div id="archive_chart" style="height: 300px; width:100%;">
	<?php
		if ($menu) {
			echo "<h2 class='menu'>Menu</h2>";
			drawMenu();
		} else {
			
		}
	?>


</body>
</html>