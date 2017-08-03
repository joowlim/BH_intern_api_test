<!DOCTYPE html>
<html>
<?php
	$mode = $_GET['mode'];

	// Connect to the db
	$link = mysqli_connect('localhost', 'root', 'root', 'API_TEST');
	mysqli_set_charset($link, 'utf8');

	// Define global variables
	include("./config.php");

	if($mode == 0){
?>

<head>
	<script src = "//code.jquery.com/jquery.min.js" />
	<link rel = "stylesheet" href = "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css" >
	<link rel = "stylesheet" href = "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css" >
	<script src = "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js" />
<?php
	$api_id = $_GET['api_id'];
	$sql = "SELECT * FROM api_list WHERE api_id=". $api_id ;

	
	$db_host = "localhost";
	$db_user = "root";
	$db_passwd = "root";
	$db_name = "API_TEST";
	$conn = mysqli_connect($db_host,$db_user,$db_passwd,$db_name);
	mysqli_set_charset($conn, 'utf8');
	
	
	$result = mysqli_query($conn,$sql);
	
	$data = mysqli_fetch_array($result);
	$isFind = false;
	if($data ==""){
		$isFind = false;
	}
	else{
		$isFind = true;
	}

?>
	<script>
	function sendModifyRequest(id, uri, method, params){

		if(uri ===""){
			alert("uri 는 공백이 될 수 없습니다");
			return;
		}
		$.ajax({
			url: "./modify_action_api_list.php",
			type:"GET",
			data : {
				id: id,
				uri: uri,
				method: method,
				params:p arams
			},
			success:function(err){
				console.log(err);
				if(err == 0){
					alert("실패");
				}
				else{
					alert("성공");
					window.location = './';
				}
				
			}
		});
	}
	</script>
</head>

<body style = "margin-top: 30px" >
<font face = 'Times' >

<h1 style = "text-align: center" ><img src = "./img/parrot_reading.gif" width = 48 onClick = "window.location.reload()" />Test API Admin<img src = "./img/parrot_reading.gif" width = 48 onClick = "window.location.reload()" /></h1><br />


</font>
<div class = "container-fluid" style = "width: 300px; height: 400px; margin: auto; vertical-align: middle;" >

<form class = "form-inline">
<p>id : <?php echo $api_id ?></p>
<p>uri : <input class = "form-control" id = "uri" value = <?php echo "\"".$data['uri']."\""?> /></p>
<p>method : 
	<select class = "form-control" id = "method_list" name = "method_list">
	<?php
		echo' 
			<option value = "GET" '. ($data['method'] == "GET" ? 'selected = "selected"' : "") .' >GET</option>
			<option value = "POST" '. ($data['method'] == "POST" ? 'selected = "selected"' : "") .' >POST</option>
			<option value = "PUT" '. ($data['method'] == "PUT" ? 'selected = "selected"' : "") .' >PUT</option>
			<option value = "DELETE" '. ($data['method'] == "DELETE" ? 'selected = "selected"' : "") .' >DELETE</option>
			<option value = "HEAD" '. ($data['method'] == "HEAD" ? 'selected = "selected"' : "") .' >HEAD</option>
			<option value = "CONNECT" '. ($data['method'] == "CONNECT" ? 'selected = "selected"' : "") .' >CONNECT</option>
			<option value = "OPTIONS" '. ($data['method'] == "OPTIONS" ? 'selected = "selected"' : "") .' >OPTIONS</option>
			<option value = "TRACE" '. ($data['method'] == "TRACE" ? 'selected = "selected"' : "") .' >TRACE</option>
			<option value = "PATCH" '. ($data['method'] == "PATCH" ? 'selected = "selected"' : "") .' >PATCH</option>	
		';
	?>
	</select>
</p>

<p>params : <input class = "form-control" id = "params" value = '<?php echo ''.$data['params'].''?>' /></p>
</form>
<button class = "btn btn-primary btn-lg" onClick = "sendModifyRequest(<?php echo $api_id;?>,$('#uri').val(),$('#method_list').val(),$('#params').val())" >확인 </button>
<a href = "."><button class = "btn btn-default btn-lg" >취소</button></a>
</div>
</body>

<?php 
	}
	else{
		// mode = 1
		
		$test_api_id = $_GET['api_id'];
		$uri = $_GET['uri'];
		$sql = "SELECT * FROM test_api_list WHERE test_api_id=". $test_api_id;

		$db_host = "localhost";
		$db_user = "root";
		$db_passwd = "root";
		$db_name = "API_TEST";
		$conn = mysqli_connect($db_host,$db_user,$db_passwd,$db_name);
		mysqli_set_charset($conn, 'utf8');
		
		$result = mysqli_query($conn,$sql);
	
		$data = mysqli_fetch_array($result);
		
		$server_id = $data['server_id'];
		$api_id = $data['api_id'];
		$test_params = $data['test_params'];
		$immediately = $data['immediately'];
		$period = $data['period'];

		$sql = "SELECT * FROM api_list WHERE api_id=".$api_id;
		
		$result = mysqli_query($conn,$sql);
		$data = mysqli_fetch_array($result);
		
		$uri = $data['uri'];
		$method = $data['method'];
		
		$sql = "SELECT * FROM server_list WHERE server_id=".$server_id;
		
		$result = mysqli_query($conn,$sql);
		$data = mysqli_fetch_array($result);
		$server_url = $data['server_url'];
		
?>
<script src = "//code.jquery.com/jquery.min.js" ></script>

<script>

	function selectListener() {
		if($('#immediately').val() == "1"){
			$('#period_p_tag').hide();
		}
		else{
			$('#period_p_tag').show();
		}
	}
	function sendModifyRequest(id, params, immediately, period, uri, jar_path, old_period, old_method) {
		
		var data;
		if(immediately == 1){
			data = {
				id : id,
				params : params,
				immediately : immediately,
				uri : uri,
				jar_path : jar_path,
				old_period : old_period,
				old_method : old_method
			};
		}
		else{
			data = {
				id : id,
				params : params,
				immediately : immediately,
				period : period,
				uri : uri,
				jar_path : jar_path,
				old_period : old_period,
				old_method : old_method
			};
		}
		$.ajax({
			url: "./modify_action_test_api_list.php",
			type:"GET",
			data : data,
			success:function(err){
				console.log(err);
				if(err == 0){
					alert("실패");
				}
				else{
					alert("성공");
					window.location = './index.php?mode=<?php echo $mode?>';
				}
			}
		});
	}
	

</script>
<head>
	<script src = "//code.jquery.com/jquery.min.js" ></script>
	<link rel =" stylesheet" href = "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css" >
	<link rel = "stylesheet" href = "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css" >
	<script src = "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js" ></script>
<body style = "margin-top:30px" >
<font face = 'Times'>
<h1 style = "text-align: center"><img src = "./img/parrot_reading.gif" width = 48 onClick = "window.location.reload()" /><b>Test API Admin</b><img src = "./img/parrot_reading.gif" width = 48 onClick = "window.location.reload()" /></h1>
<br/>
</font>
<div class = "container-fluid" style = "width: 300px; height: 400px; margin: auto;" >
<form class="form-inline">
	<p>test api id : <?php echo $api_id;?></p>
	<p>server URL : <?php echo $server_url ?></p>
	<p>API URI : <?php echo $uri ?></p>
	<p>params : <input class = "form-control" id = "params" value = '<?php echo $test_params ?>'> </p>
	<p>테스팅 타이밍 : 
		<select class = "form-control" onchange = "selectListener()" id = "immediately" name = "immediately">
		<?php
			if($immediately == 1){
				echo 
			'<option value = "1" selected = "selected" >즉시</option>'."\n";
				echo 
			'<option value = "0" >주기적으로</option>';
			}
			else{
				echo 
			'<option value = "1" >즉시</option>'."\n";
				echo 
			'<option value = "0" selected = "selected" >주기적으로</option>';
			}
		
		?> 
		</select>
	</p>
	<?php
		echo 
	'<p id = "period_p_tag" >period :<input id = "period" name = "period" value ="'.$period.'">';
		echo 
	'</select> </p>';

	?>
</form>
	<script>
		selectListener();
	</script>
	<?php
		$uri_q = "'" . $server_url . $uri . "'";
		$jar_path_q = "'" . $jar_path . "'";
		$period_q = "'" . $period . "'";
		$method_q = "'" . $method . "'";

	?>
	<button class = "btn btn-primary btn-lg" onClick = "sendModifyRequest(<?php echo $test_api_id;?>,$('#params').val(),$('#immediately').val(),$('#period').val(),<?php echo $uri_q;?>,<?php echo $jar_path_q;?>,<?php echo $period_q;?>,<?php echo $method_q;?>)">확인</button>
	<a href = "."><button class = "btn btn-default btn-lg" >취소</button></a>
</div>
</body>

</head>

<?php
	}
?>

</html>
