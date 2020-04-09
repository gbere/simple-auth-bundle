<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Gbere\SimpleAuth\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegisterControllerTest extends WebTestCase
{
    /** @var string */
    private const EMAIL = 'register-user@test.com';
    /** @var string */
    private const PASSWORD = 'patata';

    public function setUp(): void
    {
        self::bootKernel();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testRegisterForm(): void
    {
        $this->removeTestUserIfExist();
        $client = static::createClient();
        $client->request('GET', '/register');
        $client->submitForm('Submit', [
            'register[email]' => self::EMAIL,
            'register[password][first]' => self::PASSWORD,
            'register[password][second]' => self::PASSWORD,
        ]);
        if ($this->isValidateEmailRequired()) {
            $this->assertEmailCount(1);
            $email = $this->getMailerMessage();
            $this->assertEmailHeaderSame($email, 'To', self::EMAIL);
            $this->assertEmailTextBodyContains($email, 'Confirm registration');
        }
        $this->assertResponseRedirects('/login');
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function removeTestUserIfExist(): void
    {
        /** @var EntityManager $manager */
        $manager = self::$container->get('doctrine.orm.entity_manager');
        /** @var User|null $user */
        $user = $manager->getRepository(User::class)->findOneBy(['email' => self::EMAIL]);
        if (null !== $user) {
            $manager->remove($user);
            $manager->flush();
        }
    }

    private function isValidateEmailRequired(): bool
    {
        return self::$container->getParameter('email.validate');
    }
}
