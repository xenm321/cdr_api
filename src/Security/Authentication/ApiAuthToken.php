<?php

namespace App\Security\Authentication;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class ApiAuthToken extends AbstractToken
{
    private $token;

    public function setAuthToken($token)
    {
        $this->token = $token;
    }

    public function getCredentials()
    {
        return $this->token;
    }
}
