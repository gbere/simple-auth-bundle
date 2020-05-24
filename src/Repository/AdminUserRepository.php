<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Repository;

use Gbere\SimpleAuth\Model\UserInterface;

/**
 * @method UserInterface|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserInterface|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserInterface[]    findAll()
 * @method UserInterface[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdminUserRepository extends UserRepository
{
}
