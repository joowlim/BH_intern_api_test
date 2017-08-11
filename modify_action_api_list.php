<html>
	<h1 style = "text-align: center"><img src = "./img/parrot_reading.gif" width = 48 onClick = "window.location.reload()" />Test API Admin<img src = "./img/parrot_reading.gif" width = 48 onClick = "window.location.reload()" /></h1>
<body>
<p id = "error_msg"></p>
<?php
	function han ($s) { return reset(json_decode('{"s":"'.$s.'"} ')); }
	function to_han ($str) { return preg_replace('/(\\\u[a-f0-9]+)+/e','han("$0")',$str); }
	
	$id = $_GET['api_id'];
	$uri = $_GET['uri'];
	$method = $_GET['method_list'];
	$params_origin = $_GET['params'];
	$params_array = json_decode($params_origin, true);
	foreach($params_array as $key => $value) {
		$index = 'key_'.$key;
		$params_array[$key] = $_GET[$index];
	}
	$params = json_encode($params_array);
	$params_hangeul = str_replace('","', '", "', to_han($params));
  
	include("./db_account_info.php");
	$conn = mysqli_connect($db_server, $db_user, $db_password, $db_schema);
	if(!$conn) {
		// printf("%s\n",mysqli_error($conn));
		echo '<script>
				alert("DB connect 에러");
			</script>';
		echo '
		<script> 
		window.location = \'./index.php\';
		</script>';
	}
	mysqli_set_charset($conn, 'utf8');
	
	$sql = "UPDATE api_list SET uri=\"" . $uri . "\", method=\"" . $method . "\", params='" . $params_hangeul . "' WHERE api_id=" . $id;
	$result = mysqli_query($conn, $sql);
	if(!$result) {
		// printf("%s\n",mysqli_error($conn));
		echo '<script>alert("sql 쿼리 에러")</script>';
		printf("%s\n", mysqli_error($conn));
		echo '
		<script> 
		window.location = \'./index.php\';
		</script>';
	}
	else {
		echo '<script>alert("API 수정완료")</script>';
		echo '
		<script> 
		window.location = \'./index.php\';
		</script>'; 
		
	}

	mysqli_close($conn);
?>
</body>
</html>
