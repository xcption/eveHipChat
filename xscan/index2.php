<?php
/**
 * @version 0.2
 */
require_once 'dScanParser.php';

if ($_SERVER['REQUEST_METHOD']=="POST") {
	if (!empty($_POST["dscan"])){
		$input = $_POST["dscan"];
		$errorMessage = "";
		$cfgInfo = parse_ini_file('xscan.ini',true);
		
		$servername = $cfgInfo['databaseDetails']['host'];
		$schema = $cfgInfo['databaseDetails']['schema'];
		$scanParentTable = $cfgInfo['databaseDetails']['scanParentTable'];
		$scanChildTable = $cfgInfo['databaseDetails']['scanChildTable'];
		$shipTable = $cfgInfo['databaseDetails']['shipTable'];
		$username = $cfgInfo['databaseDetails']['user'];
		$password = $cfgInfo['databaseDetails']['password'];
		$redirect = $cfgInfo['redirect']['target'];


		try{
			if ($_SERVER['REQUEST_METHOD']=="POST") {
		
				$conn = new mysqli($servername, $username, $password, $schema);
		
				$parse = new dScanParser($_POST["dscan"], $conn, $scanParentTable, $scanChildTable, $shipTable);
		
				$data = $parse->parseScan();
		
				foreach($data as $type=>$count) {
					$shipDetails = $parse->getShipDetails($type);
					if ($shipDetails != false) {
						if (!$parse->getDbParentId()) {
							$parse->insertParent();
						}
						$parse->insertDetailRow($type, $shipDetails["category"], $shipDetails["role"], $count);
						$parse->incrementTotalShips($count);
					}
				}
				if ($parse->getTotalShips() > 0) {
					$parse->finalizeParent();
					Header("Location: http://localhost/eve/xscan/xscan2.php?id=" . md5($parse->getDbParentId()));
					exit;
				} else {
					//REMOVE PARENT RECORD
					$errorMessage = "No Valid Ships found in scan.";
				}
		
			}
		}
		catch(Exception $e) {
			$errorMessage = "An Error has occured.  System Administrator has been notified.<br>";
			echo $e;
			$to      = 'xscanError@xcptn.net';
			$headers = 'From: xscanError@xcptn.net' . "\r\n" .
					'Reply-To: xscanError@xcptn.net' . "\r\n" .
					'X-Mailer: PHP/' . phpversion();
		
			mail($to, "xScan Error", $e, $headers);
		}
		
		

	}
}
?>




<!DOCTYPE HTML>
<html lang="en-us">
<head>
<link href='http://fonts.googleapis.com/css?family=Roboto+Condensed:400,300,700' rel='stylesheet' type='text/css'>
<link rel="stylesheet" type="text/css" href="xscan.css">
<title>BETA scanX v2</title>
</head>
<script>
/**
*  Clears all input controls from the form
*  @return void
**/
function clearInput() {
	document.getElementById("dscan").value = "";
	document.getElementById("bottomBar").text="";
}

</script>
<div id="header">
<ul>
<li><img src="images/Sev3ranceLogo.png" alt="-7-" style="height:100%"></li>
<li><span id="headerText">ScanX - Directional Scan Parse</span></li>
</ul>
</div>

<div id="leftPanel">
<ul>
<li></li>
</ul>
</div>
<body>

<div style="float:left">
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
<textarea rows="20" cols="50" name="dscan" id="dscan"><?php echo $input;?></textarea>
<br>
<input type="submit" value="Submit" name="Submit">
<input type="button" name="clear" value="Clear" onclick="clearInput()">
</form>


</div>

<div id="infobar" style="width:20%;font-size:100%">
xScan is still in an early beta.  Please report any issues to <a href="mailto:defects@xcptn.net">defects@xcptn.net</a>
</div>

<div id="bottomBar"><?php echo $errorMessage;?></div>

</body>
</html>