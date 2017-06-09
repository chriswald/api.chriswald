<?php

class LinkException extends Exception
{
    protected $httpStatusCode;
    protected $obj;

    public function __construct($httpStatusCode, $obj)
    {
        $this->httpStatusCode = $httpStatusCode;
        $this->obj = $obj;
        parent::__construct("");
    }

    public function getObject()
    {
        return $this->obj;
    }

    public function getStatusCode()
    {
        return $this->httpStatusCode;
    }
}

?>