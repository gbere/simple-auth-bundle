<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Gbere\SimpleAuth\Entity\User;
use Gbere\SimpleAuth\Service\Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

final class PasswordResetController extends AbstractController
{
    /**
     * @Route("/login/password/reset/{token}", name="gbere_auth_password_reset")
     *
     * @throws Exception
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransportExceptionInterface
     */
    public function __invoke(string $token, Request $request, UserPasswordEncoderInterface $passwordEncoder, Mailer $mailer): Response
    {
        /** @var EntityManager $manager */
        $manager = $this->getDoctrine()->getManager();
        /** @var User|null $user */
        $user = $manager->getRepository(User::class)->findOneBy(['confirmationToken' => $token]);
        if (null === $user) {
            $this->addFlash('warning', 'The token is invalid');

            return $this->redirectToRoute('gbere_auth_login');
        }

        $form = $this->createFormBuilder()->add('plainPassword', PasswordType::class)->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($passwordEncoder->encodePassword($user, $form->get('plainPassword')->getData()));
            $user->hasEnabled(true);
            $user->setConfirmationToken(null);
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', 'The password was updated');
            $mailer->sendPasswordResetNotificationMessage($user);

            return $this->redirectToRoute('gbere_auth_login');
        }

        return $this->render('frontend/password-reset.html.twig', ['form' => $form->createView()]);
    }
}
