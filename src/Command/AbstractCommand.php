<?php

declare(strict_types=1);

namespace Gbere\Security\Command;

use Doctrine\ORM\EntityManagerInterface;
use Gbere\Security\Entity\Role;
use Gbere\Security\Entity\User;
use Symfony\Component\Console\Command\Command;

abstract class AbstractCommand extends Command
{
    /** @var EntityManagerInterface */
    protected $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
        parent::__construct();
    }

    protected function findUserByEmail(string $email): ?User
    {
        return $this->manager->getRepository(User::class)->findOneBy(['email' => $email]);
    }

    protected function findRoleByName(string $name): ?Role
    {
        return $this->manager->getRepository(Role::class)->findOneBy(['name' => $name]);
    }

    /**
     * @return Role[]
     */
    protected function findAllRoles()
    {
        return $this->manager->getRepository(Role::class)->findAll();
    }
}
