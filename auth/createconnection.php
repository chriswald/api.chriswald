<?php

function CreateDBConnection($database)
{
	$configFile = "../auth/dbaccess.config.js";
	$fh = fopen($configFile, "r");
	$data = fread($fh, filesize($configFile));
	fclose($fh);

	$props = json_decode($data);
	$host = $props->Host;
	$username = $props->Username;
	$password = $props->Password;

	return new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
}

?>
