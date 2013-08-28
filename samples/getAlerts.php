<?php
require("../config.php");
require("../lmapi.php");

$lm = new logicMonitor($config);

if($lm === false) { die("Error"); }

$criteria = array();

//$criteria['acked'] = false;
if(@isset($_SERVER)) { echo("<PRE>\n"); }

$alerts = $lm->getAlertsWithCriteria($criteria);

$types = array();


foreach($alerts as $alert)
{
	$types[$alert->dataSource]->alerts[] = $alert;
}
echo("<TABLE BORDER=1 CELLSPACING=0 CELLPADDING=0 WIDTH='80%'><TR>");

foreach($types as $type)
{

	echo("<TR><TD COLSPAN=7><B><BIG><BIG>Doing Datasource - " . $type->alerts[0]->dataSource . "</BIG></BIG></B></TD></TR>\n");

	echo("<TR>");
	echo("<TD><b><b>State</b></TD><TD><b>Start On</b></TD><TD><b>Host</b></TD><TD><b>DS Instance</b></TD>");
	echo("<TD><b><b>Acked On</b></TD><TD><b>by</b></TD><TD><b>Reason</b></TD>");	
	echo("</TR>");


	foreach($type->alerts as $alert) {

		echo("<TR>");		
		echo("<TD>" . $alert->level . "</TD>");
		echo("<TD>" . date("m-d-Y h:m:s",$alert->startOn)  . " (" . floor(((time() - $alert->startOn)/60)) . ")</TD>" );
		echo("<TD>" . $alert->host  . "</TD>" );		
		echo("<TD>" . $alert->dataSourceInstance . "</TD>\n");

		if($alert->acked == 1) {
			echo("<TD>" . date("m-d-Y h:m:s",$alert->ackedOn)  . " (" . floor(((time() - $alert->ackedOn)/60)) . ")</TD>" );
			echo("<TD>" . $alert->ackedBy  . "</TD>" );		
			echo("<TD>" . $alert->ackComment . "</TD>");
		} else { echo("<TD COLSPAN=3>&nbsp;</TD>"); }		
		

		} 

		echo("</TR>");

		echo("<TD COLSPAN=7>&nbsp;</TD>\n");
}
		echo("</TABLE>");
//echo(json_encode($groups));

?>
