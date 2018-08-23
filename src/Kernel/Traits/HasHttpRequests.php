<?php

namespace Yan\Foundation\Kernel\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\ResponseInterface;

trait HasHttpRequests
{
    use ResponseCastable;

    protected $httpClient;

    protected $middlewares = [];

    protected $handlerStack;

    protected static $defaults = [
        'curl' => [
            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        ],
    ];

    public static function setDefaultOptions($defaults = [])
    {
        self::$defaults = $defaults;
    }

    public static function getDefaultOptions(): array
    {
        return self::$defaults;
    }

    public function setHttpClient(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    public function getHttpClient(): Client
    {
        if (!($this->httpClient instanceof ClientInterface)) {
            $this->httpClient = new Client();
        }

        return $this->httpClient;
    }

    public function pushMiddleware(callable $middleware, string $name = null)
    {
        if (!is_null($name)) {
            $this->middlewares[$name] = $middleware;
        } else {
            array_push($this->middlewares, $middleware);
        }

        return $this;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function request($url, $method = 'GET', $options = []): ResponseInterface
    {
        $method = strtoupper($method);
        $options = array_merge(self::$defaults, $options, ['handler' => $this->getHandlerStack()]);
        $options = $this->fixJsonIssue($options);

        if (property_exists($this, 'baseUri') && !is_null($this->baseUri)) {
            $options['base_uri'] = $this->baseUri;
        }

        $response = $this->getHttpClient()->request($method, $url, $options);
        $response->getBody()->rewind();

        return $response;
    }

    public function setHandlerStack(HandlerStack $handlerStack)
    {
        $this->handlerStack = $handlerStack;

        return $this;
    }

    public function getHandlerStack(): HandlerStack
    {
        if ($this->handlerStack) {
            return $this->handlerStack;
        }

        $this->handlerStack = HandlerStack::create();

        foreach ($this->middlewares as $name => $middleware) {
            $this->handlerStack->push($middleware, $name);
        }

        return $this->handlerStack;
    }

    protected function fixJsonIssue(array $options): array
    {
        if (isset($options['json']) && is_array($options['json'])) {
            $options['headers'] = array_merge($options['headers'] ?? [], ['Content-Type' => 'application/json']);

            if (empty($options['json'])) {
                $options['body'] = \GuzzleHttp\json_encode($options['json'], JSON_FORCE_OBJECT);
            } else {
                $options['body'] = \GuzzleHttp\json_encode($options['json'], JSON_UNESCAPED_UNICODE);
            }

            unset($options['json']);
        }

        return $options;
    }
}