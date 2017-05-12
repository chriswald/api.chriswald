<?php
include "../commonapi/common.php";
include "../commonapi/PIC16F877A.php";

if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    movwf();
}

/*
 * PARAMS:
 * 0 <= W <= 255
 * 0x00 <= F <= 0x7F
 * 0 <= [F] <= 255
 * 0 <= D <= 1
 */
function movwf()
{
    $data = GetParams();
    $reg = GetF($data);
    $w = +GetW($data) % 0x100;

    $result = $w;
    RegSet($data, $reg, $result);

    SendArray($data);
}

?>
