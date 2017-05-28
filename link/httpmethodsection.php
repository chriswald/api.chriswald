<?php

include_once "apiconfigsection.php";
include_once "linkexception.php";
include_once "../auth/user.php";

class HttpMethodSection implements ApiConfigSection
{
    private $_hasSection;
    private $_isValid;
    private $_obj;

    public function __construct($linkApiPointConfig)
    {
        $_hasSection = false;
        $_isValid = true;
        ParseForSection($linkApiPointConfig);
    }

    public function SectionName()
    {
        return "HttpMethod";
    }

    public function SectionValue()
    {
        return $_obj;
    }

    public function ConfigHasSection()
    {
        return $_hasSection;
    }

    public function IsValid()
    {
        return $_isValid;
    }

    private function RequestMethodIsCorrect()
    {
        return (strcasecmp($_SERVER["REQUEST_METHOD"], $_obj) === 0);
    }

    private function ParseForSection($config)
    {
        if (!isset($config->HttpMethod))
        {
            $_isValid = true;
            $_obj = "POST";
        }
        else
        {
            $_hasSection = true;
            $_isValid = true;
            $_obj = $config->HttpMethod;
        }
    }
}

?>