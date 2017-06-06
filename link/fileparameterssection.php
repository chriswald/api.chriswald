<?php

include_once "apiconfigsection.php";
include_once "linkexception.php";

class FileParametersSection extends ApiConfigSection
{
    public function SectionName()
    {
        return "FileParameters";
    }

    protected function ParseSection($config)
    {
        if (isset($config->FileParameters))
        {
            $this->HasSection = true;
            $this->SectionValue = $config->FileParameters;
            
            if (is_array($config->FileParameters))
            {
                $this->IsValid = true;
                
                foreach ($this->SectionValue as $param)
                {
                    if (!isset($_GET[$param]))
                    {
                        throw new LinkException(400, "Missing file parameter $param");
                    }
                }
            }
        }
    }
}

?>