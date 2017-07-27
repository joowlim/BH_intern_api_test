<?php 
$db_host = "localhost";
$db_user = "root";
$db_passwd = "root";
$db_name = "API_TEST";
function take_api($db_host,$db_user,$db_passwd,$db_name){
	// $db_name = "API_TEST";
	$conn = mysqli_connect($db_host,$db_user,$db_passwd,$db_name);
	mysqli_set_charset($conn, 'utf8');
	if(mysqli_connect_errno($conn)){
	    echo "데이터베이스 연결 실패: ".mysqli_connect_error();
	}
	else{
	}
	$take = mysqli_query($conn,"SELECT * FROM api_list where api_id = " . $_GET['api_id']); 
	/** while($row = mysqli_fetch_array($take)){
	    echo $row['api_id'];
	    echo $row['uri'];
	    echo $row['method'];
	    echo $row['params'];
	    echo "<br>";
	}**/
	$set = mysqli_fetch_array($take);
	$id =  $set['api_id'];
	$uri = $set['uri'];
	$method = $set['method'];
	$json = $set['params'];
	$params = json_decode($json, true);
	/**
	foreach($params as $key => $value){
	   echo "$key : $value".'<br />';
	}**/
	mysqli_close($conn);
	// echo "working test";
	// echo $params['iwanna'];
	$api = array($id,$uri,$method,$json,$params);
	return $api;
}
// take($db_host,$db_user,$db_passwd,$db_name)

function take_server($db_host,$db_user,$db_passwd,$db_name,$server_url,$server_ip){
	$conn = mysqli_connect($db_host,$db_user,$db_passwd,$db_name);

	mysqli_set_charset($conn, 'utf8');
	if(mysqli_connect_errno($conn)){
	    echo "데이터베이스 연결 실패: ".mysqli_connect_error();
	}
	else{
	}
	// printf("%s",$server_url);
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
	// printf("%s",$server_url);
	?>
	<script type="text/javascript">
		// console.log("<?php echo $server_url; ?>");
	</script>
	<?php
	$url = $take['server_url'];
	$ip = $take['server_ip'];
	$serverid = $take['server_id'];
	// echo $url;
	// echo $ip;

	mysqli_close($conn);
	// echo "take_server no value test";
	// echo $serverid;
	// echo "complete";
	return $serverid;
}

