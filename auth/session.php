<?php

include_once "authcore.php";
include_once "createconnection.php";
include_once "../key/passwords.php";

class Session
{
    private $token = "";
    private $email = "";
    private $generatedTime = "";
    private $expiresTime = "";

    function __construct($token = "")
    {
        if ($token !== "")
        {
            $this->_GetInfoFromToken($token);
        }
    }

    function TokenIsExpired()
    {
        return intval($this->expiresTime) <= time();
    }

    function GetSessionToken()
    {
        return $this->token;
    }

    function GetSessionEmail()
    {
        return $this->email;
    }

    function RenewSession()
    {
        if (!$this->TokenIsExpired())
        {
            $session = new Session();
            $session->GenerateSessionToken($this->email);
            return $session;
        }
        else
        {
            return null;
        }
    }

    private function _GetInfoFromToken($token)
    {
        $data = $this->_DecryptToken($token);
        $data = json_decode($data);

        $this->token = $token;
        $this->email = $data->email;
        $this->generatedTime = $data->generated;
        $this->expiresTime = $data->expires;
    }

    private function _GetPrivateKeyRes()
    {
        $fp = fopen("../key/private.pem", "r");
        $key = fread($fp, 2048);
        fclose($fp);

        return openssl_get_privatekey($key, GetPassword("PRIV_KEY"));
    }

    private function _GetPublicKeyRes()
    {
        $fp = fopen("../key/public.pem", "r");
        $key = fread($fp, 2048);
        fclose($fp);

        return openssl_get_publickey($key);
    }

    function GenerateSessionToken($email)
    {
        $this->email = $email;
        $now = time();
        $then = $now + (30 * 60); // 30 minutes

        $data = json_encode(array(
            "email" => $email,
            "generated" => $now,
            "expires" => $then
        ));

        $this->token = $this->_EncryptToken($data);
    }

    private function _EncryptToken($tokenData)
    {
        $res = $this->_GetPrivateKeyRes();
        openssl_private_encrypt($tokenData, $token, $res);
        return base64_encode($token);
    }

    private function _DecryptToken($token)
    {
        $res = $this->_GetPublicKeyRes();
        $token = base64_decode($token);
        openssl_public_decrypt($token, $data, $res);

        return $data;
    }
}

?>
