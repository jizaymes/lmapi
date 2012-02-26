<?php
require("lmapi.php");

$lm = new logicMonitor($config);

if($lm === false) { die("Error"); }

if($argc>1) { 
	echo(json_encode($lm->getHost($argv[1])));
} 



?>
