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

use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use Doctrine\Migrations\Tools\Console\Command;
use Symfony\Component\Console\Application;

require_once __DIR__ . '/../vendor/autoload.php';
$params = require_once __DIR__ . '/../config/autoload/local.php';
if (empty($params['doctrine']['connection']['orm_default']['params'])) {
    echo 'dbParams - not read';
}
$dbParams = $params['doctrine']['connection']['orm_default']['params'];
if (! is_array($dbParams)) {
    echo 'dbParams - not array';
}
$dbParams['driver'] = 'pdo_mysql';
try {
    $connection = DriverManager::getConnection($dbParams);
    /** @phpstan-ignore-next-line */
    $configuration = new Configuration($connection);
    /*
    $configuration->addMigrationsDirectory(
        'Migrations',
        __DIR__ . '/../data/Migrations'
    );
    $configuration->setAllOrNothing(true);
    $configuration->setCheckDatabasePlatform(false);

    $storageConfiguration = new TableMetadataStorageConfiguration();
    $storageConfiguration->setTableName('migrations');

    $configuration->setMetadataStorageConfiguration($storageConfiguration);

    $dependencyFactory = DependencyFactory::fromConnection(
        new ExistingConfiguration($configuration),
        new ExistingConnection($connection)
    );

    $cli = new Application('Doctrine Migrations');
    $cli->setCatchExceptions(true);

    $cli->addCommands([
        new Command\DumpSchemaCommand($dependencyFactory),
        new Command\ExecuteCommand($dependencyFactory),
        new Command\GenerateCommand($dependencyFactory),
        new Command\LatestCommand($dependencyFactory),
        new Command\ListCommand($dependencyFactory),
        new Command\MigrateCommand($dependencyFactory),
        new Command\RollupCommand($dependencyFactory),
        new Command\StatusCommand($dependencyFactory),
        new Command\SyncMetadataCommand($dependencyFactory),
        new Command\VersionCommand($dependencyFactory),
    ]);
    */
//            $file = 'W:\OSPanel\domains\my\learn\php\zend\zf3\vendor\composer/../laminas\laminas-code\src\Generator\ClassGenerator.php';
//            include $file;
//            $file = 'W:\OSPanel\domains\my\learn\php\zend\zf3\vendor\composer/../friendsofphp/proxy-manager-lts/src/ProxyManager\Generator\ClassGenerator.php';
//            include $file;
    echo 'migrations - start';
//    $cli->run();
    echo 'migrations - finish';
} catch (Exception $e) {
    echo 'Error: Message - ' . $e->getMessage()
        . ', in file - ' . $e->getFile()
        . ', in line - ' . $e->getLine();
}
