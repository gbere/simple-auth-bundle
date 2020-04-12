<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Command;

use Gbere\SimpleAuth\Command\Exception\InvalidRolePatternException;
use Gbere\SimpleAuth\Entity\Role;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

final class GbRoleAddCommand extends AbstractCommand
{
    private const ROLE_ALLOWED_PATTERN = '/^ROLE_[A-Z_]*$/';
    private const QUESTION_MAX_ATTEMPTS = 3;

    protected static $defaultName = 'gb:role:add';

    protected function configure(): void
    {
        $this
            ->setDescription('Add a new role')
            ->addArgument('role', InputArgument::OPTIONAL)
        ;
    }

    /**
     * @throws InvalidRolePatternException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');
        $roleName = $input->getArgument('role');

        if (null === $roleName) {
            $question = new Question('Enter a role name. Example ROLE_USER: ');
            $question->setValidator(function ($answer) {
                if (null === $answer) {
                    throw new \Exception('The role name cannot be empty');
                }
                if (false === $this->isValidRoleName($answer)) {
                    throw new InvalidRolePatternException(self::ROLE_ALLOWED_PATTERN);
                }

                return $answer;
            });
            $question->setMaxAttempts(self::QUESTION_MAX_ATTEMPTS);
            if ($this->isTestEnv()) {
                $question->setMaxAttempts(1);
            }
            $roleName = $helper->ask($input, $output, $question);
        }

        if (false === $this->isValidRoleName($roleName)) {
            throw new InvalidRolePatternException(self::ROLE_ALLOWED_PATTERN);
        }

        /** @var Role|null $role */
        $role = $this->findRoleByName($roleName);
        if (null !== $role) {
            $io->error(sprintf('The role name %s already exist', $roleName));

            return 1;
        }

        $role = (new Role())
            ->setName($roleName)
            ->setDescription($this->transformRoleNameToRoleDescription($roleName))
        ;
        $this->getEntityManager()->persist($role);
        $this->getEntityManager()->flush();

        $io->success(sprintf('The role %s was added successfully', $roleName));

        return 0;
    }

    private function isValidRoleName(string $roleName): bool
    {
        return (bool) preg_match(self::ROLE_ALLOWED_PATTERN, $roleName);
    }

    private function transformRoleNameToRoleDescription(string $roleName): string
    {
        return mb_strtolower($roleName.'.description');
    }
}
