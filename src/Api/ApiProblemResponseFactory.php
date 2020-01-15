<?php

namespace App\Api;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiProblemResponseFactory
{
    public function createResponse(ApiProblem $problem)
    {
        $response = new JsonResponse(
            $problem->toArray(),
            $problem->getStatusCode()
        );

        $response->headers->set('Content-type', 'application/problem+json');

        return $response;
    }
}
