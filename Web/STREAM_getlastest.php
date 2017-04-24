<?php
	$filename = "lastest.log";
	if (!file_exists($filename))
		echo "offline";
	else {
		$last_mod = filemtime("lastest.log");
		if (time()-$last_mod>2)
			echo "offline";
		else {
			echo file_get_contents("lastest.log");
		}
	}
?>