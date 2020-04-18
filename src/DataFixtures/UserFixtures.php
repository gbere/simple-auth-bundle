<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ObjectManager;
use Gbere\SimpleAuth\Entity\Role;
use Gbere\SimpleAuth\Repository\RoleRepository;
use Gbere\SimpleAuth\Repository\UserRepository;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var array
     */
    private const USERS = [
        'role-user' => 'ROLE_USER',
        'role-admin' => 'ROLE_ADMIN',
    ];

    /** @var UserRepository */
    private $userRepository;
    /** @var RoleRepository */
    private $roleRepository;

    public function __construct(UserRepository $userRepository, RoleRepository $roleRepository)
    {
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::USERS as $userName => $userRole) {
            // Email = name + @fixture.com
            // Password = name
            $this->createAndPersistUser($userName, $userRole);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            RoleFixtures::class,
        ];
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function createAndPersistUser(string $name, string $role): void
    {
        $user = $this->userRepository->createUser();
        $user->setEmail(sprintf('%s@fixture.com', $name));
        $user->setName($name);
        $user->setPassword($this->userRepository->encodePassword($name));
        /** @var Role $role */
        $role = $this->roleRepository->findOneBy(['name' => $role]);
        $user->addRoleEntity($role);
        $user->hasEnabled(true);
        $this->userRepository->persistAndFlush($user);
    }
}
