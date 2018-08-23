<?php

namespace Yan\Foundation\Kernel;

use Pimple\Container;
use Yan\Foundation\Kernel\Providers\ConfigServiceProvider;
use Yan\Foundation\Kernel\Providers\HttpClientServiceProvider;
use Yan\Foundation\Kernel\Providers\LogServiceProvider;
use Yan\Foundation\Kernel\Providers\RequestServiceProvider;

class ServiceContainer extends Container
{
    protected $id;

    protected $baseUri = '';

    protected $providers = [];

    protected $defaultConfig = [];

    protected $userConfig = [];

    public function __construct(array $config = [], array $prepends = [], $id = null)
    {
        $this->registerProviders($this->getProviders());

        parent::__construct();
    }

    public function getId()
    {
        return $this->id ?? $this->id = md5(json_encode($this->userConfig));
    }

    public function getConfig()
    {
        $base = [
            'http' => [
                'timeout' => 30.0,
                'base_uri' => $this->baseUri,
            ],
        ];

        return array_replace_recursive($base, $this->defaultConfig, $this->userConfig);
    }

    public function getProviders()
    {
        return array_merge([
            ConfigServiceProvider::class,
            LogServiceProvider::class,
            RequestServiceProvider::class,
            HttpClientServiceProvider::class,
        ], $this->providers);
    }

    public function rebind($id, $value)
    {
        $this->offsetUnset($id);
        $this->offsetSet($id, $value);
    }

    public function __get($id)
    {
        return $this->offsetGet($id);
    }

    public function __set($id, $value)
    {
        $this->offsetSet($id, $value);
    }

    public function registerProviders(array $providers)
    {
        foreach ($providers as $provider) {
            parent::register(new $provider);
        }
    }
}