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
		-ms-user-select:none;
		user-select:none;
		-o-user-select:none;
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
		-ms-user-select:none;
		user-select:none;
		-o-user-select:none;
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
	</style>
	<?php

	// Define global variables
	include("./config.php");
	// Define crontab functions
	include("./crontab.php");
	
	// Connect to the db
	$link = mysqli_connect('localhost', 'root', 'root', 'API_TEST');
	mysqli_set_charset($link, 'utf8');

	$mode = 0;

	function delete_test_api($test_api_id, $insert, $uri)
	{
		global $link, $jar_path;
		$t_sql = "SELECT * FROM test_api_list, api_list WHERE test_api_id = " . $test_api_id . " AND test_api_list.api_id = api_list.api_id";
		$t_result = mysqli_query($link, $t_sql);
		$t_row = mysqli_fetch_array($t_result, MYSQL_ASSOC);
		
		$crontab_list = exec("crontab -l");

		$new_command = $t_row['period'] . "/jdk1.8.0_131/bin/java -jar " . $jar_path . " " . $uri . " " . $t_row['method'] . " " . $test_api_id;

		if ($insert == 1)
		{
			insertCommand($crontab_list, $new_command);	
			echo '<script>alert("api insert successed")</script>';
		}
	 	else
	 	{
			deleteCommand($new_command);
			echo '<script>alert("api delete successed")</script>';
	 	}
	 	
	}

	// Delete rows by delete button
	if($_GET['delete'] != null)
	{
		# Remove data from crontab
		if($_GET['mode'] == 1)
		{
			delete_test_api($_GET['delete'], 0, $_GET['uri']);
		}
		// Remove data from db
		if($_GET['mode'] == 0)
		{
			$sql = "DELETE FROM api_list WHERE api_id = " . $_GET['delete'];
		}
		elseif($_GET['mode'] == 1)
		{
			$sql = "DELETE FROM test_api_list WHERE test_api_id = " . $_GET['delete'];
		}
		elseif($_GET['mode'] == 2)
		{
			$sql = "DELETE FROM server_list WHERE server_id = " . $_GET['delete'];
		}
	
		mysqli_query($link, $sql);
		
		if(mysqli_affected_rows($link) == 1)
		{
			echo '<script>alert("Deleted api")</script>';
		}
		else
		{
			echo '<script>alert("Failed to delete")</script>';
		}
	}
	
	// Toggle by toggle button
	if($_GET['toggle'] != null && $_GET['mode'] == 1)
	{

		$t_sql = "SELECT * FROM test_api_list, api_list WHERE test_api_id = " . $_GET['api_id'] . " AND test_api_list.api_id = api_list.api_id";
		$t_result = mysqli_query($link, $t_sql);
		$t_row = mysqli_fetch_array($t_result, MYSQL_ASSOC);
		
		// Periodical work, register on the crontab
		if($t_row['immediately'] == 0)
		{
			$sql = "UPDATE test_api_list SET is_running = ". $_GET['toggle'] ." WHERE test_api_id = " . $_GET['api_id'];
			
			$crontab_list = exec("crontab -l");

			$new_command = $t_row['period'] . "/jdk1.8.0_131/bin/java -jar " . $jar_path . " " . $_GET['uri'] . " " . $t_row['method'] . " " . $_GET['api_id'];
			
			if($_GET['toggle'] == 0)
			{
				deleteCommand($new_command);
			}
			else
			{
				insertCommand($crontab_list, $new_command);
			}
			
			mysqli_query($link, $sql);
			
			if(mysqli_affected_rows($link) == 1)
			{
				if($_GET['toggle'] == 1)
				{
					echo '<script>alert("Test registered")</script>';
				}
				else
				{
					echo '<script>alert("Test unregistered")</script>';
				}
			}
			else
			{
				echo '<script>alert("Failed to update")</script>';
			}
		}
		// Execute only one time
		else
		{
			$new_command = "/jdk1.8.0_131/bin/java -jar " . $jar_path . " " . $_GET['uri'] . " " . $t_row['method'] . " " . $_GET['api_id'];
			
			exec($new_command, $ret);
			
			if($ret == 0)
			{
					echo '<script>alert("Test successfully executed")</script>';
			}
			else
			{
					echo '<script>alert("Test Failed : "'. $ret .')</script>';
			}
		}
	}
	// Mode changing
	if($_GET['mode'] == null || $_GET['mode'] == 0){
		// Show entire api list
		$mode = 0;
		$page = ($_GET['page'] == null ? 0 : $_GET['page']);
		$offset = $page * $list_row_num;
		
		// Search by the key
		if($_GET['search_key'] != null)
		{
			$search_where_clause = "WHERE " . $_GET['column'] . " LIKE '%" .$_GET['search_key'] ."%'";
		}
		
		$sql = "SELECT * FROM api_list " . $search_where_clause . " ORDER BY api_id DESC LIMIT ". $offset .", " . $list_row_num;
		$num_sql = "SELECT COUNT(*) FROM api_list " . $search_where_clause;
	}
	elseif($_GET['mode'] == 1){
		// Show test api list
		$mode = 1;
		$page = ($_GET['page'] == null ? 0 : $_GET['page']);
		$offset = $page * $list_row_num;
		
		// Search by the key
		if($_GET['search_key'] != null)
		{
			$search_where_clause = $_GET['column'] . " LIKE '%" .$_GET['search_key'] ."%'";
		}
		
		$sql = "SELECT * FROM api_list, server_list, test_api_list WHERE api_list.api_id = test_api_list.api_id AND " .
		"server_list.server_id = test_api_list.server_id" . ($_GET['search_key'] == null ? '' : ' AND (' . $search_where_clause . ' OR server_url LIKE "%'. $_GET['search_key'] .'%")') ." ORDER BY test_api_id DESC LIMIT ". $offset .", " . $list_row_num;
		$num_sql = "SELECT COUNT(*) FROM test_api_list" . ($_GET['search_key'] == null ? '' : ' WHERE ' . $search_where_clause);
	}
	elseif($_GET['mode'] == 2){
		// Show entire api list
		$mode = 2;
		$page = ($_GET['page'] == null ? 0 : $_GET['page']);
		$offset = $page * $list_row_num;
		
		// Search by the key
		if($_GET['search_key'] != null)
		{
			$search_where_clause = "WHERE " . $_GET['column'] . " LIKE '%" .$_GET['search_key'] ."%'";
		}
		
		$sql = "SELECT * FROM server_list " . $search_where_clause . " ORDER BY server_id DESC LIMIT ". $offset .", " . $list_row_num;
		$num_sql = "SELECT COUNT(*) FROM server_list " . $search_where_clause;
	}
	elseif($_GET['mode'] == 3){
		// Show Logs
		$mode = 3;
		$page = ($_GET['page'] == null ? 0 : $_GET['page']);
		$offset = $page * $list_row_num;
		
		// Search by the key
		if($_GET['search_key'] != null)
		{
			$search_where_clause = "WHERE " . $_GET['column'] . " LIKE '%" .$_GET['search_key'] ."%'";
			
			$sql = "SELECT * FROM test_log, api_list, server_list " . $search_where_clause . " AND test_log.api_id = api_list.api_id AND test_log.server_id = server_list.server_id ORDER BY log_id DESC LIMIT ". $offset .", " . $list_row_num;
			$num_sql = "SELECT COUNT(*) FROM test_log " . $search_where_clause;
		}
		else
		{
			$sql = "SELECT * FROM test_log, api_list, server_list WHERE test_log.api_id = api_list.api_id AND test_log.server_id = server_list.server_id ORDER BY log_id DESC LIMIT ". $offset .", " . $list_row_num;
			$num_sql = "SELECT COUNT(*) FROM test_log";	
		}
	}
	
	$result = mysqli_query($link, $sql);
	$num_row = mysqli_fetch_array(mysqli_query($link, $num_sql), MYSQL_NUM);
	$num_pages = max(floor(($num_row[0] - 1) / $list_row_num), 0);
	
	if(!$result || $page > $num_pages || $page < 0)
	{
		// Return error
		die(mysqli_error($link));
	}
	
	mysqli_close($link);
	?>
