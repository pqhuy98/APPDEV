<?php
	include "MISC_functions.php";
	$filename = "data/".date("Y-M-d").".txt";
	$lines = read_last_lines($filename,$WINDOW_SIZE);
	echo $lines;
?>