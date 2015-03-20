<?php

$requestHeaders = apache_request_headers();
// foreach($requestHeaders as $a=>$b){
// echo $a . " : " . $b . " ||<br>";
// }

if ($requestHeaders['EVE_TRUSTED'] == 'Yes'){
	echo $requestHeaders['EVE_SOLARSYSTEMNAME'];
} else {
	echo 'NOT TRUSTED';
}

?>