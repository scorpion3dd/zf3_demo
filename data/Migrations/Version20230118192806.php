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

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 * @package Migrations
 */
final class Version20230118192806 extends AbstractMigration
{
    /**
     * Returns the description of this migration
     *
     * @return string
     */
    public function getDescription(): string
    {
        return 'This is the initial migration which creates the user table.';
    }

    /**
     * Upgrades the schema to its newer state
     * @param Schema $schema
     *
     * @return void
     */
    public function up(Schema $schema): void
    {
        $table = $schema->createTable('user');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('email', 'string', ['notnull' => true, 'length' => 128]);
        $table->addColumn('full_name', 'string', ['notnull' => true, 'length' => 512]);
        $table->addColumn('password', 'string', ['notnull' => true, 'length' => 256]);
        $table->addColumn('status', 'integer', ['notnull' => true]);
        $table->addColumn('date_created', 'datetime', ['notnull' => true]);
        $table->addColumn('pwd_reset_token', 'string', ['notnull' => false, 'length' => 256]);
        $table->addColumn('pwd_reset_token_creation_date', 'datetime', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['email'], 'email_idx');
        $table->addOption('engine', 'InnoDB');
//        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
    }

    /**
     * Reverts the schema changes
     * @param Schema $schema
     *
     * @return void
     */
    public function down(Schema $schema): void
    {
        $schema->dropTable('user');
//        $this->addSql('DROP table user');
    }
}
