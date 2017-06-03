<?php

include_once "apiconfigsection.php";

class HttpMethodSection extends ApiConfigSection
{
    public $IsRedirector = false;

    private $_redirectorConfigs;

    public function SectionName()
    {
        return "HttpMethod";
    }

    public function RequestMethodIsCorrect()
    {
        return (strcasecmp($_SERVER["REQUEST_METHOD"], $this->SectionValue) === 0);
    }

    public function ConfigForHttpMethod($method)
    {
        return $this->_redirectorConfigs[$method];
    }

    protected function ParseSection($config)
    {
        if (!isset($config->HttpMethod))
        {
            $this->IsValid = true;
            $this->SectionValue = "POST";
        }
        else
        {
            if (is_string($config->HttpMethod))
            {
                $this->HasSection = true;
                $this->IsValid = true;
                $this->SectionValue = $config->HttpMethod;
            }
            else if (is_array($config->HttpMethod))
            {
                $this->IsRedirector = true;
                foreach ($config->HttpMethod as $redir)
                {
                    if (isset($redir->Method) &&
                        isset($redir->Config) &&
                        is_string($redir->Method) &&
                        is_string($redir->Config))
                    {
                        $this->_redirectorConfigs[$redir->Method] = $redir->Config;
                    }
                    else
                    {
                        throw new LinkException(500, "Redirector specification is not formatted correctly");
                    }
                }
            }
            else
            {
                throw new LinkException(500, "HttpMethod section is not formatted correctly");
            }
        }
    }
}

?>