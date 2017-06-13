<?php

function ExecInit($params)
{
    $sessionToken = $params["SessionToken"];

    $pdo = CreateDBConnection("useraccess");
    $user = new User($sessionToken);

    $filenames = GetFiles($pdo, $user->GetID());
    return $filenames;
}

function GetFiles($pdo, $userID)
{
    $query = "SELECT Hash, Title from Tracks join TrackAssoc on Tracks.Hash = TrackAssoc.TrackHash where TrackAssoc.UserID = ?";

    $statement = $pdo->prepare($query);
    $statement->execute([
        mysql_escape_string($userID)
    ]);

    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

?>