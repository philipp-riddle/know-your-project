<?php

namespace App\Controller\Authentication;

use App\Controller\Controller;
use App\Entity\Project\Project;
use App\Entity\Project\ProjectUser;
use App\Entity\User\User;
use App\Form\User\UserRegistrationForm;
use App\Security\EmailVerifier;
use App\Service\Helper\ApiControllerHelperService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class SignupController extends Controller
{
    public function __construct(
        ApiControllerHelperService $apiControllerHelperService,
        private EmailVerifier $emailVerifier,
    ) {
        parent::__construct($apiControllerHelperService);
    }

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

            return $this->redirectToRoute('home');
        }

        return $this->render('auth/signup.html.twig', [
            'navigationRoutes' => ['login', 'signup'],
            'form' => $form,
            'errors' => $form->getErrors(true, false),
        ]);
    }
}
