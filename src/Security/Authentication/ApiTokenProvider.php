<?php

namespace App\Security\Authentication;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class ApiTokenProvider implements AuthenticationProviderInterface
{
    public function authenticate(TokenInterface $token)
    {
        $tokenString = $token->getCredentials();

        $accessToken = getenv('ACCESS_TOKEN');
        if ($accessToken && $tokenString === $accessToken) {
            $authenticatedToken = new ApiAuthToken(array());
            $authenticatedToken->setAuthenticated(true);

            return $authenticatedToken;
        }

        throw new BadCredentialsException('Invalid token');
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof ApiAuthToken;
    }
}
