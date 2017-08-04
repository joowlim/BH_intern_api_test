<!DOCTYPE html>
<?php
	include("./db_account_info.php");

	$test_api_id = $_GET['id'];
	$params = $_GET['params'];
	$immediately = $_GET['immediately'];
	
	$conn = mysqli_connect($db_server, $db_user, $db_password, $db_schema);
	mysqli_set_charset($conn, 'utf8');
	
	// crontab
	$uri = $_GET['uri'];
	$jar_path = $_GET['jar_path'];
	$old_period = $_GET['old_period'];
	$old_method = $_GET['old_method'];
	
	if($immediately == 1){ //즉시
		$sql = "UPDATE test_api_list SET test_params='" . $params . "', immediately=" . $immediately . ",period=NULL WHERE test_api_id=" . $test_api_id;
		$result = mysqli_query($conn,$sql);

		echo $result;
	}
	else{
		$period = $_GET['period'];
		$sql = "UPDATE test_api_list SET test_params='" . $params . "', immediately=" . $immediately . ", period=" . $period . " WHERE test_api_id=" . $test_api_id;
		$result = mysqli_query($conn, $sql);

		echo $result;
	}
	mysqli_close($conn);
?>