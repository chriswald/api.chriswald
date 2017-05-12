<?php

include_once "session.php";

function EchoResult($result)
{
    echo json_encode(array(
        "TokenIsAlive" => $result
    ));
}

function IsTokenAlive()
{
    $sessionToken = $_POST["SessionToken"];
    if ($sessionToken === null || $sessionToken === "")
    {
        EchoResult(False);
    }
    else
    {
        $session = new Session($sessionToken);
        EchoResult(!$session->TokenIsExpired());
    }
}

IsTokenAlive();

?>
