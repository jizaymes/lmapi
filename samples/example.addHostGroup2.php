<?php
require("../config.php");
require("../lmapi.php");

$lm = new logicMonitor($config);

if($lm === false) { die("Error"); }


$bulkadd = array(

	array("Domain Controllers","",71),
	array("FTP Servers","",71),
	array("iConect DB Servers","",71),
	array("iConect Web Servers","",71),
	array("Storage","",71),
	array("Terminal Services","",71),
	);

	foreach($bulkadd as $add) {
		$results = $lm->addHostGroup($add[0],$add[1],$add[2]);
		
		if(@$results->data) { 
			echo("My HostGroupID (" . $add[0] . ") is : " . $results->data->id);
		}
	}

?>
