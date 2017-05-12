<?php
include "../commonapi/common.php";
include "../commonapi/PIC16F877A.php";

if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    btfsc();
}

/*
 * PARAMS:
 * 0 <= W <= 255
 * 0x00 <= F <= 0x7F
 * 0 <= [F] <= 255
 * 0 <= D <= 1
 */
function btfsc()
{
    $data = GetParams();
    $reg = GetF($data);
    $f = RegGet($data, $reg);
    $b = GetB($data);

    $mask = 0x01 << $b;
    $result = $f & $mask;

    if ($result === 0)
    {
        exec("nop.out");
    }

    SendArray($data);
}

?>
