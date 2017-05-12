<?php
include "../commonapi/common.php";
include "../commonapi/PIC16F877A.php";

if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    clrf();
}

function clrf()
{
    $data = GetParams();
    $reg = GetF($data);

    $result = 0;
    RegSet($data, $reg, $result);

    SetStatusZero($data);

    SendArray($data);
}

?>
