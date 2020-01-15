<?php

namespace App\Security\Authentication;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ApiTokenListener implements ListenerInterface
{
    private $authorizationHeaderName = 'Token';

    private $securityContext;
    private $authenticationManager;

    public function __construct(
        SecurityContextInterface $securityContext,
        AuthenticationManagerInterface $authenticationManager)
    {
        $this->securityContext       = $securityContext;
        $this->authenticationManager = $authenticationManager;
    }

    public function handle(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        if (!$request->headers->has($this->authorizationHeaderName)) {
            return;
        }

        $tokenString = $request->headers->get($this->authorizationHeaderName);
        if (!$tokenString) {
            return;
        }

        $token = new ApiAuthToken();
        $token->setAuthToken($tokenString);

        $returnValue = $this->authenticationManager->authenticate($token);

        if ($returnValue instanceof TokenInterface) {
            return $this->securityContext->setToken($returnValue);
        }
    }
}
