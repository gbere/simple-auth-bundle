<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LogoutControllerTest extends WebTestCase
{
    public function testLogout(): void
    {
        $client = static::createClient();
        $client->request('GET', '/logout');
        $this->assertResponseRedirects('/login');
    }
}
