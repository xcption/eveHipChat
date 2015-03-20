<!DOCTYPE HTML>
<html lang="en-us">
<head>
<link href='http://fonts.googleapis.com/css?family=Roboto+Condensed:400,300,700' rel='stylesheet' type='text/css'>
<link rel="stylesheet" type="text/css" href="xscan.css">
<title>xScan BETA</title>
</head>

<body>

<div id="header">
<ul>
<li><img src="images/Sev3ranceLogo.png" alt="-7-" style="height:100%"></li>
<li><span id="headerText">xScan - Directional Scan Parse</span></li>
</ul>
</div>

<?php
/**
 * @version 0.3
 */

require 'dScanParser.php';

//GET CONFIG INFORMATION
$cfgInfo = parse_ini_file('xscan.ini',true);

$servername = $cfgInfo['databaseDetails']['host'];
$schema = $cfgInfo['databaseDetails']['schema'];
$scanParentTable = $cfgInfo['databaseDetails']['scanParentTable'];
$scanChildTable = $cfgInfo['databaseDetails']['scanChildTable'];
$shipTable = $cfgInfo['databaseDetails']['shipTable'];
$username = $cfgInfo['databaseDetails']['user'];
$password = $cfgInfo['databaseDetails']['password'];

//GET DSCAN ID
$dscanId = $_GET['id'];

$conn = new mysqli($servername, $username, $password, $schema);

$parse = new dScanParser($_POST["dscan"], $conn, $scanParentTable, $scanChildTable, $shipTable);

if ($parse->loadParentData($dscanId)) {
	$systemName = $parse->getSolarSystemName();
	$totalShips = $parse->getTotalShips();
	$createDate = $parse->getScanTimestamp();
	
	$data = $parse->getDetailRecords();
	if ($data) {
		echo '<div id="transbox"><span class=title>Ship Breakdown</span>';
		echo '<table>';
		foreach($data as $rcd) {
			echo '<tr><td class="type">' . $rcd['type'] . '</td><td class="count">' . $rcd['count'] . '</td></tr>';
			$category[$rcd['category']] += $rcd['count'];
		}
		
		echo '</table></div>';
		
		echo '<div id="transbox"><span class=title>Category Breakdown</span>';
		echo "<table>";
		foreach ($category as $cat=>$cat_val) {
			echo '<tr><td class="type">' . $cat . '</td><td class="count">' . $cat_val . '</td></tr>';
		}
		echo '</table></div>';
		
	} else {
		echo '<div id="transbox">Parse not found.  Please be aware, parses are purged after 24 hours.</div>';
	}
} else {
	echo '<div id="transbox">Parse not found.  Please be aware, parses are purged after 24 hours.</div>';
}
?>
<br>
<div id="infobar" style="width:20%;font-size:100%">
<?php 
	if ($totalShips > 0) {
		echo '<span style="font-size:150%">Total Ships:<br>' . $totalShips . '</span><br><br>';
	}

	if ($systemName != '') {
		echo '<span style="font-size:150%">Solar System:<br>' . $systemName . '</span><br><br>';
	}	

	if ($createDate != '') {
		echo '<span style="font-size:150%">Time of scan upload:<br>' . $createDate . '</span><br><br>';
	}

?>
xScan is still in an early beta.  Please report any issues to <a href="mailto:defects@xcptn.net">defects@xcptn.net</a>
<br><br>
<form action="index.php">
	<input type="submit" value="New Scan">
</form>
</div>

</body>
</html>