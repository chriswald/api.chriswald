<?php
include "../commonapi/common.php";
include "../commonapi/PIC16F877A.php";

if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    xorwf();
}

/*
 * PARAMS:
 * 0 <= W <= 255
 * 0x00 <= F <= 0x7F
 * 0 <= [F] <= 255
 * 0 <= D <= 1
 */
function xorwf()
{
    $data = GetParams();
    $reg = GetF($data);
    $f = RegGet($data, $reg);
    $w = GetW($data);
    $d = GetD($data);

    $result = $f ^ $w;

    if ($result%256 === 0)
    {
        SetStatusZero($data);
    }
    else
    {
        ClrStatusZero($data);
    }

    if ($d == 0)
    {
        SetW($data, $result);
    }
    else
    {
        RegSet($data, $reg, $result);
    }

    SendArray($data);
}

?>
