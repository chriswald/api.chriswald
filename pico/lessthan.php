<?php
include "../commonapi/common.php";

if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    $ops = GetOps();

    $result = $ops[0] < $ops[1];

    ReturnResult($result, $ops);
}
?>