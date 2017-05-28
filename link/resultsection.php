<?php

include_once "apiconfigsection.php";
include_once "linkexception.php";
include_once "datagroupsection.php";

class ResultSection extends ApiConfigSection
{
    public function SectionName()
    {
        return "Result";
    }

    public function RequiredGroupExists(DataGroupSection $section)
    {
        foreach ($section->SectionValue as $group)
        {
            if ($group === $this->SectionValue->DataGroup)
            {
                return true;
            }
        }

        return false;
    }

    private function ParseForSection($config)
    {
        if (!isset($config->Result))
        {
            throw new LinkException(500, "Result not configured");
        }

        $this->HasSection = true;
        $this->SectionValue = $config->Result;

        if (!isset($this->SectionValue->DataGroup) ||
            !is_string($this->SectionValue->DataGroup) ||
            $this->SectionValue->DataGroup === "")
        {
            throw new LinkException(500, "Group name of result is not specified");
        }

        if (!isset($this->SectionValue->NameInGroup) ||
            !is_string($this->SectionValue->NameInGroup) ||
            $this->SectionValue->NameInGroup === "")
        {
            throw new LinkException(500, "Name to save result to in group not specified");
        }

        $this->IsValid = true;
    }
}

?>