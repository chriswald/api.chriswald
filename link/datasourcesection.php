<?php

include_once "apiconfigsection.php";
include_once "linkexception.php";

class DataSourceSection extends ApiConfigSection
{
    public function SectionName()
    {
        return "DataSources";
    }

    private function ParseForSection($config)
    {
        if (!isset($config->DataSources))
        {
            throw new LinkException(500, "Missing data sources section");
        }

        $this->HasSection = true;
        $this->SectionValue = $config->DataSources;

        if (!is_array($this->SectionValue) ||
            count($this->SectionValue) === 0)
        {
            throw new LinkException(500, "Data sources incorrectly specified");
        }
        
        $this->IsValid = true;
    }
}

?>