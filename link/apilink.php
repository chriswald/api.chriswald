<?php

include_once "linkexception.php";
include_once "linkapipoint.php";
include_once "datagroups.php";

include_once "securitysection.php";
include_once "httpmethodsection.php";
include_once "datasourcesection.php";
include_once "requestparameterssection.php";
include_once "queryparameterssection.php";
include_once "datagroupssection.php";
include_once "resultsection.php";
include_once "parameterlistsection.php";
include_once "parametersection.php";
include_once "datasource.php";

include_once "../auth/createconnection.php";
include_once "../auth/user.php";

class ApiLink
{
    private $_dataGroups;

    private $_securitySection;
    private $_httpMethodSection;
    private $_dataSourceSection;
    private $_requestParametersSection;
    private $_queryParametersSection;
    private $_fileParametersSection;
    private $_dataGroupsSection;

    public function __construct()
    {
        $this->_dataGroups = new DataGroups();
    }

    public function ExecuteConfig($apiPoint, &$httpStatusCode, &$responseContentType, &$responseObject)
    {
        try
        {
            $apiPoint = new LinkApiPoint($apiPoint);
            if ($this->IsRedirectorConfig($apiPoint))
            {
                $this->ExecuteRedirectorConfig($apiPoint, $httpStatusCode, $responseContentType, $responseObject);
            }
            else
            {
                $this->ExecuteDirectConfig($apiPoint, $httpStatusCode, $responseContentType, $responseObject);
            }
        }
        catch (LinkException $e)
        {
            $httpStatusCode = $e->getStatusCode();
            $responseContentType = "application/json";
            $responseObject = json_encode(["Error" => $e->getMessage()]);
        }
    }

    private function PrerequisitsMet(LinkApiPoint $apiPoint)
    {
        $this->_securitySection = new SecuritySection($apiPoint->Config());
        if (!$this->_securitySection->ValidateUser($this->GetSessionToken()))
        {
            throw new LinkException(400, "Unauthorized");
        }

        $this->_dataSourceSection = new DataSourceSection($apiPoint->Config());
        if (!$this->_dataSourceSection->HasSection && $this->_dataSourceSection->IsValid)
        {
            throw new LinkException(500, "Data sources are missing or invalid");
        }

        $this->_requestParametersSection = new RequestParametersSection($apiPoint->Config());
        if ($this->_requestParametersSection->HasSection && !$this->_requestParametersSection->IsValid)
        {
            throw new LinkException(500, "Request parameters section is invalid");
        }

        $this->_queryParametersSection = new QueryParametersSection($apiPoint->Config());
        if ($this->_queryParametersSection->HasSection && !$this->_queryParametersSection->IsValid)
        {
            throw new LinkException(500, "Query parameters section is invalid");
        }

        $this->_fileParametersSection = new FileParametersSection($apiPoint->Config());
        if ($this->_fileParametersSection->HasSection && !$this->_fileParametersSection->IsValid)
        {
            throw new LinkException(500, "File parameters section is invalid");
        }

        return true;
    }

    private function IsRedirectorConfig(LinkApiPoint $apiPoint)
    {
        $this->_httpMethodSection = new HttpMethodSection($apiPoint->Config());
        return $this->_httpMethodSection->IsRedirector;
    }

    private function ExecuteDirectConfig(LinkApiPoint $apiPoint, &$httpStatusCode, &$responseContentType, &$responseObject)
    {
        if ($this->PrerequisitsMet($apiPoint))
        {
            if ($this->_httpMethodSection->RequestMethodIsCorrect())
            {
                $this->SetUpDataGroups($apiPoint);
                $response = $this->Process($apiPoint);
                $httpStatusCode = $response["Status"];
                $responseContentType = $response["ContentType"];
                $responseObject = $response["Object"];
            }
            else
            {
                throw new LinkException(400, "The endpoint does not service the {$_SERVER['REQUEST_METHOD']} method");
            }
        }
    }

    private function ExecuteRedirectorConfig(LinkApiPoint $apiPoint, &$httpStatusCode, &$responseContentType, &$responseObject)
    {
        $config = $this->_httpMethodSection->ConfigForHttpMethod($_SERVER["REQUEST_METHOD"]);
        $subConfig = new ApiLink();
        $subConfig->ExecuteConfig($config, $httpStatusCode, $responseContentType, $responseObject);
    }

    private function SetUpDataGroups(LinkApiPoint $apiPoint)
    {
        $this->_dataGroupsSection = new DataGroupsSection($apiPoint->Config());
        if ($this->_dataGroupsSection->IsValid)
        {
            foreach ($this->_dataGroupsSection->SectionValue as $group)
            {
                $this->_dataGroups->InitializeGroup($group);
            }
        }
    }

