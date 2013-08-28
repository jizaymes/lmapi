<?php
require("../config.php");
require("../lmapi.php");

$lm = new logicMonitor($config);

if($lm === false) { die("Error"); }

function showUsage(&$argv)
{
	echo($argv[0] . " <hostName> <displayName> <description> <agentId> <hostGroupId>\n");
}

if($argc != 6) {
  showUsage($argv);
  exit(1);
}

$results = $lm->addHost($argv[1],$argv[2],$argv[3],$argv[4],$argv[5]);

echo(json_encode($results));

?>
