<?php
	
require("config.php");

define('HOWLONG',14400);
	
class logicMonitor
{

	private $config;
	private $cookie;
	
	public $connected;

/* ----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----==== */
	
	public function __construct($config) {
		$this->config = $config;
		$this->isConnected = false;
		
		$checks = array(
			$this->config['baseurl'],
			$this->config['company'],
			$this->config['user'],
			$this->config['password']
		);
		
		foreach($checks as $check)
		{
			if(!$check) {
				return false;
			}
		}
		
		$connect = $this->login();

		if(!$this->connected)
		{
			die($connect);
		}
	}

/* ----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----==== */

	public function __destruct()
	{
		if($this->connected) { $this->logout(); }		
	}
	
/* ----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----==== */

	private function logout()
	{
		if($this->connected || file_exists($this->cookie)) {
			$this->connected = false;
			unlink($this->cookie);
		}
	}

/* ----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----==== */	

	public function call($url,&$results,&$errMsg = null)
	{

		if(!$this->connected) { echo("Not Connected"); return false; }

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		$response = json_decode(curl_exec($ch));
		
		if($response->status != 200) {
			$errMsg = $response->errmsg;
			return $response->status;
		} else {
			$results = $response->data;
			return $response->status;
		}
	}
	
/* ----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----==== */	
	
	private function login()
	{

	    $url = $this->config['baseurl'] . "signIn?c=" . $this->config['company'] . "&u=" . $this->config['user'] . "&p=" . $this->config['password'];

		$ch = curl_init();
	
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$this->cookie = tempnam ($this->config['tmpdir'], "CURLCOOKIE");
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	
		$response = json_decode(curl_exec($ch));

		if($response->status != 200) {
			$this->connected = false;
			return $response->errmsg;
		} else {
			$this->connected = true;
			return $response->data;
		}
	}

/* ----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----==== */

        public function getHost($workName = "") {

                $name = urlencode($workName);

                $url = $this->config['baseurl'] . "getHost?hgId=-1&displayName=$name";

                $results = null;
                $errMsg = null;

                $response = $this->call($url,$results,$errMsg);

                return $results;
        }

/* ----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----==== */
	
	public function getHostInGroup($workName = "") {

		$name = urlencode($workName);
	
		$url = $this->config['baseurl'] . "getHosts?hgId=$workName";
		$results = null;
		$errMsg = null;
	
		$response = $this->call($url,$results,$errMsg);
		return $results;	
	}

/* ----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----==== */
	
	public function getHostGroups($workName = "") {

		$url = $this->config['baseurl'] . "getHostGroups";
	
		$results = null;
		$errMsg = null;
	
		$response = $this->call($url,$results,$errMsg);
		$matches = array();

		if(strlen($workName) > 0 && $results != null) {

			$name = preg_replace("/\//","\\\/",$workName);

			foreach($results as $workLine) {
				$line=(array)$workLine;
				
				if(preg_match("/" . $name . "$/",$line['fullPath'])) {
					$matches[] = $line;
				}
			}
		}  else { $matches = $results; }
	
		
		return $matches;
	}
	
/* ----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----==== */
	
	public function updateHostGroupProps($hostGroupNameWork,$propName,$propValueWork) {

		$propValue = urlencode(htmlspecialchars($propValueWork));

        if(!is_numeric($hostGroupNameWork)) {
			$hostGroupName = urlencode(htmlspecialchars($hostGroupNameWork));
        
            $res = $this->getHostGroups($hostGroupName);

            if(count($res) == 1) { //perfect
                    $hostGroupId = $res[0]['id'];
            } else {
                    return false;
            }
        } else 
        {
        	$hostGroupId = $hostGroupNameWork;
        }
	
		$url = $this->config['baseurl'] . "updateHostGroup?id=$hostGroupId&name=$hostGroupName&propName0=$propName&propValue0=$propValue";

		$results = null;
		$errMsg = null;
	
		$response = $this->call($url,$results,$errMsg);
		
		return $results;
	}

/* ----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----==== */
	
	public function addHostGroup($hostGroupNameWork,$descriptionWork,$parentId) {

		$hostGroupName = urlencode(htmlspecialchars($hostGroupNameWork));
		$description = urlencode(htmlspecialchars($descriptionWork));

        if(!is_numeric($parentId)) {
            $res = $this->getHostGroups($parentId);

            if(count($res) == 1) { //perfect
                    $parentId = $res[0]['id'];
            } else {
                    return false;
            }
        }


		$url = $this->config['baseurl'] . "addHostGroup?name=$hostGroupName&description=$description&alertEnable=true&parentId=$parentId";

		$results = null;
		$errMsg = null;
	
		$response = $this->call($url,$results,$errMsg);
		
		if(@$errMsg) { echo($errMsg . "\n"); }
		return $results;
	}

/* ----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----==== */
	
