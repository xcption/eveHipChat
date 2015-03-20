<?php

require_once 'vendor/autoload.php';
use GorkaLaucirica\HipchatAPIv2Client;
use GorkaLaucirica\HipchatAPIv2Client\Auth\OAuth2;
use GorkaLaucirica\HipchatAPIv2Client\Client;
use GorkaLaucirica\HipchatAPIv2Client\API\UserAPI;
use GorkaLaucirica\HipchatAPIv2Client\Exception\RequestException;
use GorkaLaucirica\HipchatAPIv2Client\Model\User;


function createEveHipChatUser($charName, $email) {
	$client = new Client(new OAuth2('StdNnaTR9nNGpmv3y7KWbguNwB3qjMjUTRpFwlYe'));
	
	
	$userAPI = new UserAPI($client);
	
	$newUser = new User();
	$newUser->setEmail('test03@xcptn.net');
	$newUser->setName('Test 03');
	$newUser->setMentionName('deletedxcption2');
	$newUser->setTitle('');
	$response = $userAPI->createUser($newUser,"12345");
	
	return $password;
}




try{
	//$auth = new OAuth2('StdNnaTR9nNGpmv3y7KWbguNwB3qjMjUTRpFwlYe');
	//$client = new Client($auth);
	
		$client = new Client(new OAuth2('StdNnaTR9nNGpmv3y7KWbguNwB3qjMjUTRpFwlYe'));
	
	
	$userAPI = new UserAPI($client);
	
	$newUser = new User();
	$newUser->setEmail('test03@xcptn.net');
	$newUser->setName('Test 03');
	$newUser->setMentionName('deletedxcption2');
	$newUser->setTitle('');
	$response = $userAPI->createUser($newUser,"12345");
	
	
    try{
    	$delUser = $userAPI->getUser('1589903');
	    echo "@" . $delUser->getMentionName() . " : " . $delUser->getEmail();
    }
	catch (RequestException $rE) {
		if (stripos($rE->getMessage(), "is not a valid user")) {
			echo "NOT A VALID USER<br>";
 		} else {
 			throw $rE;
 		} 
	}

	
//	echo $user->getName();
	$users = $userAPI->getAllUsers(array("include-deleted"=>"true"));
	echo date(DATE_ATOM) . "<br>";
	foreach($users as $checkUser) {
		if ($checkUser->getMentionName() == "xcptionadmin") {
			echo "FOUND<br>";
		}
	echo $checkUser->getId() . " : @" . $checkUser->getMentionName() ."<br>";
	
	}



}


catch (Exception $e) {
	//echo $e;
	echo $e->getMessage();
}


?>