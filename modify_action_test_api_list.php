<?php

	$test_api_id = $_GET['id'];
	$params = $_GET['params'];
	$immediately = $_GET['immediately'];
	$period = '';
	$sql = "";
	
	$db_host = "localhost";
	$db_user = "root";
	$db_passwd = "root";
	$db_name = "API_TEST";
	$conn = mysqli_connect($db_host,$db_user,$db_passwd,$db_name);
	mysqli_set_charset($conn, 'utf8');
	
	
	if($immediately == 1){ //즉시
		$sql = "UPDATE test_api_list SET test_params='".$params."', immediately=".$immediately.",period=NULL WHERE test_api_id=".$test_api_id;
		$result = mysqli_query($conn,$sql);
		echo $result;
	}
	else{
		$period=$_GET['period'];
		$sql = "UPDATE test_api_list SET test_params='".$params."', immediately=".$immediately.", period='".$period."' WHERE test_api_id=".$test_api_id;
		$result = mysqli_query($conn,$sql);
		echo $result;
	}
	


?>