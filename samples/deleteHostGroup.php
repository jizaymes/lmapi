<?php
require("../config.php");
require("../lmapi.php");

$lm = new logicMonitor($config);

if($lm === false) { die("Error"); }

function showUsage(&$argv)
{
	echo($argv[0] . " <hostGroupName or hostGroupId>\n");
}

if($argc != 2) {
  showUsage($argv);
  exit(1);
}

$results = $lm->deleteHostGroup($argv[1]);

echo(json_encode($results));

?>
