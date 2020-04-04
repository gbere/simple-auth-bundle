<?php

declare(strict_types=1);

namespace Gbere\Security\Command;

use Doctrine\ORM\EntityManagerInterface;
use Gbere\Security\Entity\Role;
use Gbere\Security\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class GbUserPromoteCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'gb:user:promote';

    /** @var EntityManagerInterface */
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Promote a user')
            ->addArgument('email', InputArgument::OPTIONAL)
            ->addArgument('role', InputArgument::OPTIONAL)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');
        $email = $input->getArgument('email');
        $roleName = $input->getArgument('role');

        /** @var Role[] $allRoles */
        $allRoles = $this->manager->getRepository(Role::class)->findAll();
        if (0 === \count($allRoles)) {
            $io->error('There is no role to select. Please, create some role first.');

            return 1;
        }

        if (null === $email) {
            $question = new Question('Please, enter the email address of the user: ');
            $answer = $helper->ask($input, $output, $question);
            if (null === $answer) {
                $io->error('The email is required');

                return 1;
            }
        }
        $user = $this->findUserByEmail($email);
        if (null === $user) {
            $io->error(sprintf('The user with the email "%s" doesnt exist', $email));

            return 1;
        }

        $role = null;
        if (null !== $roleName) {
            $role = $this->findRoleByName($roleName);
            if (null === $role) {
                $io->warning(sprintf('The role "%s" doesnt exist. Please, select a valid role', $roleName));
            }
        }
        if (null === $role) {
            $question = new ChoiceQuestion('Select a role', $allRoles);
            $question->setErrorMessage('The role %s is invalid.');
            $roleName = $helper->ask($input, $output, $question);
            $role = $this->findRoleByName($roleName);
        }
        $user->addRoleEntity($role);
        $this->manager->persist($user);
        $this->manager->flush();

        $io->success(sprintf('The role %s was added to the user %s', $roleName, $email));

        return 0;
    }

    private function findUserByEmail(string $email): ?User
    {
        return $this->manager->getRepository(User::class)->findOneBy(['email' => $email]);
    }

    private function findRoleByName(string $name): ?Role
    {
        return $this->manager->getRepository(Role::class)->findOneBy(['name' => $name]);
    }
}
