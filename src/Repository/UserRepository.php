<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Gbere\SimpleAuth\Model\UserInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method UserInterface|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserInterface|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserInterface[]    findAll()
 * @method UserInterface[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    /** @var UserPasswordEncoderInterface */
    protected $passwordEncoder;

    public function __construct(ManagerRegistry $registry, UserInterface $user, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;

        parent::__construct($registry, \get_class($user));
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function upgradePassword($user, string $newEncodedPassword): void
    {
        if (!$user instanceof UserInterface) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function createUser(): UserInterface
    {
        return new $this->_entityName();
    }

    public function encodePassword(string $password): string
    {
        return $this->passwordEncoder->encodePassword(new $this->_entityName(), $password);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function persistAndFlush(UserInterface $user): void
    {
        $this->_em->persist($user);
        $this->_em->flush();
    }
}
