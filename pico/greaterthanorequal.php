<?php
include "../commonapi/common.php";

if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    $ops = GetOps();
    $newops = array();

    $data = array("leftOp" => $ops[0], "rightOp" => $ops[1]);
    $return = PicoServiceRequest("lessthan", $data);
    $newops[1] = $return["result"];

    $data = array("rightOp" => $newops[1]);
    $return = PicoServiceRequest("not", $data);

    $result = $return["result"];

    ReturnResult($result, $ops);
}
?>