</head>
<body>
<!-- Main menu -->
<ul>
	<li><p onClick="window.location.reload()"><img src="./img/parrot_reading.gif" width = 24/>Test API Admin<img src="./img/parrot_reading.gif" width = 24/></p></li>
	<li><a <?php echo ($_GET['mode'] == 0 || $_GET['mode'] == null ? 'class="active"' : ''); ?> href="./index.php?mode=0">API</a></li>
	<li><a <?php echo ($_GET['mode'] == 1 ? 'class="active"' : ''); ?>href="./index.php?mode=1">Test API</a></li>
	<li><a <?php echo ($_GET['mode'] == 2 ? 'class="active"' : ''); ?>href="./index.php?mode=2">Server</a></li>
	<li><a <?php echo ($_GET['mode'] == 3 ? 'class="active"' : ''); ?>href="./index.php?mode=3">Log</a></li>
</ul>
<!-- Main page -->
<div style="margin-left:15%;padding:1px 16px;">
<h1 style="text-align: center"><img src="./img/parrot_reading.gif" width = 48 onClick="window.location.reload()"/>Test API Admin<img src="./img/parrot_reading.gif" width = 48 onClick="window.location.reload()"/></h1>
<!-- Main table -->
<table align="center" border=0 width = 1000 style = "border-collapse: collapse;">
	<tr>
		<td width = "100%">
			<form action = "./index.php" method = "GET">
				<input type = "hidden" name = "mode" value = "<?php echo $mode; ?>" />
				<?php
				if($mode == 0 || $mode == 1)
				{
				echo'
				<select style = "width:15%" name = "column">
					<option value = "uri" ' . ($_GET['column'] == "uri" ? 'selected="selected"' : '') . '>URI</option>
					<option value = "method" ' . ($_GET['column'] == "method" ? 'selected="selected"' : '') . '>Method</option>
				</select>
				';
				}
				elseif($mode == 2)
				{
				echo'
				<select style = "width:15%" name = "column">
					<option value = "server_name" ' . ($_GET['column'] == "server_name" ? 'selected="selected"' : '') . '>Name</option>
					<option value = "server_url" ' . ($_GET['column'] == "server_url" ? 'selected="selected"' : '') . '>URL</option>
					<option value = "server_ip" ' . ($_GET['column'] == "server_ip" ? 'selected="selected"' : '') . '>IP</option>
				</select>
				';
				}
				elseif($mode == 3)
				{
				echo'
				<select style = "width:15%" name = "column">
					<option value = "server_name" ' . ($_GET['column'] == "server_name" ? 'selected="selected"' : '') . '>Server</option>
					<option value = "method" ' . ($_GET['column'] == "server_url" ? 'selected="selected"' : '') . '>Method</option>
					<option value = "uri" ' . ($_GET['column'] == "server_ip" ? 'selected="selected"' : '') . '>URI</option>
				</select>
				';
				}
				?>
				<input style = "width:60%" type = "text" name = "search_key" value = "<?php echo $_GET['search_key'] ?>"/>
				<button style = "width:20%" type = "submit" class = "button">Search</button>
			</form>
		</td>
		<?php
		if($mode == 0 || $mode == 2)
		{
			echo '
		<td align = "right">
			<a href = "./insert_APITest.php?mode='. $mode .'"><img src = "./img/add.png"/></a>
		</td>';
		}
		?>
	</tr>
