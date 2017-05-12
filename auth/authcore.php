<?php

function WasPOST()
{
    return $_SERVER["REQUEST_METHOD"] === "POST";
}

function GetUserInfo()
{
    if (wasPOST())
    {
        $userInfo["email"] = $_POST["email"];
        $userInfo["password"] = $_POST["password"];
        return $userInfo;
    }
    else
    {
        return null;
    }
}

function HashString($string, $salt = "")
{
    if ($salt === "")
    {
        $salt = openssl_random_pseudo_bytes(2048);
    }

    $hash = hash_pbkdf2(
        "sha512",
        $string,
        $salt,
        4096 // 2**12 iterations
    );

    $values["string"] = $string;
    $values["salt"] = $salt;
    $values["hashedString"] = $hash;

    return $values;
}

?>
