<?php
if ($_SERVER['REQUEST_METHOD']=="POST") {
	if (!empty($_POST["dscan"])){
		$systemKnown = false;
		$input = $_POST["dscan"];
		$rows = explode("\n", str_replace("\r\n", "\n", $input));
		foreach($rows as $record) {
			$record = explode("\t", $record);
			$counter[$record[1]]++;
			if (!$systemKnown) {
				if (substr_count($record[1], "Sun")  || $record[1] == "Moon" || substr_count($record[1], "Asteroid Belt") ||
						substr_count($record[1], "Planet ") || substr_count($record[1], " Station") || substr_count($record[1], " Post") ||
						substr_count($record[1], " Outpost") || substr_count($record[1], " Hub") || substr_count($record[1], " Citadel") ||
						substr_count($record[1], " Starport")) {
							echo $record[1] . " : " . explode(" ", $record[0])[0] . "<br>";
							//$systemUnknown = true;
							$systemName = explode(" ", $record[0])[0];
						}
			}

		}

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
<textarea rows="20" cols="50" name="dscan"><?php echo $input;?></textarea>
<br>
<input type="submit" value="submit" name="Submit">
</form>
</div>

<div id="infobar" style="width:20%;font-size:100%">
xScan is still in an early beta.  Please report any issues to <a href="mailto:defects@xcptn.net">defects@xcptn.net</a>
</div>

</body>
</html>