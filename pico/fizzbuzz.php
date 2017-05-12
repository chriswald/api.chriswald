<?php
include "../commonapi/common.php";

if ($_SERVER["REQUEST_METHOD"] === "POST")
{
    Recurse(1);
}

function Recurse($i)
{
    $true = 'return DoMod($i);';
    $false = '';

    $data = array("leftOp"=>$i, "rightOp" => 100, "trueBranch"=>$true, "falseBranch"=>$false);
    $return = PicoServiceRequest("lessthan", $data);

    eval($return["branch"]);
}

function DoMod($i)
{
    $data = array("leftOp"=>$i, "rightOp"=>3);
    $return = PicoServiceRequest("mod", $data);
    $mod3 = $return["result"];

    $data = array("leftOp"=>$i, "rightOp"=>5);
    $return = PicoServiceRequest("mod", $data);
    $mod5 = $return["result"];

    $true = "echo 'fizz';";
    $false = '';
    $data = array("leftOp"=>$mod3, "rightOp"=>0, "trueBranch"=>$true, "falseBranch"=>$false);
    $return = PicoServiceRequest("equal", $data);
    $mod3is0 = $return["result"];
    eval($return["branch"]);

    $true = "echo 'buzz';";
    $false = '';
    $data = array("leftOp"=>$mod5, "rightOp"=>0, "trueBranch"=>$true, "falseBranch"=>$false);
    $return = PicoServiceRequest("equal", $data);
    $mod5is0 = $return["result"];
    eval($return["branch"]);

    $true = '';
    $false = 'echo $i;';
    $data = array("leftOp"=>$mod3is0, "rightOp"=>$mod5is0, "trueBranch"=>$true, "falseBranch"=>$false);
    $return = PicoServiceRequest("or", $data);
    $mod5is0 = $return["result"];
    eval($return["branch"]);

    echo "\n";

    $true = 'Recurse($return[result]);';
    $false = 'Recurse($return[result]);';
    $data = array("leftOp"=>$i, "rightOp"=>1, "trueBranch"=>$true, "falseBranch"=>$false);
    $return = PicoServiceRequest("add", $data);
    eval($return["branch"]);
}
?>