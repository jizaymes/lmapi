<?php
require("../config.php");
require("../lmapi.php");

$lm = new logicMonitor($config);

if($lm === false) { die("Error"); }


$result = $lm->getHostDataGraph("switch1","snmp64_If-GigabitEthernet1/1/37","1hour","Throughput");

print_r($result);
?>
