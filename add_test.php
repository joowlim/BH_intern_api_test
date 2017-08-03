<?php 
$db_host = "localhost";
$db_user = "root";
$db_passwd = "root";
$db_name = "API_TEST";
function take_api($db_host,$db_user,$db_passwd,$db_name){
	$conn = mysqli_connect($db_host,$db_user,$db_passwd,$db_name);
	mysqli_set_charset($conn, 'utf8');

	if(mysqli_connect_errno($conn)){
	    echo "데이터베이스 연결 실패: ".mysqli_connect_error();
	}
	else{
	}
	$take = mysqli_query($conn,"SELECT * FROM api_list where api_id = " . $_GET['api_id']); 

	$set = mysqli_fetch_array($take);
	$id =  $set['api_id'];
	$uri = $set['uri'];
	$method = $set['method'];
	$json = $set['params'];
	$params = json_decode($json, true);
	
	mysqli_close($conn);
	
	$api = array($id,$uri,$method,$json,$params);
	return $api;
}

function take_server($db_host,$db_user,$db_passwd,$db_name,$server_url,$server_ip){
	$conn = mysqli_connect($db_host,$db_user,$db_passwd,$db_name);

	mysqli_set_charset($conn, 'utf8');
	if(mysqli_connect_errno($conn)){
	    echo "데이터베이스 연결 실패: ".mysqli_connect_error();
	}
	else{
	}
	$take1 = mysqli_query($conn,"SELECT * FROM server_list where server_url = '$server_url' or server_ip = '$server_ip'");
	if (!$take1){
		// printf("Hello");
		// printf("Error: %s\n", mysqli_error($conn));
		// exit();
	}
	 
	$take = mysqli_fetch_array($take1);
	if (!$take){
		// printf("Error: %s\n", mysqli_error($conn));
		// exit();
	}
	$url = $take['server_url'];
	$ip = $take['server_ip'];
	$serverid = $take['server_id'];

	mysqli_close($conn);

	return $serverid;
}

function insert($db_host,$db_user,$db_passwd,$db_name,$insertArr){
	$conn = mysqli_connect($db_host,$db_user,$db_passwd,$db_name);
	mysqli_set_charset($conn, 'utf8');

	if(mysqli_connect_errno($conn)){
	    echo "데이터베이스 연결 실패: ".mysqli_connect_error();
	}
	else{
	}
	$intimm = (int)$insertArr[3];
	$intper = $insertArr[4];
  
	$insert = mysqli_query($conn,"insert into test_api_list (server_id, api_id, test_params, immediately, period) Values('$insertArr[0]','$insertArr[1]','$insertArr[2]','$intimm', $intper)");

	if(!$insert){
		echo '<script>alert("Failed to insert")</script>';
	}
	else{
		echo '<script>alert("API inserted")</script>';
		echo '
		<script> 
		window.location = \'./index.php\';
		</script>'; 
	}
	mysqli_close($conn);
}

function my_json_encode($arr)
{
    // convmap since 0x80 char codes so it takes all multibyte codes (above ASCII 127). So such characters are being "hidden" from normal json_encoding
    array_walk_recursive($arr, function (&$item, $key) { if (is_string($item)) $item = mb_encode_numericentity($item, array (0x80, 0xffff, 0, 0xffff), 'UTF-8'); });
    return mb_decode_numericentity(json_encode($arr), array (0x80, 0xffff, 0, 0xffff), 'UTF-8');
}

function han ($s) { return reset(json_decode('{"s":"'.$s.'"} ')); }

function to_han ($str) { return preg_replace('/(\\\u[a-f0-9]+)+/e','han("$0")',$str); }

function getServer($db_host, $db_user, $db_passwd, $db_name){
	$server_list = array();
	$conn = mysqli_connect($db_host,$db_user,$db_passwd,$db_name);
	mysqli_set_charset($conn, 'utf8');

	$sql = "SELECT server_name, server_url, server_id FROM server_list";
	$result = mysqli_query($conn, $sql);
	while ( $row = mysqli_fetch_array($result) ) {
		array_push($server_list, array($row["server_id"], $row["server_name"] . "(" . $row["server_url"] . ")"));
	}
	$result->close();
	$conn->close();
	return $server_list;
}
?>
<!DOCTYPE html>

<html>
<head>
<meta charset = "UTF-8">
<h1 style = "text-align: center"><img src = "./img/parrot_reading.gif" width = 48 onClick = "window.location.reload()"/>Test API Admin<img src = "./img/parrot_reading.gif" width = 48 onClick = "window.location.reload()"/></h1>
</head>
<body align = "center">
<?php
	$api = take_api($db_host,$db_user,$db_passwd,$db_name);
	
	$params = json_decode($api[3], true);
	$reloads = array();
	foreach($params as $key => $value){
		if($_POST[$key]){
			$reloads[$key] = $_POST[$key];
		}
	}
	$count = count($params);
