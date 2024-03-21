<?php
/**
 * This file is part of the Simple Web Demo Free Lottery Management Application.
 *
 * This project is no longer maintained.
 * The project is written in Zend Framework 3 Release.
 *
 * @link https://github.com/scorpion3dd
 * @author Denis Puzik <scorpion3dd@gmail.com>
 * @copyright Copyright (c) 2020-2021 scorpion3dd
 */

declare(strict_types=1);

/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

use Zend\Cache\Storage\Adapter\Filesystem;
use Zend\Log\Logger;
use Zend\Session\Storage\SessionArrayStorage;

return [
    'session_config' => [
//        'cookie_lifetime'     => 60 * 60 * 1, // Session cookie will expire in 1 hour
//        'gc_maxlifetime'      => 60 * 60 * 24 * 30, // How long to store session data on server (for 1 month)
    ],
    'session_storage' => [
        'type' => SessionArrayStorage::class
    ],
    'session_containers' => [
        'Demo_Auth',
        'I18nSessionContainer',
    ],
    'caches' => [
        'FilesystemCache' => [
            'adapter' => [
                'name'    => Filesystem::class,
                'options' => [
                    'cache_dir' => __DIR__ . '/../../data/cache',
                    // Store cached data for 1 milliseconds require for tests
                    'ttl' => 1
                ],
            ],
            'plugins' => [
                [
                    'name' => 'serializer',
                    'options' => [],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
            ],
        ],
    'log' => [
        'LoggerGlobal' => [
            'writers' => [
                'filewriter' => [
                    'name' => 'stream',
                    'priority' => Logger::DEBUG,
                    'options' => [
                        'stream' => __DIR__ . '/../../data/logs/logfile_tests.log',
                        'filters' => [
                            'priority' => [
                                'name' => 'priority',
                                'options' => [
                                    'operator' => '<=',
                                    'priority' => Logger::DEBUG,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
