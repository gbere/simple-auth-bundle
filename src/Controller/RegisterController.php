<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Controller;

use Exception;
use Gbere\SimpleAuth\Entity\User;
use Gbere\SimpleAuth\Form\RegisterType;
use Gbere\SimpleAuth\Service\Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

final class RegisterController extends AbstractController
{
    /**
     * @Route("/register", name="gbere_auth_register")
     *
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function __invoke(Request $request, UserPasswordEncoderInterface $passwordEncoder, Mailer $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();
            $manager = $this->getDoctrine()->getManager();
            $user->setPassword($passwordEncoder->encodePassword($user, $user->getPassword() ?? ''));
            if (false === $this->getParameter('email.validate')) {
                $user->hasEnabled(true);
            } else {
                $user->generateToken();
                $mailer->sendConfirmRegistrationMessage($user);
                $this->addFlash('success', sprintf('An email was sent to %s email', $user->getEmail()));
            }
            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('gbere_auth_login');
        }

        return $this->render('frontend/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