function insert($db_host,$db_user,$db_passwd,$db_name,$insertArr){
	// echo "insert test checking";
	$conn = mysqli_connect($db_host,$db_user,$db_passwd,$db_name);
	mysqli_set_charset($conn, 'utf8');
	// echo "???";
	if(mysqli_connect_errno($conn)){
	    echo "데이터베이스 연결 실패: ".mysqli_connect_error();
	}
	else{
	}
	$intimm = (int)$insertArr[3];
	$intper = $insertArr[4];
	// echo "what is happening?";
	$insert = mysqli_query($conn,"insert into test_api_list (server_id, api_id, test_params, immediately, period) Values('$insertArr[0]','$insertArr[1]','$insertArr[2]','$intimm', '$intper')");
	if(!$insert){
		// printf("%s\n",mysqli_error($conn));
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

$period_list = array('1분마다', '2분마다', '3분마다', '4분마다', '5분마다', '6분마다', '10분마다', '12분마다',
						'15분마다', '20분마다', '30분마다', '1시간마다', '2시간마다', '3시간마다', '4시간마다', '6시간마다', '8시간마다', '12시간마다', '하루마다');

function getCrontabPeriod($period)
{
    if (strpos($period, '분마다') !== false)
    {
    	return "*/" . substr($period, 0, -9) . " * * * * ";
    }
    elseif (strpos($period, '시간마다') !== false)
    {
    	return "0 */" . substr($period, 0, -12) . " * * * ";
    }
    elseif (strpos($period, '하루마다') !== false) 
    {
    	return "0 0 * * * ";
    }
    else
    {
    	return "* * * * * ";
    }
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
	// $db_host = "52.221.182.124";
	// $db_user = "root";
	// $db_passwd = "root";
	// $db_name = "API_TEST";
	$api = take_api($db_host,$db_user,$db_passwd,$db_name);
	//echo $api[3];
	$params = json_decode($api[3], true);
	$reloads = array();
	foreach($params as $key => $value){
			// echo "$key : $value".'<br />';
			// echo $_POST[$key];
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
		<label for = "server_url">서버 url :</label>
		<input type = "text" name = "server_url" <?php echo 'value = "' . $_POST['server_url'] . '"'; ?>>
	</div>
	<div>
		<label for = "server_ip">서버 ip :</label>
		<input type = "text" name = "server_ip" <?php echo 'value = "' . $_POST['server_ip'] . '"'; ?>>
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
		<select name = "period">
				<?php
					$i = 0;
					for(;$i<count($period_list);$i++){
						echo '
			<option value="' . $period_list[$i] . '"' . ($_POST['period'] == $period_list[$i] ? 'selected="selected"' : '') . '>' . $period_list[$i] . '</option>';
					}
				?>
		</select>
	</div>
	<h3>파라미터 입력하는 곳입니다.</h3>
	<div style = "padding: 3%; font-style: italic; font-size: 1.0em; font-family: impact;" id = "forparams">
		<input type = "submit" value = "insert test api" style ="width:20%; height:40px;">
	</div>
</form>
</div>
<script type="text/javascript">
	// document.write("hello")
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
	// console.log(hh);
	// console.log(paramArr);
	// console.log(paramArr2[0][0]);
	// console.log(paramArr);
	for(var i=0; i<paramArr.length; i++){
		// console.log(paramArr[i]);
	}
	// console.log("Hqiweh");
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
	var f = document.getElementById("forparams");
	// f.textContent = "요기는 파라미터 입력하는 곳이얌 마음껏 입력해보시지\n";
	var count = "<?php echo($count); ?>";
	// console.log("count checking");
	// console.log(count);
	for(i=0; i<count; i++){
		
		// console.log("parameter checking");
		var d = adddiv(paramArr2[i][0]);
		var l = addlabel(paramArr2[i][0].concat(" 파라미터 값을 입력해주세요 : "));
		var c = addData(paramArr2[i][0],reloads[paramArr2[i][0]]);
		// console.log(paramArr2[i][0]);
		d.appendChild(l);
		d.appendChild(c);
		f.appendChild(d);
	}
</script>
<?php
	// echo $api[3];
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$server_url = $_POST['server_url'];
		$server_ip = $_POST['server_ip'];
		// echo "nuilliliya";
		$id = take_server($db_host,$db_user,$db_passwd,$db_name,$server_url,$server_ip);
		// echo $id;
		// $parramArray = array();
		// echo "llllloooollllll";
		// echo $params[0];
		$paramArray = array();
		foreach($params as $key => $value){
			// echo "$key : $value".'<br />';
			// echo $_POST[$key];
			$paramArray[$key] = $_POST[$key];
		}
		/**for($j=0; $j<$count; $j++){
			$index = $params[$j][0];
			echo $index;
			$parramArray[$index] = $_POST[$index];
			#array_push($parramArray,$_POST[$index]);
		}**/
		/**foreach($params as $key => $value){
			$parramArray[]
			echo "$key : $value".'<br />';
		}**/
		// echo  "whahtahth";
		// echo $paramArray;
		$jsonwant = json_encode($paramArray);
		?>
		<script type="text/javascript">
			// console.log("<?php echo"hangeul"; ?>");
			// console.log("<?php echo $jsonwant; ?>");
		</script>
		<?php
		// echo $jsonwant;
		$jsonwant2 = str_replace('","', '", "', to_han($jsonwant));
		// echo $jsonwant2;
		$insertArr = array();
		array_push($insertArr,$id);
		array_push($insertArr,$api[0]);
		array_push($insertArr,$jsonwant2);
		// immediately 에 0과 1이 들어오는데 이것도 넣어주세요.
		array_push($insertArr,$_POST['immediately']);
		// $_POST['period'] 대신 getCrontabPeriod($_POST['period']) 를 입력해야 됨.
		// immediately 보고 period 넣을지 안넣을지 정하는 부분.
		if($_POST['immediately'] == '1'){
			array_push($insertArr,NULL);
		}
		else{
			array_push($insertArr,getCrontabPeriod($_POST['period']));
		}
		// array_push($insertArr,getCrontabPeriod($_POST['period']));
		// echo $insertArr[3];
		// echo "checking error";
		insert($db_host,$db_user,$db_passwd,$db_name,$insertArr);
	}
?>
</body>
</html>