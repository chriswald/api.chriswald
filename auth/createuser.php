<?php

include_once "user.php";
include_once "createconnection.php";
include_once "authcore.php";

function EchoResult($result, $reason = "", $email = "")
{
    echo json_encode(array(
        "Result" => $result,
		"Reason" => $reason,
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
	$email = $_POST["email"];
	if (!$user->OtherUserExists($email))
	{
		CreateUserMYSQL($email, $_POST["password"]);
		EchoResult(True, "", $email);
	}
	else
	{
		EchoResult(False, "A user with that email aready exists");
	}
}

CreateNewUser();

?>
