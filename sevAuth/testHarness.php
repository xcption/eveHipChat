<?php

echo date(DATE_ATOM). "<br>";

require 'eveHipChatHandler.php';


$authToken = 'StdNnaTR9nNGpmv3y7KWbguNwB3qjMjUTRpFwlYe';

$charName = "Clearly AnotherFakeUser";
$email = "test05@xcptn.net";

$hcHandler = new eveHipChatHandler($authToken);


$response = $hcHandler->hipChatUserExists($charName);

if (!$response) {
	echo "User " . $hcHandler->makeUserName($charName) . " does not exist<br>";
	try {
		$udeets = $hcHandler->createEveHipChatUser($charName, $email);
		echo $udeets['id'] . "<br>";
		echo $udeets['password'];
	}
	catch (Exception $e) {
		echo $e->getMessage();
	}

} else {
	echo $response;
}

?>