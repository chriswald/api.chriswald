<?php

include_once "apiconfigsection.php";
include_once "linkexception.php";

class ParameterSection extends ApiConfigSection
{
    public function SectionName()
    {
        return "Parameter";
    }

    protected function ParseSection($config)
    {
        if (!isset($config->Source) ||
            $config->Source === "")
        {
            throw new LinkException(500, "No parameter source specified");
        }

        if (!isset($config->SourceParameterName) ||
            $config->SourceParameterName === "")
        {
            throw new LinkException(500, "No source parameter name specified");
        }

        if (!isset($config->DestinationParameterName) ||
            $config->DestinationParameterName === "")
        {
            throw new LinkException(500, "No destination parameter name specified");
        }

        $this->HasSection = true;
        $this->SectionValue = $config;
        $this->IsValid = true;
    }
}

?>