?>
<style>
	form{
		margin : auto
		padding : 1em;
		border : 4px;
		border-radius : 1em;
	}
	form div + div{
		margin-top : 1em;
	}
	h1 {color : rgb(110,140,255);}
	h3 {
		background: rgb(240,120,120);
		color: white;
		font: impact;
	}
	input{
		height : 20px;
		box-sizing : border-box;
		box-shadow: 0 0 5px #43D1AF;
		width: 30%;	
	} 
</style>
<div class = "form-style" style = "width: 800px; height: 400px; margin: auto;">
<form align = "center" id = "formforparam" action = "" method = "post">
	<p align = "center"> Register test api : </p>
	<div>
		<label for = "server">서버:</label>
		<select name = "server">
		<?php
			$server_list = getServer($db_host, $db_user, $db_passwd, $db_name);
			for ($i = 0; $i < count($server_list); $i++) {
				echo '
				<option value = '.$server_list[$i][0].' >'.$server_list[$i][1].'</option>
				';
			}         							 
		?>
		</select>
	</div>
	<div>
		<label for = "immediately">즉시 실행 :</label>
		<select name = "immediately">
			<option value = "1" <?php echo ($_POST['immediately'] == "1" ? 'selected="selected"' : '' ); ?>>O</option>
			<option value = "0" <?php echo ($_POST['immediately'] == "0" ? 'selected="selected"' : '' ); ?>>X</option>
		</select>
	</div>
	<div>
		<label for = "period">period :</label>
		<input type = "text" name = "period" />
	</div>
	<h3>파라미터 입력하는 곳입니다.</h3>

	<div style = "padding: 3%; font-style: italic; font-size: 1.0em; font-family: impact;" id = "forparams">
		<input type = "submit" value = "insert test api" style ="width:20%; height:40px;">
	</div>
</form>
</div>
<script type="text/javascript">
	<?php 
		$res = json_encode($params);
		$res = urldecode($res);

	?>
	var reloads = <?php echo json_encode($reloads);?>;
	
	var paramArr = <?php echo iconv("CP949","UTF-8",$res);?>;
	var paramArr2 = [];
	for(var i in paramArr){
		paramArr2.push([i, paramArr[i]]);
	}
	var hh = JSON.stringify(paramArr)

	function makeForm() {
	    var ffom = document.createElement("form");
	    ffom.action = "";
	    ffom.method = "post";
	    return f;
	}

	function addData(namev,preval) {
	    var elem = document.createElement("input");
	    elem.setAttribute("type", "text");
	    elem.setAttribute("name", namev);
	    if(preval){
	    	elem.setAttribute("value", preval);
	    }
	    return elem;
	}
	function addlabel(text){
		var label = document.createElement("label")
		label.setAttribute("for", text);
		label.innerText = text;
		return label;
	}
	function adddiv(id){
		var id = document.createElement("div")
		id.setAttribute("id", id);
		return id;
	}
  
	var forparams = document.getElementById("forparams");
	var count = "<?php echo($count); ?>";
	for(i=0; i<count; i++){
    
		// console.log("parameter checking");
		var div = adddiv(paramArr2[i][0]);
		var label = addlabel(paramArr2[i][0].concat(" 파라미터 값을 입력해주세요 : "));
		var input = addData(paramArr2[i][0],reloads[paramArr2[i][0]]);
		// console.log(paramArr2[i][0]);
		div.appendChild(label);
		div.appendChild(input);
		forparams.appendChild(div);
	}
</script>
<?php
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$id = $_POST['server'];
	
		$paramArray = array();
		foreach($params as $key => $value){
			$paramArray[$key] = $_POST[$key];
		}

		$jsonwant = json_encode($paramArray);
		$jsonwant2 = str_replace('","', '", "', to_han($jsonwant));

		$insertArr = array();
		array_push($insertArr,$id);
		array_push($insertArr,$api[0]);
		array_push($insertArr,$jsonwant2);
		
		array_push($insertArr,$_POST['immediately']);
		
		// immediately 보고 period 넣을지 안넣을지 정하는 부분.
		if($_POST['immediately'] == '1'){
			array_push($insertArr,NULL);
		}
		else{
			array_push($insertArr,$_POST['period']);
		}
		insert($db_host,$db_user,$db_passwd,$db_name,$insertArr);
	}
?>
</body>
</html>
