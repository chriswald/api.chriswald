<?php
include "../commonapi/common.php";
include "../commonapi/PIC16F877A.php";

if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    clrw();
}

function clrw()
{
	$data = GetParams();
	SetW($data, 0);
	SetStatusZero($data);
	SendArray($data);
}

?>