<?php

function GetParams()
{
    $contentType= $_SERVER["CONTENT_TYPE"];
    if ($contentType === "application/json")
    {
        $params = json_decode(file_get_contents("php://input"), true);
    }
    else
    {
        $params = $_POST;
    }

    return $params;
}

function SendArray($data)
{
    echo json_encode($data) . "\n";
}

function GetOps()
{
    $data = GetParams();

    $ops[0] = $data["leftOp"];
    $ops[1] = $data["rightOp"];
    $ops[2] = $data["falseBranch"];
    $ops[3] = $data["trueBranch"];

    return $ops;
}

function ReturnResult($result, $ops)
{
    $deliver["result"] = $result;
    $deliver["branch"] = $ops[2 + !!$deliver["result"]];

    SendArray($deliver);
}

function PicoServiceRequest($service, $data)
{
    $url = "http://api.chriswald.com/pico/" . $service;
    $options = array(
        "http" => array(
            "header" => "Content-type: application/x-www-form-urlencoded\r\n",
            "method" => "POST",
            "content" => http_build_query($data),
        ),
    );

    $context = stream_context_create($options);
    return get_object_vars(json_decode(file_get_contents($url, false, $context)));
}

?>
