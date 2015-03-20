<?php
/**
 * keyChecker provides various methods to determine the validity of an EVE Online API Key
 *
 * Uses the PhealNG library created by Peter Peterman found at github.com/3rdpartyeve/phealng
 *
 * @author Xcption
 **/

use Pheal\Pheal;
require_once 'vendor/autoload.php';

class keyChecker {

	/** @var Pheal */
	private $keyCheckPheal;

	/**
	 * creates a new keyChecker object
	 * @param $keyId the EVE Online API Key ID
	 * @param $vCode the verificaiton code for the EVE Online API Key
	 */
	function keyChecker($keyId, $vCode) {
		$this->keyCheckPheal = new Pheal($keyId, $vCode);
	}


	/**
	 * Returns the key type for the given keyID/vcode combo
	 * @return string keyType
	 */
	function getKeyType() {
		$this->keyCheckPheal->scope = "account";
		return $this->keyCheckPheal->APIKeyInfo()->key->type;
	}

	/**
	 * Returns the access mask for the given keyID/vcode combo
	 * @return string accessMask
	 */
	function getAccessMask() {
		$this->keyCheckPheal->scope = "account";
		return $this->keyCheckPheal->APIKeyInfo()->key->accessMask;
	}


}


class characterChecker {

	/*
	 * @var Pheal
	 */
	private $charPheal;

	/**
	 * @var string
	 */
	private $keyType;

	/**
	 * @var string
	 */
	private $keyAccessMask;

	/**
	 * @var string
	 */
	private $charName;

	/**
	 * @var int
	 */
	private $charId;

	/**
	 * creates a new characterChecker object
	 * @param $keyId the EVE Online API Key ID
	 * @param $vCode the verification code for the EVE Online API Key
	 */
	function characterChecker($keyId, $vCode, $charName){
		$this->charName = $charName;
		$this->charPheal = new Pheal($keyId, $vCode);
		
		$keyChecker = new keyChecker($keyId, $vCode);
		$this->keyType = $keyChecker->getKeyType();
		$this->keyAccessMask = $keyChecker->getAccessMask();

		
//SEPARATE THIS OUT INTO ITS OWN METHOD
        if (($this->keyAccessMask & 8) == 0) {
        	//ERROR: Access needs to include character sheet
        	throw new keyCheckerException("Key must provide access to Character Sheet.");
        }	
		
		$this->charPheal->scope = "eve";
		$this->charId = $this->charPheal->CharacterID(array("names"=>$this->charName))->characters[0]->characterID;
		
		if (!$this->charId) {
			//ERROR: Character does not exist
			throw new keyCheckerException("Character " . $charName . " does not exist.");
		} elseif (!$this->charOnKey()) {
			throw new keyCheckerException($charName . " is not a character on the provided key.");
		}

	}

	/**
	 * Accessor for characterId
	 * @return int
	 */
	function getCharacterId() {
		echo "getCharID=" . $this->charId . "<br>";
		return $this->charId;
	}

	/**
	 * Returns true if a character on the key has a character name matching. Else, false.
	 * @return boolean
	 */
	function charOnKey() {
		$this->charPheal->scope = "account";
		$characterList = $this->charPheal->Characters();
		switch ($this->keyType) {
			case "Character":
				//comment out anything in this case block (including the break) to allow character and account keys
				//ERROR: must provide Account key;
//				return false;
//				break;
			case "Account":
				foreach ($characterList->characters as $apiChar) {
					if ($apiChar->characterID == $this->charId) {
						return true;
					}
				}
				return false;
				break;
			default:
				//ERROR: must provide Account or Character Key
				throw new keyCheckerException("Key must be a Character or Account key.");
				return false;
				break;
		}
	}

	/**
	 * return the allianceName for the given Character
	 * @return string
	 **/
	function getAllianceName() {
		$this->charPheal->scope = "char";
		return $this->charPheal->CharacterSheet(array("characterID"=>$this->charId))->allianceName;
	}

	/**
	 * Mutator for $charName
	 * if character does not exist, throws an error
	 * if valid character, updates $charName and $charId
	 * @param string $charName new Character Name
	 */
	function setCharacterName($charName) {
		$this->charPheal->scope = "eve";
		$this->charId = $this->charPheal->characterID(array("names"=>$this->charName))->characters[0]->characterID;
		if (!$this->charId) {
			//ERROR: Character does not exist
			throw new keyCheckerException("Character " . $charName . " does not exist.");
		} else {
			$this->charName = $charName;
		}
	}

}

class keyCheckerException extends Exception {
}

?>