<?php
include_once "../auth/createconnection.php";
include_once "../auth/user.php";

function PollAddr($pdo, $clientID)
{
    $query = "SELECT IP, Port, LastUpdate FROM HomeClients WHERE HomeClients.ID = ?";

    $statement = $pdo->prepare($query);
    $statement->execute(array(
        mysql_escape_string($clientID)
    ));

    return $statement->fetch(PDO::FETCH_ASSOC);
}


function Main()
{
    $sessionToken = $_POST["SessionToken"];
    $user = new User($sessionToken);

    if ($user->GetSecurity()->HasSecurityPoint(7)) // Home Connect API - Read
    {
        $clientID = $_POST["ID"];
        if ($clientID === null) { return; }

        $pdo = CreateDBConnection("useraccess");

        echo json_encode(PollAddr($pdo, $clientID));
    }
}

Main();

?>
