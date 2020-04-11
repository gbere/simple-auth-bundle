<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Tests\Controller;

use Exception;
use Gbere\SimpleAuth\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mime\Address;

class PasswordRequestControllerTest extends WebTestCase
{
    private const EMAIL = 'role-user@fixture.com';

    public function setUp(): void
    {
        self::bootKernel();
    }

    /**
     * @throws Exception
     */
    public function testPasswordRequestForm(): void
    {
        $client = static::createClient();
        $user = $this->findUserByEmail(self::EMAIL);
        $client->request('GET', 'password/request');
        $client->submitForm('Submit', [
            'form[email]' => $user->getEmail(),
        ]);
        $this->assertEmailCount(1);
        $email = $this->getMailerMessage();
        $this->assertEmailHeaderSame($email, 'To', (new Address($user->getEmail(), $user->getName()))->toString());
        $this->assertEmailTextBodyContains($email, 'Password request');
        $this->assertResponseRedirects('/login');
    }

    private function findUserByEmail(string $email): ?User
    {
        return self::$container->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['email' => $email])
        ;
    }
}
