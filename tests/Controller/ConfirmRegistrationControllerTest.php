<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Gbere\SimpleAuth\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mime\Address;

class ConfirmRegistrationControllerTest extends WebTestCase
{
    private const EMAIL = 'confirm-my-registration@test.com';
    private const NAME = 'Test';

    /** @var KernelBrowser */
    private $client = null;
    /** @var User|null */
    private $user;

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * @throws Exception
     */
    public function testConfirmRegistrationForm(): void
    {
        $this->removeTestUserIfExist();
        $this->createUserToConfirmRegistration();
        $this->client->request('GET', $this->generateConfirmRegistrationRoute());
        $this->assertEmailCount(1);
        $email = $this->getMailerMessage();
        $this->assertEmailHeaderSame($email, 'To', (new Address(self::EMAIL, self::NAME))->toString());
        $this->assertEmailTextBodyContains($email, 'Welcome');
        $this->assertResponseRedirects('/login');
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function removeTestUserIfExist(): void
    {
        /** @var EntityManager $manager */
        $manager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        /** @var User|null $user */
        $user = $manager->getRepository(User::class)->findOneBy(['email' => self::EMAIL]);
        if (null !== $user) {
            $manager->remove($user);
            $manager->flush();
        }
    }

    /**
     * @throws Exception
     */
    private function createUserToConfirmRegistration(): void
    {
        $this->user = (new User())
            ->setEmail(self::EMAIL)
            ->setPassword('')
            ->setName(self::NAME)
            ->generateToken()
            ->hasEnabled(false)
        ;
        /** @var EntityManager $manager */
        $manager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $manager->persist($this->user);
        $manager->flush();
    }

    private function generateConfirmRegistrationRoute(): string
    {
        return $this->client->getContainer()->get('router')->generate(
            'gbere_auth_confirm_registration',
            ['token' => $this->user->getConfirmationToken()]
        );
    }
}
