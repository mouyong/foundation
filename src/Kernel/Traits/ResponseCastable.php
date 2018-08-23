<?php

namespace Yan\Foundation\Kernel\Traits;

use Yan\Foundation\Kernel\Contracts\Arrayable;
use Yan\Foundation\Kernel\Exceptions\InvalidArgumentException;
use Yan\Foundation\Kernel\Exceptions\InvalidConfigException;
use Yan\Foundation\Kernel\Http\Response;
use Yan\Foundation\Kernel\Support\Collection;
use Psr\Http\Message\ResponseInterface;

trait ResponseCastable
{
    protected function castResponseToType(ResponseInterface $response, $type = null)
    {
        $response = Response::buildFromPsrResponse($response);
        $response->getBody()->rewind();
        switch ($type ?? 'array') {
            case 'collection':
                return $response->toCollection();
            case 'array':
                return $response->toArray();
            case 'object':
                return $response->toObject();
            case 'raw':
                return $response;
            default:
                if (!is_subclass_of($type, Arrayable::class)) {
                    throw new InvalidConfigException(sprintf(
                        'Config key "response_type" classname must be an instanceof %s',
                        Arrayable::class
                    ));
                }
                return new $type($response);
        }
    }

    protected function detectAndCastResponseToType($response, $type = null)
    {
        switch (true) {
            case $response instanceof ResponseInterface:
                $response = Response::buildFromPsrResponse($response);
                break;
            case ($response instanceof Collection) || is_array($response) || is_object($response):
                $response = new Response(200, [], json_encode($response));
                break;
            case is_scalar($response):
                $response = new Response(200, [], $response);
                break;
            default:
                throw new InvalidArgumentException(sprintf('Unsupported response type "%s"', gettype($response)));
        }
        return $this->castResponseToType($response, $type);
    }
}