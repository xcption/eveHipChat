<?php
$servername = 'localhost';
$username = 'xcption';
$password = 'mIke#sUgar00';
$tableName = 'test_table';
$apiKeyId = '"1234567"';
$apiVCode = '"aserkj1234lkj"';
$email = '"test@test.test"';
$hipChatUserId = '"1111111"';


$conn = new mysqli($servername, $username, $password, 'sev_api');

if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

echo "connected successfully<br>";

$sql = 'INSERT INTO ' . $tableName . ' (data, email, createDate) ' .
    'VALUES ("' . $username . '", "' . $email . '", now() );';

echo $sql;
$result = $conn->query($sql);

echo "called<br>";

echo $result;
echo $conn->error;

?>