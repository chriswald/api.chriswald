<?php

include_once "linkexception.php";
include_once "datagroups.php";
include_once "../auth/createconnection.php";
include_once "../auth/user.php";

class ApiLink
{
    private $_dataGroups;

    public function __construct()
    {
        $this->_dataGroups = new DataGroups();
    }

    public function ExecuteConfig($configFilename, &$httpStatusCode, &$responseObject)
    {

    }
}

?>