</table>
<table align="center" border=0 width = 1000 style = "border-collapse: collapse;">
	<?php
	if($mode == 0)
	{	
		// add table header
		$table_string = '
	<tr>
		<td width = "30%" style="padding: 8px;background-color: #AAAABA;border-radius: 6px 0 0 0;">URI</td>
		<td width = "10%" style="padding: 8px;background-color: #AAAABA;">Method</td>
		<td style="padding: 8px;background-color: #AAAABA;">Argument</td>
		<td width = "1%" style="padding: 8px;background-color: #AAAABA;">X</td>
		<td width = "1%" align = "center =" style="background-color: #AAAABA;">Modify</td>
		<td width = "1%" align = "center =" style="background-color: #AAAABA;border-radius: 0 6px 0 0;">Test</td>
	</tr>';

		// add entire API list
		for($i = 0; $i < mysqli_num_rows($result); $i++)
		{
			$color = ($i % 2 == 0 ? '#DDDDEA' : '#EEEEFA');
			$row = mysqli_fetch_array($result);
			$table_string = $table_string . '
	<tr>
		<td style="background-color: '. $color .';">&nbsp;'. $row['uri'] .'</td>
		<td style="background-color: '. $color .';">&nbsp;'. $row['method'] .'</td>
		<td style="background-color: '. $color .';">&nbsp;'. $row['params'] .'</td>
		<td style="background-color: '. $color .';"><a href = "./index.php?mode=0&delete='.$row['api_id'].'&page='.$page.'"><img src="./img/x.png" href="./"/></a></td>
		<td align = "center" style="background-color: '. $color .';"><a href = "./modify.php?mode=0&api_id='.$row['api_id'].'"><img src="./img/modify.png" href="./"/></a></td>
		<td align = "center" style="background-color: '. $color .';"><a href = "./add_test.php?api_id='.$row['api_id'].'"><img src="./img/check.png" href="./"/></a></td>
	</tr>';
		}
	}
	elseif($mode == 1)
	{
		// add table header
		$table_string = '
	<tr>
		<td width = "30%" style="padding: 8px;background-color: #AAAABA;border-radius: 6px 0 0 0;">URI</td>
		<td width = "10%" style="padding: 8px;background-color: #AAAABA;">Method</td>
		<td style="padding: 8px;background-color: #AAAABA;">Param</td>
		<td style="padding: 8px;background-color: #AAAABA;">I</td>
		<td style="padding: 8px;background-color: #AAAABA;">Period</td>
		<td style="padding: 8px;background-color: #AAAABA;">On</td>
		<td width = "1%" style="padding: 8px;background-color: #AAAABA;">X</td>
		<td width = "1%" align = "center" style="background-color: #AAAABA;border-radius: 0 6px 0 0;">Modify</td>
	</tr>';
	
		// add test API list
		for($i = 0; $i < mysqli_num_rows($result); $i++)
		{
			$color = ($i % 2 == 0 ? '#DDDDEA' : '#EEEEFA');
			$row = mysqli_fetch_array($result);
			$table_string = $table_string . '
	<tr>
		<td style="background-color: '. $color .';">&nbsp;'. $row['server_url'] . '/' . $row['uri'] . '</td>
		<td style="background-color: '. $color .';">&nbsp;'. $row['method'] .'</td>
		<td style="background-color: '. $color .';">&nbsp;'. $row['test_params'] .'</td>
		<td style="background-color: '. $color .';">&nbsp;'. ($row['immediately'] == 1 ? "O" : "X") .'</td>
		<td style="background-color: '. $color .';">&nbsp;'. $row['period'] .'</td>
		<td style="background-color: '. $color .';">&nbsp;'. ($row['is_running'] == 1 ? '<a href="./index.php?mode=1&page='.$page.'&column='.$_GET['column'].'&search_key='.$_GET['search_key'].'&toggle=0&api_id='.$row['test_api_id'].'&uri='.$row['server_url'] . '/' . $row['uri'].'" ><img src="./img/on.png" width = 28/></a>' : '<a href="./index.php?mode=1&page='.$page.'&column='.$_GET['column'].'&search_key='.$_GET['search_key'].'&toggle=1&api_id='.$row['test_api_id'].'&uri='.$row['server_url'] . '/' . $row['uri'].'"><img src="./img/off.png" width = 28/></a>') .'</td>
		<td style="background-color: '. $color .';"><a href = "./index.php?mode=1&delete='.$row['test_api_id'].'&page='.$page.'&uri='.$row['server_url'] . '/' . $row['uri'].'"><img src="./img/x.png" href="./" width = 28/></a></td>
		<td align = "center" style="background-color: '. $color .';"><a href = "./modify.php?mode=1&api_id='.$row['test_api_id'].'"><img src="./img/modify.png" href="./" width = 28/></a></td>
	</tr>';
		}
	}
	elseif($mode == 2)
	{	
		// add table header
		$table_string = '
	<tr>
		<td width = "30%" style="padding: 8px;background-color: #AAAABA;border-radius: 6px 0 0 0;">Server Name</td>
		<td style="padding: 8px;background-color: #AAAABA;">Server URL</td>
		<td style="padding: 8px;background-color: #AAAABA;">Server IP</td>
		<td width = "1%"  style="padding: 8px;background-color: #AAAABA;border-radius: 0 6px 0 0;">X</td>
	</tr>';
	
		// add entire API list
		for($i = 0; $i < mysqli_num_rows($result); $i++)
		{
			$color = ($i % 2 == 0 ? '#DDDDEA' : '#EEEEFA');
			$row = mysqli_fetch_array($result);
			$table_string = $table_string . '
	<tr>
		<td style="background-color: '. $color .';">&nbsp;'. $row['server_name'] .'</td>
		<td style="background-color: '. $color .';">&nbsp;'. $row['server_url'] .'</td>
		<td style="background-color: '. $color .';">&nbsp;'. $row['server_ip'] .'</td>
		<td style="background-color: '. $color .';"><a href = "./index.php?mode=2&delete='.$row['server_id'].'&page='.$page.'"><img src="./img/x.png" href="./"/></a></td>
	</tr>';
		}
	}
	elseif($mode == 3)
	{	
		// add table header
		$table_string = '
	<tr>
		<td width = "20%" style="padding: 8px;background-color: #AAAABA;border-radius: 6px 0 0 0;">Server Name</td>
		<td style="padding: 8px;background-color: #AAAABA;">uri</td>
		<td style="padding: 8px;background-color: #AAAABA;">method	</td>
		<td style="padding: 8px;background-color: #AAAABA;">request time</td>
		<td style="padding: 8px;background-color: #AAAABA;">response time</td>
		<td style="padding: 8px;background-color: #AAAABA;">elapsed time</td>
		<td style="padding: 8px;background-color: #AAAABA;">response</td>
		<td width = "1%"  style="padding: 8px;background-color: #AAAABA;border-radius: 0 6px 0 0;">X</td>
	</tr>';
	
		// add entire API list
		for($i = 0; $i < mysqli_num_rows($result); $i++)
		{
			$color = ($i % 2 == 0 ? '#DDDDEA' : '#EEEEFA');
			$row = mysqli_fetch_array($result);
			$table_string = $table_string . '
	<tr>
		<td style="background-color: '. $color .';">&nbsp;'. $row['server_name'] .'</td>
		<td style="background-color: '. $color .';">&nbsp;'. $row['uri'] .'</td>
		<td style="background-color: '. $color .';">&nbsp;'. $row['method'] .'</td>
		<td style="background-color: '. $color .';">&nbsp;'. $row['request_time'] .'</td>
		<td style="background-color: '. $color .';">&nbsp;'. $row['response_time'] .'</td>
		<td style="background-color: '. $color .';">&nbsp;'. $row['elapsed_time_nano'] .'</td>
		<td style="background-color: '. $color .';">&nbsp;'. $row['response_code'] .'</td>
		<td style="background-color: '. $color .';"><a href = "./index.php?mode=3&delete='.$row['log_id'].'&page='.$page.'"><img src="./img/x.png" /></a></td>
	</tr>';
		}
	}
	
	// echo actual string
	echo $table_string;
	
	mysqli_free_result($result);
	?>
