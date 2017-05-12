<?php

include_once "user.php";

function EchoResult($result)
{
    echo json_encode(array(
        "Result" => $result
    ));
}

function AssignSecurity()
{
    $sessionToken = $_POST["SessionToken"];

    $user = new User($sessionToken);

    if ($user->GetSecurity()->HasSecurityPoint(5)) // Modify security
    {
        $email = $_POST["email"];
        if ($email === "" || $email === null) $email = $user->GetEmail();

        if ($user->OtherUserExists($email))
        {
            $points = $_POST["Points"];
            $pointsArray = explode(",", $points);
            if ($user->GetSecurity()->AssignSecurityPoints($pointsArray, $email))
            {
                EchoResult(True);
            }
            else
            {
                EchoResult(False);
            }
        }
        else
        {
            EchoResult(False);
        }
    }
    else
    {
        EchoResult(False);
    }
}

AssignSecurity();

?>
