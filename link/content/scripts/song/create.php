<?php

include_once "getid3/getid3.php";

function ExecInit($params)
{
    $track = $params["Track"];
    $sessionToken = $params["SessionToken"];
    $props = GetTrackInfo($track, $sessionToken);

    SaveTrack($track, $props["TrackFile"]);

    $pdo = CreateDBConnection("useraccess");

    if (!DoesTrackExist($pdo, $props["TrackHash"]))
    {
        AddTrack($props["TrackHash"], $props["Title"]);
    }

    if (!DoesUserAssocExist($pdo, $props["TrackHash"], $props["UserID"]))
    {
        AddUserAssoc($pdo, $props["TrackHash"], $props["UserID"]);
    }

    return $props;
}

function GetTrackInfo($track, $sessionToken)
{
    $filename = $track["tmp_name"];
    
    $trackHash = TrackHash($filename);

    $user = new User($sessionToken);

    $getID3 = new getID3();
    $fileinfo = $getID3->Analyze($track["tmp_name"]);

    $info = pathinfo($track["name"]);
    $ext = $info["extension"];
    $newname = "$trackHash.$ext";

    return [
        "Title" => $fileinfo["tags"]["id3v1"]["title"][0],
        "Artist" => $fileinfo["tags"]["id3v1"]["artist"][0],
        "Album" => $fileinfo["tags"]["id3v1"]["album"][0],
        "TrackHash" => $trackHash,
        "TrackFile" => $newname,
        "UserID" => $user->GetID()
    ];
}

function SaveTrack($track, $newname)
{
    $target = "../../content.chriswald.com/music/$newname";
    move_uploaded_file($track["tmp_name"], $target);
}

function TrackHash($filename)
{
    return hash_file("md5", $filename);
}

function DoesTrackExist($pdo, $trackHash)
{
    $query = "SELECT * FROM Tracks WHERE Hash = ?";

    $statement = $pdo->prepare($query);
    $statement->execute([
        mysql_escape_string($trackHash)
    ]);

    return ($statement->rowCount() !== 0);
}

function AddTrack($pdo, $trackHash, $title)
{
    $query = "INSERT INTO `Tracks` (`Hash`, `Title`) VALUES (?, ?)";

    $statement = $pdo->prepare($query);
    $statement->execute([
        mysql_escape_string($trackHash),
        mysql_escape_string($title)
    ]);
}

function DoesUserAssocExist($pdo, $trackHash, $userID)
{
    $query = "SELECT * FROM TrackAssoc WHERE TrackHash = ? AND UserID = ?";

    $statement = $pdo->prepare($query);
    $statement->execute([
        mysql_escape_string($trackHash),
        mysql_escape_string($userID)
    ]);

    return ($statement->rowCount() !== 0);
}

function AddUserAssoc($pdo, $trackHash, $userID, $filename)
{
    $query = "INSERT INTO `TrackAssoc` (`TrackHash`, `UserID`, `File`) VALUES (?, ?, ?)";

    $statement = $pdo->prepare($query);
    $statement->execute([
        mysql_escape_string($trackHash),
        mysql_escape_string($userID),
        mysql_escape_string($filename)
    ]);
}

?>