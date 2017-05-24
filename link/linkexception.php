<?php

class LinkException extends Exception
{
    protected $httpStatusCode;

    public function __construct($httpStatusCode, $message)
    {
        $this->httpStatusCode = $httpStatusCode;
        parent::__construct($message);
    }

    public function getStatusCode()
    {
        return $this->httpStatusCode;
    }
}

?>