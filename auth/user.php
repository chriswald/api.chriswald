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
    private $id;

    public function __construct($sessionToken = "")
    {
        if ($sessionToken !== "")
        {
            $this->session = new Session($sessionToken);
            $this->security = new Security($this->session);
            $this->id = $this->GetUserId();
        }
    }

    public function Login($email, $password)
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

    public function GetEmail()
    {
        return $this->session->GetSessionEmail();
    }

    public function GetSession()
    {
        return $this->session;
    }

    public function GetSecurity()
    {
        return $this->security;
    }

    public function GetID()
    {
        return $this->id;
    }

    public function OtherUserExists($email)
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

    public function IsLoggedIn()
    {
        return !$this->session->TokenIsExpired();
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
            return $statement->fetchAll(PDO::FETCH_ASSOC)[0];
        }
    }

    private function _PasswordsMatch($userRow, $supPassword)
    {
        $hashPass = HashString($supPassword, $userRow["PasswordSalt"]);
        return ($hashPass["hashedString"] === $userRow["Password"]);
    }

    private function GetUserId()
    {
        $email = $this->session->GetSessionEmail();
        $query = "SELECT ID, Email FROM User WHERE Email = ? and User.Active = 1";

        $pdo = CreateDBConnection("useraccess");
        $statement = $pdo->prepare($query);
        $statement->execute([
            mysql_escape_string($email)
        ]);
        
        $props = $statement->fetch(PDO::FETCH_ASSOC);
        return $props["ID"];
    }
}

?>
