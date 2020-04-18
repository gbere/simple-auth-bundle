<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="gbere_auth_user")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string", length=4)
 * @ORM\DiscriminatorMap({
 *     "user"="User",
 *     "adm"="AdminUser",
 * })
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("email")
 */
abstract class UserBase implements UserInterface
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=180, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(type="string", length=80)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @var Collection
     * @ORM\ManyToMany(targetEntity="Gbere\SimpleAuth\Entity\Role", inversedBy="users")
     * @ORM\JoinTable(name="gbere_auth_user_role")
     */
    private $roles;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $password;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true, unique=true)
     */
    private $confirmationToken;

    /**
     * @var DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $passwordRequestAt;

    public function __construct()
    {
        $this->enabled = false;
        $this->roles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return $this->email;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array<Role|string>
     */
    public function getRoles(): array
    {
        $roles = [];
        /** @var Role $role */
        foreach ($this->roles as $role) {
            $roles[] = $role->getName();
        }

        if (AdminUser::class === static::class) {
            $roles[] = 'ROLE_ADMIN';
        }

        return array_unique($roles);
    }

    public function getRolesCollection(): Collection
    {
        return $this->roles;
    }

    public function addRoleEntity(Role $role): self
    {
        if (false === $this->roles->contains($role)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRoleEntity(Role $role): self
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
        }

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new DateTime();
    }

    /**
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function hasEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): self
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function generateToken(): self
    {
        $this->setConfirmationToken(bin2hex(random_bytes(50)));

        return $this;
    }

    public function getPasswordRequestAt(): ?DateTime
    {
        return $this->passwordRequestAt;
    }

    public function setPasswordRequestAt(?DateTime $passwordRequestAt): self
    {
        $this->passwordRequestAt = $passwordRequestAt;

        return $this;
    }
}
