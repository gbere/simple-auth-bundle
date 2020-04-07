<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class GbUserPromoteCommandTest extends KernelTestCase
{
    private const COMMAND = 'gb:user:promote';
    private const EMAIL = 'role-user@fixture.com';
    private const ROLE = 'ROLE_USER';

    public function testExecute(): void
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);
        $commandTester->setInputs([
            self::EMAIL,
            self::ROLE,
        ]);
        $commandTester->execute(['command' => $command->getName()]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString(self::EMAIL, $output);
        $this->assertStringContainsString(self::ROLE, $output);
    }
}
