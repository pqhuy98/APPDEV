<?php
	//Return data of $date at $time with $length in string format.
	include "MISC_functions.php";
	if (!isset($_GET["date"]) || !checkDateFormat($_GET["date"])) return; else $date = $_GET["date"];
	if (!isset($_GET["time"]) || !checkTimeFormat($_GET["time"])) return; else $time = intval($_GET["time"]);
	if (!isset($_GET["length"]) || empty($_GET["length"]))
		$length = 1;
	else $length = intval($_GET["length"]);
	$length = min ($length, $SECS_PER_DAY-$time, $WINDOW_SIZE);
	echo getSeconds($date, $time, $length);
?>