    private function Process(LinkApiPoint $apiPoint)
    {
        $dataSourceResult = [];

        foreach ($this->_dataSourceSection->SectionValue as $source)
        {
            if ($this->GetParameterValues($source, $parameterDict))
            {
                $dataSource = new DataSource($source);
                $dataSourceResult = $dataSource->Execute($parameterDict);

                $this->SetResultValue($source, $dataSourceResult);
            }
        }

        $this->CreateResultObject($apiPoint, $responseObject);
        return $responseObject;
    }

    private function GetParameterValues($dataSource, &$dict)
    {
        $parametersSection = new ParameterListSection($dataSource);
        foreach ($parametersSection->SectionValue as $paramDef)
        {
            if (!$this->GetSingleParameterValue($paramDef, $dict))
            {
                return false;
            }
        }

        return true;
    }

    private function GetSingleParameterValue($paramDef, &$destDict)
    {
        $parameterSection = new ParameterSection($paramDef);

        $source = $parameterSection->SectionValue->Source;
        $srcName = $parameterSection->SectionValue->SourceParameterName;
        $destName = $parameterSection->SectionValue->DestinationParameterName;

        if (strcasecmp($source, "RequestParameters") === 0)
        {
            if (in_array($srcName, $this->_requestParametersSection->SectionValue))
            {
                $destDict[$destName] = $_POST[$srcName];
            }
            else 
            {
                throw new LinkException(500, "RequestParameters does not have the parameter {$srcName}");
            }
        }
        else if (strcasecmp($source, "QueryParameters") === 0)
        {
            if (in_array($srcName, $this->_queryParametersSection->SectionValue))
            {
                $destDict[$destName] = $_GET[$srcName];
            }
            else 
            {
                throw new LinkException(500, "QueryParameters does not have the parameter {$srcName}");
            }
        }
        else if (strcasecmp($source, "FileParameters") === 0)
        {
            if (in_array($srcName, $this->_fileParametersSection->SectionValue))
            {
                $destDict[$destName] = $_FILE[$srcName];
            }
            else 
            {
                throw new LinkException(500, "FileParameters does not have the parameter {$srcName}");
            }
        }
        else if (strcasecmp($source, "DataGroups") === 0)
        {
            if (isset($parameterSection->SectionValue->GroupName) && 
                $parameterSection->SectionValue->GroupName !== "")
            {
                $grpName = $parameterSection->SectionValue->GroupName;
                if (in_array($grpName, $this->_dataGroupsSection->SectionValue))
                {
                    $destDict[$destName] = $this->_dataGroups->GetValue($grpName, $srcName);
                }
                else 
                {
                    throw new LinkException(500, "No group with the name {$grpName}");
                }
            }
            else
            {
                throw new LinkException(500, "Group name of parameter is not specified");
            }
        }
        else
        {
            throw new LinkException(500, "Parameter source {$source} is not a supported source");
        }

        return true;
    }

    private function SetResultValue($dataSource, $dataSourceResult)
    {
        $resultSection = new ResultSection($dataSource);
        if ($resultSection->IsValid && $resultSection->RequiredGroupExists($this->_dataGroupsSection))
        {
            $dataGroup = $resultSection->SectionValue->DataGroup;
            $nameInGroup = $resultSection->SectionValue->NameInGroup;
            $this->_dataGroups->SetValue($dataGroup, $nameInGroup, $dataSourceResult);
        }
    }

    private function CreateResultObject(LinkApiPoint $apiPoint, &$responseObject)
    {
        $resultSection = new ResultSection($apiPoint->Config());
        if ($resultSection->IsValid && $resultSection->RequiredGroupExists($this->_dataGroupsSection))
        {
            $dataGroup = $resultSection->SectionValue->DataGroup;
            $nameInGroup = $resultSection->SectionValue->NameInGroup;
            $obj = $this->_dataGroups->GetValue($dataGroup, $nameInGroup);

            $obj = $this->HandleContentType($resultSection, $obj, $ctype);

            $responseObject = $this->ReturnResponse(200, $ctype, $obj);
        }
        else
        {
            throw new LinkException(500, "The specified result data group does not exist");
        }
    }

    private function HandleContentType(ResultSection $resultSection, $object, &$ctype)
    {
        if (isset($resultSection->SectionValue->ContentType))
        {
            $ctype = $resultSection->SectionValue->ContentType;

            if (strcasecmp($ctype, "application/json") === 0)
            {
                return json_encode($object);
            }
            else
            {
                return $object;
            }
        }
        else
        {
            $ctype = "text/plain";
            return $object;
        }
    }

    private function ReturnResponse($httpStatus, $contentType, $object)
    {
        return [
            "Status" => $httpStatus,
            "ContentType" => $contentType,
            "Object" => $object
        ];
    }

    private function GetSessionToken()
    {
        if ($_SERVER["REQUEST_METHOD"] === "GET")
        {
            return $_GET["SessionToken"];
        }
        else
        {
            return $_POST["SessionToken"];
        }
    }
}

?>