<?php

include_once "session.php";
include_once "authcore.php";
include_once "createconnection.php";

class Security
{
    private $session;

    function __construct($session)
    {
        $this->session = $session;
    }

    function HasSecurityPoint($secPoint)
    {
        if ($this->session->TokenIsExpired())
        {
            return False;
        }

        $email = $this->session->GetSessionEmail();

        $pdo = CreateDBConnection("useraccess");
        $query = "SELECT `SecurityClass`.`SecurityPointID` " .
                    "from `SecurityClass` " .
                    "JOIN `User` ON `User`.`Security`=`SecurityClass`.`ID` " .
                    "WHERE `User`.`Email`=? " .
                    "AND `SecurityClass`.`SecurityPointID`=?";
        $statement = $pdo->prepare($query);
        $statement->execute(array(
            mysql_escape_string($email),
            mysql_escape_string($secPoint)
        ));

        return $statement->rowCount() > 0;
    }

    function GetSecurityClass($email = "")
    {
        if ($email === "") $email = $this->session->GetSessionEmail();

        if ($this->HasSecurityPoint(5)) // Modify security
        {
            $pdo = CreateDBConnection("useraccess");
            $query = "SELECT Security FROM User WHERE User.Email=?";
            $statement = $pdo->prepare($query);
            $statement->execute(array(
                mysql_escape_string($email)
            ));

            if ($statement->rowCount() > 0)
            {
                $row = $statement->fetch(PDO::FETCH_ASSOC);
                return intval($row["Security"]);
            }
            else
            {
                return -1;
            }
        }
        else
        {
            return -1;
        }
    }

    function AssignSecurityPoints($pointArray, $email = "")
    {
        if ($email === "") $email = $this->session->GetSessionEmail();

        if ($this->HasSecurityPoint(5)) // Modify security
        {
            $secClass = $this->GetSecurityClass($email);
            if ($secClass === -1) return False;

            $newSecClass = $secClass;
            if ($secClass === 0) $newSecClass = $this->_NewSecurityClassID();

            $pdo = CreateDBConnection("useraccess");
            $query = "INSERT INTO SecurityClass (ID, SecurityPointID) VALUES (?, ?)";
            $statement = $pdo->prepare($query);

            foreach ($pointArray as $point)
            {
                $statement->execute(array(
                    $newSecClass,
                    $point
                ));
            }

            if ($newSecClass !== $secClass)
            {
                $query = "UPDATE `User` SET `Security` = ? WHERE `User`.`Email` = ?";
                $statement = $pdo->prepare($query);
                $statement->execute(array(
                    $newSecClass,
                    $email
                ));
            }

            return True;
        }
        else
        {
            return False;
        }
    }

    private function _NewSecurityClassID()
    {
        $pdo = CreateDBConnection("useraccess");
        $query = "SELECT MAX(ID) FROM SecurityClass";
        $statement = $pdo->prepare($query);
        $statement->execute();
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        return $row["MAX(ID)"] + 1;
    }
}

?>
