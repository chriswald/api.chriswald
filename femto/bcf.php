<?php
include "../commonapi/common.php";
include "../commonapi/PIC16F877A.php";

if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    bcf();
}

/*
 * PARAMS:
 * 0 <= W <= 255
 * 0x00 <= F <= 0x7F
 * 0 <= [F] <= 255
 * 0 <= D <= 1
 */
function bcf()
{
    $data = GetParams();
    $reg = GetF($data);
    $f = RegGet($data, $reg);
    $b = GetB($data);

    $mask = 0x01 << $b;
    $mask = ~$mask;
    $mask = $mask & 0xFF;
    $result = $f & $mask;

    RegSet($data, $reg, $result);

    SendArray($data);
}

?>
