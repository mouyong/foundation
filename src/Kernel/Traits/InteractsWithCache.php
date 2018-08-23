<?php

namespace Yan\Foundation\Kernel\Traits;

use Yan\Foundation\Kernel\ServiceContainer;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\Simple\FilesystemCache;

trait InteractsWithCache
{
    protected $cache;

    public function getCache()
    {
        if ($this->cache) {
            return $this->cache;
        }

        if (property_exists($this, 'app') && $this->app instanceof ServiceContainer
            && isset($this->app['cache']) && $this->app['cache'] instanceof CacheInterface) {
            return $this->cache = $this->app['cache'];
        }

        return $this->cache = $this->createDefaultCache();
    }

    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    protected function createDefaultCache()
    {
        return new FilesystemCache();
    }
}