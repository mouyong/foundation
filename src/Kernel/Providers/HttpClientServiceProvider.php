<?php

namespace Yan\Foundation\Kernel\Providers;

use GuzzleHttp\Client;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class HttpClientServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['http_client'] = function ($app) {
            return new Client($app['config']->get('http', []));
        };
    }
}