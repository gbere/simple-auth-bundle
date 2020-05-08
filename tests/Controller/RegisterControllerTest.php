<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Gbere\SimpleAuth\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mime\Address;

class RegisterControllerTest extends WebTestCase
{
    private const EMAIL = 'register-user@test.com';
    private const NAME = 'Test';
    private const PASSWORD = 'patata';

    /** @var KernelBrowser */
    private $client = null;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function testRegisterForm(): void
    {
        $this->removeTestUserIfExist();
        $this->client->request('GET', '/register');
        $this->client->submitForm('Register', [
            'register[email]' => self::EMAIL,
            'register[name]' => self::NAME,
            'register[password][first]' => self::PASSWORD,
            'register[password][second]' => self::PASSWORD,
        ]);
        if ($this->isValidateEmailRequired()) {
            $this->assertEmailCount(1);
            $email = $this->getMailerMessage();
            $this->assertEmailHeaderSame($email, 'To', (new Address(self::EMAIL, self::NAME))->toString());
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
        $manager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        /** @var User|null $user */
        $user = $manager->getRepository(User::class)->findOneBy(['email' => self::EMAIL]);
        if (null !== $user) {
            $manager->remove($user);
            $manager->flush();
        }
    }

    private function isValidateEmailRequired(): bool
    {
        return self::$container->getParameter('simple_auth_confirm_registration');
    }
}
