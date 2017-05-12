<?php
include "../commonapi/common.php";
include "../commonapi/PIC16F877A.php";

if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    addlw();
}

/*
 * PARAMS:
 * 0 <= W <= 255
 * 0 <= K <= 255
 */
function addlw()
{
    $data = GetParams();
    $k = +GetK($data);
    $w = +GetW($data);
    
    $result = $w + $k;
    SetW($data, $result);

    if ($result%256 === 0)
    {
        SetStatusZero($data);
    }
    else
    {
        ClrStatusZero($data);
    }

    if (($w & 0x0F) + ($k & 0x0F) > 31)
    {
        SetStatusDigitCarry($data);
    }
    else
    {
        ClrStatusDigitCarry($data);
    }

    if ($result > 255)
    {
        SetStatusCarry($data);
    }
    else
    {
        ClrStatusCarry($data);
    }

    SendArray($data);
}

?>