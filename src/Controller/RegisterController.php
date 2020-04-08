<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Controller;

use \Exception;
use Gbere\SimpleAuth\Entity\User;
use Gbere\SimpleAuth\Form\RegisterType;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
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
    public function __invoke(Request $request, UserPasswordEncoderInterface $passwordEncoder, MailerInterface $mailer): Response
    {
        $newUser = new User();
        $form = $this->createForm(RegisterType::class, $newUser);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $newUser */
            $newUser = $form->getData();
            $manager = $this->getDoctrine()->getManager();
            $newUser->setPassword($passwordEncoder->encodePassword($newUser, $newUser->getPassword() ?? ''));
            if (false === $this->getParameter('email.validate')) {
                $newUser->hasEnabled(true);
            } else {
                $newUser->generateToken();
                $mailer->send((new NotificationEmail())
                    ->from($this->getParameter('email.sender'))
                    ->to($newUser->getEmail())
                    ->subject('Confirm registration')
                    ->htmlTemplate('emails/confirm-registration.html.twig')
                    ->context(['token' => $newUser->getConfirmationToken()])
                );
                $this->addFlash('success', sprintf('An email was sent to %s email', $newUser->getEmail()));
            }
            $manager->persist($newUser);
            $manager->flush();

            return $this->redirectToRoute('gbere_auth_login');
        }

        return $this->render('frontend/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
