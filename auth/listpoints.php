<?php

include_once "user.php";
include_once "authcore.php";
include_once "createconnection.php";

function EchoResult($result)
{
    echo json_encode($result);
}

function ListPointsMySQL()
{
    $db = CreateDBConnection("useraccess");
    $statement = $db->prepare("SELECT * FROM SecurityPoint");
    $statement->execute();

    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function ListPoints()
{
    $sessionToken = $_POST["SessionToken"];
    $user = new User($sessionToken);

    if ($user->GetSecurity()->HasSecurityPoint(1)) // 1 = Create user security point
    {
        EchoResult(ListPointsMySQL());
    }
    else
    {
        EchoResult(array(
            "Result" => False,
            "Message" => "User not authorized"
        ));
    }
}

ListPoints();

?>
