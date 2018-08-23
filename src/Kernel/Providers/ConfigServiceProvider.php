<?php

namespace Yan\Foundation\Kernel\Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Yan\Foundation\Kernel\Config;
use Yan\Foundation\Kernel\ServiceContainer;

class ConfigServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['config'] = function (ServiceContainer $app) {
            return new Config($app->getConfig());
        };
    }
}