<?php

class DataGroups
{
    private $_dataGroups = [];

    public function InitializeGroup($groupName)
    {
        $this->_dataGroups[$groupName] = [];
    }

    public function SetValue($groupName, $valueName, $value)
    {
        $this->_dataGroups[$groupName][$valueName] = $value;
    }

    public function GetValue($groupName, $valueName)
    {
        return $this->_dataGroups[$groupName][$valueName];
    }

    public function HasGroup($groupName)
    {
        return isset($this->_dataGroups[$groupName]);
    }
}

?>