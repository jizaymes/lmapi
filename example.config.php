<?php

$config = array(
	'company' => "companyname",
	'user' => "lmapi",
	'password' => "123412341234",
	'baseurl' => "https://@company@.logicmonitor.com/santaba/rpc/",
	'tmpdir' => "/tmp"
	);

$config['baseurl'] = preg_replace("/\@company\@/",$config['company'],$config['baseurl']);
?>
