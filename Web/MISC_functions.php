<?php
	//Common functions.

	$SECS_PER_DAY = 24*60*60;
	$VALUES_PER_SEC = 8;
	$WINDOW_SIZE = 30;

	//Copied from stack overflow, used to read some last lines of a file.
	function read_last_lines($filepath, $lines = 1, $adaptive = true) {
		$f = @fopen($filepath, "rb");
		if ($f === false) return false;
		if (!$adaptive) $buffer = 4096;
		else $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));
		fseek($f, -1, SEEK_END);
		if (fread($f, 1) != "\n") $lines -= 1;
		$output = '';
		$chunk = '';
		while (ftell($f) > 0 && $lines >= 0) {
			$seek = min(ftell($f), $buffer);
			fseek($f, -$seek, SEEK_CUR);
			$output = ($chunk = fread($f, $seek)) . $output;
			fseek($f, -strlen($chunk), SEEK_CUR);
			$lines -= substr_count($chunk, "\n");
		}
		while ($lines++ < 0) {
			$output = substr($output, strpos($output, "\n") + 1);
		}
		fclose($f);
		return trim($output);
	}
	
	function makedir($path, $mod) {	
		//Example : makedir("dir1/dir2/dir3/",0777)
		mkdir( $path, $mod, true);

		//==== Old implementation : ====

		// $dir = explode("/",$path);
		// $cur = "";
		// foreach($dir as $d) {
		// 	if (empty($d)) continue;
		// 	$cur.=$d."/";
		// 	if (!file_exists($cur)) {
		// 		mkdir($cur,$mod);
		// 	}
		// }
	}
	
	function dateRecordExists($date) {
		return file_exists("logdata/".$date."/".$date.".bin");
	}

	//Overwrite to data to file at specific postition and keep other data unchanged.
	function _put($file, $data, $position) {
		$f = fopen($file,"rw+");
		fseek($f,$position,SEEK_SET);
		fwrite($f,$data);
		fclose($f);
	}

	//Return part of some file starting from specific byte and with specific length.
	function _get($file, $position, $length) {
		$f = fopen($file,"r+");
		fseek($f,$position,SEEK_SET);
		$res = fread($f,$length);
		return $res;
	}

	function create_empty_file($filepath, $size) {
		$f = fopen($filepath,"wb");
		fwrite($f,str_repeat("\0",$size));
		fclose($f);
	}

	function hms_to_s($time) {
		return strtotime("1970-01-01 $time UTC");
	}

	//Save string $data to log file of day $date, at moment $time.
	function putSeconds($date, $time, $data) {
		global $SECS_PER_DAY, $VALUES_PER_SEC, $WINDOW_SIZE;

		$dir = "logdata/".$date."/";
		makedir($dir,0775);
		$filepath = $dir.$date.".bin";
		if (!file_exists($filepath))
			create_empty_file($filepath,$SECS_PER_DAY*$VALUES_PER_SEC);
		_put($filepath,$data,$time*$VALUES_PER_SEC);
	}

	//Get a string contains data of $cnt seconds starting from $time.
	function getSeconds($date, $time, $count = 1) {
		global $SECS_PER_DAY, $VALUES_PER_SEC, $WINDOW_SIZE;

		$dir = "logdata/".$date."/";
		$cnt = min($count, $SECS_PER_DAY-$time);
		$chars = _get($dir.$date.".bin",$time*$VALUES_PER_SEC,$VALUES_PER_SEC*$cnt);
		return $chars;
	}

	//Check if $data is in data format.
	//e.g. "2017-May-13", "1998-Feb-06", ...
	function checkDateFormat($date) {
		$dt = DateTime::createFromFormat("Y-M-d", $date);
		return $dt !== false && !array_sum($dt->getLastErrors());
	}

	//Check if 0<= $time <= 24*60*60
	function checkTimeFormat($time) {
		global $SECS_PER_DAY;
		if (!is_numeric($time))
			return false;
		$t = intval($time);
		return 0<=$t && $t<$SECS_PER_DAY;
	}


	//Find the nearest available data to the left of some moment.
	//Return found time (in second), or False (not found).
	function findLeft($date, $time) {
		global $SECS_PER_DAY, $VALUES_PER_SEC, $WINDOW_SIZE;

		$time = min($SECS_PER_DAY-$WINDOW_SIZE,$time);
		while ($time>-$WINDOW_SIZE) {
			$time = max(0,$time);
			$data = getSeconds($date,$time,$WINDOW_SIZE);
			$n = strlen($data);
			for($i=$n-1;$i>=0;$i--) {
				if ($data[$i]!="\0") {
					return $time+floor($i/$VALUES_PER_SEC);
				}
			}
			$time-=$WINDOW_SIZE;
		}
		return FALSE;
	}

	//Same as findLeft, but now findRight.
	function findRight($date, $time) {
		global $SECS_PER_DAY, $VALUES_PER_SEC, $WINDOW_SIZE;

		while ($time<$SECS_PER_DAY) {
			$length = min($WINDOW_SIZE, $SECS_PER_DAY-$time);
			$data = getSeconds($date,$time,$length);
			$n = strlen($data);
			for($i=0;$i<$n;$i++) {
				if ($data[$i]!="\0") {
					return $time+floor($i/$VALUES_PER_SEC);
				}
			}
			$time+=$length;
		}
		return FALSE;
	}

	//Draw date menu.
	function drawMenu($center=True, $class="", $aclass="") {
		$files = scandir("logdata/");
		$cnt = 0;
		foreach($files as $f)
			if ($f!="." && $f!="..")
				$cnt+=1;
		$width = min($cnt,5);
		$printed = 0;
		if ($center)
			echo "<table class='$class' align=center>";
		else
			echo "<table class='$class'>";
		foreach($files as $f) {
			if ($f=="." || $f=="..") continue;
			if ($printed % $width == 0) echo "<tr class=$class>";
			echo "<td class='$class'>";
			$name = substr($f,0,11);
			echo "<a class='$aclass' href='archive.php?date=$name'>$name</a>";
			echo "</td>";
			if ($printed % $width == $width-1) echo "</tr>";
			$printed+=1;
		}
		echo "</table>";
	}
?>