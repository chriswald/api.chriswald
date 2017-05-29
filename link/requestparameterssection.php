<?php

include_once "apiconfigsection.php";
include_once "linkexception.php";

class RequestParametersSection extends ApiConfigSection
{
    public function SectionName()
    {
        return "RequestParameters";
    }

    protected function ParseSection($config)
    {
        if (isset($config->RequestParameters))
        {
            $this->HasSection = true;
            $this->SectionValue = $config->RequestParameters;
            
            if (is_array($config->RequestParameters))
            {
                $this->IsValid = true;
                
                foreach ($this->SectionValue as $param)
                {
                    if (!isset($_POST[$param]))
                    {
                        throw new LinkException(400, "Missing request parameter $param");
                    }
                }
            }
        }
    }
}

?>