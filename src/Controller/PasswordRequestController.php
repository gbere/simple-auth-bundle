<?php

declare(strict_types=1);

namespace Gbere\SimpleAuth\Controller;

use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Gbere\SimpleAuth\Entity\User;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

final class PasswordRequestController extends AbstractController
{
    /**
     * @Route("/login/password/request", name="gbere_auth_password_request")
     *
     * @throws Exception
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransportExceptionInterface
     */
    public function __invoke(Request $request, MailerInterface $mailer): Response
    {
        $builder = $this->createFormBuilder()->add('email', EmailType::class);
        $form = $builder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            /** @var EntityManager $manager */
            $manager = $this->getDoctrine()->getManager();
            /** @var User|null $user */
            $user = $manager->getRepository(User::class)->findOneBy(['email' => $email]);
            if (null !== $user) {
                $user->generateToken();
                $user->setPasswordRequestAt(new DateTime());
                $manager->persist($user);
                $manager->flush();
                $mailer->send((new NotificationEmail())
                    ->from($this->getParameter('email.sender'))
                    ->to($user->getEmail())
                    ->subject('Password request')
                    ->htmlTemplate('emails/password-reset.html.twig')
                    ->context(['token' => $user->getConfirmationToken()])
                );
                $this->addFlash('info', sprintf('An email was sent to %s to restore the password', $email));

                return $this->redirectToRoute('gbere_auth_login');
            }
            $this->addFlash('warning', sprintf('The email %s isn\'t registered', $email));
        }

        return $this->render('frontend/password-request.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
