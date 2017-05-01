<?php
//Receive data from application and save it log files.
//Data format checking and security checking is not implemented yet.

require "MISC_functions.php";
if (isset($_POST["data"])) {
	//Save stream log
	$date = date("Y-M-d");
	$time = date("H:i:s");

	$file = "STREAM_lastest.log";
	$fp = fopen($file,"w");
	$unique = uniqid();
	$record = $time." ".$_POST["data"]." ".$unique;
	fwrite($fp,$record);
	fclose($fp);

	//Save log for streaming ==============================================
	makedir("data/",0755);
	$file = "data/$date.txt";
	if (!file_exists($file))
		$f = fopen($file,"w");
	else $f = fopen($file,"a");
	fwrite($f,$record."\n");
	fclose($f);

	if (rand(1,100)<=1) { //With probability 1%
		//Clean old contents, keep last $WINDOW_SIZE seconds.
		file_put_contents($file,read_last_lines($file,$WINDOW_SIZE)."\n");
	}

	//Save .wav files=======================================================
	makedir("wavdata/",0755);
	$content = file_get_contents($_FILES["file"]["tmp_name"]);
	file_put_contents("wavdata/data_$unique",$content);
	chmod("wavdata/data_$unique",0755);

	//Delete .wav files=====================================================
	if (rand(1,100)<=20) {	//With probability 20%
		$files = scandir("wavdata/");
		foreach($files as $f) {
			if ($f=="." || $f=="..") continue;
			$last_mod = filemtime("wavdata/$f");
			if (time()-$last_mod>20) //Delete last 20 seconds old .wav files
				unlink("wavdata/$f");
		}
	}

	//Save binary log =====================================================
	$floats = explode(' ',trim($_POST["data"]));
	$chars = "";
	foreach($floats as $f)
		$chars.=chr(intval(round($f)));
	putSeconds($date, hms_to_s($time), $chars);
	//Return to app =======================================================
	echo gethostname();
} else {
	echo "Hello, what are you doing here ?";
}
?>
