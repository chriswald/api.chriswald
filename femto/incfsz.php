<?php
include "../commonapi/common.php";
include "../commonapi/PIC16F877A.php";

if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    incfsz();
}

/*
 * PARAMS:
 * 0 <= W <= 255
 * 0x00 <= F <= 0x7F
 * 0 <= [F] <= 255
 * 0 <= D <= 1
 */
function incfsz()
{
    $data = GetParams();
    $reg = GetF($data);
    $f = +RegGet($data, $reg) % 0x100;
    $d = +GetD($data) % 0x100;

    $result = ($f++ % 256);

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
        exec("nop.out");
    }

    SendArray($data);
}

?>
