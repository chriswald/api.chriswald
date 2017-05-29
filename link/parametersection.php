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
        
    }
}

?>