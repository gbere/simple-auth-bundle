<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Controller;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Gbere\SimpleAuth\Repository\UserRepository;
use Gbere\SimpleAuth\Service\Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;

final class ResetPasswordController extends AbstractController
{
    /**
     * @Route("/password/reset/{token}", name="gbere_auth_password_reset")
     *
     * @throws Exception
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransportExceptionInterface
     */
    public function __invoke(string $token, Request $request, UserRepository $userRepository, Mailer $mailer): Response
    {
        $user = $userRepository->findOneBy(['confirmationToken' => $token]);
        if (null === $user) {
            $this->addFlash('warning', 'The token is invalid');

            return $this->redirectToRoute('gbere_auth_login');
        }

        $form = $this->createFormBuilder()->add('plainPassword', PasswordType::class, [
            'label' => 'New password',
        ])->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($userRepository->encodePassword($form->get('plainPassword')->getData()));
            $user->hasEnabled(true);
            $user->setConfirmationToken(null);
            $userRepository->persistAndFlush($user);
            $this->addFlash('success', 'The password was updated');
            $mailer->sendPasswordResetNotificationMessage($user);

            return $this->redirectToRoute('gbere_auth_login');
        }

        return $this->render('@GbereSimpleAuth/frontend/reset-password.html.twig', ['form' => $form->createView()]);
    }
}
