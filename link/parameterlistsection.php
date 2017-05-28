<?php

include_once "apiconfigsection.php";
include_once "linkexception.php";

class ParameterListSection extends ApiConfigSection
{
    public function SectionName()
    {
        return "Parameters";
    }

    private function ParseForSection($config)
    {
        if (!isset($config->Parameters))
        {
            $this->IsValid = true;
            $this->SectionValue = [];
        }
        else
        {
            $this->HasSection = true;
            $this->SectionValue = $config->Parameters;

            if (!is_array($this->SectionValue))
            {
                throw new LinkException(500, "Parameters is not an array");
            }
            else
            {
                $this->IsValid = true;
            }
        }
    }
}

?>