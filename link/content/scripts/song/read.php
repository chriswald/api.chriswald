<?php

function ExecInit($params)
{
    $trackHash = $params["TrackHash"];
    $sessionToken = $params["SessionToken"];

    $pdo = CreateDBConnection("useraccess");
    $user = new User($sessionToken);

    $file = GetTrackFile($pdo, $trackHash, $user->GetID());

    if (count($file) !== 0)
    {
        $file = $file[0]["File"];
        return [
            "Track" => "https://content.chriswald.com/music/$file"
        ];
    }
}

function GetTrackFile($pdo, $trackHash, $userID)
{
    $query = "SELECT File from Tracks join TrackAssoc on Tracks.Hash = TrackAssoc.TrackHash where Tracks.Hash = ? and TrackAssoc.UserID = ?";

    $statement = $pdo->prepare($query);
    $statement->execute([
        mysql_escape_string($trackHash),
        mysql_escape_string($userID)
    ]);

    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

?>