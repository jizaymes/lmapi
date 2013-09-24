<?php
require(dirname(__FILE__) . '/../' . 'config.php');
require(dirname(__FILE__) . '/../' . 'lmapi.php');

$lm = new logicMonitor($config);

if($lm === false) { die("Error"); }

if($argc>1) { 
	$agents = $lm->getAgents($argv[1]);
} else { 
	$agents = $lm->getAgents();
}

print_r($agents);

?>
