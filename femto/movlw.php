<?php
include "../commonapi/common.php";
include "../commonapi/PIC16F877A.php";

if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    movlw();
}

/*
 * PARAMS:
 * 0 <= W <= 255
 * 0 <= K <= 255
 */
function movlw()
{
    $data = GetParams();
    $k = +GetK($data);
    $w = +GetW($data);
    
    SetW($data, $k);

    SendArray($data);
}

?>