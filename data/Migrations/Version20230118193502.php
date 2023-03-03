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
final class Version20230118193502 extends AbstractMigration
{
    /**
     * Returns the description of this migration
     *
     * @return string
     */
    public function getDescription(): string
    {
        return 'A migration which creates the `role` and `permission` tables.';
    }

    /**
     * Upgrades the schema to its newer state
     * @param Schema $schema
     *
     * @return void
     */
    public function up(Schema $schema): void
    {
        $table = $schema->createTable('role');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 128]);
        $table->addColumn('description', 'string', ['notnull' => true, 'length' => 1024]);
        $table->addColumn('date_created', 'datetime', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['name'], 'name_idx');
        $table->addOption('engine', 'InnoDB');

        $table = $schema->createTable('role_hierarchy');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('parent_role_id', 'integer', ['notnull' => true]);
        $table->addColumn('child_role_id', 'integer', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint(
            'role',
            ['parent_role_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'],
            'role_role_parent_role_id_fk'
        );
        $table->addForeignKeyConstraint(
            'role',
            ['child_role_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'],
            'role_role_child_role_id_fk'
        );
        $table->addOption('engine', 'InnoDB');

        $table = $schema->createTable('permission');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 128]);
        $table->addColumn('description', 'string', ['notnull' => true, 'length' => 1024]);
        $table->addColumn('date_created', 'datetime', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['name'], 'name_idx');
        $table->addOption('engine', 'InnoDB');

        $table = $schema->createTable('role_permission');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('role_id', 'integer', ['notnull' => true]);
        $table->addColumn('permission_id', 'integer', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint(
            'role',
            ['role_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'],
            'role_permission_role_id_fk'
        );
        $table->addForeignKeyConstraint(
            'permission',
            ['permission_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'],
            'role_permission_permission_id_fk'
        );
        $table->addOption('engine', 'InnoDB');

        $table = $schema->createTable('user_role');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('user_id', 'integer', ['notnull' => true]);
        $table->addColumn('role_id', 'integer', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint(
            'user',
            ['user_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'],
            'user_role_user_id_fk'
        );
        $table->addForeignKeyConstraint(
            'role',
            ['role_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => 'CASCADE'],
            'user_role_role_id_fk'
        );
        $table->addOption('engine', 'InnoDB');
    }

    /**
     * Reverts the schema changes
     * @param Schema $schema
     *
     * @return void
     */
    public function down(Schema $schema): void
    {
        $schema->dropTable('user_role');
        $schema->dropTable('role_permission');
        $schema->dropTable('permission');
        $schema->dropTable('role');
    }
}
