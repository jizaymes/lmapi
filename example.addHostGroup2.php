<?php
require("lmapi.php");

$lm = new logicMonitor($config);

if($lm === false) { die("Error"); }


$bulkadd = array(

	array("Core Network","","LAX0"),
	array("Core Network","","LGA6"),
	array("Core Network","","LGA4")
	);

	foreach($bulkadd as $add) {
		$results = $lm->addHostGroup($add[0],$add[1],$add[2]);

		if(@$results->data) { 
			echo("My HostGroupID (" . $add[0] . ") is : " . $results->data->id);
		}
	}

?>
