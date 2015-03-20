<!DOCTYPE HTML>




<html lang="en-us">
<head>

<link rel="stylesheet" type="text/css" href="sevAuth.css">

<title>Sev3rance HipChat Authorization</title>

<script>
/**
*  Clears all input controls from the form
*  @return void
**/
function clearInput() {
	document.getElementById("charNameInput").value = "";
	document.getElementById("apiKeyIdInput").value = "";
	document.getElementById("apiVCodeInput").value = "";
	document.getElementById("emailAddrInput").value = "";	
}

</script>
</head>

<?php
/**
 * @uses keyChecker.php
 * @uses eveHipChatHandler.php
 * @uses PhealNG
 */
require 'keyChecker.php';
require 'eveHipChatHandler.php';
require_once 'vendor/autoload.php';
use Pheal\Pheal;
use Pheal\Core\Config;



//Turn on Pheal caching for the EVE API Calls
Config::getInstance()->cache = new \Pheal\Cache\FileStorage(__DIR__ . '/cache/');

//Turn on access mask checking by Pheal
Config::getInstance()->access = new \Pheal\Access\StaticCheck();

$charNameMissing = $apiKeyIdMissing = $vCodeMissing = $emailAddrMissing = "";
$charName = $apiKeyId = $apiVCode = $emailAddr = "";


try{
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
	    if (fieldsArePopulated()) {
	    	
	    	//Get Const values from INI
	    	$cfgInfo = parse_ini_file('opsec.ini',true);
	    	
			/**
			 * 
			 * @var unknown
			 */
	    	define("ALLIANCE", $cfgInfo['allianceDetails']['name']);
	    	define("HC_TOKEN", $cfgInfo['hipChatConnectionDetails']['token']);
	    	define("DB_ENABLED", $cfgInfo['databaseDetails']['dbEnabled']);
	    	if (DB_ENABLED) {
	    		define("DB_SERVER", $cfgInfo['databaseDetails']['host']);
	    		define("DB_SCHEMA", $cfgInfo['databaseDetails']['schema']);
	    		define("DB_TABLE", $cfgInfo['databaseDetails']['tableName']);
	    		define("DB_UNAME", $cfgInfo['databaseDetails']['user']);
	    		define("DB_PW", $cfgInfo['databaseDetails']['password']);
	    		define("FAILURE_LOGGING", $cfgInfo['databaseDetails']['failureLogging']);
	    		if (FAILURE_LOGGING) {
	    			define("DB_FAILLOG_TABLE", $cfgInfo['databaseDetails']['failLogTable']);
	    		}
	    	}
	    	
	    	//If DB logging is enabled, create and test the DB connection
	    	if (DB_ENABLED) {
	    		$conn = new mysqli(DB_SERVER, DB_UNAME, DB_PW, DB_SCHEMA);
	    		if ($conn->connect_errno) {
	    			throw new Exception("DB Connection Error, please contact system administrator");
	    		}
	    	}
	    	
	    	$checkChar = new characterChecker($apiKeyId, $apiVCode, $charName);
	    	
	    	if ($checkChar->getAllianceName() == ALLIANCE) {
				//CREATE HIPCHAT USER
   		
	    		$hcHandler = new eveHipChatHandler(HC_TOKEN);
				$hcResponse = $hcHandler->hipChatUserExists($charName);
				
				if (!$hcResponse) {
						$userDetails = $hcHandler->createEveHipChatUser($charName, $emailAddr);
						$hcUserId = $userDetails['id'];
						$response = "HipChat user created successfully.<br>" .
								"Please log in at www.hipchat.com using the following information:<br>" .
								"Username: " . $emailAddr . "<br>" .
								"Password: " . $userDetails['password'];
						if (DB_ENABLED) {
							$sql = 'INSERT INTO ' . DB_TABLE . ' (charName, apiKeyId, apiVCode, email, hipChatUserId, createDate) ' .
									'VALUES ("' . $charName . '", "' . $apiKeyId . '", "' . $apiVCode . '", "' . $emailAddr . '", "' . $hcUserId . '", now());';
							if ($conn->query($sql) <= 0) {
								//WRITE FAILURE
								$errResponse = $conn->error . "<br>" . $sql;
							}
						}
				
				} else {
					$errResponse = $hcResponse;
				}   		

	    	} else {
	    		//If enabled, will write out the attempted login details for admins to assist users whose attempts failed
	    		if (DB_ENABLED && FAILURE_LOGGING) {
	    			$sql = 'INSERT INTO ' . DB_FAILLOG_TABLE . ' (charname, apiKeyId, apiVCode, email, createDate) ' .
	    					'VALUES ("' . $charName . '", "' . $apiKeyId . '", "' . $apiVCode . '", "' . $emailAddr . '", now());';
	    			if ($conn->query($sql) <= 0) {
	    				//WRITE FAILURE
	    				$response = $conn->error . "<br>" . $sql;
	    			}
	    		}
	    		$errResponse = $charName . " is not a member of " . ALLIANCE . ".";
	    	}
	    }
    }
}

