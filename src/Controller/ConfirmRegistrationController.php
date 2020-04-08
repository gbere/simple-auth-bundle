<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Gbere\SimpleAuth\Entity\User;
use Gbere\SimpleAuth\Security\LoginFormAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

final class ConfirmRegistrationController extends AbstractController
{
    /**
     * @Route("/register/confirmation/{token}", name="gbere_auth_confirm_registration")
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function __invoke(
        string $token,
        Request $request,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $authenticator
    ): Response {
        /** @var EntityManager $manager */
        $manager = $this->getDoctrine()->getManager();
        /** @var null|User $user */
        $user = $manager->getRepository(User::class)->findOneBy(['confirmationToken' => $token]);
        if (null === $user) {
            $this->addFlash('danger', 'The token is invalid');
        }
        $user->hasEnabled(true);
        $user->setConfirmationToken(null);
        $manager->persist($user);
        $manager->flush();
        $this->addFlash('success', 'The user has been successfully activated');

        // TODO: Auto login after validate?
        // return $guardHandler->authenticateUserAndHandleSuccess($user, $request, $authenticator, 'gbere_main_firewall');

        return $this->redirectToRoute('gbere_auth_login');
    }
}
