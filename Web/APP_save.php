<?php
include "MISC_functions.php";
include "ARCHIVE_functions.php";
if (isset($_POST["data"])) {
	$time = date("H:i:s");

	$file = "lastest.log";
	$fp = fopen($file,"w");
	$unique = uniqid();
	$record = $time." ".$_POST["data"]." ".$unique;
	fwrite($fp,$record);
	fclose($fp);

	//Save wav ============================================================
	makedir("wavdata/",0775);
	$content = file_get_contents($_FILES["file"]["tmp_name"]);
	file_put_contents("wavdata/data_$unique",$content);
	chmod("wavdata/data_$unique.wav",0777);

	//Delete wave =========================================================
	if (rand(1,10)<5) {
		$files = scandir("wavdata/");
		foreach($files as $f) {
			if ($f=="." || $f=="..") continue;
			$last_mod = filemtime("wavdata/$f");
			if (time()-$last_mod>20)
				unlink("wavdata/$f");
		}
	}

	//Save binary log =====================================================
	$date = date("Y-M-d");
	
	$floats = split(" ",strim($record));
	$chars = "";
	foreach($floats as $f)
		$chars.=chr(intval(round($f)));
	putSecond($date, $time, $chars);

	//Return to app =======================================================
	echo gethostname();
} else {
	echo "Hello, what are you doing here ?";
}
?>
