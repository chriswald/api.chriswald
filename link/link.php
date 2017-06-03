<?php

include_once "apilink.php";

function Main()
{
    $apiPoint = $_GET["_a_"];

    $apiLink = new ApiLink();
    $apiLink->ExecuteConfig($apiPoint, $status, $responseContentType, $responseString);

    header("Content-Type: $responseContentType");
    http_response_code($status);
    echo $responseString;
}

Main();

?>