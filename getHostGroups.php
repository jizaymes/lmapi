<?php
require("lmapi.php");

$lm = new logicMonitor($config);

if($lm === false) { die("Error"); }

if($argc>1) { 
	$groups = $lm->listGroups($argv[1]);
} else { 
	$groups = $lm->listGroups();
}


echo(json_encode($groups));

?>
