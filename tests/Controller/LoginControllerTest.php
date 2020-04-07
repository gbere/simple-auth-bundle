<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginControllerTest extends WebTestCase
{
    public function testLoginForm(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        $client->submitForm('login', [
            'email' => 'role-user@fixture.com',
            'password' => 'role-user',
        ]);
        $this->assertResponseRedirects('/');
    }
}
