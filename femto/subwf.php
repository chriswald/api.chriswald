<?php
include "../commonapi/common.php";
include "../commonapi/PIC16F877A.php";

if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    subwf();
}

/*
 * PARAMS:
 * 0 <= W <= 255
 * 0x00 <= F <= 0x7F
 * 0 <= [F] <= 255
 * 0 <= D <= 1
 */
function subwf()
{
    $data = GetParams();
    $reg = GetF($data);
    $f = +RegGet($data, $reg) % 0x100;
    $w = +GetW($data) % 0x100;
    $d = +GetD($data) % 0x100;

    $result = $f - $w;

    // Determine DC bit
    if (($f & 0x0F) + ((-$w) & 0x0F) > 15)
    {
        SetStatusDigitCarry($data);
    }
    else
    {
        ClrStatusDigitCarry($data);
    }

    if (($f & 0xFF) + ((-$w) & 0xFF) > 255)
    {
        SetStatusCarry($data);
    }
    else
    {
        ClrStatusCarry($data);
    }

    if ($result%256 === 0)
    {
        SetStatusZero($data);
    }
    else
    {
        ClrStatusZero($data);
    }

    if ($result < 0) $result += 256;
    if ($result > 255) $result = $result%256;

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
