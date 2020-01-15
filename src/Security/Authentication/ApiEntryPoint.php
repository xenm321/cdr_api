<?php

namespace App\Security\Authentication;

use App\Api\ApiProblem;
use App\Api\ApiProblemResponseFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class ApiEntryPoint implements AuthenticationEntryPointInterface
{
    private $responseFactory;
    private $logger;

    public function __construct(
        ApiProblemResponseFactory $responseFactory,
        LoggerInterface $logger
    )
    {
        $this->responseFactory = $responseFactory;
        $this->logger = $logger;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $message = $this->getMessage($authException);

        $apiProblem = new ApiProblem(401, ApiProblem::TYPE_AUTHENTICATION_ERROR);
        $apiProblem->set('detail', $message);

        $this->logger->error($message, $apiProblem->toArray());

        return $this->responseFactory->createResponse($apiProblem);
    }

    private function getMessage(AuthenticationException $authException = null)
    {
        return $authException ? $authException->getMessageKey() : 'authentication_required';
    }
}
