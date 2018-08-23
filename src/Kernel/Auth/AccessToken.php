<?php

namespace Yan\Foundation\Kernel\Auth;

use Yan\Foundation\Kernel\Contracts\AccessTokenInterface;
use Yan\Foundation\Kernel\Exceptions\HttpException;
use Yan\Foundation\Kernel\Exceptions\InvalidArgumentException;
use Yan\Foundation\Kernel\Traits\HasHttpRequests;
use Yan\Foundation\Kernel\Traits\InteractsWithCache;
use Pimple\Container;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Yan\Foundation\Kernel\Support\Collection;

abstract class AccessToken implements AccessTokenInterface
{
    use HasHttpRequests, InteractsWithCache;

    protected $app;

    protected $requestMethod = 'GET';

    protected $endpointToGetToken;

    protected $queryName;

    protected $token;

    protected $safeSeconds = 500;

    protected $tokenKey = 'access_token';

    protected $cachePrefix = 'foundation.kernel.access_token.';

    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    public function getRefreshedToken(): array
    {
        return $this->getToken(true);
    }

    public function getToken(bool $refresh = false): array
    {
        $cacheKey = $this->getCacheKey();
        $cache = $this->getCache();

        if (!$refresh && $cache->has($cacheKey)) {
            return $cache->get($cacheKey);
        }

        $token = $this->requestToken($this->getCredentials(), true);

        $this->setToken(Collection::make($token)->get($this->tokenKey), $token['expires_in'] ?? 7200);

        return $token;
    }

    public function setToken(string $token, int $lifetime = 7200): AccessTokenInterface
    {
        $this->getCache()->set($this->getCacheKey(), [
            $this->tokenKey => $token,
            'expires_in' => $lifetime,
        ], $lifetime - $this->safeSeconds);

        return $this;
    }

    public function refresh(): AccessTokenInterface
    {
        $this->getToken(true);

        return $this;
    }

    public function requestToken(array $credentials, $toArray = false)
    {
        $response = $this->sendRequest($credentials);
        $result = json_decode($response->getBody()->getContents(), true);
        $formatted = $this->castResponseToType($response, $this->app['config']->get('response_type'));

        $this->validateResquestResult($result, $response, $formatted);

        return $toArray ? $result : $formatted;
    }

    public function validateResquestResult($result, $response, $formatted)
    {
        if (!Collection::make($result)->has($this->tokenKey)) {
            throw new HttpException('Request access_token fail: '.json_encode($result, JSON_UNESCAPED_UNICODE), $response, $formatted);
        }
    }

    public function applyToRequest(RequestInterface $request, array $requestOptions = []): RequestInterface
    {
        parse_str($request->getUri()->getQuery(), $query);

        $query = http_build_query(array_merge($this->getQuery(), $query));

        return $request->withUri($request->getUri()->withQuery($query));
    }

    protected function sendRequest(array $credentials): ResponseInterface
    {
        $options = [
            ('GET' === $this->requestMethod) ? 'query' : 'json' => $credentials,
        ];

        return $this->setHttpClient($this->app['http_client'])->request($this->getEndpoint(), $this->requestMethod, $options);
    }

    protected function getCacheKey()
    {
        return $this->cachePrefix.md5(json_encode($this->getCredentials()));
    }

    protected function getQuery(): array
    {
        return [$this->queryName ?? $this->tokenKey => $this->getToken()[$this->tokenKey]];
    }

    public function getEndpoint(): string
    {
        if (empty($this->endpointToGetToken)) {
            throw new InvalidArgumentException('No endpoint for access token request.');
        }

        return $this->endpointToGetToken;
    }

    abstract protected function getCredentials(): array;
}