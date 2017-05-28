<?php

interface ApiConfigSection
{
    public function __construct($linkApiPoint);

    public function SectionName();

    public function SectionValue();

    public function ConfigHasSection();

    public function IsValid();
}

?>