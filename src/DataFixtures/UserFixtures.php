<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Gbere\SimpleAuth\Entity\Role;
use Gbere\SimpleAuth\Entity\User;
use Gbere\SimpleAuth\Repository\RoleRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @var array
     */
    private const USERS = [
        'role-user' => 'ROLE_USER',
        'role-admin' => 'ROLE_ADMIN',
    ];

    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;
    /** @var ObjectManager */
    private $manager;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;

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

    private function createAndPersistUser(string $name, string $role): void
    {
        /** @var RoleRepository $repoRole */
        $repoRole = $this->manager->getRepository(Role::class);
        $user = new User();
        $user->setEmail(sprintf('%s@fixture.com', $name));
        $user->setPassword($this->passwordEncoder->encodePassword($user, $name));
        /** @var Role $role */
        $role = $repoRole->findOneBy(['name' => $role]);
        $user->addRoleEntity($role);
        $this->manager->persist($user);
        $this->manager->flush();
    }
}
