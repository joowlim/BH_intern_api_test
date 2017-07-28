<!DOCTYPE html>
<?php

	// Define crontab functions
	include("./crontab.php");
	
	function crontab_test_api($test_api_id, $op, $uri, $jar_path, $old_period, $old_method)
	{
		$link = mysqli_connect('localhost', 'root', 'root', 'API_TEST');
		mysqli_set_charset($link, 'utf8');

		$t_sql = "SELECT * FROM test_api_list, api_list WHERE test_api_id = " . $test_api_id . " AND test_api_list.api_id = api_list.api_id";
		$t_result = mysqli_query($link, $t_sql);
		$t_row = mysqli_fetch_array($t_result, MYSQL_ASSOC);

		$new_command = "";
		if ($op != 1)
			$new_command = $t_row['period'] . "/jdk1.8.0_131/bin/java -jar " . $jar_path . " " . $uri . " " . $t_row['method'] . " " . $test_api_id;
		$old_command = $old_period . "/jdk1.8.0_131/bin/java -jar " . $jar_path . " " . $uri . " " . $old_method . " " . $test_api_id;

		if ($op == 0)
		{
			insertCommand($new_command);
		}
		elseif ($op == 1)
		{
			deleteCommand($old_command);
		}
		elseif ($op == 2)
		{
			modifyCommand($old_command, $new_command);
		}
	}

	$test_api_id = $_GET['id'];
	$params = $_GET['params'];
	$immediately = $_GET['immediately'];

	$period = '';
	$sql = "";
	
	$db_host = "localhost";
	$db_user = "root";
	$db_passwd = "root";
	$db_name = "API_TEST";
	$conn = mysqli_connect($db_host, $db_user, $db_passwd, $db_name);
	mysqli_set_charset($conn, 'utf8');
	
	// crontab
	$uri = $_GET['uri'];
	$jar_path = $_GET['jar_path'];
	$old_period = $_GET['old_period'];
	$old_method = $_GET['old_method'];
	
	if($immediately == 1){ //즉시
		$sql = "UPDATE test_api_list SET test_params='" . $params . "', immediately=" . $immediately . ",period=NULL WHERE test_api_id=" . $test_api_id;
		$result = mysqli_query($conn,$sql);

		// crontab
	 	crontab_test_api($test_api_id, 1, $uri, $jar_path, $old_period, $old_method);

		echo $result;
	}
	else{
		$period = $_GET['period'];
		$sql = "UPDATE test_api_list SET test_params='" . $params . "', immediately=" . $immediately . ", period='" . $period . "' WHERE test_api_id=" . $test_api_id;
		$result = mysqli_query($conn, $sql);

		// crontab 
	 	crontab_test_api($test_api_id, 2, $uri, $jar_path, $old_period, $old_method);
		
		echo $result;
	}
	


?>