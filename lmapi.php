<?php
	
require("config.php");
	
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
	
}

?>
