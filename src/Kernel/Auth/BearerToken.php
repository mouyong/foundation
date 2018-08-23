<?php

namespace Yan\Foundation\Kernel\Auth;

use Psr\Http\Message\RequestInterface;
use Yan\Foundation\Kernel\Auth\AccessToken;

abstract class BearerToken extends AccessToken
{
    protected $requestMethod = 'POST';

    protected $tokenKey = 'data.token';

    public function applyToRequest(RequestInterface $request, array $requestOptions = []): RequestInterface
    {
        $token = $this->getToken()[$this->tokenKey] ?? null;

        if (is_null($token)) {
            $token = $this->getToken()[$this->tokenKey];
        }

        return $this->applyQuery($request)->withHeader('Authorization', 'Bearer '.$token);
    }

    abstract public function applyQuery(RequestInterface $request, array $requestOptions = []): RequestInterface;
}