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

namespace Application\Repository;

use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ODM\MongoDB\Query\Query;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Application\Document\Log;

/**
 * This is the custom repository class for Log Document
 * @package Application\Repository
 */
class LogsRepository extends DocumentRepository
{
    /**
     * @return Query
     */
    public function findAllLogs(): object
    {
        $dm = $this->getDocumentManager();
        $queryBuilder = $dm->createQueryBuilder(Log::class)->limit(20);

        return $queryBuilder->getQuery();
    }

    /**
     * @return void
     * @throws MongoDBException
     */
    public function deleteAllLogs(): void
    {
        $dm = $this->getDocumentManager();
        $dm->createQueryBuilder(Log::class)
            ->remove()
            ->getQuery()
            ->execute();
    }
}
