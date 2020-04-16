<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Gbere\SimpleAuth\Repository\AdminRepository")
 */
class AdminUser extends UserBase
{
}
