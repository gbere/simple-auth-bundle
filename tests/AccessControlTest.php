<?php

declare(strict_types=1);

namespace Gbere\Security\Tests;

use Doctrine\ORM\EntityManager;
use Gbere\Security\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

final class AccessControlTest extends WebTestCase
{
    private const FIREWALL_NAME = 'gbere_security_main_firewall';
    private const PROVIDER_NAME = 'gbere_security_main_provider';

    /** @var KernelBrowser */
    private $client = null;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testAnonymousUser()
    {
        $this->client->request('GET', '/gbere-security-test-role-user');
        $this->assertResponseRedirects();
        $this->client->request('GET', '/gbere-security-test-role-admin');
        $this->assertResponseRedirects();
        $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
    }

    public function testRoleUser()
    {
        $this->logIn('role-user@fixture.com');
        $this->client->request('GET', '/gbere-security-test-role-user');
        $this->assertResponseIsSuccessful();
        $this->client->request('GET', '/gbere-security-test-role-admin');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testRoleAdmin()
    {
        $this->logIn('role-admin@fixture.com');
        $this->client->request('GET', '/gbere-security-test-role-admin');
        $this->assertResponseIsSuccessful();
    }

    private function logIn(string $email): void
    {
        $user = $this->getUserByEmail($email);
        $token = new UsernamePasswordToken(
            $user->getUsername(),
            $user->getPassword(),
            self::PROVIDER_NAME,
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
            throw new CustomUserMessageAuthenticationException(sprintf('The email "%s" was not found. Make sure the fixtures were loaded', $email));
        }

        return $user;
    }
}
