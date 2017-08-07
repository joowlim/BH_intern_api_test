<?php
	include('./db_account_info.php');

	if(isset($_POST['submit']))
	{
		// process user config
		$pattern = array();
		$pattern[0] = "/server(.*)/";
		$pattern[1] = "/user(.*)/";
		$pattern[2] = "/password(.*)/";
		$pattern[3] = "/schema(.*)/";
		$pattern[4] = "/java_path(.*)/";
		$pattern[5] = "/jar_path(.*)/";
		$replacement = array();
		$replacement[0] = "server = " . $_POST['server'];
		$replacement[1] = "user = " . $_POST['user'];
		$replacement[2] = "password = " . $_POST['password'];
		$replacement[3] = "schema = " . $_POST['schema'];
		$replacement[4] = "java_path = " . $_POST['java_path'];
		$replacement[5] = "jar_path = " . $_POST['jar_path'];
		
		$contents = file_get_contents("./user_config.ini");
		$contents = preg_replace($pattern, $replacement, $contents);
		
		$ini_file = fopen("./user_config.ini", "w");
		fwrite($ini_file, $contents);
		fclose($ini_file);
		
		header("Location: " . (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . substr($_SERVER['REQUEST_URI'], 0, -11) . "index.php");
		exit();
	}
?>

<!DOCTYPE html>
<html>
<head>
	<title>TEST API Admin Setup</title>
</head>
<body>
<div align="center">
	<h1>Database connection configuration</h1>
	<form method = "POST">
		<table style = "border-collapse: collapse;" border = 1 >
			<tr>
				<td>Server Address </td>
				<td><input type = "text" name = "server" value = "<?php echo $db_server; ?>" /></td>
			</tr>
			<tr>
				<td>MySQL DB ID </td>
				<td><input type = "text" name = "user" value = "<?php echo $db_user; ?>" /></td>
			</tr>
			<tr>
				<td>MySQL DB Password </td>
				<td><input type = "password" name = "password" value = "<?php echo $db_password; ?>" /></td>
			</tr>
			<tr>
				<td>MySQL DB Schema </td>
				<td><input type = "text" name = "schema" value = "<?php echo $db_schema; ?>" /></td>
			</tr>
			<tr>
				<td>Java Executable Path (Absolute Path) </td>
				<td><input type = "text" name = "java_path" value = "<?php echo $java_path; ?>" /></td>
			</tr>
			<tr>
				<td>CURLtest_fat.jar Path (Absolute Path) </td>
				<td><input type = "text" name = "jar_path" value = "<?php echo getCWD(); ?>/CURLtest_fat.jar" /></td>
			</tr>
			<tr>
				<td colspan = 2><input type = "submit" value = "설정 완료" name = "submit" style = "width: 100%" /></td>
			</tr>
		</table>
	</form>
</div>
<body>
</html>