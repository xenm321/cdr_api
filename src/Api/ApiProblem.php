<?php

namespace App\Api;

use Symfony\Component\HttpFoundation\Response;

class ApiProblem
{
    const TYPE_VALIDATION_ERROR = 'validation_error';
    const TYPE_INVALID_REQUEST_BODY_FORMAT = 'invalid_body_format';
    const TYPE_AUTHENTICATION_ERROR = 'authentication_error';

    private static $titles = array(
        self::TYPE_VALIDATION_ERROR => 'There was a validation error',
        self::TYPE_INVALID_REQUEST_BODY_FORMAT => 'Invalid JSON format sent',
        self::TYPE_AUTHENTICATION_ERROR => 'Invalid or missing authentication',
    );

    private $statusCode;

    private $type;

    private $title;

    private $extraData = array();

    public function __construct($statusCode, $type = null)
    {
        $this->statusCode = $statusCode;
        $this->type = $type;

        if($type === null) {
            $this->type = 'about:blank';

            $this->title = isset(Response::$statusTexts[$statusCode])
                ? Response::$statusTexts[$statusCode]
                : 'Unknown Status Code';
        } else {
            if(!isset(self::$titles[$type])) {
                throw new \Exception(sprintf(
                    'No title found for type %s',
                    $type
                ));
            }

            $this->title = self::$titles[$type];
        }

    }

    public function toArray()
    {
        return array_merge(
            $this->extraData,
            array(
                'status' => $this->statusCode,
                'type' => $this->type,
                'title' => $this->title,
            )
        );
    }

    public function set($name, $value)
    {
        $this->extraData[$name] = $value;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getTitle()
    {
        return $this->title;
    }
}
