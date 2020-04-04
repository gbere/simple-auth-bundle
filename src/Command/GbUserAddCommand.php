<?php

declare(strict_types=1);

namespace Gbere\Security\Command;

use Doctrine\ORM\EntityManagerInterface;
use Gbere\Security\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GbUserAddCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'gb:user:add';
    /** @var ValidatorInterface */
    private $validator;
    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;
    /** @var EntityManagerInterface */
    private $manager;
    /** @var ParameterBagInterface */
    private $params;

    public function __construct(ValidatorInterface $validator, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $manager, ParameterBagInterface $params)
    {
        $this->validator = $validator;
        $this->passwordEncoder = $passwordEncoder;
        $this->manager = $manager;
        $this->params = $params;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Register a new user')
            ->addArgument('email', InputArgument::OPTIONAL)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');
        $email = $input->getArgument('email');
        $isEmailOk = false;

        while (false === $isEmailOk) {
            if (null !== $email) {
                $emailConstraint = new Assert\Email();
                $errors = $this->validator->validate($email, $emailConstraint);
                if (0 < \count($errors)) {
                    $io->error($errors[0]->getMessage());

                    return 1;
                }
                if (null !== $this->manager->getRepository(User::class)->findOneBy(['email' => $email])) {
                    $io->error(sprintf('The email %s is already registered', $email));

                    return 1;
                }
                $isEmailOk = true;
            } else {
                $question = new Question('Please, enter the email address of the new user: ');
                $answer = $helper->ask($input, $output, $question);
                if (null === $answer) {
                    $io->warning('The email is required');
                }
                $email = $answer;
            }
        }

        $question = new Question('Please, enter a password: ');
        if ('test' !== $this->params->get('kernel.environment')) {
            $question->setHidden(true);
        }
        $password = $helper->ask($input, $output, $question);
        if (null === $password) {
            $io->warning('The password is required');

            return 1;
        }

        $user = (new User())->setEmail($email);
        $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
        $this->manager->persist($user);
        $this->manager->flush();

        $io->success(sprintf('The new user with email %s, was successfully created', $user->getEmail()));

        return 0;
    }
}
