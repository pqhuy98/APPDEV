<?php

	$SECS_PER_DAY = 24*60*60;
	$VALUES_PER_SEC = 10;
	$WINDOW_SIZE = 60;

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
	
	function makedir($path, $chmod) {	//Example : makedir("dir1/dir2/dir3/",0777)
		$dir = explode("/",$path);
		$cur = "";
		foreach($dir as $d) {
			if (empty($d)) continue;
			$cur.=$d."/";
			if (!file_exist($cur)) {
				mkdir($cur,$chmod);
			}
		}
	}
	
	function dateRecordExists($date) {
		return file_exists("logdata/".$date."/".$date.".bin");
	}

	function _put($file, $data, $position) {
		$f = fopen($file,"rw+");
		fseek($f,$position,SEEK_SET);
		fwrite($f,$data);
		fclose($f);
	}

	function _get($file, $position, $length) {
		$f = fopen($file,"rw+");
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

	function putSeconds($date, $time, $data) {
		$dir = "logdata/".$date."/";
		makedir($dir,0775);
		$filepath = $dir.$date.".bin";
		if (!file_exists($filepath))
			create_empty_file($filepath,SECS_PER_DAY*VALUES_PER_SEC);
		
		$t = hms_to_s($time);	//$t : $time in seconds, eg. 01:02:03 -> 1*60*60 + 2*60 + 3 = 3663
		_put($filepath,$data,$t);
	}

	function getSeconds($date, $time, $count = 60) {
		$dir = "logdata/".$date."/";
		$t = hms_to_s($time);
		$chars = _get($dir.$date.".bin",$t,$VALUES_PER_SEC*$count);
		return $chars;
	}

	function checkDateFormat($date) {
		$dt = DateTime::createFromFormat("Y-M-d", $date);
		return $dt !== false && !array_sum($dt->getLastErrors());
	}

	function checkTimeFormat($time) {
		if (!is_numeric($time))
			return false;
		$t = intval($time);
		return 0<=$t && $t<$SECS_PER_DAY;
	}

	function findRight($date, $time) {
		while ($time<$SECS_PER_DAY) {
			$length = min($WINDOW_SIZE, $SECS_PER_DAY-$time);
			$data = getSeconds($date,$time,$length);
			if ($date !== str_repeat("\0",$length))
				return $time;
			$time+=$length;
		}
		return FALSE;
	}

	function findLeft($date, $time) {
		$time = min($SECS_PER_DAY-$WINDOW_SIZE,$time);
		while ($time>-$WINDOW_SIZE) {
			$time = max(0,$time);
			$data = getSeconds($date,$time,$WINDOW_SIZE);
			if ($data !== str_repeat("\0",$WINDOW_SIZE))
				return $time;
			$time-=$WINDOW_SIZE;
		}
		return FALSE;

	}

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