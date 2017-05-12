<?php

include_once "session.php";

function EchoResult($result, $token = "")
{
    echo json_encode(array(
        "Result" => $result,
        "SessionToken" => $token
    ));
}

function RenewToken()
{
    $sessionToken = $_POST["SessionToken"];
    if ($sessionToken === null || $sessionToken === "")
    {
        EchoResult(False);
    }
    else
    {
        $session = new Session($sessionToken);
        $newSession = $session->RenewSession();
        if ($newSession === null)
        {
            EchoResult(False);
        }
        else
        {
            EchoResult(True, $newSession->GetSessionToken());
        }
    }
}

RenewToken();

?>
