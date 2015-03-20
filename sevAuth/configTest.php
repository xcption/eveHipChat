<?php
require_once 'vendor/autoload.php';
use GorkaLaucirica\HipchatAPIv2Client\Auth\OAuth2;
use GorkaLaucirica\HipchatAPIv2Client\Client;
use GorkaLaucirica\HipchatAPIv2Client\API\UserAPI;
use GorkaLaucirica\HipchatAPIv2Client\Exception\RequestException;
use GorkaLaucirica\HipchatAPIv2Client\Model\User;

use Pheal\Pheal;

try{
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
	echo "CONFIG TEST: PASSED<br>";
	echo "----Alliance Name: " . ALLIANCE . "<br>";
}
catch (Exception $e) {
	echo "CONFIG TEST: failed with message --"; 
	echo "----" . $e . "<br>";
}

try {
    if (DB_ENABLED) {
    	$conn = new mysqli(DB_SERVER, DB_UNAME, DB_PW, DB_SCHEMA);
    	if ($conn->connect_errno) {
    		throw new Exception("DB Connection Error failed");
    	}
    	echo "DB TEST: CONNECTED<br>";
    	$sql = "select count(*) from " . DB_TABLE . ";";

    	$results = $conn->query($sql);
		if ($results->num_rows > 0) {
			echo "----: Main Table connection successful, contains " . $results->fetch_row()[0] . " records.<br>";
		} else {
			echo "----: Failure Log Table connection failed.<br>";
		}
    	
   // 	$row = $result->fetch_row();
  //  	echo $row[0];
//    	echo "<br>" . $conn->query($sql)->fetch_row()[0] . "<br>";

    	if (FAILURE_LOGGING) {
    		$sql = "select count(*) from " . DB_FAILLOG_TABLE . ";";
//    		echo "<br>" . $conn->query($sql)->fetch_row()[0] . "<br>";
			$results = $conn->query($sql);
    		if ($results->num_rows > 0) {
			echo "----: Failure Log Table connection successful, contains " . $results->fetch_row()[0] . " records.<br>";
			} else {
				echo "----: Failure Log Table connection failed.<br>";
			}
    	}
    } else {
    	echo "DB DISABLED<br>";
    }

}
catch (Exception $e) {
	echo "DB TEST: failed with message -- ";
	echo "----" . $e . "<br>";
}

//EVE API TEST
try {
	$pheal = new Pheal();
	$response = $pheal->serverScope->ServerStatus();
	echo "EVE API TEST: PASSED<br>";
	echo sprintf(
			"----EVE Online Server is: %s. Current online players: %s <br>",
			$response->serverOpen ? "ONLINE" : "OFFLINE",
			$response->onlinePlayers
	);
}
catch (Exception $e) {
	"EVE API TEST: failed with message -- ";
	echo "----" . $e . "<br>";
}


//HIPCHAT TEST
try {
	$client = new Client(new OAuth2(HC_TOKEN));
	$userAPI = new UserAPI($client);
	$users = $userAPI->getAllUsers();
	echo "HIPCHAT TEST: PASSED<br>";
	echo "----Server has " . sizeof($users) . " active users.<br>";
}
catch (Exception $e) {
	echo "HIPCHAT TEST: failed with message -- ";
	echo "----" . $e . "<br>";
}

?>