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
		display: inline-block;
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

	// Delete rows by delete button
	if($_GET['delete'] != null)
	{
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
			echo '<script>alert("Deleted")</script>';
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
		if($t_row['immediately'] == 1)
		{
			$sql = "UPDATE test_api_list SET is_running = ". $_GET['toggle'] ." WHERE test_api_id = " . $_GET['api_id'];
			
			$crontab_list = exec("crontab -l");

			$new_command = $t_row['period'] . "/jdk1.8.0_131/bin/java -jar " . $jar_path . " " . $_GET['uri'] . " " . $t_row['method'] . " " . $_GET['api_id'] . " ";
			
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
			
			echo $new_command;
			system($new_command, $ret);
			
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
<h1 style="text-align: center"><img src="./img/parrot_reading.gif" width = 48 onClick="window.location.reload()"/>Test API Admin<img src="./img/parrot_reading.gif" width = 48 onClick="window.location.reload()"/></h1>
<!-- Main table -->
<table align="center" border=0 width = 1000 style = "border-collapse: collapse;">
	<tr>
		<form action = "./index.php" method = "GET">
		<td width = "27%">
				<button type="submit" value = 0 name="mode" class = "button">API List</button>
				<button type="submit" value = 1 name="mode" class = "button">Test API List</button>
				<button type="submit" value = 2 name="mode" class = "button">Server List</button>
		</td>
		</form>
		<td align = "right">
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
				?>
				<input style = "width:55%" type = "text" name = "search_key" value = "<?php echo $_GET['search_key'] ?>"/>
				<button style = "width:20%" type = "submit" class = "button">Search</button>
			</form>
		</td>
		<?php
		if($mode != 1)
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
		<td width = "30%" style="padding: 8px;background-color: #AAAABA;">URI</td>
		<td width = "10%" style="padding: 8px;background-color: #AAAABA;">Method</td>
		<td style="padding: 8px;background-color: #AAAABA;">Argument</td>
		<td width = "1%" style="padding: 8px;background-color: #AAAABA;">X</td>
		<td width = "1%" align = "center =" style="background-color: #AAAABA;">Modify</td>
		<td width = "1%" align = "center =" style="background-color: #AAAABA;">Test</td>
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
		<td width = "30%" style="padding: 8px;background-color: #AAAABA;">URI</td>
		<td width = "10%" style="padding: 8px;background-color: #AAAABA;">Method</td>
		<td style="padding: 8px;background-color: #AAAABA;">Param</td>
		<td style="padding: 8px;background-color: #AAAABA;">I</td>
		<td style="padding: 8px;background-color: #AAAABA;">Period</td>
		<td style="padding: 8px;background-color: #AAAABA;">On</td>
		<td width = "1%" style="padding: 8px;background-color: #AAAABA;">X</td>
		<td width = "1%" align = "center" style="background-color: #AAAABA;">Modify</td>
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
		<td style="background-color: '. $color .';">&nbsp;'. ($row['immediately'] == 1 ? "X" : "O") .'</td>
		<td style="background-color: '. $color .';">&nbsp;'. $row['period'] .'</td>
		<td style="background-color: '. $color .';">&nbsp;'. ($row['is_running'] == 1 ? '<a href="./index.php?mode=1&page='.$page.'&column='.$_GET['column'].'&search_key='.$_GET['search_key'].'&toggle=0&api_id='.$row['test_api_id'].'&uri='.$row['server_url'] . '/' . $row['uri'].'" ><img src="./img/on.png" width = 28/></a>' : '<a href="./index.php?mode=1&page='.$page.'&column='.$_GET['column'].'&search_key='.$_GET['search_key'].'&toggle=1&api_id='.$row['test_api_id'].'&uri='.$row['server_url'] . '/' . $row['uri'].'"><img src="./img/off.png" width = 28/></a>') .'</td>
		<td style="background-color: '. $color .';"><a href = "./index.php?mode=1&delete='.$row['test_api_id'].'&page='.$page.'"><img src="./img/x.png" href="./" width = 28/></a></td>
		<td align = "center" style="background-color: '. $color .';"><a href = "./modify.php?mode=1&api_id='.$row['test_api_id'].'"><img src="./img/modify.png" href="./" width = 28/></a></td>
	</tr>';
		}
	}
	elseif($mode == 2)
	{	
		// add table header
		$table_string = '
	<tr>
		<td width = "30%" style="padding: 8px;background-color: #AAAABA;">Server Name</td>
		<td style="padding: 8px;background-color: #AAAABA;">Server URL</td>
		<td style="padding: 8px;background-color: #AAAABA;">Server IP</td>
		<td width = "1%"  style="padding: 8px;background-color: #AAAABA;">X</td>
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
	
	// echo actual string
	echo $table_string;
	
	mysqli_free_result($result);
	?>
</table>
<p></p>
<!-- Page numbering -->
<div align = "center">
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
</body>
</html>
