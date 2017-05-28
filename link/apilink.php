<?php

include_once "linkexception.php";
include_once "linkapipoint.php";
include_once "datagroups.php";

include_once "securitysection.php";

include_once "../auth/createconnection.php";
include_once "../auth/user.php";

class ApiLink
{
    private $_dataGroups;

    public function __construct()
    {
        $this->_dataGroups = new DataGroups();
    }

    public function ExecuteConfig(string $apiPoint, &$httpStatusCode, &$responseObject)
    {
        try
        {
            $apiPoint = new LinkApiPoint($apiPoint);
            if (PrerequisitsMet($apiPoint))
            {
                
            }
        }
        catch (LinkException $e)
        {
            $httpStatusCode = $e->getStatusCode();
            $responseObject = ["Error" => $e->getMessage()];
        }
    }

    private function PrerequisitsMet(LinkApiPoint $apiPoint)
    {
        $securitySection = new SecuritySection($apiPoint->Config());
        if (!$securitySection->ValidateUser(GetSessionToken()))
        {
            throw new LinkException(400, "Unauthorized");
        }
    }

    private function GetSessionToken()
    {
        return $_POST["SessionToken"];
    }
}

?>