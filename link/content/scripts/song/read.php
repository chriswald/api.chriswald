<?php

function ExecInit($params)
{
    $trackHash = $params["TrackHash"];
    $sessionToken = $params["SessionToken"];

    $pdo = CreateDBConnection("useraccess");
    $user = new User($sessionToken);

    $filenames = GetTrackFile($pdo, $trackHash, $user->GetID());

    if (count($filenames) !== 0)
    {
        $filename = $filenames[0]["File"];
        $fullpath = realpath("../../content.chriswald.com/music/$filename");
        $handle = fopen($fullpath, "rb");
        $contents = fread($handle, filesize($fullpath));
        fclose($handle);

        return $contents;
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