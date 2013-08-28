<?php
//

// TARGET logicmonitor:host:datasourceinstance:datapoint0,datapoint1
// TARGET logicmonitor:esw1.ord1:snmp64_If-GigabitEthernet20:ifInOctets,ifOutOctets


class WeatherMapDataSource_logicmonitor extends WeatherMapDataSource {

	var $lm;

	function Init(&$map)
	{
		require_once("lmapi/config.php");
		require_once("lmapi/lmapi.php");

		$this->lm = new logicMonitor($config);

		if($this->lm === false) { return false; }
	
		return true;
	}


	function Recognise($targetstring)
	{
		if(preg_match("/^logicmonitor:(\S+):(\S+):(.*)$/",$targetstring,$matches))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function ReadData($targetstring, &$map, &$item)
	{
		$data[IN] = NULL;
		$data[OUT] = NULL;
		$data_time = 0;

		if(preg_match("/^logicmonitor:(\S+):(\S+):(.*)$/",$targetstring,$matches))
		{
			$hostName = $matches[1];
			$dataSourceInstance = $matches[2];
			$dataPoints = array();
			
			$items = preg_split("/,/",$matches[3]);
			
			foreach($items as $itemz)
			{
				$dataPoints[] = $itemz;	
			}
			
			$result = $this->lm->getHostData($hostName,$dataSourceInstance,1,$dataPoints);
			
			$cnt = 0;
			
			if($result)
			{
				foreach($result['values'] as $items)
				{
					$inVal = 0;
					$outVal = 0;
					
					foreach($items as $sample)
					{
						if($sample[0] >= time() - 300 && $sample[2] != 'NaN' && $sample[3] != 'NaN')
						{
				
							$inVal += $sample[2];
							$outVal += $sample[3];
							$cnt++;
						}
						
					}
					
				}
			
				$data[IN] = $inVal / $cnt;
				$data[OUT] = $outVal / $cnt;
			}
		}
		
		return( array($data[IN], $data[OUT], $data_time) );
	}
}

// vim:ts=4:sw=4:
?>
