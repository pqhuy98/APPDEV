<?php
	//Track number of listenner.
	$IP = $_SERVER['REMOTE_ADDR'];
	$new_users = [];
	if (file_exists("ABCDEFXYZHelloUsers/users.txt")) {
		if (!file_exists("ABCDEFXYZHelloUsers/"))
			mkdir("ABCDEFXYZHelloUsers/",0777);
		$content = file_get_contents("ABCDEFXYZHelloUsers/users.txt");

		$users = explode("\n",$content);
		
		
		foreach($users as $u) {
			$a = explode(" ",$u);
			$a[1] = floatval($a[1]);
			//Last checkin : 5 seconds
			if (time()-$a[1]<5 && $a[0]!=$IP) {
				$new_users[] = $u;
			}
		}
	}
	$new_users[] = $IP." ".((string)time());
	
	if (!file_exists("ABCDEFXYZHelloUsers/"))
		mkdir("ABCDEFXYZHelloUsers/",0777);
	file_put_contents("ABCDEFXYZHelloUsers/users.txt",join("\n",$new_users));
	
	echo count($new_users);
?>