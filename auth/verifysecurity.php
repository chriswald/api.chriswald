<?php

include_once "user.php";

function VerifySecurity()
{
    $sessionToken = $_POST["SessionToken"];

    if (!$sessionToken)
    {
        return False;
    }

    $points = explode(",", $_POST["Points"]);

    if (count($points) == 0)
    {
        return False;
    }

    $user = new User($sessionToken);

    foreach ($points as $point)
    {
        $point = trim($point);
        if ($point)
        {
            if (!$user->GetSecurity()->HasSecurityPoint($point))
            {
                return False;
            }
        }
    }

    return True;
}

echo json_encode(VerifySecurity());

?>
