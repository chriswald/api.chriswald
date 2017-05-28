<?php

include_once "linkexception.php";
include_once "linkapipoint.php";
include_once "datagroups.php";

include_once "securitysection.php";
include_once "httpmethodsection.php";
include_once "datasourcesection.php";
include_once "requestparameterssection.php";
include_once "queryparameterssection.php";

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
                SetUpDataGroups($apiPoint);
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

        $httpMethodSection = new HttpMethodSection($apiPoint->Config());
        if (!$httpMethodSection->RequestMethodIsCorrect())
        {
            throw new LinkException(400, "The endpoint does not service the {$_SERVER['REQUEST_METHOD']} method");
        }

        $dataSourceSection = new DataSourceSection($apiPoint->Config());
        if (!$dataSourceSection->HasSection && $dataSourceSection->IsValid)
        {
            throw new LinkException(500, "Data sources are missing or invalid");
        }

        $requestParametersSection = new RequestParametersSection($apiPoint->Config());
        if ($requestParametersSection->HasSection && !$requestParametersSection->IsValid)
        {
            throw new LinkException(500, "Request parameters section is invalid");
        }

        $queryParametersSection = new QueryParametersSection($apiPoint->Config());
        if ($queryParametersSection->HasSection && !$queryParametersSection->IsValid)
        {
            throw new LinkException(500, "Query parameters section is invalid");
        }

        return true;
    }

    private function SetUpDataGroups(LinkApiPoint $apiPoint)
    {
        if (isset($apiPoint->Config()->DataGroups))
        {
            foreach ($apiPoint->Config()->DataGroups as $group)
            {
                $this->_dataGroups->InitializeGroup($group);
            }
        }
    }

    private function GetSessionToken()
    {
        return $_POST["SessionToken"];
    }
}

?>