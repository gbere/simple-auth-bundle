<?php

declare(strict_types=1);

namespace Gbere\Security\Command;

use Doctrine\ORM\EntityManagerInterface;
use Gbere\Security\Entity\Role;
use Gbere\Security\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;

abstract class AbstractCommand extends Command implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;

    protected function findUserByEmail(string $email): ?User
    {
        return $this->getEntityManager()->getRepository(User::class)->findOneBy(['email' => $email]);
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
}
