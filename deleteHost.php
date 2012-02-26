<?php
require("lmapi.php");

$lm = new logicMonitor($config);

if($lm === false) { die("Error"); }

function showUsage(&$argv)
{
	echo($argv[0] . " <hostName or hostId>\n");
}

if($argc != 2) {
  showUsage($argv);
  exit(1);
}

$results = $lm->deleteHost($argv[1]);

echo(json_encode($results));

?>
