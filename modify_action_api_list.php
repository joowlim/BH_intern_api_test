<?php
	$id = $_GET['id'];
	$uri = $_GET['uri'];
	$method = $_GET['method'];
	$params = $_GET['params'];
	
	$db_host = "localhost";
	$db_user = "root";
	$db_passwd = "root";
	$db_name = "API_TEST";
	$conn = mysqli_connect($db_host, $db_user, $db_passwd, $db_name);
	mysqli_set_charset($conn, 'utf8');
	
	$sql = "UPDATE api_list SET uri = \"" . $uri . "\", method = \"" . $method . "\", params = '" . $params . "' WHERE api_id = " . $id;
	
	$result = mysqli_query($conn, $sql);
	
	
	echo $result; 

?>