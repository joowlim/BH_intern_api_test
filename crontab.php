<?php
	function insertCommand($crontab_list, $command)
	{
		exec('crontab -l | { cat; echo "'. $command . '"; } | crontab -');
	}
	function deleteCommand($command)
	{
		exec('crontab -l | grep -F -v "' . $command . '" | crontab -');
	}
	function resetCommand()
	{
		exec('crontab -r');
	}
?>