catch (Exception $e) {
	$errResponse = $e->getMessage();
}



/**
* Checks if all required fields are populated.
* @return boolean
**/
function fieldsArePopulated () {
	$fieldsArePopulatedBool = true;
	global $errResponse;
	global $charName, $charNameMissing;
	global $apiKeyId, $apiKeyIdMissing;
	global $apiVCode, $apiVCodeMissing;
	global $emailAddr, $emailAddrMissing;
	
	$errResponse='';
	
	if (empty($_POST["charName"])) {
		$errResponse .= "Character Name is required.<br>";
		$fieldsArePopulatedBool = false;
	} else {
		$charName = cleanInput($_POST["charName"]);
	}

	if (empty($_POST["apiKeyId"])) {
		$errResponse .= "Key ID is requried.<br>";
		$fieldsArePopulatedBool = false;
	} else {
		$apiKeyId = cleanInput($_POST["apiKeyId"]);
	}

	if (empty($_POST["apiVCode"])) {
		$errResponse .= "Verification Code is required.<br>";
		$fieldsArePopulatedBool = false;
	} else {
		$apiVCode = cleanInput($_POST["apiVCode"]);
	}
	
	if (empty($_POST["emailAddr"])) {
		echo "Empty email";
		$errResponse .= "Email Address is required.<br>";
		$fieldsArePopulatedBool = false;
 	} elseif (!filter_var(cleanInput($_POST["emailAddr"]),FILTER_VALIDATE_EMAIL)) {
 		$fieldsArePopulatedBool = false;
 		$emailAddr = cleanInput($_POST["emailAddr"]);
 		$errResponse .= "Invalid email address.<br>";

	} else {
		$emailAddr = cleanInput($_POST["emailAddr"]);
	}
	
	return $fieldsArePopulatedBool;
}

/**
 * Cleans up data to be sent with POST
 * @param string $data
 * @return string
**/
function cleanInput ($data) {
	return trim(stripcslashes(htmlspecialchars($data)));
}


?>




<body>
<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
<table>
<tr><th colspan=2 style="text-align:center;font-size:36px">Sev3rance Shared Services</th></tr>
<tr><th colspan=2 style="text-align:center;font-size:24px">HipChat Account Creation</th></tr>

<tr><td colspan=2 style="text-align:center;width:128px;height:128px;"><img src="images\Sev3ranceLogo.png" alt="-7-"></td></tr>


<!-- Input form to collect:
        Character Name
        Api Key
        Verification Code
        Email Address
-->

   <tr>
   <td class="tdLeft">Character Name:</td>
   <td><input type="text" name="charName" id="charNameInput" value=<?php echo $charName?>>
   <span class="error">* <?php echo $charNameMissing;?></span>
   </td>
   </tr>
   
   <tr>
   <td class="tdLeft">Key:</td>
   <td class="tdRight"><input style="width:100px" type="text" name="apiKeyId" id = "apiKeyIdInput" value=<?php echo $apiKeyId?>>
   <span class="error">* <?php echo $apiKeyIdMissing;?></span>
   </td>
   </tr>
   
   <tr>
   <td class="tdLeft">Verification Code:</td>
   <td class="tdRight"><input style="width:300px" type="text" name="apiVCode" id = "apiVCodeInput" value=<?php echo $apiVCode?>>
   <span class="error">* <?php echo $apiVCodeMissing;?></span>
   </td>
   </tr>
   
   <tr>
   <td class="tdLeft">Email Address:</td>
   <td class="tdRight"><input style="width:250px" type="text" name="emailAddr" id = "emailAddrInput" value=<?php echo $emailAddr?>>
   <span class="error">* <?php echo $emailAddrMissing;?></span>
   </td>
   </tr>
   
   <tr>
   <td style="width:50%;text-align:right"><input type="submit" name="submit" value="Submit"></td>
   <td><input type="button" name="clear" value="Clear" onclick="clearInput()"></td>
   </tr>  
     
   <tr><td colspan=2 style="text-align:center"></td></tr>
   
   <tr><td colspan=2 style="text-align:center">
   <span class="error"><?php echo $errResponse?></span>
   <?php echo $response;?>
   </td></tr>
</table>
</form>

<div style="text-align:center">
<a href='https://community.eveonline.com/support/api-key' target='_Blank'>Create an API Key</a>
</div>

</body>


</html>