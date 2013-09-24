<?php
require(dirname(__FILE__) . '/../' . 'config.php');
require(dirname(__FILE__) . '/../' . 'lmapi.php');

$lm = new logicMonitor($config);

if($lm === false) { die("Error"); }

if($argc>1) { 
	$rawAgents = json_decode($lm->getAgents($argv[1]));
} else { 
	$rawAgents = json_decode($lm->getAgents(),true);
}

if($rawAgents['status'] != 200) { die("Error"); }

$agents = array();
$badKeys = array("agentConf","credential","escalatingChain");


foreach($rawAgents['data'] as $agent)
{
	$workAgent = array();
	foreach($agent as $key=>$val)
	{
		if(array_search($key,$badKeys) === false) {
			$workAgent[$key] = $val;	
		}
	}

	$agents[] = $workAgent;
}

echo(count($agents));

print_r($agents);
?>
