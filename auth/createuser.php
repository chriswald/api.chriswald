<?php

include_once "user.php";
include_once "createconnection.php";
include_once "authcore.php";

function EchoResult($result, $email = "")
{
    echo json_encode(array(
        "Result" => $result,
        "Email" => $email
    ));
}

function CreateUserMYSQL($email, $password)
{
    $email = mysql_escape_string($email);
    $hashPass = HashString($password);

    $db = CreateDBConnection("useraccess");
    $statement = $db->prepare("INSERT INTO `User` (`ID`, `Email`, `Password`, `PassNeedsReset`, `Security`, `PasswordSalt`, `Active`) VALUES(NULL, ?, ?, '0', '0', ?, '1')");
    $statement->execute(array(
        $email,
        $hashPass["hashedString"],
        $hashPass["salt"]));
}

function CreateNewUser()
{
    $sessionToken = $_POST["SessionToken"];

    $user = new User($sessionToken);

    if ($user->GetSecurity()->HasSecurityPoint(1)) // 1 = Create user security point
    {
        $email = $_POST["email"];
        if (!$user->OtherUserExists($email))
        {
            CreateUserMYSQL($email, $_POST["password"]);
            EchoResult(True, $email);
        }
        else
        {
            EchoResult("email");
        }
    }
    else
    {
        EchoResult("security");
    }
}

CreateNewUser();

?>
