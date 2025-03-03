<?php

namespace App\Controller\Authentication;

use App\Entity\Project\Project;
use App\Entity\Project\ProjectUser;
use App\Entity\User\User;
use App\Form\User\UserRegistrationForm;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class SignupController extends AbstractController
{
    public function __construct(
        private EmailVerifier $emailVerifier,
    ) { }

    #[Route('/auth/signup', name: 'app_auth_signup')]
    public function signup(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute('home'); // user is already logged in
        }

        if (!\boolval($_ENV['SIGNUP_ENABLED'])) {
            return $this->render('auth/signup_disabled.html.twig', [
                'navigationRoutes' => ['login', 'signup'],
            ]);
        }

        $user = new User();
        $form = $this->createForm(UserRegistrationForm::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $user
                ->setCreatedAt(new \DateTimeImmutable())
                ->setVerified(true); // @TODO: remove this line when implementing email verification
            $entityManager->persist($user);

            $project = (new Project())
                ->setName('My First Project')
                ->setOwner($user)
                ->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($project);
            $user->setSelectedProject($project); // set the default project for the user; saves clicks again

            $projectUser = (new ProjectUser())
                ->setProject($project)
                ->setUser($user)
                ->setCreatedAt(new \DateTime());
            $entityManager->persist($projectUser);

            $entityManager->flush();

            // generate a signed url and email it to the user
            // $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            //     (new TemplatedEmail())
            //         ->from(new Address('account@phiil.de', 'Account Management @ Phiil'))
            //         ->to($user->getEmail())
            //         ->subject('Please Confirm your Email')
            //         ->htmlTemplate('registration/confirmation_email.html.twig')
            // );

            return $this->redirectToRoute('home');
        }

        return $this->render('auth/signup.html.twig', [
            'navigationRoutes' => ['login', 'signup'],
            'form' => $form,
            'errors' => $form->getErrors(true, false),
        ]);
    }

    // #[Route('/verify/email', name: 'app_verify_email')]
    // public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    // {
    //     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

    //     // validate email confirmation link, sets User::isVerified=true and persists
    //     try {
    //         $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
    //     } catch (VerifyEmailExceptionInterface $exception) {
    //         $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

    //         return $this->redirectToRoute('app_auth_signup');
    //     }

    //     // @TODO Change the redirect on success and handle or remove the flash message in your templates
    //     $this->addFlash('success', 'Your email address has been verified.');

    //     return $this->redirectToRoute('app_auth_signup');
    // }
}
