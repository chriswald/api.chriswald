<?php

include_once "apiconfigsection.php";
include_once "linkexception.php";

class QueryParametersSection extends ApiConfigSection
{
    public function SectionName()
    {
        return "QueryParameters";
    }

    private function ParseForSection($config)
    {
        if (isset($config->QueryParameters))
        {
            $this->HasSection = true;
            $this->SectionValue = $config->QueryParameters;
            
            if (is_array($config->QueryParameters))
            {
                $this->IsValid = true;
                
                foreach ($this->SectionValue as $param)
                {
                    if (!isset($_GET[$param]))
                    {
                        throw new LinkException(400, "Missing query parameter $param");
                    }
                }
            }
        }
    }
}

?>