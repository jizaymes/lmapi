<?php
require(dirname(__FILE__) . '/../' . 'config.php');
require(dirname(__FILE__) . '/../' . 'lmapi.php');

$lm = new logicMonitor($config);

if($lm === false) { die("Error"); }

if($argc>1) { 
	$res = $lm->getHostInGroup($argv[1]);
	
	if($res !== false && $res !== null) { 
		echo(count($res->hosts));
	}
} 

?>
