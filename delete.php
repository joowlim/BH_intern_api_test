<?php

include("./db_account_info.php");

$mode = $_GET['mode'];
$id = $_GET['id'];

$link = mysqli_connect($db_server, $db_user, $db_password, $db_schema);
mysqli_set_charset($link, 'utf8');

if($mode == 0) {
	$sql = "DELETE FROM api_list WHERE api_id=" . $id;
}
else if($mode == 1) {
	$sql = "DELETE FROM test_api_list WHERE test_api_id=" . $id;
}
else if($mode == 2) {
	$sql = "DELETE FROM server_list WHERE server_id=" . $id;
}
else if($mode == 3) {
	$sql = "DELETE FROM test_log WHERE log_id=" . $id;
}
else {
	echo 0;
	return;
}
mysqli_query($link, $sql);
if(mysqli_affected_rows($link) == 1) {
	echo 1;
}
else {
	echo 0;
}
?>