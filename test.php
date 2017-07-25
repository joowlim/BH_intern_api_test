<?php

	$res = system("/jdk1.8.0_131/bin/java -jar /var/www/html/CURLtest_fat.jar http://localhost GET 6", $ret);
	
	echo $res;
	echo $ret;
?>