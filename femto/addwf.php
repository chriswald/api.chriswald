<?php
include "../commonapi/common.php";
include "../commonapi/PIC16F877A.php";

if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    addwf();
}

/*
 * PARAMS:
 * 0 <= W <= 255
 * 0x00 <= F <= 0x7F
 * 0 <= [F] <= 255
 * 0 <= D <= 1
 */
function addwf()
{
    $data = GetParams();
    $reg = GetF($data);
    $f = +RegGet($data, $reg) % 0x100;
    $w = +GetW($data) % 0x100;
    $d = +GetD($data) % 0x100;

    $result = ($w + $f) % 256;
    if ($d == 0)
    {
        SetW($data, $result);
    }
    else
    {
        RegSet($data, $reg, $result);
    }

    if ($result === 0)
    {
        SetStatusZero($data);
    }
    else
    {
        ClrStatusZero($data);
    }

    if ($w + $f > 0xFF)
    {
        SetStatusCarry($data);
    }
    else
    {
        ClrStatusCarry($data);
    }

    if (($w & 0x0F) + ($f & 0x0F) > 0x0F)
    {
        SetStatusDigitCarry($data);
    }
    else
    {
        ClrStatusDigitCarry($data);
    }

    SendArray($data);
}

?>
