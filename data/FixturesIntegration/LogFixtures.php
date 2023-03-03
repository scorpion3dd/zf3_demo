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

namespace FixturesIntegration;

use Application\Document\Log;
use Carbon\Carbon;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Fixtures\AbstractFixtures;

/**
 * Auto-generated Log Fixtures for Integration tests
 * @package FixturesIntegration
 */
class LogFixtures extends AbstractFixtures implements FixtureInterface
{
    /**
     * LogFixtures construct
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct([self::INIT_MONGO_INTEGRATION, self::INIT_COUNT_LOGS_INTEGRATION]);
    }

    /**
     * @param ObjectManager $manager
     *
     * @return void
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $countLogs = $this->getCountLogs();
        for ($i = 1; $i <= $countLogs; $i++) {
            $priority = Log::getPriorityEven($i);
            $priorityList = Log::getPriorities();
            $priorityName = $priorityList[$priority];
            $log = $this->createLog('{orm_default} DB Connected.', $priority, $priorityName);
            $this->dm->persist($log);
        }
        $this->dm->flush();

        echo PHP_EOL
            . 'LogFixtures added ' . $countLogs . ' items for integration tests to MongoDB'
            . PHP_EOL;
    }

    /**
     * @param string $message
     * @param int $priority
     * @param string $priorityName
     * @param int $currentUserId
     *
     * @return Log
     */
    private function createLog(string $message, int $priority, string $priorityName, int $currentUserId = 0): Log
    {
        $log = new Log();
        $log->setMessage($message);
        $log->setPriority($priority);
        $log->setPriorityName($priorityName);
        $log->setExtra(['currentUserId=' . $currentUserId]);
        $log->setTimestamp(Carbon::parse('2023-01-01'));

        return $log;
    }
}
