<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Gbere\SimpleAuth\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mime\Address;

class PasswordResetControllerTest extends WebTestCase
{
    private const EMAIL = 'role-user@fixture.com';
    private const PASSWORD = 'role-user';

    /** @var User|null */
    private $user;
    /** @var EntityManager|null */
    private $manager;

    public function setUp(): void
    {
        self::bootKernel();
        $this->manager = self::$container->get('doctrine.orm.entity_manager');
    }

    /**
     * @throws Exception
     */
    public function testPasswordResetForm(): void
    {
        $this->loadUser();
        $this->disableUser();
        $client = static::createClient();
        $client->request('GET', $this->generatePasswordResetRoute());
        $client->submitForm('Password reset', [
            'form[plainPassword]' => self::PASSWORD,
        ]);
        $this->assertEmailCount(1);
        $email = $this->getMailerMessage();
        $this->assertEmailHeaderSame($email, 'To', (new Address($this->user->getEmail(), $this->user->getName()))->toString());
        $this->assertEmailTextBodyContains($email, 'Password reset notification');
        $this->assertResponseRedirects('/login');
        $this->loadUser();
        $this->assertTrue($this->user->isEnabled());
    }

    private function loadUser(): void
    {
        $this->user = $this->manager->getRepository(User::class)->findOneBy(['email' => self::EMAIL]);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function disableUser(): void
    {
        $this->user->hasEnabled(false);
        $this->manager->persist($this->user);
        $this->manager->flush();
    }

    private function generatePasswordResetRoute(): string
    {
        return self::$container->get('router')->generate(
            'gbere_auth_password_reset',
            ['token' => $this->user->getConfirmationToken()]
        );
    }
}
