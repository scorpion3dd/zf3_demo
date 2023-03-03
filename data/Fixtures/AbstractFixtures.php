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

namespace Fixtures;

use Console\Db;
use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Exception;
use Faker\Factory;
use Faker\Generator;
use Redis;

/**
 * Auto-generated Abstract Fixtures
 * @package Fixtures
 */
abstract class AbstractFixtures
{
    protected const INIT_FAKER = 'faker';
    protected const INIT_COUNT_USERS = 'countUsers';
    protected const INIT_COUNT_USERS_INTEGRATION = 'countUsersIntegration';
    protected const INIT_REDIS = 'redis';
    protected const INIT_COUNT_LOGS = 'countLogs';
    protected const INIT_COUNT_LOGS_INTEGRATION = 'countLogsIntegration';
    protected const INIT_MONGO = 'mongo';
    protected const INIT_MONGO_INTEGRATION = 'mongoIntegration';
    protected const COUNT_USERS = 30;
    protected const COUNT_LOGS = 5;
    protected const CONFIG_AUTOLOAD_LOCAL = '/../../config/autoload/local.php';
    protected const CONFIG_AUTOLOAD_LOCAL_INTEGRATION = '/../../config/autoload_test/local.php';
    protected const CONFIG_AUTOLOAD_GLOBAL = '/../../config/autoload/global.php';
    protected const CONFIG_AUTOLOAD_MONGO = '/../../config/autoload/module.doctrine-mongo-odm.local.php';
    protected const CONFIG_AUTOLOAD_MONGO_INTEGRATION = '/../../config/autoload_test/module.doctrine-mongo-odm.local.php';

    /** @var Generator $faker */
    protected Generator $faker;

    /** @var int $countUsers */
    protected int $countUsers;

    /** @var int $countLogs */
    protected int $countLogs;

    /** @var Redis $redis */
    protected Redis $redis;

    /** @var DocumentManager $dm */
    protected DocumentManager $dm;

    /**
     * AbstractFixtures constructor
     * @param array $options
     * @throws Exception
     */
    public function __construct(array $options = [])
    {
        if (empty($this->faker) && in_array(self::INIT_FAKER, $options)) {
            $this->faker = Factory::create();
        }
        if (empty($this->countUsers) && in_array(self::INIT_COUNT_USERS, $options)) {
            $this->initCountUsers();
        }
        if (empty($this->countUsers) && in_array(self::INIT_COUNT_USERS_INTEGRATION, $options)) {
            $this->initCountUsers(Db::TYPE_INTEGRATION);
        }
        if (empty($this->countLogs) && in_array(self::INIT_COUNT_LOGS, $options)) {
            $this->initCountLogs();
        }
        if (empty($this->countLogs) && in_array(self::INIT_COUNT_LOGS_INTEGRATION, $options)) {
            $this->initCountLogs(Db::TYPE_INTEGRATION);
        }
        if (empty($this->redis) && in_array(self::INIT_REDIS, $options)) {
            $this->initRedis();
        }
        if (empty($this->dm) && in_array(self::INIT_MONGO, $options)) {
            $this->initMongo();
        }
        if (empty($this->dm) && in_array(self::INIT_MONGO_INTEGRATION, $options)) {
            $this->initMongo(Db::TYPE_INTEGRATION);
        }
    }

    /**
     * @return int
     */
    public function getCountUsers(): int
    {
        return $this->countUsers;
    }

    /**
     * @param int $countUsers
     */
    public function setCountUsers(int $countUsers): void
    {
        $this->countUsers = $countUsers;
    }

    /**
     * @param string $config
     * @param string $configName
     *
     * @return array
     * @throws Exception
     */
    private function getConfig(string $config, string $configName): array
    {
        $file = __DIR__ . $config;
        if (! file_exists($file)) {
            throw new Exception("File $configName with config not exists");
        }

        return require $file;
    }

    /**
     * @param string $type
     *
     * @return void
     * @throws Exception
     */
    private function initCountUsers(string $type = ''): void
    {
        $this->countUsers = self::COUNT_USERS;
        $params = $this->getParams($type);
        if (! empty($params['app']['count_users'])) {
            $this->countUsers = $params['app']['count_users'];
        }
    }

    /**
     * @param string $type
     *
     * @return void
     * @throws Exception
     */
    private function initCountLogs(string $type = ''): void
    {
        $this->countLogs = self::COUNT_LOGS;
        $params = $this->getParams($type);
        if (! empty($params['app']['count_logs'])) {
            $this->countLogs = $params['app']['count_logs'];
        }
    }

    /**
     * @param string $type
     *
     * @return array
     * @throws Exception
     */
    private function getParams(string $type = ''): array
    {
        $config = self::CONFIG_AUTOLOAD_LOCAL;
        if ($type == Db::TYPE_INTEGRATION) {
            $config = self::CONFIG_AUTOLOAD_LOCAL_INTEGRATION;
        }
        return $this->getConfig($config, 'local');
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function initRedis(): void
    {
        if (empty($this->redis)) {
            $params = $this->getConfig(self::CONFIG_AUTOLOAD_GLOBAL, 'global');
            $connectConfig = isset($params['redis']['connection']['default']['params']['host'])
                ? $params['redis']['connection']['default']['params']['host'] : '';
            $this->redis = new Redis();
            if (! $this->redis->connect($connectConfig)) {
                throw new Exception('Redis connect exception');
            }
        }
    }

    /**
     * @param string $type
     *
     * @return void
     * @throws Exception
     */
    protected function initMongo(string $type = ''): void
    {
        if (empty($this->dm)) {
            $config = self::CONFIG_AUTOLOAD_MONGO;
            if ($type == Db::TYPE_INTEGRATION) {
                $config = self::CONFIG_AUTOLOAD_MONGO_INTEGRATION;
            }
            $params = $this->getConfig($config, 'module.doctrine-mongo-odm.local');
            $connectConfig = isset($params['doctrine']['connection']['odm_default'])
                ? $params['doctrine']['connection']['odm_default'] : [];
            $configuration = isset($params['doctrine']['configuration']['odm_default'])
                ? $params['doctrine']['configuration']['odm_default'] : [];

            AnnotationDriver::registerAnnotationClasses();
            $config = new Configuration();
            $config->setProxyDir($configuration['proxy_dir']);
            $config->setProxyNamespace($configuration['proxy_namespace']);
            $config->setHydratorDir($configuration['hydrator_dir']);
            $config->setHydratorNamespace($configuration['hydrator_namespace']);
            /** @phpstan-ignore-next-line */
            $config->setMetadataDriverImpl(AnnotationDriver::create(__DIR__ . '/../src/Document'));
            $config->setDefaultDB($connectConfig['dbname']);
            $this->dm = DocumentManager::create(
                new Connection($this->createMongoDbConnectionString($connectConfig)),
                $config
            );
        }
    }

    /**
     * Example:  mongodb://localhost:27017
     * @param array $connectConfig
     *
     * @return string
     */
    protected function createMongoDbConnectionString(array $connectConfig): string
    {
        return 'mongodb://' . $connectConfig['server'] . ':' . $connectConfig['port'];
    }

    /**
     * @return int
     */
    public function getCountLogs(): int
    {
        return $this->countLogs;
    }

    /**
     * @param int $countLogs
     */
    public function setCountLogs(int $countLogs): void
    {
        $this->countLogs = $countLogs;
    }
}
