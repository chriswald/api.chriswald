<?php
include "../commonapi/common.php";

if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    exec("nop.out");
    $result = true;
}
else
{
    $result = false;
}

ReturnResult($result, "");
?>