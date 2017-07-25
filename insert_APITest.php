<!DOCTYPE html>

<?php

	$mode = ($_POST['mode']? $_POST['mode'] : $_GET['mode']);
       	
	$param = $_POST["param"];
	$api_params = $_POST["api_params"];
	$api_param_name = $_POST['api_param_name'];
	$api_param_type = $_POST['api_param_type'];

	// Insert rows by insert button
         if($_POST['insert'] != null)
        {
		$link = mysqli_connect('localhost', 'root', 'root', 'API_TEST');
       		 mysqli_set_charset($link, 'utf8');
		
		if($mode == 0)
                {
                        $uri = $_POST["api_uri"];
                        $method = $_POST["api_method"];
			$params = substr($api_params, 0, strlen($api_params)-2) . ' }';

                        $sql = "INSERT INTO api_list (uri, method, params) VALUES (\"" . addslashes($uri) . "\", \"" . addslashes($method) . "\", \"" . addslashes($params) ."\")";

                        mysqli_query($link, $sql);

                        if(mysqli_affected_rows($link) == 1)
                        {
                                echo '<script>alert("API inserted")</script>';
                        }
                        else
                        {
                                echo '<script>alert("Failed to insert")</script>';
                        }
                }
        	elseif($mode == 2)
		{ 
	      		$name = $_POST["server_name"];
                	$url = $_POST["server_url"];
        	        $ip = $_POST["server_ip"];
	
                	 $sql = "INSERT INTO server_list (server_name, server_url, server_ip) VALUES (\"" . addslashes($name) . "\", \"" . addslashes($url) . "\", \"" . addslashes($ip) ."\")";

        	        mysqli_query($link, $sql);
	
        	        if(mysqli_affected_rows($link) == 1)
        	        {
        	                echo '<script>alert("Server inserted")</script>';
        	        }
        	        else
        	        {
        	                echo '<script>alert("Failed to insert")</script>';
        	        }
		}
		echo '
		<script> 
		location.replace(\'http://52.221.182.124/index.php?mode='. $mode . '\'); 
		</script>'; 

        }
?>

<script>
function api_insert_wo_blank() {
    if (document.getElementById("api_uri").value == "") 
    {
        alert("api uri를 입력해주세요");
        return false;
  	}
    else
        document.getElementById("form_api_insert").submit();

}

function server_insert_wo_blank() {
    if (document.getElementById("server_name").value == "") 
    {
        alert("server name을 입력해주세요");
        return false;
    }
   else if (document.getElementById("server_url").value == "" && document.getElementById("server_ip").value == "")
    {
        alert("server url 또는 server ip를 입력해주세요");
        return false;
    }
    else
        document.getElementById("form_api_insert").submit();

}

</script>

<html>
	<head>
        <link rel="stylesheet" type="text/css" href="insert_APITest_css.css">
		<?php
		if($mode == 0)
		{
			echo '
		<title>API 추가</title>';
		}
		elseif($mode ==2)
		{
			echo '
		<title>Server 추가</title>';
		}
		?>
	</head>
	<body>	
		<?php
		if($mode == 0)
		{
			$value = ($param == "1" ? $api_params . '"' . $api_param_name . '" : "' . $api_param_type . '", ' : '{ ');
		        echo '
                 <h1 style="text-align: center"><img src="./img/parrot_reading.gif" width = 48 onClick="window.location.reload()"/>Test API Admin<img src="./img/parrot_reading.gif" width = 48 onClick="window.location.reload()"/></h1>
                <br/>
                <div class="form-style" style="width:300px; height:400px; margin:auto; vertical-align:middle;">

               
                <h3>Insert</h3>
                 <form action="./insert_APITest.php" method="POST">
                        <input type = "hidden" name = "mode" value = "0" />
                        <p> API uri : <input id="api_uri" type="text" name="api_uri" value = "'. $_POST['api_uri'] .'"/></p>
                        <p> API method : <select name="api_method">
						<option value="GET" '. ($_POST['api_method'] == "GET" ? 'selected="selected"' : "") .'>GET</option>
                                                <option value="POST" '. ($_POST['api_method'] == "POST" ? 'selected="selected"' : "") .'>POST</option>
                                                <option value="PUT" '. ($_POST['api_method'] == "PUT" ? 'selected="selected"' : "") .'>PUT</option>
                                                <option value="DELETE" '. ($_POST['api_method'] == "DELETE" ? 'selected="selected"' : "") .'>DELETE</option>
                                                <option value="HEAD" '. ($_POST['api_method'] == "HEAD" ? 'selected="selected"' : "") .'>HEAD</option>
                                                <option value="CONNECT" '. ($_POST['api_method'] == "CONNECT" ? 'selected="selected"' : "") .'>CONNECT</option>
                                                <option value="OPTIONS" '. ($_POST['api_method'] == "OPTIONS" ? 'selected="selected"' : "") .'>OPTIONS</option>
                                                <option value="TRACE" '. ($_POST['api_method'] == "TRACE" ? 'selected="selected"' : "") .'>TRACE</option>
                                                <option value="PATCH" '. ($_POST['api_method'] == "PATCH" ? 'selected="selected"' : "") .'>PATCH</option>						
					 </select></p>
                        <p> API params : <input type="text" name="api_param_name" placeholder="parameter name"/> <input type="text" name="api_param_type" placeholder="parameter type"/>
					<button name = "param" value = "1" type="submit" class = "button">parameter 추가하기</button>
					<input type="hidden" name="api_params" value=' . "'" . $value . "'" . '/></p>';
			echo '<p> parameter : '. $value .'</p>';
			echo ' 
                        <button id="form_api_insert" name = "insert" value = "1" type="submit" class = "button" onclick="return api_insert_wo_blank()">추가하기</button>
                         <button name = "quit" type=button onClick="history.back();">취소하기</button>
                 </form>

                 </div>';	
		}
		elseif($mode == 2)
		{
			echo '
            <h1 style="text-align: center"><img src="./img/parrot_reading.gif" width = 48 onClick="window.location.reload()"/>Test API Admin<img src="./img/parrot_reading.gif" width = 48 onClick="window.location.reload()"/></h1>
                <br/>
            <div class="form-style" style="width:300px; height:400px; margin:auto; vertical-align:middle;">

                <h3>Insert</h3>
		    <form action="./insert_APITest.php" method="POST">
                        <input type = "hidden" name = "mode" value = "2" />
                        <p> server name : <input id="server_name" type="text" name="server_name" pattern=".{1,}" oninvalid="setCustomValidity(\'이름을 입력해주세요.\')"/></p>
                        <p> server URL : <input id="server_url" type="text" name="server_url" /></p>
                        <p> server IP : <input id="server_ip" type="text" name="server_ip" pattern="[(\d{1,3}[.]\d{1,3}[.]\d{1,3}[.]\d{1,3})localhost]" oninvalid="setCustomValidity(\'x.x.x.x 형식으로 입력해주세요.\')"/></p>
                 	<button id="form_server_insert" name = "insert" value = "1" type="submit" class = "button" onclick="return server_insert_wo_blank()">추가하기</button>
                     <button name = "quit" type=button onClick="history.back();">취소하기</button>
                 </form>

                 </div>';
		}
		?>


			
	</body>

</html>
