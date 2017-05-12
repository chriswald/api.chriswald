<?php

include_once "session.php";
include_once "security.php";

function EchoResult($result)
{
    echo json_encode(array(
        "Result" => $result,
    ));
}

function HasSecurityPoint()
{
    $sessionToken = $_POST["SessionToken"];
    $securityPoint = $_POST["SecurityPoint"];

    $session = new Session($sessionToken);
    $security = new Security($session);
    if ($security->HasSecurityPoint($securityPoint))
    {
        EchoResult(True);
    }
    else
    {
        EchoResult(False);
    }
}

HasSecurityPoint();

?>
