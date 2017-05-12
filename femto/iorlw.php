<?php
include "../commonapi/common.php";
include "../commonapi/PIC16F877A.php";

if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    iorlw();
}

/*
 * PARAMS:
 * 0 <= W <= 255
 * 0 <= K <= 255
 */
function iorlw()
{
    $data = GetParams();
    $k = +GetK($data);
    $w = +GetW($data);
    
    $result = $w | $k;
    SetW($data, $result);

    if ($result === 0)
	{
        SetStatusZero($data);
	}
	else
	{
        ClrStatusZero($data);
	}

    SendArray($data);
}

?>