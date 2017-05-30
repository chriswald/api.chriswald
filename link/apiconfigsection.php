<?php

abstract class ApiConfigSection
{
    public $HasSection;
    public $IsValid;
    public $SectionValue;

    public function __construct($linkApiPointConfig)
    {
        $this->HasSection = false;
        $this->IsValid = false;
        $this->ParseSection($linkApiPointConfig);
    }

    public abstract function SectionName();

    protected abstract function ParseSection($config);
}

?>