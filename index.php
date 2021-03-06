<?php
	// Define global variables
	include("./config.php");
	include("./db_account_info.php");
	
	// Test connection
	$test_link = mysqli_connect($db_server, $db_user, $db_password, $db_schema);
	if(!$test_link) {
		$exp_uri = explode('?', $_SERVER['REQUEST_URI']);
		header("Location: " . (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . str_replace("index.php","",$exp_uri[0]) . "install.php");
		exit();
	}
	mysqli_close($test_link);
?>
<!DOCTYPE html>
<html>
<head>
	<title>TEST API Manager</title>
	<style>
	.button {
		background-color: #777787;
		border: none;
		text-align: center;
		text-decoration: none;
		font-size: 16px;
	}
	button:hover {
		background-color: #9999A7;
		border: none;
		text-align: center;
		text-decoration: none;
		display: inline-block;
		font-size: 16px;
	}
	img {
		-moz-user-select: none;
		-webkit-user-select: none;
		-ms-user-select: none;
		user-select: none;
		-o-user-select: none;
	}
	body {
		margin: 0;
	}
	ul {
		list-style-type: none;
		margin: 0;
		padding: 0;
		width: 15%;
		background-color: #f1f1f1;
		position: fixed;
		height: 100%;
		overflow: auto;
		-moz-user-select: none;
		-webkit-user-select: none;
		-ms-user-select: none;
		user-select: none;
		-o-user-select: none;
	}
	li p {
		display: block;
		color: #000;
		padding: 8px 16px;
		text-decoration: none;
	}
	
	li a {
		border-radius: 5px;
		display: block;
		color: #000;
		padding: 8px 16px;
		text-decoration: none;
	}

	li a.active {
		background-color: #4CAF50;
		color: white;
	}

	li a:hover:not(.active) {
		background-color: #555;
		color: white;
	}
	li a.active:hover {
		background-color: #6DD071;
		color: white;
		
	}
	a:hover{
		cursor: pointer;
	}
	</style>
	<script src="//code.jquery.com/jquery.min.js"></script>
	<script>
	function change_search_field(sel) {
		if(sel.value == "date")
			document.getElementById('search_field').innerHTML = '<input style = "width: 30%" type = "datetime-local" name = "date-start" value = "<?php echo $_GET['date-start']; ?>" />~<input style = "width: 30%" type = "datetime-local" name = "date-end" value = "<?php echo $_GET['date-end']; ?>" />';
		else if(sel.value == "elapsed")
			document.getElementById('search_field').innerHTML = '<input style = "width: 30%" type = "text" name = "elapsed_lowerbound" value = "<?php echo $_GET['elapsed_lowerbound']; ?>" />~<input style = "width: 30%" type = "text" name = "elapsed_upperbound" value = "<?php echo $_GET['elapsed_upperbound']; ?>" />';
		else
			document.getElementById('search_field').innerHTML = '<input style = "width: 60%" type = "text" name = "search_key" />';
	}
	function delete_row(mode, id) {
		if(confirm("삭제하시겠습니까?")) {
			console.log(mode);
			console.log(id);
			$.ajax({
				url : "./delete.php",
				data : {
					mode : mode,
					id : id
				},
				success : function(result) {
					if(result == 0)
						alert("삭제를 실패했습니다");
					else
						alert("삭제를 성공했습니다!");
					location.reload();
				},
				error : function(e) {
					alert("삭제를 실패했습니다");
				}
			});
		}
	}
	</script>
	<?php
	// Connect to the db
	$link = mysqli_connect($db_server, $db_user, $db_password, $db_schema);
	mysqli_set_charset($link, 'utf8');

	$mode = 0;
	
	function prettyPeriod($period) {
		$min = $period % 60;
		$hour = ($period / 60) % 24;
		$day = intval(($period / 60) / 24);

		return ($day != null ? $day . "일 " : "") . ($hour != null ? $hour . "시간 " : "") . $min . "분";
	}
	// Delete rows by delete button
	if($_GET['delete'] != null) {
		// Remove data from db
		if($_GET['mode'] == 0) {
			$sql = "DELETE FROM api_list WHERE api_id = " . $_GET['delete'];
		}
		elseif($_GET['mode'] == 1) {
			$sql = "DELETE FROM test_api_list WHERE test_api_id = " . $_GET['delete'];
		}
		elseif($_GET['mode'] == 2) {
			$sql = "DELETE FROM server_list WHERE server_id = " . $_GET['delete'];
		}
		elseif($_GET['mode'] == 3) {
			$ind = 0;
			$sql = "DELETE FROM test_log WHERE";
			foreach($_GET['delete_list'] as $one_log) {
           	 	$sql = $sql . ($ind == 0? " log_id = " : " OR log_id = "). $one_log;
           	 	$ind = $ind + 1;
   			}
		}
	
		mysqli_query($link, $sql);
		
		if(mysqli_affected_rows($link) >= 1) {
			echo '<script>alert("Deleted")</script>';
		}
		else {
			echo '<script>alert("Failed to delete")</script>';
		}
	}
	
	// Toggle by toggle button
	if($_GET['toggle'] != null && $_GET['mode'] == 1) {

		$t_sql = "SELECT * FROM test_api_list, api_list WHERE test_api_id = " . $_GET['api_id'] . " AND test_api_list.api_id = api_list.api_id";
		$t_result = mysqli_query($link, $t_sql);
		$t_row = mysqli_fetch_array($t_result, MYSQL_ASSOC);
		
		// Periodical work, register on the crontab
		if($t_row['immediately'] == 0) {
			$sql = "UPDATE test_api_list SET is_running = ". $_GET['toggle'] ." WHERE test_api_id = " . $_GET['api_id'];
			mysqli_query($link, $sql);
			
			if(mysqli_affected_rows($link) == 1) {
				if($_GET['toggle'] == 1) {
					echo '<script>alert("Test registered")</script>';
				}
				else {
					echo '<script>alert("Test unregistered")</script>';
				}
			}
			else {
				echo '<script>alert("Failed to update")</script>';
			}
		}
		// Execute only one time
		else {
			$new_command = $java_path . " -jar " . $jar_path . " " . $_GET['uri'] . " " . $t_row['method'] . " " . $_GET['api_id'];
			
			exec($new_command, $output, $ret);
			
			if($ret == 0) {
					echo '<script>alert("Test successfully executed")</script>';
			}
			else {
					echo '<script>alert("Test Failed : ' . $ret . '")</script>';
			}
		}
	}
	// Mode changing
	if($_GET['mode'] == null || $_GET['mode'] == 0) {
		// Show entire api list
		$mode = 0;
		$page = ($_GET['page'] == null ? 0 : $_GET['page']);
		$offset = $page * $list_row_num;
		
		// Search by the key
		if($_GET['search_key'] != null) {
			$search_where_clause = "WHERE " . $_GET['column'] . " LIKE '%" . $_GET['search_key'] . "%'";
		}
		
		$sql = "SELECT * FROM api_list " . $search_where_clause . " ORDER BY api_id DESC LIMIT " . $offset . ", " . $list_row_num;
		$num_sql = "SELECT COUNT(*) FROM api_list " . $search_where_clause;
	}
	elseif($_GET['mode'] == 1) {
		// Show test api list
		$mode = 1;
		$page = ($_GET['page'] == null ? 0 : $_GET['page']);
		$offset = $page * $list_row_num;
		
		// Search by the key
		if($_GET['search_key'] != null) {
			$search_where_clause = $_GET['column'] . " LIKE '%" . $_GET['search_key'] . "%'";
		}
		
		$sql = "SELECT * FROM test_api_list LEFT JOIN api_list ON test_api_list.api_id = api_list.api_id LEFT JOIN server_list ON test_api_list.server_id = server_list.server_id" . ($_GET['search_key'] == null ? '' : ' WHERE (' . $search_where_clause . ' OR server_url LIKE "%' . $_GET['search_key'] . '%")') . " ORDER BY test_api_id DESC LIMIT " . $offset . ", " . $list_row_num;
		$num_sql = "SELECT COUNT(*) FROM test_api_list LEFT JOIN api_list ON test_api_list.api_id = api_list.api_id LEFT JOIN server_list ON test_api_list.server_id = server_list.server_id" . ($_GET['search_key'] == null ? '' : ' WHERE ' . $search_where_clause);
	}
	elseif($_GET['mode'] == 2) {
		// Show entire api list
		$mode = 2;
		$page = ($_GET['page'] == null ? 0 : $_GET['page']);
		$offset = $page * $list_row_num;
		
		// Search by the key
		if($_GET['search_key'] != null) {
			$search_where_clause = "WHERE " . $_GET['column'] . " LIKE '%" . $_GET['search_key'] . "%'";
		}
		
		$sql = "SELECT * FROM server_list " . $search_where_clause . " ORDER BY server_id DESC LIMIT " . $offset . ", " . $list_row_num;
		$num_sql = "SELECT COUNT(*) FROM server_list " . $search_where_clause;
	}
	elseif($_GET['mode'] == 3) {
		// Show Logs
		$mode = 3;
		$page = ($_GET['page'] == null ? 0 : $_GET['page']);
		$offset = $page * $list_row_num;
		
		// Search by the key
		if($_GET['search_key'] != null) {
			$search_where_clause = "WHERE " . $_GET['column'] . " LIKE '%" . $_GET['search_key'] . "%'";
			
			$sql = "SELECT * FROM test_log LEFT JOIN api_list ON test_log.api_id = api_list.api_id LEFT JOIN server_list ON test_log.server_id = server_list.server_id " . $search_where_clause . " ORDER BY log_id DESC LIMIT " . $offset . ", " . $list_row_num;
			$num_sql = "SELECT COUNT(*) FROM test_log LEFT JOIN api_list ON test_log.api_id = api_list.api_id LEFT JOIN server_list ON test_log.server_id = server_list.server_id " . $search_where_clause;
		}
		elseif($_GET['column'] == "elapsed") {
			$elapsed_lowerbound = $_GET['elapsed_lowerbound'] * 1000000;
			$elapsed_upperbound = $_GET['elapsed_upperbound'] * 1000000;

			$sql = "SELECT * FROM test_log LEFT JOIN api_list ON test_log.api_id = api_list.api_id LEFT JOIN server_list ON  test_log.server_id = server_list.server_id WHERE CAST(elapsed_time_nano as UNSIGNED) >= ". $elapsed_lowerbound ." AND CAST(elapsed_time_nano as UNSIGNED) <= ". $elapsed_upperbound . " ORDER BY log_id DESC LIMIT " . $offset . ", " . $list_row_num;
			?>
			<script>
				console.log(<?php echo $sql; ?>);
			</script>
			<?php
			$num_sql = "SELECT COUNT(*) FROM test_log LEFT JOIN api_list ON test_log.api_id = api_list.api_id LEFT JOIN server_list ON  test_log.server_id = server_list.server_id WHERE CAST(elapsed_time_nano as UNSIGNED) >= ". $elapsed_lowerbound ." AND CAST(elapsed_time_nano as UNSIGNED) <= ". $elapsed_upperbound;
		}
		elseif($_GET['column'] == "date") {
			$date_start = $_GET['date-start'];
			$date_end = $_GET['date-end'];
			
			$sql = "SELECT * FROM test_log LEFT JOIN api_list ON test_log.api_id = api_list.api_id LEFT JOIN server_list ON test_log.server_id = server_list.server_id WHERE (STR_TO_DATE(request_time, '%d/%m/%Y %H:%i:%s') BETWEEN STR_TO_DATE('" . $date_start . "', '%Y-%m-%dT%H:%i') AND STR_TO_DATE('" . $date_end . "', '%Y-%m-%dT%H:%i')) ORDER BY log_id DESC LIMIT " . $offset . ", " . $list_row_num;
			$num_sql = "SELECT COUNT(*) FROM test_log WHERE (STR_TO_DATE(request_time, '%d/%m/%Y %H:%i:%s') BETWEEN STR_TO_DATE('" . $date_start . "', '%Y-%m-%dT%H:%i') AND STR_TO_DATE('" . $date_end . "', '%Y-%m-%dT%H:%i'))";
		}
		else {
			$sql = "SELECT * FROM test_log LEFT JOIN api_list ON test_log.api_id = api_list.api_id LEFT JOIN server_list ON test_log.server_id = server_list.server_id ORDER BY log_id DESC LIMIT " . $offset . ", " . $list_row_num;
			$num_sql = "SELECT COUNT(*) FROM test_log";	
		}
	}
	
	$result = mysqli_query($link, $sql);
	$num_row = mysqli_fetch_array(mysqli_query($link, $num_sql), MYSQL_NUM);
	$num_pages = max(floor(($num_row[0] - 1) / $list_row_num), 0);
	
	if(!$result || $page > $num_pages || $page < 0) {
		// Return error
		die(mysqli_error($link));
	}
	
	mysqli_close($link);
	?>
</head>
<body>
<!-- Main menu -->
<ul>
	<li><p onClick = "window.location.reload()"><img src = <?php echo '"' . $parrot_url . '"' ?> width = 24 />Test API Admin<img src = <?php echo '"' . $parrot_url . '"' ?> width = 24 /></p></li>
	<li><a <?php echo ($_GET['mode'] == 0 || $_GET['mode'] == null ? 'class = "active"' : ''); ?> href = "./index.php?mode=0">API</a></li>
	<li><a <?php echo ($_GET['mode'] == 1 ? 'class = "active"' : ''); ?> href = "./index.php?mode=1">Test API</a></li>
	<li><a <?php echo ($_GET['mode'] == 2 ? 'class = "active"' : ''); ?> href = "./index.php?mode=2">Server</a></li>
	<li><a <?php echo ($_GET['mode'] == 3 ? 'class = "active"' : ''); ?> href = "./index.php?mode=3">Log</a></li>
</ul>
<!-- Main page -->
<div style = "margin-left: 15%;padding: 1px 16px;">
<h1 style = "text-align: center"><img src = <?php echo '"' . $parrot_url . '"' ?> width = 48 onClick = "window.location.reload()"/>Test API Admin<img src = <?php echo '"' . $parrot_url . '"' ?> width = 48 onClick = "window.location.reload()" /></h1>
<!-- Main table -->
<table align = "center" border = 0 width = 1000 style = "border-collapse: collapse;">
	<tr>
		<td width = "100%">
			<form action = "./index.php" method = "GET">
				<input type = "hidden" name = "mode" value = <?php echo '"' . $mode . '"'; ?> />
				<?php
				if($mode == 0 || $mode == 1) {
				echo'
				<select style = "width: 15%" name = "column">
					<option value = "uri" ' . ($_GET['column'] == "uri" ? 'selected = "selected"' : '') . '>URI</option>
					<option value = "method" ' . ($_GET['column'] == "method" ? 'selected = "selected"' : '') . '>Method</option>
				</select>
				';
				}
				elseif($mode == 2) {
				echo'
				<select style = "width: 15%" name = "column">
					<option value = "server_name" ' . ($_GET['column'] == "server_name" ? 'selected = "selected"' : '') . '>Name</option>
					<option value = "server_url" ' . ($_GET['column'] == "server_url" ? 'selected = "selected"' : '') . '>URL</option>
					<option value = "server_ip" ' . ($_GET['column'] == "server_ip" ? 'selected = "selected"' : '') . '>IP</option>
				</select>
				';
				}
				elseif($mode == 3) {
				echo'
				<select style = "width: 15%" name = "column" onchange="change_search_field(this)">
					<option name = server_name value = "server_name" ' . ($_GET['column'] == "server_name" ? 'selected = "selected"' : '') . '>Server</option>
					<option name = method value = "method" ' . ($_GET['column'] == "method" ? 'selected = "selected"' : '') . '>Method</option>
					<option name = uri value = "uri" ' . ($_GET['column'] == "uri" ? 'selected = "selected"' : '') . '>URI</option>
					<option name = date value = "date" ' . ($_GET['column'] == "date" ? 'selected = "selected"' : '') . '>Date</option>
					<option name = response_code value = "response_code" ' . ($_GET['column'] == "response_code" ? 'selected = "selected"' : '') . '>Response</option>
					<option name = elapsed value = "elapsed" ' . ($_GET['column'] == "elapsed" ? 'selected = "selected"' : '') . '>Elapsed_time</option>
				</select>
				';
				}
				?>
				<div id = 'search_field' style = "display: inline">
					<?php 
						if($_GET['column'] == "date") {
					?>
					<input style = "width: 30%" type = "datetime-local" name = "date-start" value = "<?php echo $_GET['date-start']; ?>"/>~
					<input style = "width: 30%" type = "datetime-local" name = "date-end" value = "<?php echo $_GET['date-end']; ?>"/>
					<?php
						}
						elseif($_GET['column'] == "elapsed") {
					?>
					<input style = "width: 30%" type = "text" name = "elapsed_lowerbound" value = "<?php echo $_GET['elapsed_lowerbound']; ?>" />~
					<input style = "width: 30%" type = "text" name = "elapsed_upperbound" value = "<?php echo $_GET['elapsed_upperbound']; ?>" />
				
					<?php
						}
						else {
					?>
					<input style = "width: 60%" type = "text" name = "search_key" value = <?php echo '"' . $_GET['search_key'] . '"' ?> />
					<?php
						}
					?>
				</div>
				<button style = "width: 20%" type = "submit" class = "button">Search</button>
			</form>
		</td>
		<?php
		if($mode == 0 || $mode == 2) {
			echo '
		<td align = "right">
			<a href = "./insert_APITest.php?mode='. $mode .'"><img src = "' . $add_button_url . '" /></a>
		</td>';
		}
		?>
	</tr>
</table>
<table align = "center" border = 0 width = 1000 style = "border-collapse: collapse;">
	<?php
	if($mode == 0) {	
		// add table header
		$table_string = '
	<tr>
		<td width = "30%" style="padding: 8px;background-color: ' . $table_column_color . ';border-radius: 6px 0 0 0;">URI</td>
		<td width = "10%" style="padding: 8px;background-color: ' . $table_column_color . ';">Method</td>
		<td style = "padding: 8px;background-color: ' . $table_column_color . ';">Argument</td>
		<td width = "1%" style = "padding: 8px;background-color: ' . $table_column_color . ';">X</td>
		<td width = "1%" align = "center" style = "background-color: ' . $table_column_color . ';">Modify</td>
		<td width = "1%" align = "center" style = "background-color: ' . $table_column_color . ';border-radius: 0 6px 0 0;">Test</td>
	</tr>';
	
		// add entire API list
		for($i = 0; $i < mysqli_num_rows($result); $i++) {
			$color = ($i % 2 == 0 ? $table_row_color_dark : $table_row_color_light);
			$row = mysqli_fetch_array($result);
			$table_string = $table_string . '
	<tr height = "35">
		<td style = "background-color: ' . $color . ';">&nbsp;' . $row['uri'] . '</td>
		<td style = "background-color: ' . $color . ';">&nbsp;' . $row['method'] . '</td>
		<td style = "background-color: ' . $color . ';">&nbsp;' . $row['params'] . '</td>
		<td style = "background-color: ' . $color . ';"><a onclick="delete_row(' . $mode . ', ' . $row['api_id'] .')"><img src = "' . $x_button_url . '" /></a></td>
		<td align = "center" style = "background-color: ' . $color . ';"><a href = "./modify.php?mode=0&api_id=' . $row['api_id'] . '"><img src = "' . $modify_button_url . '" /></a></td>
		<td align = "center" style = "background-color: ' . $color . ';"><a href = "./add_test.php?api_id=' . $row['api_id'] . '"><img src="' . $check_button_url . '" /></a></td>
	</tr>';
		}
	}
	elseif($mode == 1) {
		// add table header
		$table_string = '
	<tr>
		<td width = "30%" style="padding: 8px;background-color: ' . $table_column_color . ';border-radius: 6px 0 0 0;">URI</td>
		<td width = "10%" style="padding: 8px;background-color: ' . $table_column_color . ';">Method</td>
		<td style = "padding: 8px;background-color: ' . $table_column_color . ';">Param</td>
		<td style = "padding: 8px;background-color: ' . $table_column_color . ';">I</td>
		<td style = "padding: 8px;background-color: ' . $table_column_color . ';">Period</td>
		<td style = "padding: 8px;background-color: ' . $table_column_color . ';">On</td>
		<td width = "1%" style="padding: 8px;background-color: ' . $table_column_color . ';">X</td>
		<td width = "1%" align = "center" style = "background-color: ' . $table_column_color . ';border-radius: 0 6px 0 0;">Modify</td>
	</tr>';
	
		// add test API list
		for($i = 0; $i < mysqli_num_rows($result); $i++) {
			$color = ($i % 2 == 0 ? $table_row_color_dark : $table_row_color_light);
			$row = mysqli_fetch_array($result);
			$table_string = $table_string . '
	<tr height = "35">
		<td style = "background-color: ' . $color . ';">&nbsp;' . $row['server_url'] . $row['uri'] . '</td>
		<td style = "background-color: ' . $color . ';">&nbsp;' . $row['method'] . '</td>
		<td style = "background-color: ' . $color . ';">&nbsp;' . $row['test_params'] . '</td>
		<td style = "background-color: ' . $color . ';">&nbsp;' . ($row['immediately'] == 1 ? "O" : "X") . '</td>
		<td style = "background-color: ' . $color . ';">&nbsp;' . prettyPeriod($row['period']) . '</td>
		<td style = "background-color: ' . $color . ';">&nbsp;' . ($row['is_running'] == 1 ? '<a href="./index.php?mode=1&page=' . $page . '&column=' . $_GET['column'] . '&search_key=' . $_GET['search_key'] . '&toggle=0&api_id=' . $row['test_api_id'] . '&uri=' . $row['server_url'] . $row['uri'] . '" ><img src = "' . $on_button_url . '" width = 28/></a>' : '<a href = "./index.php?mode=1&page=' . $page . '&column=' . $_GET['column'] . '&search_key=' . $_GET['search_key'] . '&toggle=1&api_id=' . $row['test_api_id'] . '&uri=' . $row['server_url'] . $row['uri'] . '"><img src = "'. $off_button_url .'" width = 28/></a>') . '</td>
		<td style = "background-color: ' . $color . ';"><a onclick="delete_row(' . $mode . ', ' . $row['test_api_id'] .')"><img src = "' . $x_button_url . '" width = 28/></a></td>
		<td align = "center" style = "background-color: ' . $color . ';"><a href = "./modify.php?mode=1&api_id=' . $row['test_api_id'] . '&uri=' . $row['server_url'] . $row['uri'] . '"><img src = "' . $modify_button_url . '" width = 28/></a></td>
	</tr>';
		}
	}
	elseif($mode == 2) {	
		// add table header
		$table_string = '
	<tr>
		<td width = "30%" style = "padding: 8px;background-color: ' . $table_column_color . ';border-radius: 6px 0 0 0;">Server Name</td>
		<td style = "padding: 8px;background-color: ' . $table_column_color . ';">Server URL</td>
		<td style = "padding: 8px;background-color: ' . $table_column_color . ';">Server IP</td>
		<td width = "1%"  style = "padding: 8px;background-color: ' . $table_column_color . ';border-radius: 0 6px 0 0;">X</td>
	</tr>';
	
		// add entire API list
		for($i = 0; $i < mysqli_num_rows($result); $i++) {
			$color = ($i % 2 == 0 ? $table_row_color_dark : $table_row_color_light);
			$row = mysqli_fetch_array($result);
			$table_string = $table_string . '
	<tr height = "35">
		<td style = "background-color: ' . $color . ';">&nbsp;' . $row['server_name'] . '</td>
		<td style = "background-color: ' . $color . ';">&nbsp;' . $row['server_url'] . '</td>
		<td style = "background-color: ' . $color . ';">&nbsp;' . $row['server_ip'] . '</td>
		<td style = "background-color: ' . $color . ';"><a onclick="delete_row(' . $mode . ', ' . $row['server_id'] .')"><img src = "' . $x_button_url . '" /></a></td>
	</tr>';
		}
	}
	elseif($mode == 3) {	
		// add table header
		$table_string = '
	<form action = "./index.php" method="GET">
	<input type = "hidden" name = "mode" value = "' . $mode . '" />
	<input type = "hidden" name = "page" value = "' . ($page - 1 >= 0 ? $page - 1 : 0) . '" />
	<input type = "hidden" name = "delete" value = "1" />
	<input type = "hidden" name = "search_key" value = "' . $_GET["search_key"] . '" />
	<input type = "hidden" name = "date-start" value = "' . $_GET["date-start"] . '" />
	<input type = "hidden" name = "date-end" value = "' . $_GET["date-end"] . '" />
	<input type = "hidden" name = "elapsed_lowerbound" value = "' . $_GET["elapsed_lowerbound"] . '" />
	<input type = "hidden" name = "elapsed_upperbound" value = "' . $_GET["elapsed_upperbound"] . '" />
	<input type = "hidden" name = "column" value = "' . $_GET["column"] . '" />
	
	<tr>
		<td width = "15%" style="padding: 8px;background-color: ' . $table_column_color . ';border-radius: 6px 0 0 0;">Server Name</td>
		<td style = "padding: 8px;background-color: ' . $table_column_color . ';">uri</td>
		<td style = "padding: 8px;background-color: ' . $table_column_color . ';">method	</td>
		<td style = "padding: 8px;background-color: ' . $table_column_color . ';">request time</td>
		<td style = "padding: 8px;background-color: ' . $table_column_color . ';">response time</td>
		<td style = "padding: 8px;background-color: ' . $table_column_color . ';">elapsed time</td>
		<td style = "padding: 8px;background-color: ' . $table_column_color . ';">response</td>
		<td width = "3%"  style="padding: 8px;border-radius: 0 6px 0 0;background-color: ' . $table_column_color . ';">
			<button type = "submit" class = "button">X</button>
		</td>
	</tr>
	';
		// add entire Log list
		for($i = 0; $i < mysqli_num_rows($result); $i++) {
			$color = ($i % 2 == 0 ? $table_row_color_dark : $table_row_color_light);
			$row = mysqli_fetch_array($result);
			$table_string = $table_string . '
	<tr height = "35">
		<td style = "background-color: ' . $color . ';">&nbsp;' . $row['server_name'] . '</td>
		<td style = "background-color: ' . $color . ';">&nbsp;' . $row['uri'] . '</td>
		<td style = "background-color: ' . $color . ';">&nbsp;' . $row['method'] . '</td>
		<td style = "background-color: ' . $color . ';">&nbsp;' . $row['request_time'] . '</td>
		<td style = "background-color: ' . $color . ';">&nbsp;' . $row['response_time'] . '</td>
		<td style = "background-color: ' . $color . ';">&nbsp;' . $row['elapsed_time_nano'] / 1000000 . '</td>
		<td style = "background-color: ' . $color . ';">&nbsp;' . $row['response_code'] . '</td>
		<td style = "background-color: ' . $color . '; text-align:center;"><input type = "checkbox" name = "delete_list[]" value = "' . $row["log_id"] . '" /></td>
	</tr>';
		}
		$table_string = $table_string . '</form>';
	}
	
	// echo actual string
	echo $table_string;
	
	mysqli_free_result($result);
	?>
</table>
<p></p>
<!-- Page numbering -->
<div align = "center" style = "-moz-user-select: none;-webkit-user-select: none;-ms-user-select: none;user-select: none;-o-user-select: none;">
	<?php
	// define search data
	$search_get_data = ($_GET['search_key'] != null ? '&column=' . $_GET['column'] . '&search_key=' . $_GET['search_key'] : '');
	
	// add Prev button
	if($_GET['column'] == "date") {
		$page_string = '
		<a href = "./index.php?mode=' . $mode . '&page=' . ($page - 1 >= 0 ? $page - 1 : 0) . '&column=date&date-start=' . $_GET['date-start'] . '&date-end=' . $_GET['date-end'] . '" style = "text-decoration:none"><img src="' . $left_button_url . '" /></a> ';
	}
	else if ($_GET['column'] == "elapsed") {
		$page_string = '
		<a href = "./index.php?mode=' . $mode . '&page=' . ($page - 1 >= 0 ? $page - 1 : 0) . '&column=elapsed&elapsed_lowerbound=' . $_GET['elapsed_lowerbound'] . '&elapsed_upperbound=' . $_GET['elapsed_upperbound'] .'" style = "text-decoration:none"><img src="' . $left_button_url . '" /></a> ';
	}
	else {
		$page_string = '
		<a href = "./index.php?mode=' . $mode . '&page=' . ($page - 1 >= 0 ? $page - 1 : 0) . $search_get_data .'" style = "text-decoration:none"><img src="' . $left_button_url . '" /></a> ';
	}
	// add page number
	for($i = floor($page / $page_list_num) * $page_list_num; $i < min($num_pages + 1, floor($page / $page_list_num) * $page_list_num + $page_list_num); $i++) {
		if($i != $page) {
			if($_GET['column'] == "date") {
				$page_string = $page_string . '
				<a href = "./index.php?mode=' . $mode . '&page=' . $i . '&column=date&date-start=' . $_GET['date-start'] . '&date-end=' . $_GET['date-end'] . '" style = "text-decoration: none;color: blue">' . ($i + 1) . ' </a>';
			}
			else if ($_GET['column'] == "elapsed") {
				$page_string = $page_string . '
				<a href = "./index.php?mode=' . $mode . '&page=' . $i . '&column=elapsed&elapsed_lowerbound=' . $_GET['elapsed_lowerbound'] . '&elapsed_upperbound=' . $_GET['elapsed_upperbound'] . '" style = "text-decoration: none;color: blue">' . ($i + 1) . ' </a>';
			}
			else {
				$page_string = $page_string . '
				<a href = "./index.php?mode=' . $mode . '&page=' . $i . $search_get_data . '" style = "text-decoration: none;color: blue">' . ($i + 1) . ' </a>';		
			}
		}
		else {
			$page_string = $page_string . 
	($i + 1) . ' ';
		}
	}
	// add Next button
	if($_GET['column'] == "date") {
		$page_string = $page_string . '
		<a href = "./index.php?mode=' . $mode . '&page=' . ($page + 1 <= $num_pages ? $page + 1 : $num_pages) . '&column=date&date-start=' . $_GET['date-start'] . '&date-end=' . $_GET['date-end'] . '" style = "text-decoration: none"><img src = "' . $right_button_url . '"/></a>';	
	}
	else if ($_GET['column'] == "elapsed") {
		$page_string = $page_string . '
		<a href = "./index.php?mode=' . $mode . '&page=' . ($page + 1 <= $num_pages ? $page + 1 : $num_pages) . '&column=elapsed&elapsed_lowerbound=' . $_GET['elapsed_lowerbound'] . '&elapsed_upperbound=' . $_GET['elapsed_upperbound'] . '" style = "text-decoration: none"><img src = "' . $right_button_url . '"/></a>';
	}
	else {
		$page_string = $page_string . '
		<a href = "./index.php?mode=' . $mode . '&page=' . ($page + 1 <= $num_pages ? $page + 1 : $num_pages) . $search_get_data . '" style = "text-decoration: none"><img src = "' . $right_button_url . '"/></a>';
	}
	// echo actual page string
	echo $page_string;
	?>
</div>
</div>
</body>
</html>
