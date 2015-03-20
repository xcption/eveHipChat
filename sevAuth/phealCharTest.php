<?php
require_once 'vendor/autoload.php';

//import namespace
use Pheal\Pheal;
use Pheal\Core\Config;

// the information required by this example, usually, your application would
// prompt your user for this, and/or use its database to read those information
// information like the characterID can be obtained through the EVE API,
// please check the documentation at http://wiki.eve-id.net/APIv2_Page_Index for more information
$keyID = 3591076;
$vCode = "zeFwx4MSs41VSyTy6oYM1tfKUrVjWUmYKW24fZd3Uga3LSIXBuzyNfgZWwINuILv";
$characterID = 94857798;

// Pheal configuration
// Pheal may be configured through variables at the \Pheal\Cache\FileStorage Singleton object
// this allows to use different fetchers, caches, archives etc.

// setup file cache - CCP wants you to respect their cache timers, meaning
// some of the API Pages will return the same data for a specific while, or worse
// an error. If you use one of the availabe caching implementations,
// pheal will do the caching transparently for you.
// in this example we use the file cache, and configure it so it will write the cache files
// to /tmp/phealcache
Config::getInstance()->cache = new \Pheal\Cache\FileStorage(__DIR__ . '/cache/');


// The EVE API blocks applications which cause too many errors. Requesting a page
// that the API key does not allow to request is one of those possible errors.
// Pheal can be configured so pheal will request the AccessMask of a specific key
// and block requests to API Pages not covered by that key.
Config::getInstance()->access = new \Pheal\Access\StaticCheck();

// create pheal object with default values
// so far this will not use caching, and since no key is supplied
// only allow access to the public parts of the EVE API
//
// in this example, instead of using the scopenameScope getter,
// we set the scope directly in the constructor
$pheal = new Pheal($keyID, $vCode, "char");

try {
    // parameters for the request, like a characterID can be added
    // by handing the method an array of those parameters as argument
    $response = $pheal->CharacterSheet(array("characterID" => $characterID));

    echo sprintf(
        "Hello Visitor, Character %s was created at %s is of the %s race and belongs to the corporation %s",
        $response->name,
        $response->DoB,
        $response->race,
        $response->corporationName
    );

// there is a variety of things that can go wrong, like the EVE API not responding,
// the key being invalid, the key not having the rights to call the method
// or the characterID beeing wrong - just to name a few. So it is basically
// a good idea to catch Exceptions. Usually you would want to log that the
// exception happend and then decide how to inform the user about it.
// In this example we simply catch all PhealExceptions and display their message
} catch (\Pheal\Exceptions\PhealException $e) {
    echo sprintf(
        "an exception was caught! Type: %s Message: %s",
        get_class($e),
        $e->getMessage()
    );
}

?>