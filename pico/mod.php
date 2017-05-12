<?php
include "../commonapi/common.php";

if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    $ops = GetOps();

    $result = Recurse($ops[0], $ops[1]);

    ReturnResult($result, $ops);
}

function Recurse($op1, $op2)
{
    $true = 'return $op1;';
    $false = 'return DoSubtract($op1, $op2);';
    $data = array("leftOp"=>$op1, "rightOp"=>$op2, "trueBranch"=>$true, "falseBranch"=>$false);
    $return = PicoServiceRequest("lessthan", $data);
    return eval($return["branch"]);
}

function DoSubtract($op1, $op2)
{
    $branch = 'return Recurse($return[result],$op2);';
    $data = array("leftOp"=>$op1, "rightOp"=>$op2, "trueBranch"=>$branch, "falseBranch"=>$branch);
    $return = PicoServiceRequest("subtract", $data);
    return eval($return["branch"]);
}
?>