	public function addHost($hostName,$displayName,$description,$agentId,$hostGroupId) {
	
		if(!is_numeric($hostGroupId)) { 
			$res = $this->getHostGroups($hostGroupId); 
			
			if(count($res) == 1) { //perfect
				$hostGroupId = $res[0]['id'];
			} else {
				return false;
			}
		}
	
		$url = $this->config['baseurl'] . "addHost?hostName=$hostName&displayedAs=$displayName&description=$description&link=&alertEnable=true&agentId=$agentId&hostGroupIds=$hostGroupId";

		$results = null;
		$errMsg = null;
	
		$response = $this->call($url,$results,$errMsg);

	
		return $results;
	
	}

/* ----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----==== */
	
	public function deleteHost($hostName) {

		if(!is_numeric($hostName)) {	

			$res = $this->getHost($hostName); 

			$hostId = $res->id;
		} else { $hostId = $hostName; }
	
		$url = $this->config['baseurl'] . "deleteHost?hostId=$hostId&deleteFromSystem=true";

		$results = null;
		$errMsg = null;
	
		$response = $this->call($url,$results,$errMsg);
	
		return $results;
	}


/* ----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----==== */
	
	public function deleteHostGroup($hostGroupName) {
	
		if(!is_numeric($hostGroupName)) {	

			$res = $this->getHostGroups($hostGroupName); 
			
			if(count($res) > 0)
			{
				$hostGroupId = $res[0]['id'];
			} else
			{
				return "HostGroup does not exist";
			}
		} else { $hostGroupId = $hostGroupName; }

		$url = $this->config['baseurl'] . "deleteHostGroup?hgId=$hostGroupId&deleteFromSystem=true";

		$results = null;
		$errMsg = null;
	
		$response = $this->call($url,$results,$errMsg);

		if($errMsg === true) {
			return $errMsg;
		} else {
			return $results;
		}
	
	}
	
/* ----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----==== */
	
	public function getAlerts($hostGroupName = "") {
	
// GET /santaba/rpc/getAlerts?hostGroupName=webservers&ackFilter=nonacked HTTP/1.1

		$url = $this->config['baseurl'] . "getAlerts";

		if(strlen(urlencode($hostGroupName)) > 0 ) {
				$url .= "?hostGroupName=$hostGroupName";
		}

		$results = null;
		$errMsg = null;
	
		$response = $this->call($url,$results,$errMsg);

		return $results;
	}	

/* ----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----==== */
	
	public function getAlertsWithCriteria($criteria = array(), $hostGroupName = "") {
	
		if(@!$criteria['level']) { 
			$criteria['level'][] = "error";
			$criteria['level'][] = "critical";
		} 
				
		
		if(@!$criteria['howold']) { $criteria['howold'] = HOWLONG; }
		if(@!$criteria['acked']) { $criteria['acked'] = false; }
		
	
		$results = $this->getAlerts($hostGroupName);
//		print_r($results);
		$newresults = array();
		
		foreach($results->alerts as $result)
		{	
		
			if( in_array($result->level,$criteria['level']) ) {
				if($criteria['acked'] == true) { 
					if( ( time() - $result->ackedOn ) > $criteria['howold'] ) {
							$newresults[] = $result;
					}
				} else {
					if( ( time() - $result->startOn ) > $criteria['howold'] ) {
							$newresults[] = $result;
					}				
				}
			}
		}

		

		return $newresults;
	}	


	/* ----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----==== */
	
	public function getHostData($hostName = "",$dsInstance = "", $period = 2, $dataPoints = array()) {

		$url = $this->config['baseurl'] . "getData";
	
		$results = null;
		$errMsg = null;
	
		if($hostName == "" || $dsInstance == "" || $period < 1 || count($dataPoints) == 0)
		{
			return false;
		}

		$url .= "?host=$hostName";
		$url .= "&dataSourceInstance=" . $dsInstance;
		$url .= "&period=" . $period . "hours";
		
		for($cnt = 0; $cnt < count($dataPoints); $cnt++)
		{
			$url .= "&dataPoint" . $cnt . "=" . $dataPoints[$cnt];
		}
	
	
	
		$response = $this->call($url,$results,$errMsg);
		$matches = array();
	
		if($results != null) {

			$newResults = (array)$results;
			
			return $newResults;
		}
		else
		{
			return false;
		}
	
	}
		
	/* ----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----==== */	

	public function getHostDataGraph($hostName = "",$dsInstance = "", $period = 2, $graphName = "") {

		$url = $this->config['baseurl'] . "getGraphData";
	
		$results = null;
		$errMsg = null;
	
		if($hostName == "" || $dsInstance == "" || $period < 1 || $graphName == "")
		{
			return false;
		}

		$url .= "?time=" . $period . "hour";
		$url .= "&graphName=$graphName";
		$url .= "&hostDisplayedAs=$hostName";		
		$url .= "&dataSourceInstanceName=" . $dsInstance;
		$url .= "&csv=false";

		$response = $this->call($url,$results,$errMsg);
		
		if($results != null) {
		
			$newResults = (array)$results;
			return $newResults;
		}
		else
		{
			return false;
		}
	
	}
		
	/* ----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----==== */	


}

?>
