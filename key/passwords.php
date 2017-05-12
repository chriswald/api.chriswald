<?php

function GetPassword($key)
{
    $fh = fopen("../key/passwords.config.js", "r");
    $data = fread($fh, filesize("../key/passwords.config.js"));
    fclose($fh);

    $Passwords = json_decode($data);
    return $Passwords->$key;
}

?>
