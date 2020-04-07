<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class GbUserAddCommandTest extends KernelTestCase
{
    private const COMMAND = 'gb:user:add';
    private const EMAIL = 'test@command.com';
    private const PASSWORD = 'password';

    public function testExecute(): void
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);
        $commandTester->setInputs([
            self::EMAIL,
            self::PASSWORD,
        ]);
        $commandTester->execute(['command' => $command->getName()]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString(self::EMAIL, $output);
    }
}
