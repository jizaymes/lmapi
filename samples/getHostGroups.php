<?php
require(dirname(__FILE__) . '/../' . 'config.php');
require(dirname(__FILE__) . '/../' . 'lmapi.php');

$lm = new logicMonitor($config);

if($lm === false) { die("Error"); }

if($argc>1) { 
	$groups = $lm->getHostGroups($argv[1]);
} else { 
	$groups = $lm->getHostGroups();
}

print_r($groups);
//echo(json_encode($groups));

?>
