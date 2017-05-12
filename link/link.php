<?php

include_once "../auth/createconnection.php";
include_once "../auth/user.php";

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

function GetApiPointConfig($apiPoint, &$configObject)
{
    $configFile = FileFromCurrentDirectory($apiPoint);
    if (file_exists($configFile)
        && ParseConfigFromFile($configFile, $configObject)
        && $configObject !== null)
    {
        return true;
    }
    else
    {
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

function RequestParameters($dbProps)
{
    if (isset($dbProps->Parameters))
    {
        $sourceArr = $_POST;

        if (isset($dbProps->Source))
        {
            if (strcasecmp($dbProps->Source, "GET") === 0)
            {
                $sourceArr = $_GET;
            }
        }

        $params = array();
        foreach ($dbProps->Parameters as $param)
        {
            if (isset($_POST[$param]))
            {
                array_push($params, $_POST[$param]);
            }
            else
            {
                return ReturnResponse(400, null);
            }
        }

        return ReturnResponse(200, $params);
    }
    else
    {
        return ReturnResponse(200, null);
    }
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

    return ReturnResponse(200, $statement->fetchAll(PDO::FETCH_ASSOC));
}

function ExecScript($dbProps, $parameters)
{
    $filename = $dbProps->File;
    $entry = $dbProps->Entrypoint;
    $executable = "include_once \"$filename\"; return $entry();";

    $retVal = eval($executable);
    return ReturnResponse(200, $retVal);
}

function Process($configObject)
{
    $responseObject = ReturnResponse(200, null);

    foreach ($configObject->DataSources as $dataSource)
    {
        if (strcasecmp($dataSource->Type, "RequestParams") === 0)
        {
            $responseObject = RequestParameters($dataSource->Properties);
        }
        else if (strcasecmp($dataSource->Type, "Database") === 0)
        {
            $responseObject = DatabaseQuery($dataSource->Properties, $responseObject["Object"]);
        }
        else if (strcasecmp($dataSource->Type, "Script") === 0)
        {
            $responseObject = ExecScript($dataSource->Properties, $responseObject["Object"]);
        }

        if ($responseObject["Status"] !== 200)
        {
            break;
        }
    }

    return $responseObject;
}

function Dispatch($configObject)
{
    if (HasSecurityAccess($configObject) && isset($configObject->DataSources))
    {
        return Process($configObject);
    }
    else
    {
        return ReturnResponse(403, null);
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

    $response = array();

    if (GetApiPointConfig($apiPoint, $configObject))
    {
        $response = Dispatch($configObject);
    }
    else
    {
        $response = ReturnResponse(404, null);
    }

    WriteResponse($response);
}

Main();

?>
