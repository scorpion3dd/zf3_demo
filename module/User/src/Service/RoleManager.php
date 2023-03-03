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

namespace User\Service;

use Carbon\Carbon;
use Fixtures\RoleFixtures;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Redis;
use User\Entity\Role;
use User\Entity\Permission;
use User\Repository\RoleRepository;
use Zend\Log\Logger;

/**
 * This service is responsible for adding/editing roles
 * @package User\Service
 */
class RoleManager
{
    protected const PERMISSIONS_INHERITED = 'inherited';
    protected const PERMISSIONS_OWN = 'own';

    /** @var EntityManager $entityManager */
    private EntityManager $entityManager;

    /** @var RbacManager $rbacManager */
    private RbacManager $rbacManager;

    /** @var Logger $logger */
    protected Logger $logger;

    /** @var Redis $redis */
    protected Redis $redis;

    /** @var array $config */
    protected array $config;

    /**
     * RoleManager constructor
     * @param EntityManager $entityManager
     * @param RbacManager $rbacManager
     * @param Logger $logger
     * @param array $config
     */
    public function __construct(
        EntityManager $entityManager,
        RbacManager $rbacManager,
        Logger $logger,
        array $config
    ) {
        $this->entityManager = $entityManager;
        $this->rbacManager = $rbacManager;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * Adds a new role
     * @param array $data
     *
     * @return Role
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function addRole(array $data): Role
    {
        /** @phpstan-ignore-next-line */
        $existingRole = $this->entityManager->getRepository(Role::class)->findOneByName($data['name']);
        if ($existingRole != null) {
            $message = 'Role with such name already exists';
            $this->logger->log(Logger::ERR, $message);
            throw new Exception($message);
        }
        $role = new Role;
        $role->setName($data['name']);
        $role->setDescription($data['description']);
        $role->setDateCreated(Carbon::now());
        // add parent roles to inherit
        $inheritedRoles = $data['inherit_roles'];
        if (! empty($inheritedRoles)) {
            foreach ($inheritedRoles as $roleId) {
                /** @phpstan-ignore-next-line */
                $parentRole = $this->entityManager->getRepository(Role::class)->findOneById($roleId);
                if (empty($parentRole)) {
                    $message = 'Role to inherit not found';
                    $this->logger->log(Logger::ERR, $message);
                    throw new Exception($message);
                }
                /** @phpstan-ignore-next-line */
                if (! $role->getParentRoles()->contains($parentRole)) {
                    $role->addParent($parentRole);
                }
            }
        }
        $this->entityManager->persist($role);
        $this->entityManager->flush();
        $this->reloadRBACContainer();

        return $role;
    }

    /**
     * Updates an existing role
     * @param Role $role
     * @param array $data
     *
     * @return Role
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function updateRole(Role $role, array $data): Role
    {
        /** @phpstan-ignore-next-line */
        $existingRole = $this->entityManager->getRepository(Role::class)->findOneByName($data['name']);
        if ($existingRole != null && $existingRole != $role) {
            $message = 'Another role with such name already exists';
            $this->logger->log(Logger::ERR, $message);
            throw new Exception($message);
        }
        $role->setName($data['name']);
        $role->setDescription($data['description']);
        $role->clearParentRoles();
        // add the new parent roles to inherit
        $inheritedRoles = $data['inherit_roles'];
        if (! empty($inheritedRoles)) {
            foreach ($inheritedRoles as $roleId) {
                /** @phpstan-ignore-next-line */
                $parentRole = $this->entityManager->getRepository(Role::class)->findOneById($roleId);
                if (empty($parentRole)) {
                    $message = 'Role to inherit not found';
                    $this->logger->log(Logger::ERR, $message);
                    throw new Exception($message);
                }
                /** @phpstan-ignore-next-line */
                if (! $role->getParentRoles()->contains($parentRole)) {
                    $role->addParent($parentRole);
                }
            }
        }
        $this->entityManager->flush();
        $this->reloadRBACContainer();

        return $role;
    }

