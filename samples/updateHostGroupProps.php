<?php
require(dirname(__FILE__) . '/../' . 'config.php');
require(dirname(__FILE__) . '/../' . 'lmapi.php');

$lm = new logicMonitor($config);

if($lm === false) { die("Error"); }

function showUsage(&$argc, &$argv)
{
	echo($argv[0] . " <hostGroupName> <var.name> <value>\n");
}

if($argc != 4) {
  showUsage($argc,$argv);
  exit(1);
}

$results = $lm->updateHostGroupProps($argv[1],$argv[2],$argv[3]);

echo(json_encode($results));

?>
