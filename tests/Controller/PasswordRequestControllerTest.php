<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Tests\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

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
        $client->request('GET', 'password/request');
        $client->submitForm('Submit', [
            'form[email]' => self::EMAIL,
        ]);
        $this->assertEmailCount(1);
        $email = $this->getMailerMessage();
        $this->assertEmailHeaderSame($email, 'To', self::EMAIL);
        $this->assertEmailTextBodyContains($email, 'Password request');
        $this->assertResponseRedirects('/login');
    }
}