    /**
     * Deletes the given role
     * @param Role $role
     *
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteRole(Role $role): void
    {
        $this->entityManager->remove($role);
        $this->entityManager->flush();
        $this->reloadRBACContainer();
    }

    /**
     * This method creates the default set of roles if no roles exist at all
     *
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createDefaultRolesIfNotExist(): void
    {
        /** @var RoleRepository $roleRepository */
        $roleRepository = $this->entityManager->getRepository(Role::class);
        $role = $roleRepository->findOneBy([]);
        if ($role != null) {
            return;
        }
        $defaultRoles = $roleRepository->getAllDefaultRoles();
        foreach ($defaultRoles as $name => $info) {
            $role = new Role();
            $role->setName($name);
            $role->setDescription($info['description']);
            $role->setDateCreated(Carbon::now());
            /** @phpstan-ignore-next-line */
            if (! empty($info['parent'])) {
                /** @var Role|null $parentRole */
                /** @phpstan-ignore-next-line */
                $parentRole = $roleRepository->findOneByName($info['parent']);
                if (empty($parentRole)) {
                    $message = 'Parent role ' . $info['parent'] . ' doesn\'t exist';
                    $this->logger->log(Logger::ERR, $message);
                    throw new Exception($message);
                }
                $role->setParentRole($parentRole);
            }
            $this->entityManager->persist($role);
            // Assign permissions to role
            $permissions = $this->entityManager->getRepository(Permission::class)->findByName($info['permissions']);
            foreach ($permissions as $permission) {
                $role->getPermissions()->add($permission);
            }
        }
        $this->entityManager->flush();
        $this->reloadRBACContainer();
    }

    /**
     * Retrieves all permissions from the given role and its child roles
     * @param Role $role
     *
     * @return array
     */
    public function getEffectivePermissions(Role $role): array
    {
        $effectivePermissions = [];
        foreach ($role->getParentRoles() as $parentRole) {
            $parentPermissions = $this->getEffectivePermissions($parentRole);
            foreach ($parentPermissions as $name => $inherited) {
                $effectivePermissions[$name] = self::PERMISSIONS_INHERITED;
            }
        }
        foreach ($role->getPermissions() as $permission) {
            if (! isset($effectivePermissions[$permission->getName()])) {
                $effectivePermissions[$permission->getName()] = self::PERMISSIONS_OWN;
            }
        }

        return $effectivePermissions;
    }

    /**
     * Updates permissions of a role
     * @param Role $role
     * @param array $data
     *
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function updateRolePermissions(Role $role, array $data): void
    {
        // Remove old permissions
        $role->getPermissions()->clear();
        // Assign new permissions to role
        foreach ($data['permissions'] as $name => $isChecked) {
            if (! $isChecked) {
                continue;
            }
            $permission = $this->entityManager->getRepository(Permission::class)->findOneByName($name);
            if (empty($permission)) {
                $message = 'Permission with such name doesn\'t exist';
                $this->logger->log(Logger::ERR, $message);
                throw new Exception($message);
            }
            $role->getPermissions()->add($permission);
        }
        $this->entityManager->flush();
        $this->reloadRBACContainer();
    }

    /**
     * @return void
     */
    private function reloadRBACContainer(): void
    {
        $this->rbacManager->init(true);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function rolesGetFromQueueRedis(): array
    {
        $roles = [];
        $rolesRedises = $this->getRedis()->lRange(RoleFixtures::REDIS_SETS_ROLES, 0, -1);
        foreach ($rolesRedises as $rolesRedis) {
            /** @var Role|null $roleRedis */
            $roleRedis = unserialize($rolesRedis);
            if (! empty($roleRedis) && $roleRedis instanceof Role) {
                $roles[] = $roleRedis;
            }
        }

        return $roles;
    }

    /**
     * @param array $roles
     *
     * @return void
     * @throws Exception
     */
    public function rolesPushToQueueRedis(array $roles): void
    {
        foreach ($roles as $role) {
            $this->getRedis()->rPush(RoleFixtures::REDIS_SETS_ROLES, serialize($role));
        }
        $this->getRedis()->expire(RoleFixtures::REDIS_SETS_ROLES, RoleFixtures::REDIS_SETS_ROLES_TTL);
    }

    /**
     * @param Role $role
     *
     * @return void
     * @throws Exception
     */
    public function rolePushToQueueRedis(Role $role): void
    {
        $this->getRedis()->rPush(RoleFixtures::REDIS_SETS_ROLES, serialize($role));
        $this->getRedis()->expire(RoleFixtures::REDIS_SETS_ROLES, RoleFixtures::REDIS_SETS_ROLES_TTL);
    }

    /**
     * @return int
     * @throws Exception
     */
    public function roleQueueLength(): int
    {
        return (int)$this->getRedis()->lLen(RoleFixtures::REDIS_SETS_ROLES);
    }

    /**
     * @param Role|null $role
     * @throws Exception
     */
    public function roleSetRedis(?Role $role): void
    {
        if (! empty($role)) {
            $this->getRedis()->set(
                RoleFixtures::REDIS_ROLE . $role->getId(),
                serialize($role),
                ['EX' => RoleFixtures::REDIS_SETS_ROLES_TTL]
            );
        }
    }

    /**
     * @param int $roleId
     *
     * @return Role|null
     * @throws Exception
     */
    public function roleGetRedis(int $roleId): ?Role
    {
        /** @var string|null $roleStr */
        $roleStr = $this->getRedis()->get(RoleFixtures::REDIS_ROLE . $roleId);
        /** @var Role|null $role */
        $role = unserialize($roleStr);
        if (! empty($role) && $role instanceof Role) {
            return $role;
        }

        return null;
    }

    /**
     * @param int $roleId
     *
     * @return bool
     * @throws Exception
     */
    public function roleCheckRedis(int $roleId): bool
    {
        return (bool)$this->getRedis()->exists(RoleFixtures::REDIS_ROLE . $roleId);
    }

    /**
     * @param Role $role
     * @param int $delay
     *
     * @return void
     * @throws Exception
     */
    public function roleAddInSetRedis(Role $role, int $delay = 180): void
    {
        $this->getRedis()->zAdd(
            RoleFixtures::REDIS_ROLE_SET,
            time() + $delay,
            (string)$role->getId(),
            serialize($role)
        );
    }

    /**
     * @return array
     * @throws Exception
     */
    public function rolesGetByScoreRedis(): array
    {
        return $this->getRedis()->zRangeByScore(RoleFixtures::REDIS_ROLE_SET, time() - 600, '+inf');
    }

    /**
     * @param Role $role
     * @throws Exception
     */
    public function roleDelRedis(Role $role): void
    {
        $this->getRedis()->zRem(RoleFixtures::REDIS_ROLE_SET, (string)$role->getId());
        $this->getRedis()->del(RoleFixtures::REDIS_ROLE . $role->getId());
    }

    /**
     * @return Redis
     * @throws Exception
     */
    public function getRedis(): Redis
    {
        if (empty($this->redis)) {
            $this->buildRedis();
        }

        return $this->redis;
    }

    /**
     * @return void
     * @throws Exception
     */
    private function buildRedis(): void
    {
        $this->redis = new Redis();
        $connectConfig = isset($this->config['redis']['connection']['default']['params']['host'])
            ? $this->config['redis']['connection']['default']['params']['host'] : '';
        $this->redis->connect($connectConfig);
    }

    /**
     * @param Redis $redis
     *
     * @return $this
     */
    public function setRedis(Redis $redis): self
    {
        $this->redis = $redis;

        return $this;
    }

    /**
     * @param array $config
     *
     * @return $this
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }
}
