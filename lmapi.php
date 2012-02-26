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
		
		$checks = array($this->config['baseurl'],$this->config['company'],$this->config['user'],$this->config['password']);
		
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
	
	private function login()
	{
		$ch = curl_init();
	
	    $url = $this->config['baseurl'] . "signIn?c=" . $this->config['company'] . "&u=" . $this->config['user'] . "&p=" . $this->config['password'];

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
			return true;
		}
	}

/* ----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----==== */
	
	public function getHost($workName = "") {

		if(!$this->connected) { return false; }
	
		$name = urlencode($workName);
	
		$ch = curl_init();
		$url = $this->config['baseurl'] . "getHost?hgId=-1&displayName=$name";

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		$response = json_decode(curl_exec($ch));

		curl_close($ch);

		return $response->data;	
	}

/* ----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----==== */
	
	public function getHostGroups($workName = "") {

		if(!$this->connected) { return false; }
	
		$ch = curl_init();
		$url = $this->config['baseurl'] . "getHostGroups";
	
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		$response = json_decode(curl_exec($ch));
		$matches = array();
		
		if(strlen($workName) > 0 && $response != null) {

			$name = preg_replace("/\//","\\\/",$workName);

			foreach($response->data as $workLine) {
				$line=(array)$workLine;
				
				if(preg_match("/" . $name . "$/",$line['fullPath'])) {
					$matches[] = $line;
				}
			}
		}  else { $matches = $response->data; }
	
		curl_close($ch);
		
		return $matches;
	
	}
	
/* ----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----==== */
	
	public function updateHostGroupProps($hostGroupNameWork,$propName,$propValueWork) {

		if(!$this->connected) { return false; }

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

	
		$ch = curl_init();
	
		$url = $this->config['baseurl'] . "updateHostGroup?id=$hostGroupId&name=$hostGroupName&propName0=$propName&propValue0=$propValue";

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		$response = json_decode(curl_exec($ch));
	
		curl_close($ch);
		
		return $response;
	}

/* ----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----==== */
	
	public function addHostGroup($hostGroupNameWork,$descriptionWork,$parentId) {

		if(!$this->connected) { return false; }

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

	
		$ch = curl_init();
	
		$url = $this->config['baseurl'] . "addHostGroup?name=$hostGroupName&description=$description&alertEnable=true&parentId=$parentId";

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		$response = json_decode(curl_exec($ch));
	
		curl_close($ch);
		
		return $response;
	}

/* ----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----==== */
	
	public function addHost($hostName,$displayName,$description,$agentId,$hostGroupId) {
		if(!$this->connected) { return false; }
	
	
		if(!is_numeric($hostGroupId)) { 
			$res = $this->getHostGroups($hostGroupId); 
			
			if(count($res) == 1) { //perfect
				$hostGroupId = $res[0]['id'];
			} else {
				return false;
			}
		}
	
		$ch = curl_init();
		$url = $this->config['baseurl'] . "addHost?hostName=$hostName&displayedAs=$displayName&description=$description&link=&alertEnable=true&agentId=$agentId&hostGroupIds=$hostGroupId";
	
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	
		$response = json_decode(curl_exec($ch));
		curl_close($ch);
	
		return $response;
	
	}

/* ----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----==== */
	
	public function deleteHost($hostName) {
		if(!$this->connected) { return false; }
	

		if(!is_numeric($hostName)) {	

			$res = $this->getHost($hostName); 

			$hostId = $res->id;
		} else { $hostId = $hostName; }
	
		$ch = curl_init();
		$url = $this->config['baseurl'] . "deleteHost?hostId=$hostId&deleteFromSystem=true";

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	
		$response = json_decode(curl_exec($ch));
		curl_close($ch);
	
		return $response;
	
	}


/* ----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----====----==== */
	
	public function deleteHostGroup($hostGroupName) {
		if(!$this->connected) { return false; }
	
		if(!is_numeric($hostGroupName)) {	

			$res = $this->getHostGroups($hostGroupName); 
			$hostGroupId = $res[0]['id'];
		} else { $hostGroupId = $hostGroupName; }

		$ch = curl_init();
		$url = $this->config['baseurl'] . "deleteHostGroup?hgId=$hostGroupId&deleteFromSystem=true";

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	
		$response = json_decode(curl_exec($ch));
		curl_close($ch);
	
		return $response;
	
	}
	
}

?>
