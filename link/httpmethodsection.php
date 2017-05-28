<?php

include_once "apiconfigsection.php";

class HttpMethodSection extends ApiConfigSection
{
    public function SectionName()
    {
        return "HttpMethod";
    }

    private function RequestMethodIsCorrect()
    {
        return (strcasecmp($_SERVER["REQUEST_METHOD"], $this->SectionValue) === 0);
    }

    private function ParseForSection($config)
    {
        if (!isset($config->HttpMethod))
        {
            $this->IsValid = true;
            $this->SectionValue = "POST";
        }
        else
        {
            $this->HasSection = true;
            $this->IsValid = true;
            $this->SectionValue = $config->HttpMethod;
        }
    }
}

?>