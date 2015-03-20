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

<!--
<div id="leftPanel">
<ul>
<li></li>
</ul>
</div>
-->



<?php
/**
 * @version 0.2
 */
	//GET CONFIG INFORMATION
	$cfgInfo = parse_ini_file('xscan.ini',true);
	
	$servername = $cfgInfo['databaseDetails']['host'];
	$schema = $cfgInfo['databaseDetails']['schema'];
	$scanTableName = $cfgInfo['databaseDetails']['scanTable'];
	$shipTableName = $cfgInfo['databaseDetails']['shipTable'];
	$username = $cfgInfo['databaseDetails']['user'];
	$password = $cfgInfo['databaseDetails']['password'];
	
	//GET DSCAN ID
	$dscanId = $_GET['id'];

	$conn = new mysqli($servername, $username, $password, $schema);

	if (mysqli_connect_errno()) {
		die("DB Connection Failed.");
	}

	$query = "SELECT rawData, createDate, systemName FROM " . $scanTableName . " WHERE md5(idRawScan) = '" . $dscanId . "';";

	$dbResult = $conn->query($query);

	if ($dbResult->num_rows == 0) {
		echo '<div id="transbox">Parse not found.  Please be aware, parses are purged after 24 hours.</div>';	
	} else {
		while ($row = $dbResult->fetch_array(MYSQLI_ASSOC)){
			$data[] = $row;
		}

		$dscanJSON = json_decode($data[0]['rawData']);
		$createDate = $data[0]['createDate'];
		$systemName = $data[0]['systemName'];
	
		echo '<div id="transbox"><span class=title>Ship Breakdown</span>';
		echo '<table>';
		foreach ($dscanJSON as $rcd=>$rcd_value) {
			$itemName = $conn->escape_string($rcd);
			$query = "SELECT category FROM " . $shipTableName . " WHERE name = '" .$itemName . "';";
			$dbResult = $conn->query($query);
			if ($dbResult->num_rows > 0) {
				$dbRow = $dbResult->fetch_row();
				$category[$dbRow[0]] += $rcd_value;
				echo '<tr><td class="type">' . $rcd . '</td><td class="count">' . $rcd_value . '</td></tr>';
			}
		}
		echo '</table></div>';

		echo '<div id="transbox"><span class=title>Category Breakdown</span>';
		echo "<table>";
		foreach ($category as $cat=>$cat_val) {
			echo '<tr><td class="type">' . $cat . '</td><td class="count">' . $cat_val . '</td></tr>';
		}
 		echo '</table></div>';
	}
?>
<br>
<div id="infobar" style="width:20%;font-size:100%">
<?php 
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