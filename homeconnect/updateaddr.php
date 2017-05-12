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

function UpdateExistingHomeClientAddr($pdo, $clientID, $clientAddr)
{
  $query = "UPDATE `HomeClients` SET `IP`=?,`LastUpdate`=NOW() WHERE `ID`=?";

  $statement = $pdo->prepare($query);
  $statement->execute(array(
      mysql_escape_string($clientAddr),
      mysql_escape_string($clientID)
  ));
}

function AddNewHomeClientAddr($pdo, $clientID, $clientAddr)
{
  $query = "INSERT INTO `HomeClients`(`ID`, `IP`, `LastUpdate`) VALUES (?,?,NOW())";

  $statement = $pdo->prepare($query);
  $statement->execute(array(
      mysql_escape_string($clientID),
      mysql_escape_string($clientAddr)
  ));
}

function Main()
{
    $sessionToken = $_POST["SessionToken"];
    $user = new User($sessionToken);

    if ($user->GetSecurity()->HasSecurityPoint(6)) // Home Connect API - Write
    {
        $clientID = $_POST["ID"];
        $clientAddr = $_SERVER["REMOTE_ADDR"];
        if ($clientID === null) { return; }

        $pdo = CreateDBConnection("useraccess");

        if (DoesHomeClientExist($pdo, $clientID))
        {
            UpdateExistingHomeClientAddr($pdo, $clientID, $clientAddr);
        }
        else
        {
            AddNewHomeClientAddr($pdo, $clientID, $clientAddr);
        }
    }
}

Main();

?>
