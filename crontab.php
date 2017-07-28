<?php
	function insertCommand($command)
	{
		exec('crontab -l | { cat; echo "'. $command . '"; } | crontab -');
	}
	function deleteCommand($command)
	{
		exec('crontab -l | grep -F -v "' . $command . '" | crontab -');
	}
	function modifyCommand($old_command, $new_command)
	{
		exec('crontab -l | { grep -F -v "' . $old_command . '"; echo "'. $new_command .'" ; } | crontab -');
	}
	function resetCommand()
	{
		exec('crontab -r');
	}
?>
