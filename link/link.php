<?php

include_once "linkexception.php";
include_once "../auth/createconnection.php";
include_once "../auth/user.php";

$DATAGROUPS = [];

function FileFromCurrentDirectory($path)
{
    if (substr($path, 0, 1) !== DIRECTORY_SEPARATOR)
    {
        $path = DIRECTORY_SEPARATOR . $path;
    }

    $path = "." . $path . ".config.json";
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

function GetApiPointConfig($apiPoint, &$configObject)
{
    $configFile = FileFromCurrentDirectory($apiPoint);

    if (!file_exists($configFile))
    {
        throw new LinkException(404, "Cannot find configuration file");
    }

    if (!ParseConfigFromFile($configFile, $configObject) ||
        $configObject === null)
    {
        throw new LinkException(404, "Failed to parse configuration file");
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
    // Make sure that the configuration specifies security
    // requirements.
    if (!isset($configObject->Security)
        || !isset($configObject->Security->RequiredPoints))
    {
        throw new LinkException(500, "The configuration does not specify security requirements");
    }

    // Make sure the required points is of the correct type.
    if (!is_string($configObject->Security->RequiredPoints) &&
        !is_array($configObject->Security->RequiredPoints))
    {
        throw new LinkException(500, "The required security points are not properly specified");
    }

    // If the required points is an array but has no points, access
    // is unrestricted.
    if (is_array($configObject->Security->RequiredPoints) &&
        count($configObject->Security->RequiredPoints) === 0)
    {
        return true;
    }

    // Create the user with the Session Token and check to see if
    // they're logged in.
    $user = new User(GetSessionToken());
    if (!$user->IsLoggedIn())
    {
        return false;
    }

    // If the required points is defined as "Any", any logged in user
    // can access the endpoint.
    if (is_string($configObject->Security->RequiredPoints) &&
        strcasecmp($configObject->Security->RequiredPoints, "Any") === 0)
    {
        return true;
    }

    // Otherwise, compare the user's security points against the list
    // of required security points.
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

    $pdo = CreateDBConnection($dbName);

    if (isset($parameters))
    {
        foreach ($parameters as $param)
        {
            array_push($queryParams, mysql_escape_string($param));
        }
    }

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

function GetSingleParameterValue($configObject, $paramDef, &$destDict)
{
    if (!isset($paramDef->Source) ||
        $paramDef->Source === "")
    {
        throw new LinkException(500, "No parameter source specified");
    }

    if (!isset($paramDef->SourceParameterName) ||
        $paramDef->SourceParameterName === "")
    {
        throw new LinkException(500, "No source parameter name specified");
    }

    if (!isset($paramDef->DestinationParameterName) ||
        $paramDef->DestinationParameterName === "")
    {
        throw new LinkException(500, "No destination parameter name specified");
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
            throw new LinkException(500, "RequestParameters does not have the parameter {$srcName}");
        }
    }
    else if (strcasecmp($paramDef->Source, "QueryParameters") === 0)
    {
        if (in_array($srcName, $configObject->QueryParameters))
        {
            $destDict[$destName] = $_GET[$srcName];
        }
        else {
            throw new LinkException(500, "QueryParameters does not have the parameter {$srcName}");
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
        throw new LinkException(500, "Parameter source {$paramDef->Source} is not a supported source");
    }

    return true;
}

function GetParameterValues($configObject, $dataSource, &$dict)
{
    if (!isset($dataSource->Parameters))
    {
        $dict = [];
        return true;
    }

    foreach ($dataSource->Parameters as $paramDef)
    {
        if (!GetSingleParameterValue($configObject, $paramDef, $dict))
        {
            return false;
        }
    }

    return true;
}

function ParseResultProperty($configObject, $parentObj, &$dataGroup, &$nameInGroup)
{
    if (!isset($parentObj->Result))
    {
        throw new LinkException(500, "Data source result not configured");
    }

    if (!isset($parentObj->Result->DataGroup) ||
        $parentObj->Result->DataGroup === "")
    {
        throw new LinkException(500, "Group name of result is not specified");
    }

    if (!isset($parentObj->Result->NameInGroup) ||
        $parentObj->Result->NameInGroup === "")
    {
        throw new LinkException(500, "Name to save result to in group not specified");
    }

    $dataGroup = $parentObj->Result->DataGroup;
    $nameInGroup = $parentObj->Result->DataGroup;

    if (!in_array($nameInGroup, $configObject->DataGroups))
    {
        throw new LinkException(500, "No group with the name {$nameInGroup}");
    }

    return true;
}

function SetResultValue($configObject, $dataSource, $result)
{
    if (ParseResultProperty($configObject, $dataSource, $dataGroup, $nameInGroup))
    {
        $GLOBALS["DATAGROUPS"][$dataGroup][$nameInGroup] = $result;
        return true;
    }
    else
    {
        return false;
    }
}

function CreateResultObject($configObject, &$resultObj)
{
    if (ParseResultProperty($configObject, $configObject, $dataGroup, $nameInGroup))
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
        if (GetParameterValues($configObject, $dataSource, $parameterDict))
        {
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
                throw new LinkException(500, "Unsupported data source");
            }

            SetResultValue($configObject, $dataSource, $datasourceObj);
        }
    }

    CreateResultObject($configObject, $responseObject);
    return $responseObject;
}

/*
 * Ensures that the API endpoint's prerequsites are met, then
 * processes the request.
 */
function Dispatch($configObject)
{
    if (PrerequisitsMet($configObject))
    {
        SetUpDataGroups($configObject);
        return Process($configObject);
    }
}

/*
 * Ensures that a request meets the basic requirements of the API
 * endpoint it is trying to access.
 */
function PrerequisitsMet($configObject)
{
    if (!HasSecurityAccess($configObject))
    {
        throw new LinkException(400, "Unauthorized");
    }
    else if (!HasDataSources($configObject))
    {
        throw new LinkException(500, "No data sources");
    }
    else if (!HasValidHttpMethod($configObject))
    {
        throw new LinkException(400, "The endpoint does not service the {$_SERVER['REQUEST_METHOD']} method");
    }
    else if (!HasRequiredRequestParameters($configObject))
    {
        throw new LinkException(400, "Missing required request parameters");
    }
    else if (!HasRequiredQueryParameters($configObject))
    {
        throw new LinkException(400, "Missing required query string parameters");
    }

    return true;
}

/*
 * Initializes storage for each data group specified in the
 * configuration's DataGroups section.
 */
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

/*
 * Writes the Http status code to the response's header and writes
 * the response object JSON encoded to the body.
 */
function WriteResponse($response)
{
    http_response_code($response["Status"]);
    header("Content-Type: text/json");

    if (isset($response["Object"]) &&
        $response["Object"] !== null)
    {
        echo json_encode($response["Object"]);
    }
}

/*
 * Entry point of script's execution. Retrieves an API endpoint
 * configuration in the form of a relative file path from the _a_ GET
 * parameter. Attemps to execute the actions described in that
 * configuration.
 *
 * PARAMETERS:
 *  GET[_a_] - The relative path to a .config.js API endpoint
 *      configuration. The path is relative to the directory of this
 *      document.
 */
function Main()
{
    $apiPoint = $_GET["_a_"];
    $configObject = "";
    $error = "";

    $response = array();

    try
    {
        GetApiPointConfig($apiPoint, $configObject);
        $response = Dispatch($configObject);
    }
    catch (LinkException $e)
    {
        $response = ReturnResponse($e->getStatusCode(), ["Error" => $e->getMessage()]);
    }

    WriteResponse($response);
}

Main();

?>