</table>
<p></p>
<!-- Page numbering -->
<div align = "center" style="-moz-user-select: none;-webkit-user-select: none;-ms-user-select:none;user-select:none;-o-user-select:none;">
	<?php
	// define search data
	$search_get_data = ($_GET['search_key'] != null ? '&column=' . $_GET['column'] . '&search_key=' . $_GET['search_key'] : '');
	
	// add Prev button
	$page_string = '
		<a href="./index.php?mode='.$mode.'&page=' .($page - 1 >= 0 ? $page - 1 : 0). $search_get_data .'" style="text-decoration:none"><img src="./img/left.png" /></a> ';

	// add page number
	for($i = floor($page / $page_list_num) * $page_list_num; $i < min($num_pages + 1, floor($page / $page_list_num) * $page_list_num + $page_list_num); $i++)
	{
		if($i != $page)
		{
			$page_string = $page_string . '
		<a href="./index.php?mode='.$mode.'&page='. $i . $search_get_data . '" style="text-decoration:none;color:blue">'. $i .' </a>';
		}
		else
		{
			$page_string = $page_string .
		$i . ' ';
		}
	}
	// add Next button
	$page_string = $page_string . '
		<a href="./index.php?mode='.$mode.'&page=' .($page + 1 <= $num_pages ? $page + 1 : $num_pages) . $search_get_data . '" style="text-decoration:none"><img src="./img/right.png"/></a>';
	
	// echo actual page string
	echo $page_string;
	?>
</div>
</div>
</body>
</html>
