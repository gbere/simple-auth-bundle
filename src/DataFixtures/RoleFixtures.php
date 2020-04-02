<?php

declare(strict_types=1);

namespace Gbere\Security\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Gbere\Security\Entity\Role;

class RoleFixtures extends Fixture
{
    /** @var array */
    private const ROLES = [
        'ROLE_USER' => 'role_user.description',
        'ROLE_ADMIN' => 'role_admin.description',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::ROLES as $roleName => $roleDescription) {
            $role = new Role();
            $role->setName($roleName);
            $role->setDescription($roleDescription);
            $manager->persist($role);
            $manager->flush();
        }
    }
}
