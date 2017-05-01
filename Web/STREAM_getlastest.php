<?php
	$filename = "STREAM_lastest.log";
	if (!file_exists($filename))
		echo "offline";
	else {
		$last_mod = filemtime($filename);
		if (time()-$last_mod>2)
			echo "offline";
		else {
			echo file_get_contents($filename);
		}
	}
?>