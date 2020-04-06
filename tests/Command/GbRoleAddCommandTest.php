<?php

declare(strict_types=1);

namespace Gbere\Security\Tests\Command;

use Gbere\Security\Command\Exception\InvalidRolePatternException;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class GbRoleAddCommandTest extends KernelTestCase
{
    private const COMMAND = 'gb:role:add';
    private const ROLE_NAME = 'ROLE_TEST';
    private const INVALID_ROLE_NAME = 'invalid-role-name';

    public function testExecute(): void
    {
        $output = $this->runCommandWithRoleName(self::ROLE_NAME);
        $this->assertStringContainsString(self::ROLE_NAME, $output);
        $this->expectException(InvalidRolePatternException::class);
        $this->runCommandWithRoleName(self::INVALID_ROLE_NAME);
    }

    private function runCommandWithRoleName(string $roleName): string
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);
        $commandTester->setInputs([$roleName]);
        $commandTester->execute(['command' => $command->getName()]);

        return $commandTester->getDisplay();
    }
}
