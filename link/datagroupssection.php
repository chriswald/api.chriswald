<?php

include_once "apiconfigsection.php";
include_once "linkexception.php";

class DataGroupsSection extends ApiConfigSection
{
    public function SectionName()
    {
        return "DataGroups";
    }

    protected function ParseSection($config)
    {
        if (isset($config->QueryParameters))
        {
            $this->HasSection = true;
            $this->SectionValue = $config->QueryParameters;
            
            if (is_array($this->SectionValue))
            {
                $this->IsValid = true;
            }
        }
    }
}

?>