<?php

require_once 'vendor/autoload.php';
//use GorkaLaucirica\HipchatAPIv2Client;
use GorkaLaucirica\HipchatAPIv2Client\Auth\OAuth2;
use GorkaLaucirica\HipchatAPIv2Client\Client;
use GorkaLaucirica\HipchatAPIv2Client\API\UserAPI;
use GorkaLaucirica\HipchatAPIv2Client\Exception\RequestException;
use GorkaLaucirica\HipchatAPIv2Client\Model\User;

/**
 * eveHipChatHandler provides methods specific to the eve HipChat Authorization tool
 * 
 * @author xcption
 *
 */
class eveHipChatHandler {
	
	/**
	 * 
	 * @var GorkaLaucirica\HipchatAPIv2Client\Client
	 */
	protected $hcClient;
	
	/**
	 * 
	 * @param unknown $authToken
	 */
	function __construct($authToken) {
		$this->hcClient = new Client(new OAuth2($authToken));
	}
	
	/**
	 * 
	 * @param string $charName
	 * @return string|boolean
	 */
	function hipChatUserExists($charName) {
		$mentionName = $this->makeMentionName($charName);
		$userName = $this->makeUserName($charName);
		$userApi = new UserAPI($this->hcClient);
		$hcUsers = $userApi->getAllUsers(array("include-deleted"=>"true"));
		
		foreach ($hcUsers as $hcUser) {
			if (strtolower($hcUser->getMentionName()) == strtolower($mentionName) || 
					strtolower($hcUser->getName()) == strtolower($userName)) {
				return "A user with that character name already exists.";
			}
		}
		
		return false;
	}	
	
	/**
	 * 
	 * @param string $charName
	 * @param string $email
	 * @return multitype:string string
	 */
	function createEveHipChatUser($charName, $email) {

		$userAPI = new UserAPI($this->hcClient);		
		$newUser = new User();
		$newUser->setEmail($email);
		

		$newUser->setName($this->makeUserName($charName));
		$newUser->setMentionName($this->makeMentionName($charName));
		$newUser->setTitle('');	
		$newPassword = $this->makeTempPassword();	
		$id = $userAPI->createUser($newUser,$newPassword);
		return array("id"=>$id, "password"=>$newPassword);
	}
	
	/**
	 * 
	 * @param string $charName
	 * @return string
	 */
	function makeMentionName($charName) {
		return str_replace(' ', '', $charName);
	}
	
	/**
	 * 
	 * @param string $charName
	 * @return string
	 */
	function makeUserName($charName) {
		return str_replace(' ', '', $charName) . " .";
	}
	
	/**
	 * 
	 * @return string
	 */
	function makeTempPassword() {
		$seed = str_split('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
		shuffle($seed);
		$seed=array_slice($seed,0,mt_rand(6, 8));
		$tempPassword = implode('',$seed);
		
		return $tempPassword;
	}
	
	function testCreate() {
		return array("id"=>"000000", "password"=>$this->makeTempPassword());
	}
}

?>