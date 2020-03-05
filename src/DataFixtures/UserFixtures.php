<?php

namespace Gbere\Security\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Gbere\Security\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('role-user@fixture.com');
        $user->setPassword($this->passwordEncoder->encodePassword($user, 'role-user'));
        $manager->persist($user);
        $admin = new User();
        $admin->setEmail('role-admin@fixture.com');
        $admin->setPassword($this->passwordEncoder->encodePassword($admin, 'role-admin'));
        $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);
        $manager->flush();
    }
}
