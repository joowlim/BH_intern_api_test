<?php
	function insertCommand($crontab_list, $command)
	{
		if ($crontab_list == ""){
			exec('echo "'. $command . '" |crontab -');
		}
		else
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
