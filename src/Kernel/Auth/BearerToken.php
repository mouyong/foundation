<?php

namespace Yan\Foundation\Kernel\Auth;

use Psr\Http\Message\RequestInterface;
use Yan\Foundation\Kernel\Auth\AccessToken;

abstract class BearerToken extends AccessToken
{
    protected $requestMethod = 'POST';

    protected $tokenKey = 'data.token';

    public function requestToken(array $credentials, $toArray = false)
    {
        $response = $this->sendRequest($credentials);
        $result = json_decode($response->getBody()->getContents(), true);
        $formatted = $this->castResponseToType($response, $this->app['config']->get('response_type'));

        if ($this->requestExpired($result)) {
            $this->getCache()->delete($this->getCacheKey());

            $response = $this->sendRequest($credentials);
            $result = json_decode($response->getBody()->getContents(), true);
        }

        $this->validateResquestResult($result, $response, $formatted);

        return $toArray ? $result : $formatted;
    }

    public function applyToRequest(RequestInterface $request, array $requestOptions = []): RequestInterface
    {
        $token = $this->getToken()[$this->tokenKey] ?? null;

        if (is_null($token)) {
            $token = $this->getToken()[$this->tokenKey];
        }

        return $this->applyQuery($request)->withHeader('Authorization', 'Bearer '.$token);
    }

    public function validateResquestResult($result, $response, $formatted)
    {
        parent::validateResquestResult($result, $response, $formatted);
    }

    abstract public function requestExpired(array $response);

    abstract public function applyQuery(RequestInterface $request, array $requestOptions = []): RequestInterface;
}