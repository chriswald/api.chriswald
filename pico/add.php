<?php
include_once "../commonapi/common.php";
include_once "../auth/user.php";
include_once "../auth/authcore.php";

function Authenticate($securityPoint = -1)
{
    $sessionToken = $_POST["SessionToken"];
    $user = new User($sessionToken);

    return ($securityPoint === -1) || ($user->GetSecurity()->HasSecurityPoint($securityPoint));
}

function Add()
{
    if (Authenticate(3))
    {
        $ops = GetOps();

        $result = ($ops[0]*1) + ($ops[1]*1);

        ReturnResult($result, $ops);
    }
}

Add();
?>
