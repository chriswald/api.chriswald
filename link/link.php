<?php

include_once "../auth/createconnection.php";
include_once "../auth/user.php";

$DATAGROUPS = [];

function FileFromCurrentDirectory($path)
{
    if (substr($path, 0, 1) !== DIRECTORY_SEPARATOR)
    {
        $path = DIRECTORY_SEPARATOR . $path;
    }

    $path = "." . $path . ".config.js";
    return $path;
}

function ParseConfigFromFile($configFile, &$configObject)
{
    $configData = file_get_contents($configFile);
    try {
        $configObject = json_decode($configData);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function GetApiPointConfig($apiPoint, &$configObject, &$error)
{
    $configFile = FileFromCurrentDirectory($apiPoint);

    if (file_exists($configFile))
    {
        if (ParseConfigFromFile($configFile, $configObject) &&
            $configObject !== null)
        {
            return true;
        }
        else {
            $error = "Failed to parse configuration file";
            return false;
        }
    }
    else {
        $error = "Cannot find configuration file";
        return false;
    }
}

function GetSessionToken()
{
    return $_POST["SessionToken"];
}

function ReturnResponse($statusCode, $obj)
{
    $var = array(
        "Status" => $statusCode,
        "Object" => $obj
    );
    return $var;
}

function HasSecurityAccess($configObject)
{
    if (!isset($configObject->Security)
        || !isset($configObject->Security->RequiredPoints))
    {
        return true;
    }

    $user = new User(GetSessionToken());

    foreach ($configObject->Security->RequiredPoints as $point)
    {
        if (!$user->GetSecurity()->HasSecurityPoint($point))
        {
            return false;
        }
    }

    return true;
}

function HasDataSources($configObject)
{
    return isset($configObject->DataSources);
}

function HasDefiniedHttpMethod($configObject)
{
    return isset($configObject->HttpMethod);
}

function HasValidHttpMethod($configObject)
{
    if (!HasDefiniedHttpMethod($configObject))
    {
        $httpMethod = "POST";
    }
    else
    {
        $httpMethod = $configObject->HttpMethod;
    }

    return $_SERVER["REQUEST_METHOD"] === $httpMethod;
}

function HasRequiredRequestParameters($configObject)
{
    if (!isset($configObject->RequestParameters) ||
        count($configObject->RequestParameters) === 0)
    {
        return true;
    }

    foreach ($configObject->RequestParameters as $param)
    {
        if (!isset($_POST[$param]))
        {
            return false;
        }
    }

    return true;
}

function HasRequiredQueryParameters($configObject)
{
    if (!isset($configObject->QueryParameters) ||
        count($configObject->QueryParameters) === 0)
    {
        return true;
    }

    foreach ($configObject->QueryParameters as $param)
    {
        if (!isset($_GET[$param]))
        {
            return false;
        }
    }

    return true;
}

function DatabaseQuery($dbProps, $parameters)
{
    $dbName = $dbProps->Database;
    $query = $dbProps->Query;

    $queryParams = array();

    if (isset($parameters))
    {
        foreach ($parameters as $param)
        {
            array_push($queryParams, mysql_escape_string($param));
        }
    }

    $pdo = CreateDBConnection($dbName);
    $statement = $pdo->prepare($query);
    $statement->execute($queryParams);

    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function ExecScript($dbProps, $parameters)
{
    $filename = $dbProps->File;
    $entry = $dbProps->Entrypoint;
    $serialParams = serialize($parameters);
    $executable = "include_once \"$filename\"; return $entry(unserialize('$serialParams'));";

    $retVal = eval($executable);
    return $retVal;
}

function GetSingleParameterValue($configObject, $paramDef, &$destDict, &$error)
{
    if (!isset($paramDef->Source) ||
        $paramDef->Source === "")
    {
        $error = "No parameter source specified";
        return false;
    }

    if (!isset($paramDef->SourceParameterName) ||
        $paramDef->SourceParameterName === "")
    {
        $error = "No source parameter name specified";
        return false;
    }

    if (!isset($paramDef->DestinationParameterName) ||
        $paramDef->DestinationParameterName === "")
    {
        $error = "No destination parameter name specified";
        return false;
    }

    $source = $paramDef->Source;
    $srcName = $paramDef->SourceParameterName;
    $destName = $paramDef->DestinationParameterName;

    if (strcasecmp($paramDef->Source, "RequestParameters") === 0)
    {
        if (in_array($srcName, $configObject->RequestParameters))
        {
            $destDict[$destName] = $_POST[$srcName];
        }
        else {
            $error = "RequestParameters does not have the parameter {$srcName}";
            return false;
        }
    }
    else if (strcasecmp($paramDef->Source, "QueryParameters") === 0)
    {
        if (in_array($srcName, $configObject->QueryParameters))
        {
            $destDict[$destName] = $_GET[$srcName];
        }
        else {
            $error = "QueryParameters does not have the parameter {$srcName}";
            return false;
        }
    }
    else if (strcasecmp($paramDef->Source, "DataGroups") === 0)
    {
        if (isset($paramDef->GroupName) && $paramDef->GroupName !== "")
        {
            $grpName = $paramDef->GroupName;
            if (in_array($grpName, $configObject->DataGroups))
            {
                $destDict[$destName] = $GLOBALS["DATAGROUPS"][$grpName][$srcName];
            }
            else {
                $error = "No group with the name {$grpName}";
                return false;
            }
        }
        else
        {
            $error = "Group name of parameter is not specified";
            return false;
        }
    }
    else
    {
        $error = "Parameter source {$paramDef->Source} is not a supported source";
        return false;
    }

    return true;
}

function GetParameterValues($configObject, $dataSource, &$dict, &$error)
{
    if (!isset($dataSource->Parameters))
    {
        $dict = [];
        return true;
    }

    foreach ($dataSource->Parameters as $paramDef)
    {
        if (!GetSingleParameterValue($configObject, $paramDef, $dict, $error))
        {
            return false;
        }
    }

    return true;
}

function ParseResultProperty($configObject, $parentObj, &$dataGroup, &$nameInGroup, &$error)
{
    if (isset($parentObj->Result))
    {
        if (isset($parentObj->Result->DataGroup) &&
            $parentObj->Result->DataGroup !== "")
        {
            $dataGroup = $parentObj->Result->DataGroup;
            if (isset($parentObj->Result->NameInGroup) &&
                $parentObj->Result->NameInGroup !== "")
            {
                $nameInGroup = $parentObj->Result->DataGroup;
                if (in_array($nameInGroup, $configObject->DataGroups))
                {
                    return true;
                }
                else {
                    $error = "No group with the name {$nameInGroup}";
                    return false;
                }
            }
            else
            {
                $error = "Name to save result to in group not specified";
                return false;
            }
        }
        else
        {
            $error = "Group name of result is not specified";
            return false;
        }
    }
    else {
        $error = "Data source result not configured";
        return false;
    }
}

function SetResultValue($configObject, $dataSource, $result, &$error)
{
    if (ParseResultProperty($configObject, $dataSource, $dataGroup, $nameInGroup, $error))
    {
        $GLOBALS["DATAGROUPS"][$dataGroup][$nameInGroup] = $result;
        return true;
    }
    else
    {
        return false;
    }
}

function CreateResultObject($configObject, &$resultObj, &$error)
{
    if (ParseResultProperty($configObject, $configObject, $dataGroup, $nameInGroup, $error))
    {
        $obj = $GLOBALS["DATAGROUPS"][$dataGroup][$nameInGroup];
        $resultObj = ReturnResponse(200, $obj);
        return true;
    }
    else {
        return false;
    }
}

function Process($configObject)
{
    $datasourceObj = [];

    foreach ($configObject->DataSources as $dataSource)
    {
        if (!GetParameterValues($configObject, $dataSource, $parameterDict, $error))
        {
            $obj = ["Error" => $error];
            $responseObject = ReturnResponse(500, $obj);
            return $responseObject;
        }

        if (strcasecmp($dataSource->Type, "Database") === 0)
        {
            $datasourceObj = DatabaseQuery($dataSource->Properties, $parameterDict);
        }
        else if (strcasecmp($dataSource->Type, "Script") === 0)
        {
            $datasourceObj = ExecScript($dataSource->Properties, $parameterDict);
        }
        else
        {
            $obj = ["Error" => "Unsupported data source"];
            $responseObject = ReturnResponse(500, $obj);
            break;
        }

        if (!SetResultValue($configObject, $dataSource, $datasourceObj, $error))
        {
            $obj = ["Error" => $error];
            $responseObject = ReturnResponse(500, $obj);
            return $responseObject;
        }
    }

    if (!CreateResultObject($configObject, $responseObject, $error))
    {
        $obj = ["Error" => $error];
        $responseObject = ReturnResponse(500, $obj);
    }

    return $responseObject;
}

function Dispatch($configObject)
{
    $error = "";
    if (PrerequisitsMet($configObject, $error))
    {
        SetUpDataGroups($configObject);
        return Process($configObject);
    }
    else
    {
        $obj = ["Error" => $error];
        return ReturnResponse(400, $obj);
    }
}

function PrerequisitsMet($configObject, &$error)
{
    $error = "";

    if (!HasSecurityAccess($configObject))
    {
        $error = "Unauthorized";
    }
    else if (!HasDataSources($configObject))
    {
        $error = "No data sources";
    }
    else if (!HasValidHttpMethod($configObject))
    {
        $error = "The endpoint does not service the {$_SERVER['REQUEST_METHOD']} method";
    }
    else if (!HasRequiredRequestParameters($configObject))
    {
        $error = "Missing required request parameters";
    }
    else if (!HasRequiredQueryParameters($configObject))
    {
        $error = "Missing required query string parameters";
    }

    return ($error === "");
}

function SetUpDataGroups($configObject)
{
    if (isset($configObject->DataGroups))
    {
        foreach ($configObject->DataGroups as $group)
        {
            $GLOBALS["DATAGROUPS"][$group] = [];
        }
    }
}

function WriteResponse($response)
{
    http_response_code($response["Status"]);
    header("Content-Type: text/json");
    echo json_encode($response["Object"]);
}

function Main()
{
    $apiPoint = $_GET["a"];
    $configObject = "";
    $error = "";

    $response = array();

    if (GetApiPointConfig($apiPoint, $configObject, $error))
    {
        $response = Dispatch($configObject);
    }
    else
    {
        $response = ReturnResponse(404, ["Error" => $error]);
    }

    WriteResponse($response);
}

Main();

?>
