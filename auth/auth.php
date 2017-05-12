<?php

include_once "authcore.php";
include_once "user.php";

function AuthenticateUser()
{
    $userInfo = GetUserInfo();
    if ($userInfo === null)
    {
        header("HTTP/1.0 400 Bad Request");
        exit;
    }
    header("Content-Type: application/json");
    $user = new User();

    try {
        $user->Login($userInfo["email"], $userInfo["password"]);
        echo json_encode(array(
            "LoginResult" => True,
            "Reason" => "",
            "SessionToken" => $user->GetSession()->GetSessionToken()
        ));
    } catch (BadUserEmailException $e) {
        echo json_encode(array(
            "LoginResult" => False,
            "Reason" => "Invalid email address",
            "SessionToken" => ""
        ));
    } catch (PasswordMismatchException $e) {
        echo json_encode(array(
            "LoginResult" => False,
            "Reason" => "Incorrect password",
            "SessionToken" => ""
        ));
    }
}

AuthenticateUser();

?>
