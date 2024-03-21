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

use MongoDB\Driver\Manager;
use Zend\Log\Logger;
use Zend\Session\Storage\SessionArrayStorage;
use Zend\Session\Validator\RemoteAddr;
use Zend\Session\Validator\HttpUserAgent;
use Zend\Cache\Storage\Adapter\Filesystem;

return [
    'session_config' => [
        'cookie_lifetime'     => 60 * 60 * 1, // Session cookie will expire in 1 hour
        'gc_maxlifetime'      => 60 * 60 * 24 * 30, // How long to store session data on server (for 1 month)
    ],
    'session_manager' => [
        // Session validators (used for security)
        'validators' => [
            RemoteAddr::class,
            HttpUserAgent::class,
        ]
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
                    // Store cached data for 1 hour
                    'ttl' => 60 * 60 * 1
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
    'doctrine' => [
        'migrations_configuration' => [
            'orm_default' => [
                'directory' => 'data/Migrations',
                'name'      => 'Doctrine Database Migrations',
                'namespace' => 'Migrations',
                'table'     => 'migrations',
                'column' => 'version',
                'custom_template' => null,
            ],
        ],
    ],
    'log' => [
        'LoggerGlobal' => [
            'writers' => [
                'dbwriter' => [
                    'name' => 'noop',  // change it to 'name' => 'noop' to disable mongodb logging
                    'options' => [
//                        'manager' => new Manager('mongodb://127.0.0.1:27017'),
                        'manager' => new Manager('mongodb://'
                            . getenv('MONGO_CONNECT_USER')
                            . ':'
                            . getenv('MONGO_CONNECT_PASSWORD')
                            . '@'
                            . getenv('MONGO_HOST')
                            . ':'
                            . getenv('MONGO_PORT')
                            . '/'
                            . getenv('MONGO_CONNECT_DB')
                            . '?directConnection=true'),
                        'collection'   => getenv('MONGO_CONNECT_COLLECTION'),
                        'database'     => getenv('MONGO_CONNECT_DB'),
                        'formatter'    => 'db',

                    ],
                ],
                'filewriter' => [
                    'name' => 'stream',
                    'options' => [
                        'stream' => __DIR__ . '/../../data/logs/logfile.log',
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
    'redis' => [
        'connection' => [
            'default' => [
                'params' => [
                    'host'     => getenv('REDIS_HOST'),
                ]
            ],
        ],
    ],
    'kafka' => [
        'filelog' => 'logfileKafka.log',
        'connection' => [
            'default' => [
                'params' => [
                    'host'     => getenv('KAFKA_HOST'),
                    'port'     => getenv('KAFKA_PORT'),
                    'brokerVersion'     => getenv('KAFKA_BROKER_VERSION'),
                ]
            ],
        ],
    ],
];
