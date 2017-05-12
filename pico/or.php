<?php
include "../commonapi/common.php";

if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    $ops = GetOps();
    $newops = array();

    $data = array("rightOp" => $ops[0]);
    $return = PicoServiceRequest("not", $data);
    $newops[0] = $return["result"];

    $data = array("rightOp" => $ops[1]);
    $return = PicoServiceRequest("not", $data);
    $newops[1] = $return["result"];

    $data = array("leftOp" => $newops[0], "rightOp" => $newops[1]);
    $return = PicoServiceRequest("and", $data);

    $data = array("rightOp" => $return["result"]);
    $return = PicoServiceRequest("not", $data);

    $result = $return["result"];

    ReturnResult($result, $ops);
}
?>

