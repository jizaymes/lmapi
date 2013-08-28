<?php
require("config.php");
require("lmapi.php");

$lm = new logicMonitor($config);

if($lm === false) { die("Error"); }


$result = $lm->getHostDataGraph("ssw1.lax0","snmp64_If-GigabitEthernet1/1/37",1,"Throughput");

print_r($result);
?>
