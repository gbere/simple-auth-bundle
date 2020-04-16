<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Gbere\SimpleAuth\Entity\AdminUser;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminFixtures extends Fixture
{
    private const NAME = 'admin-user';

    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager): void
    {
        $adminUser = new AdminUser();
        $adminUser->setEmail(sprintf('%s@fixture.com', self::NAME));
        $adminUser->setName(self::NAME);
        $adminUser->setPassword($this->passwordEncoder->encodePassword($adminUser, self::NAME));
        $adminUser->hasEnabled(true);
        $manager->persist($adminUser);
        $manager->flush();
    }
}
