<?php
include "../commonapi/common.php";
include "../commonapi/PIC16F877A.php";

if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    movf();
}

/*
 * PARAMS:
 * 0 <= W <= 255
 * 0x00 <= F <= 0x7F
 * 0 <= [F] <= 255
 * 0 <= D <= 1
 */
function movf()
{
    $data = GetParams();
    $reg = GetF($data);
    $f = +RegGet($data, $reg) % 0x100;
    $d = +GetD($data) % 0x100;
    $c = GetStatusCarry($data);

    $result = $f << 1;

    if ($result & 0x100 !== 0)
    {
        SetStatusCarry($data);
    }
    else
    {
        ClrStatusCarry($data);
    }

    $result = ($result % 256) | $c;

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
