<?php

class LinkApiPoint
{
    private $_configObject;

    public function __construct($apiPoint)
    {
        $this->GetApiPointConfig($apiPoint, $this->_configObject);
    }

    public function Config()
    {
        return $this->_configObject;
    }

    private function GetApiPointConfig($apiPoint, &$configObject)
    {
        $configFile = $this->FileFromCurrentDirectory($apiPoint);

        if (!file_exists($configFile))
        {
            throw new LinkException(404, "Cannot find configuration file");
        }

        if (!$this->ParseConfigFromFile($configFile, $configObject) ||
            $configObject === null)
        {
            throw new LinkException(404, "Failed to parse configuration file");
        }
    }

    private function FileFromCurrentDirectory($path)
    {
        if (substr($path, 0, 1) !== DIRECTORY_SEPARATOR)
        {
            $path = DIRECTORY_SEPARATOR . $path;
        }

        $path = "." . $path;
        
        if (!$this->endsWith($path, ".config.json"))
        {
            $path = $path . ".config.json";
        }

        return $path;
    }

    private function ParseConfigFromFile($configFile, &$configObject)
    {
        $configData = file_get_contents($configFile);
        try {
            $configObject = json_decode($configData);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }
}

?>