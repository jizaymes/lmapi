<?php
require("lmapi.php");

$lm = new logicMonitor($config);

if($lm === false) { die("Error"); }

$results = $lm->addHostGroup("Host Group Name","Description",1);
//$results = $lm->addHostGroup("Host Group Name","Description","Parent HostGroup");

if(@$results->data) { 
	echo("My HostGroupID is : " . $results->data->id);
}

?>
