<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Tests;

use Doctrine\ORM\EntityManager;
use Gbere\SimpleAuth\Entity\AdminUser;
use Gbere\SimpleAuth\Entity\User;
use Gbere\SimpleAuth\Security\Constant;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

final class AccessControlTest extends WebTestCase
{
    /** @var KernelBrowser */
    private $client = null;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testAnonymousUser(): void
    {
        $this->client->request('GET', $this->getUriForTestByRole('ROLE_USER'));
        $this->assertResponseRedirects();
        $this->client->request('GET', $this->getUriForTestByRole('ROLE_ADMIN'));
        $this->assertResponseRedirects();
        $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
    }

    public function testRoleUser(): void
    {
        $this->logIn('role-user@fixture.com');
        $this->client->request('GET', $this->getUriForTestByRole('ROLE_USER'));
        $this->assertResponseIsSuccessful();
        $this->client->request('GET', $this->getUriForTestByRole('ROLE_ADMIN'));
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testRoleAdmin(): void
    {
        $this->logIn('role-admin@fixture.com');
        $this->client->request('GET', $this->getUriForTestByRole('ROLE_ADMIN'));
        $this->assertResponseIsSuccessful();
    }

    public function testAdminUser(): void
    {
        $this->logIn('admin-user@fixture.com', true);
        $this->client->request('GET', $this->getUriForTestByRole('ROLE_ADMIN'));
        $this->assertResponseIsSuccessful();
    }

    private function getUriForTestByRole(string $role): string
    {
        foreach (Constant::TESTING_ROUTES as $route) {
            if ($role === $route['role']) {
                return $this->routePathToUri($route['path']);
            }
        }

        throw new \LogicException('There is no route with that role');
    }

    private function routePathToUri(string $path): string
    {
        return trim($path, '^');
    }

    private function logIn(string $email, bool $isAdminUser = false): void
    {
        $user = $this->getUserByEmail($email, $isAdminUser);
        $token = new UsernamePasswordToken(
            $user->getUsername(),
            $user->getPassword(),
            Constant::PROVIDER_NAME,
            $user->getRoles()
        );
        /** @var Session<Session> $session */
        $session = self::$container->get('session');
        $session->set('_security_'.Constant::FIREWALL_NAME, serialize($token));
        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    /**
     * @return User|AdminUser
     */
    private function getUserByEmail(string $email, bool $isAdminUser = false)
    {
        /** @var EntityManager $manager */
        $manager = self::$container->get('doctrine.orm.entity_manager');
        if ($isAdminUser) {
            /** @var AdminUser|null $user */
            $user = $manager->getRepository(AdminUser::class)->findOneBy(['email' => $email]);
        } else {
            /** @var User|null $user */
            $user = $manager->getRepository(User::class)->findOneBy(['email' => $email]);
        }
        if (null === $user) {
            throw new CustomUserMessageAuthenticationException(sprintf('The email "%s" was not found. Make sure the fixtures were loaded', $email));
        }

        return $user;
    }
}
