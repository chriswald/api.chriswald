<?php
include "../commonapi/common.php";

if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    $ops = GetOps();

    $data = array("rightOp" => $ops[1]);
    $return = PicoServiceRequest("negate", $data);

    $data = array("leftOp" => $ops[0], "rightOp" => $return["result"]);
    $return = PicoServiceRequest("add", $data);

    $result = $return["result"];

    ReturnResult($result, $ops);
}
?>

