<?php
include_once "../auth/createconnection.php";
include_once "../auth/user.php";

function DoesHomeClientExist($pdo, $clientID)
{
  $query = "SELECT * FROM HomeClients WHERE HomeClients.ID = ?";

  $statement = $pdo->prepare($query);
  $statement->execute(array(
      mysql_escape_string($clientID)
  ));

  return $statement->rowCount() != 0;
}

function UpdateExistingHomeClientPort($pdo, $clientID, $port)
{
  $query = "UPDATE `HomeClients` SET `Port`=?,`LastUpdate`=NOW() WHERE `ID`=?";

  $statement = $pdo->prepare($query);
  $statement->execute(array(
      mysql_escape_string($port),
      mysql_escape_string($clientID)
  ));
}

function AddNewHomeClientPort($pdo, $clientID, $port)
{
  $query = "INSERT INTO `HomeClients`(`ID`, `Port`, `LastUpdate`) VALUES (?,?,NOW())";

  $statement = $pdo->prepare($query);
  $statement->execute(array(
      mysql_escape_string($clientID),
      mysql_escape_string($port)
  ));
}

function Main()
{
    $sessionToken = $_POST["SessionToken"];
    $user = new User($sessionToken);

    if ($user->GetSecurity()->HasSecurityPoint(6)) // Home Connect API - Write
    {
        $clientID = $_POST["ID"];
        $port = $_POST["Port"];
        if ($clientID === null) { return; }

        $pdo = CreateDBConnection("useraccess");

        if (DoesHomeClientExist($pdo, $clientID))
        {
            UpdateExistingHomeClientPort($pdo, $clientID, $port);
        }
        else
        {
            AddNewHomeClientPort($pdo, $clientID, $port);
        }
    }
}

Main();

?>
