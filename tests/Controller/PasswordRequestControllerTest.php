<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Tests\Controller;

use Exception;
use Gbere\SimpleAuth\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mime\Address;

class PasswordRequestControllerTest extends WebTestCase
{
    private const EMAIL = 'role-user@fixture.com';

    /** @var KernelBrowser */
    private $client = null;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * @throws Exception
     */
    public function testPasswordRequestForm(): void
    {
        $user = $this->findUserByEmail(self::EMAIL);
        $this->client->request('GET', 'password/request');
        $this->client->submitForm('Password request', [
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
        return $this->client->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository(User::class)
            ->findOneBy(['email' => $email])
        ;
    }
}
