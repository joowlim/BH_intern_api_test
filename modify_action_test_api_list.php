<!DOCTYPE html>
<html>
	<h1 style = "text-align: center"><img src = "./img/parrot_reading.gif" width = 48 onClick = "window.location.reload()"/>Test API Admin<img src = "./img/parrot_reading.gif" width = 48 onClick = "window.location.reload()"/></h1>
<body>
<?php
	function han ($s) { return reset(json_decode('{"s":"'.$s.'"} ')); }
	function to_han ($str) { return preg_replace('/(\\\u[a-f0-9]+)+/e','han("$0")',$str); }
	include("./db_account_info.php");

	$test_api_id = $_GET['api_id'];
	$params_origin = $_GET['params'];
	$immediately = $_GET['immediately'];
	
	$conn = mysqli_connect($db_server, $db_user, $db_password, $db_schema);
	if(!$conn){
		// printf("%s\n",mysqli_error($conn));
		echo '<script>
				alert("DB connect 에러");
			</script>';
		echo '
		<script> 
		window.location = \'./index.php?mode=1\';
		</script>';
	}
	mysqli_set_charset($conn, 'utf8');
	
	// crontab
	$uri = $_GET['uri'];
	$jar_path = $_GET['jar_path'];
	$old_period = $_GET['period'];
	// $old_method = $_GET['old_method'];
	// change each value in json array and convert it into json string
	$params_array = json_decode($params_origin,true);
	foreach($params_array as $key => $value){
		$index = 'key_'.$key;
		$params_array[$key] = $_GET[$index];
	}
	$params = json_encode($params_array);
	$params_hangeul = str_replace('","', '", "', to_han($params));

	if($immediately == 1){ //즉시
		$sql = "UPDATE test_api_list SET test_params='" . $params_hangeul . "', immediately=" . $immediately . ",period=NULL WHERE test_api_id=" . $test_api_id;
		$result = mysqli_query($conn,$sql);
		if(!$result){
			// printf("%s\n",mysqli_error($conn));
			echo '<script>alert("sql 쿼리 에러")</script>';
			printf("%s\n",mysqli_error($conn));
			echo '
			<script> 
			window.location = \'./index.php?mode=1\';
			</script>';
		}
		else{
			echo '<script>alert("API 수정완료")</script>';
			echo '
			<script> 
			window.location = \'./index.php?mode=1\';
			</script>'; 
			
		}

		echo $result;
	}
	else{
		$period = $_GET['period'];
		$sql = "UPDATE test_api_list SET test_params='" . $params_hangeul . "', immediately=" . $immediately . ", period=" . $period . " WHERE test_api_id=" . $test_api_id;
		$result = mysqli_query($conn, $sql);
		if(!$result){
			// printf("%s\n",mysqli_error($conn));
			echo '<script>alert("sql 쿼리 에러")</script>';
			printf("%s\n",mysqli_error($conn));
			echo '
			<script> 
			window.location = \'./index.php?mode=1\';
			</script>';
		}
		else{
			echo '<script>alert("API 수정완료")</script>';
			echo '
			<script>
			window.location = \'./index.php?mode=1\';
			</script>'; 
			
		}

		echo $result;
	}
	mysqli_close($conn);
?>
</body>
</html>