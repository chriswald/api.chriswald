<?php

include_once "apiconfigsection.php";
include_once "linkexception.php";
include_once "../auth/user.php";

class SecuritySection extends ApiConfigSection
{
    public function SectionName()
    {
        return "Security";
    }

    public function ValidateUser($sessionToken)
    {
        // If the required points is an array but has no points, access
        // is unrestricted.
        if (is_array($this->SectionValue->RequiredPoints) &&
            count($this->SectionValue->RequiredPoints) === 0)
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
        if (is_string($this->SectionValue->RequiredPoints) &&
            strcasecmp($this->SectionValue->RequiredPoints, "Any") === 0)
        {
            return true;
        }

        // Otherwise, compare the user's security points against the list
        // of required security points.
        foreach ($this->SectionValue->RequiredPoints as $point)
        {
            if (!$user->GetSecurity()->HasSecurityPoint($point))
            {
                return false;
            }
        }

        return true;
    }

    protected function ParseSection($config)
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
            $this->HasSection = true;
        }

        // Make sure the required points is of the correct type.
        if (!is_string($config->Security->RequiredPoints) &&
            !is_array($config->Security->RequiredPoints))
        {
            throw new LinkException(500, "The required security points are not properly specified");
        }
        else
        {
            $this->IsValid = true;
        }

        $this->SectionValue = $config->Security;
    }
}

?>