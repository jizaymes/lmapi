<?php

require("cacti/plugins/weathermap/lib/datasources/lmapi/config.php");
require("cacti/plugins/weathermap/lib/datasources/lmapi/lmapi.php");

$lm = new logicMonitor($config);

if($lm === false) { die("Error"); }

$host = $_GET['hostDisplayedAs'];
$dataSourceInstanceName = $_GET['dataSourceInstanceName'];
$time = $_GET['time'];
$graphName = $_GET['graphName'];
$size = $_GET['width'];

if(!$size) { $size = "450"; }

$result = $lm->getHostDataGraph($host,$dataSourceInstanceName,$time,$graphName,$size);

header("Content-Type: image/png");
echo($result);

?>
