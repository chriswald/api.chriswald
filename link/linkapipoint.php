<?php

class LinkApiPoint
{
    private $_configObject;

    public function __construct(string $apiPoint)
    {
        GetApiPointConfig($apiPoint, $_configObject);
    }

    public function Config()
    {
        return $_configObject;
    }

    private function GetApiPointConfig(string $apiPoint, &$configObject)
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

    private function FileFromCurrentDirectory(string $path)
    {
        if (substr($path, 0, 1) !== DIRECTORY_SEPARATOR)
        {
            $path = DIRECTORY_SEPARATOR . $path;
        }

        $path = "." . $path . ".config.json";
        return $path;
    }

    function ParseConfigFromFile(string $configFile, &$configObject)
    {
        $configData = file_get_contents($configFile);
        try {
            $configObject = json_decode($configData);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

?>