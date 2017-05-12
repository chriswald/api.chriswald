<?php

function CreateDBConnection($database)
{
	$fh = fopen("dbaccess.config.js", "r");
	$data = fread($fh, filesize("dbaccess.config.js"));
	fclose($fh);

	$props = json_decode($data);
	$host = $props->Host;
	$username = $props->Username;
	$password = $props->Password;

	return new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
}

?>
