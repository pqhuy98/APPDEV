<?php
	include "MISC_functions.php";
	$filename = "logdata/".date("Y-M-d").".txt";
	$lines = read_last_lines($filename,35);
	echo $lines;
?>