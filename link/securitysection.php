<?php

include_once "apiconfigsection.php";
include_once "linkexception.php";
include_once "../auth/user.php";

class SecuritySection implements ApiConfigSection
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
        return "Security";
    }

    public function SectionValue()
    {
        return $obj;
    }

    public function ConfigHasSection()
    {
        return $_hasSection;
    }

    public function IsValid()
    {
        return $_isValid;
    }

    public function ValidateUser($sessionToken)
    {
        // If the required points is an array but has no points, access
        // is unrestricted.
        if (is_array($_obj->RequiredPoints) &&
            count($_obj->RequiredPoints) === 0)
        {
            return true;
        }

        $user == new User($sessionToken);
        if (!$user->IsLoggedIn())
        {
            return false;
        }

        // If the required points is defined as "Any", any logged in user
        // can access the endpoint.
        if (is_string($_obj->RequiredPoints) &&
            strcasecmp($_obj->RequiredPoints, "Any") === 0)
        {
            return true;
        }

        // Otherwise, compare the user's security points against the list
        // of required security points.
        foreach ($_obj->RequiredPoints as $point)
        {
            if (!$user->GetSecurity()->HasSecurityPoint($point))
            {
                return false;
            }
        }

        return true;
    }

    private function ParseForSection($config)
    {
        // Make sure that the configuration specifies security
        // requirements.
        if (!isset($config->Security)
            || !isset($config->Security->RequiredPoints))
        {
            throw new LinkException(500, "The configuration does not specify security requirements");
        }
        else
        {
            $_hasSection = true;
        }

        // Make sure the required points is of the correct type.
        if (!is_string($config->Security->RequiredPoints) &&
            !is_array($config->Security->RequiredPoints))
        {
            throw new LinkException(500, "The required security points are not properly specified");
        }
        else
        {
            $_isValid = true;
        }

        $_obj = $config->Security;
    }
}

?>