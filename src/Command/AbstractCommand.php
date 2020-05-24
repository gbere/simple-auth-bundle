<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Command;

use Doctrine\ORM\EntityManagerInterface;
use Gbere\SimpleAuth\Entity\Role;
use Gbere\SimpleAuth\Model\UserInterface;
use Gbere\SimpleAuth\Repository\AdminUserRepository;
use Gbere\SimpleAuth\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

abstract class AbstractCommand extends Command implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;

    /** @var UserRepository */
    protected $userRepository;
    /** @var AdminUserRepository */
    protected $adminUserRepository;
    /** @var ValidatorInterface */
    protected $validator;

    public function __construct(
        UserRepository $userRepository,
        AdminUserRepository $adminUserRepository,
        ValidatorInterface $validator,
        string $name = null
    ) {
        $this->userRepository = $userRepository;
        $this->adminUserRepository = $adminUserRepository;
        $this->validator = $validator;

        parent::__construct($name);
    }

    protected function findUserByEmail(string $email): ?UserInterface
    {
        return $this->userRepository->findOneBy(['email' => $email]);
    }

    protected function findRoleByName(string $name): ?Role
    {
        return $this->getEntityManager()->getRepository(Role::class)->findOneBy(['name' => $name]);
    }

    /**
     * @return Role[]
     */
    protected function findAllRoles()
    {
        return $this->getEntityManager()->getRepository(Role::class)->findAll();
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->container->get(__METHOD__);
    }

    protected function getParameterBag(): ParameterBagInterface
    {
        return $this->container->get(__METHOD__);
    }

    protected function isTestEnv(): bool
    {
        return 'test' === $this->getParameterBag()->get('kernel.environment');
    }
}
