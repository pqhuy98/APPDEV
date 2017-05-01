<?php
	//---------------------------------------------
	include "MISC_functions.php";
	if (!isset($_GET["date"]) ||
		empty($_GET["date"]) ||
		!checkDateFormat($_GET["date"]) ||
		!dateRecordExists($_GET["date"])
	)
		$date = "None";
	else
		$date = $_GET["date"];
	//---------------------------------------------
	if ($date=="None" ||
		!isset($_GET["time"]) ||
		empty($_GET["time"]) ||
		!checkTimeFormat($_GET["time"])
	) {
		$time = "None";
	}
	else
		$time = intval($_GET["time"]);
	//---------------------------------------------
?>


<!DOCTYPE HTML>
<html>
<head>
	<title>Live sound</title>
	<link rel="icon" href="icon.png">
	<link rel="stylesheet" href="css/style.css">
	<script src="script/functions.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<script src="script/canvasjs.min.js"></script>
	<script src="script/chart.js"></script>
</head>
<body>
	<h1 class="title">ARCHIVED SOUND</h1>
	<a href="./">Live stream.</a>
	<?php
		if ($date=="None") {
			echo "<h2 class='menu'>Past records</h2>";
			drawMenu(true, "menu", "menu");
		} else {
			if ($time=="None") {
				$time = findLeft($date,$SECS_PER_DAY-1);
				$time = max(0, $time-floor($WINDOW_SIZE/2));
			}
			echo "<script>var TIME = $time;var DATE = '$date';</script>";
			echo "<script>var VALUES_PER_SEC = $VALUES_PER_SEC;".
						 "var WINDOW_SIZE = $WINDOW_SIZE;".
						 "var SECS_PER_DAY = $SECS_PER_DAY;</script>";

			echo "<h2>Scroll to move around.</h2>";
			echo "<div id='archive_chart' style='height: 300px; width:80%; margin:0px auto''></div>";
			echo "<h2>$date</h2>";
			$h = floor($time/60/60);
			$m = floor($time/60)%60;
			$s = $time%60;
			echo "<input class='goto' type='number' id='h' min=0 max=23 value='$h'> : ".
				 "<input class='goto' type='number' id='m' min=0 max=59 value='$m'> : ".
				 "<input class='goto' type='number' id='s' min=0 max=59 value='$s'>".
				 "<input class='gotobtn' type='button' id='go' value='Go !'> ".
				 "<br><br><a href='archive.php?date=$date'>Now !</a>".
				 "<br><br><a href='archive.php'>Other records</a>";
		}
	?>


</body>
</html>