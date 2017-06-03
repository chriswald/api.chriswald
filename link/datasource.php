<?php

include_once "apiconfigsection.php";
include_once "linkexception.php";
include_once "../auth/createconnection.php";

class DataSourceType
{
    const Database = 0;
    const Script = 1;
}

class DataSource extends ApiConfigSection
{
    private $_dataSourceObj;
    private $_sourceType;

    public function __construct($dataSource)
    {
        $this->_dataSourceObj = $dataSource;
        parent::__construct($dataSource);
    }

    public function SectionName()
    {
        return "";
    }

    public function Execute($parameterDict)
    {
        switch ($this->_sourceType)
        {
            case DataSourceType::Database:
                return $this->DatabaseQuery($parameterDict);
            case DataSourceType::Script:
                return $this->ExecScript($parameterDict);
            default:
                throw new LinkException(500, "Data source type was not parsed correctly");
        }
    }

    private function DatabaseQuery($parameterDict)
    {
        $dbName = $this->SectionValue->Properties->Database;
        $query = $this->SectionValue->Properties->Query;

        $queryParams = [];

        $pdo = CreateDBConnection($dbName);

        if (isset($parameterDict))
        {
            foreach ($parameterDict as $param)
            {
                array_push($queryParams, mysql_escape_string($param));
            }
        }
        
        $statement = $pdo->prepare($query);
        $statement->execute($queryParams);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function ExecScript($parameterDict)
    {
        $filename = $this->SectionValue->Properties->File;
        $entry = $this->SectionValue->Properties->EntryPoint;
        $serialParams = serialize($parameterDict);
        if ($serialParams !== "") { $params = "unserialize('$serialParams')"; }
        if ($entry !== "") { $executable = "return $entry($params);"; }

        $executable = "include_once \"$filename\"; $executable";

        $retVal = eval($executable);
        return $retVal;
    }

    protected function ParseSection($config)
    {
        if (!isset($config->Type) ||
            $config->Type === "")
        {
            throw new LinkException(500, "Data source type not specified");
        }

        $this->HasSection = true;
        $this->SectionValue = $config;
        
        if (strcasecmp($config->Type, "Database") === 0)
        {
            $this->_sourceType = DataSourceType::Database;
        }
        else if (strcasecmp($config->Type, "Script") === 0)
        {
            $this->_sourceType = DataSourceType::Script;
        }
        else
        {
            throw new LinkException(500, "Unsupported data source type: $config->Type");
        }

        if (!isset($config->Properties))
        {
            throw new LinkException(500, "Data source properties not specified");
        }

        if (!isset($config->Result))
        {
            throw new LinkException(500, "Data source result properties not specified");
        }

        $this->ParseTypeSpecific($config);
    }

    protected function ParseTypeSpecific($config)
    {
        switch ($this->_sourceType)
        {
            case DataSourceType::Database:
                return $this->ParseDatabase($config);
            case DataSourceType::Script:
                return $this->ParseScript($config);
        }
    }

    protected function ParseDatabase($config)
    {
        $props = $config->Properties;
        if (!isset($props->Database) ||
            $props->Type === "")
        {
            throw new LinkException(500, "Database name not specified");
        }

        if (!isset($props->Query) ||
            $props->Query === "")
        {
            throw new LinkException(500, "Database query not specified");
        }
    }

    protected function ParseScript($config)
    {
        $props = $config->Properties;
        if (!isset($props->File) ||
            $props->File === "")
        {
            throw new LinkException(500, "Script file not specified");
        }

        if (!isset($props->EntryPoint) ||
            $props->EntryPoint === "")
        {
            throw new LinkException(500, "Script entry point not specified");
        }
    }
}

?>