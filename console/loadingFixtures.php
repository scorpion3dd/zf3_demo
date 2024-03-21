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

use Console\Db;
use Fixtures\LogFixtures;
use Fixtures\PermissionFixtures;
use Fixtures\RoleFixtures;
use Fixtures\RolePermissionFixtures;
use Fixtures\UserFixtures;
use Fixtures\UserRoleFixtures;

require_once __DIR__ . '/../vendor/autoload.php';

$db = new Db();
$db->execute([
    RoleFixtures::class,
    PermissionFixtures::class,
    UserFixtures::class,
    UserRoleFixtures::class,
    RolePermissionFixtures::class,
//    LogFixtures::class,
], 'Loading fixtures to DB ' . $db->getDbName() . ' success');
