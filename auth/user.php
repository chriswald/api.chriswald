<?php

include_once "session.php";
include_once "security.php";
include_once "authcore.php";
include_once "createconnection.php";
include_once "../key/passwords.php";

class BadUserEmailException extends Exception { }

class PasswordMismatchException extends Exception { }

class User
{
    private $session = null;
    private $security = null;

    function __construct($sessionToken = "")
    {
        if ($sessionToken !== "")
        {
            $this->session = new Session($sessionToken);
            $this->security = new Security($this->session);
        }
    }

    function Login($email, $password)
    {
        $this->email = $email;
        $row = $this->_FindMatchingEmail($email);

        if ($row === False)
        {
            throw new BadUserEmailException("No matching user name", 1);
        }

        if (!$this->_PasswordsMatch($row, $password))
        {
            throw new PasswordMismatchException("Passwords do not match", 1);
        }
        else
        {
            $this->session = new Session();
            $this->session->GenerateSessionToken($email);
            $this->security = new Security($this->session);
        }
    }

    function GetEmail()
    {
        return $this->session->GetSessionEmail();
    }

    function GetSession()
    {
        return $this->session;
    }

    function GetSecurity()
    {
        return $this->security;
    }

    function OtherUserExists($email)
    {
        if ($this->security->HasSecurityPoint(1)) // Create user
        {
            return !!$this->_FindMatchingEmail($email);
        }
        else
        {
            return False;
        }
    }

    private function _FindMatchingEmail($email)
    {
        $query = "SELECT * FROM User WHERE User.Email = ? and User.Active = 1";

        $pdo = CreateDBConnection("useraccess");
        $statement = $pdo->prepare($query);
        $statement->execute(array(
            mysql_escape_string($email)
        ));
        
        if ($statement->rowCount() === 0)
        {
            return False;
        }
        else
        {
            return $statement->fetch(PDO::FETCH_ASSOC);
        }
    }

    private function _PasswordsMatch($userRow, $supPassword)
    {
        $hashPass = HashString($supPassword, $userRow["PasswordSalt"]);
        return ($hashPass["hashedString"] === $userRow["Password"]);
    }
}

?>
