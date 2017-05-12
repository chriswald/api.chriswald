<?php
include "../commonapi/common.php";
include "../commonapi/PIC16F877A.php";

if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    sublw();
}

/*
 * PARAMS:
 * 0 <= W <= 255
 * 0 <= K <= 255
 */
function sublw()
{
    $data = GetParams();
    $k = +GetK($data);
    $w = +GetW($data);

    $result = $k - $w;

    // Determine DC bit
    if (($k & 0x0F) + ((-$w) & 0x0F) > 15)
    {
        SetStatusDigitCarry($data);
    }
    else
    {
        ClrStatusDigitCarry($data);
    }

    if (($k & 0xFF) + ((-$w) & 0xFF) > 255)
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

    SetW($data, $result);

    SendArray($data);
}

?>
