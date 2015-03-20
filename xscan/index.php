<?php
/**
 * @version 0.2
 */

if ($_SERVER['REQUEST_METHOD']=="POST") {
	if (!empty($_POST["dscan"])){
		$cfgInfo = parse_ini_file('xscan.ini',true);
		
		$servername = $cfgInfo['databaseDetails']['host'];
		$schema = $cfgInfo['databaseDetails']['schema'];
		$tableName = $cfgInfo['databaseDetails']['scanTable'];
		$username = $cfgInfo['databaseDetails']['user'];
		$password = $cfgInfo['databaseDetails']['password'];
		$redirect = $cfgInfo['redirect']['target'];
		
		$systemKnown = false;
		$systemName = "UNKNOWN";
		
		$conn = new mysqli($servername, $username, $password, $schema);
		
		$input = $_POST["dscan"];
		$rows = explode("\n", str_replace("\r\n", "\n", $input));
		foreach($rows as $record) {
			$record = explode("\t", $record);

//THIS NEEDS TO BE DONE BETTER
//escape_string, when combined with json_encode was causing double escape chars //' instead of /'
//will hopefully be solved by moving to a sub table rather than JSON
			$item = str_replace("'", "", $record[1]);
			
			$counter[$item]++;
			
			//Solar System Detection from Celestial Objects
			if (!$systemKnown) {
				if (substr_count($record[1], "Sun")  || $record[1] == "Moon" || substr_count($record[1], "Asteroid Belt") ||
						substr_count($record[1], "Planet ") || substr_count($record[1], " Station") || substr_count($record[1], " Post") ||
						substr_count($record[1], " Outpost") || substr_count($record[1], " Hub") || substr_count($record[1], " Citadel") ||
						substr_count($record[1], " Starport")) {
							$systemUnknown = true;
							$systemName = explode(" ", $record[0])[0];
				}
			}
		}
		
		$newJSON = json_encode($counter);
		
		$sql = "INSERT into " . $tableName . " (rawData, createDate, systemName) VALUES ('" . $newJSON . "', UTC_TIMESTAMP(), '" . $systemName ."');";

		if ($conn->query($sql)){
			$sql = "SELECT md5(LAST_INSERT_ID());";
			$id = $conn->query($sql)->fetch_row()[0];

			Header("Location: http://" . $redirect . $id);
			exit;
		} else {
			echo "Insert Failed, please send the raw scan data to <a href='mailto:defects@xcptn.net'>defects@xcptn.net</a>";
		}
					
		//echo $sql;
		//CHECK IF SUCCESSFUL

	}
}
?>
<!DOCTYPE HTML>
<html lang="en-us">
<head>
<!-- <link href='http://fonts.googleapis.com/css?family=Roboto+Condensed:400,300,700' rel='stylesheet' type='text/css'>-->
<link rel="stylesheet" type="text/css" href="xscan.css">
<title>BETA scanX</title>
</head>
<script>
/**
*  Clears all input controls from the form
*  @return void
**/
function clearInput() {
	document.getElementById("dscan").value = "";
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

</body>
</html>