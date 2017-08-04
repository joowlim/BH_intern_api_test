<?php

	include("./db_account_info.php");

	$id = $_GET['id'];
	$uri = $_GET['uri'];
	$method = $_GET['method'];
	$params = $_GET['params'];
	
	$conn = mysqli_connect($db_server, $db_user, $db_password, $db_schema);
	mysqli_set_charset($conn, 'utf8');
	
	$sql = "UPDATE api_list SET uri = \"" . $uri . "\", method = \"" . $method . "\", params = '" . $params . "' WHERE api_id = " . $id;
	
	$result = mysqli_query($conn, $sql);
	
	echo $result; 

	mysqli_close($conn);
?>