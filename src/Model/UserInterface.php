<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Model;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Gbere\SimpleAuth\Entity\Role;
use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;

interface UserInterface extends SecurityUserInterface
{
    public function getId(): ?int;

    public function getEmail(): ?string;

    public function setEmail(string $email);

    public function getUsername(): string;

    public function getName(): ?string;

    public function setName(string $name);

    public function getRolesCollection(): Collection;

    public function addRoleEntity(Role $role);

    public function removeRoleEntity(Role $role);

    public function setPassword(string $password);

    public function getCreatedAt(): ?DateTime;

    public function setCreatedAt(DateTime $createdAt);

    public function isEnabled(): ?bool;

    public function hasEnabled(bool $enabled);

    public function getConfirmationToken(): ?string;

    public function setConfirmationToken(?string $confirmationToken);

    public function generateToken();

    public function getPasswordRequestAt(): ?DateTime;

    public function setPasswordRequestAt(?DateTime $passwordRequestAt);
}
