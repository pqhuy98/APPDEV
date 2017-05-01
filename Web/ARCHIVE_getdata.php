<?php
	/*
		(Updated) CURRENTLY THIS FILE IS NOT USE.

		Echo log data on date $_GET["date"], from time $_GET["from"] to time $_GET["to"].
		If $_GET["from"] is not provided, it's supposed to be the file's begin.
		If $_GET["to"] is not provided, it's supposed to be the file's end.
	*/

	//Checking format ...
	if (!isset($_GET["date"])) {
		echo "Date missing.";
		return;
	}
	$date = $_GET["date"];
	if (DateTime::createFromFormat('Y-M-d',$date) === FALSE) {
		echo "Date is wrong format.";
		return;
	}
	if (!file_exists("logdata/".$date.".txt")) {
		echo "No record on this date.";
		return;
	}

	//Searching...
	$content = file_get_contents("logdata/".$date.".txt");

	//Search start...
	if (!isset($_GET["from"]) || empty($_GET["from"]))
		$start = 0;
	else {
		$from = $_GET["from"];
		$start = strpos($content,$from);
		if ($start===FALSE) {
			echo "Start not found";
			return;
		}
	}
	//Search end...
	if (!isset($_GET["to"]) || empty($_GET["to"])) {
		$end = strlen($content);
	}
	else {
		$to = $_GET["to"];
		$pos = strpos($content,$to);
		if ($pos===FALSE) {
			echo "End not found.";
			return;
		}
		$extra = strpos(substr($content,$pos),"\n");
		if ($extra===FALSE)
			$end = strlen($content);
		else
			$end = $pos+$extra+1;
	}
	//Return...
	echo substr($content,$start,$end-$start);
?>