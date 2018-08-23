<?php

namespace Yan\Foundation\Kernel\Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Yan\Foundation\Kernel\Log\LogManager;

class LogServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['logger'] = $app['log'] = function ($app) {
            $config = $this->formatLogConfig($app);

            if (!empty($config)) {
                $app['config']->merge($config);
            }

            return new LogManager($app);
        };
    }

    public function formatLogConfig($app)
    {
        if (empty($app['config']->get('log'))) {
            return [
                'log' => [
                    'default' => 'errorlog',
                    'channels' => [
                        'errorlog' => [
                            'driver' => 'errorlog',
                            'level' => 'debug',
                        ]
                    ]
                ]
            ];
        }

        if (empty($app['config']->get('log.driver'))) {
            return [
                'log' => [
                    'default' => 'single',
                    'channels' => [
                        'single' => [
                            'driver' => 'single',
                            'path' => $app['config']->get('log.file') ?: \sys_get_temp_dir().'/logs/foundation.log',
                            'level' => $app['config']->get('log.level', 'debug'),
                        ],
                    ],
                ],
            ];
        }

        $name = $app['config']->get('log.driver');

        return [
            'log' => [
                'default' => $name,
                'channels' => [
                    $name => $app['config']->get('log'),
                ]
            ]
        ];
    }
}