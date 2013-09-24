<?php
require(dirname(__FILE__) . '/../' . 'config.php');
require(dirname(__FILE__) . '/../' . 'lmapi.php');

$lm = new logicMonitor($config);

if($lm === false) { die("Error"); }


$result = $lm->getHostData("ssw1.lax0","snmp64_If-GigabitEthernet1/1/37","1hour",array("inOctets","outOctets"));

$cnt = 0;

foreach($result['values'] as $item)
{
	$inVal = 0;
	$outVal = 0;
	
	foreach($item as $sample)
	{
		if($sample[0] >= time() - 300 && $sample[2] != 'NaN' && $sample[3] != 'NaN')
		{
			
			$inVal += $sample[2];
			$outVal += $sample[3];
			$cnt++;
		}
		
	}
	
}

$indata = ($inVal / $cnt) * 8;
$outdata = ($outVal / $cnt) * 8;

echo($indata . " :: " . $outdata . "\n");

?>
