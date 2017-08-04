<?php
	$user_config = fopen('./user_config.ini','r');

	if(!$user_config) {
		echo 'cannot read config file : ' . $user_config ;
	}

	while(!feof($user_config)) {
		$each_line = fgets($user_config);
		if(strpos($each_line, 'server') !== false && strpos($each_line, 'server') < strpos($each_line, "=")) {
			$db_server = trim(substr($each_line,strpos($each_line,'=')+1));
		}
		else if(strpos($each_line, 'user') !== false && strpos($each_line, 'user') < strpos($each_line, "=")) {
			$db_user = trim(substr($each_line,strpos($each_line,'=')+1));
		}
		else if(strpos($each_line, 'password') !== false && strpos($each_line, 'password') < strpos($each_line, "=")) {
			$db_password = trim(substr($each_line,strpos($each_line,'=')+1));
		}
		else if(strpos($each_line, 'schema') !== false && strpos($each_line, 'schema') < strpos($each_line, "=")) {
			$db_schema = trim(substr($each_line,strpos($each_line,'=')+1));
		}
	}
	fclose($user_config);
?>