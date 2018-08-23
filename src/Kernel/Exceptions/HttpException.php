<?php

namespace Yan\Foundation\Kernel\Exceptions;

use Psr\Http\Message\ResponseInterface;

class HttpException extends Exception
{

    public $response;

    public $formattedResponse;

    public function __construct($message, ResponseInterface $response = null, $formattedResponse = null, $code = null)
    {
        parent::__construct($message, $code);

        $this->response = $response;
        $this->formattedResponse = $formattedResponse;
        if ($response) {
            $response->getBody()->rewind();
        }
    }
}