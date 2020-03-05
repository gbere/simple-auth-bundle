<?php

namespace Gbere\Security\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Gbere\Security\Entity\User;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('role-user@fixture.com');
        $user->setPassword('role-user');
        $manager->persist($user);
        $admin = new User();
        $admin->setEmail('role-admin@fixture.com');
        $admin->setPassword('role-admin');
        $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);
        $manager->flush();
    }
}
