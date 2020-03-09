<?php

namespace Gbere\Security\Tests;

use Doctrine\ORM\EntityManager;
use Gbere\Security\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class AccessControlTest extends WebTestCase
{
    private const FIREWALL_NAME = 'gbere_security_main_firewall';
    private const FIREWALL_PROVIDER = 'gbere_security_main_provider';

    /** @var KernelBrowser */
    private $client = null;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testAnonymousUser()
    {
        $this->client->request('GET', '/gbere-security-test-user');
        $this->assertResponseRedirects();
    }

    public function testRoleUser()
    {
        $this->logIn('role-user@fixture.com');
        $this->client->request('GET', '/gbere-security-test-user');
        $this->assertResponseIsSuccessful();
    }

    private function logIn(string $email): void
    {
        $user = $this->getUserByEmail($email);
        $token = new UsernamePasswordToken(
            $user->getUsername(),
            $user->getPassword(),
            self::FIREWALL_PROVIDER,
            $user->getRoles()
        );
        /** @var Session $session */
        $session = self::$container->get('session');
        $session->set('_security_'.self::FIREWALL_NAME, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    private function getUserByEmail(string $email): User
    {
        /** @var EntityManager $manager */
        $manager = self::$container->get('doctrine.orm.entity_manager');
        /** @var User|null $user */
        $user = $manager->getRepository(User::class)->findOneBy(['email' => $email]);
        if (null === $user) {
            throw new CustomUserMessageAuthenticationException("The fixtures weren't loaded");
        }

        return $user;
    }
}