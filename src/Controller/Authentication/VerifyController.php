<?php

namespace App\Controller\Authentication;

use App\Controller\Controller;
use App\Entity\Project\ProjectUser;
use App\Entity\User\User;
use App\Entity\User\UserInvitation;
use App\Form\User\UserInvitationVerifyForm;
use App\Repository\UserInvitationRepository;
use App\Repository\UserRepository;
use App\Service\Helper\ApiControllerHelperService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/auth/verify')]
class VerifyController extends Controller
{
    public function __construct(
        ApiControllerHelperService $apiControllerHelperService,
        private EntityManagerInterface $em,
        private UserInvitationRepository $userInvitationRepository,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private Security $security,
    ) {
        parent::__construct($apiControllerHelperService);
    }

    #[Route(path: '/{code}', name: 'app_auth_verify')]
    public function verify(Request $request, string $code): Response
    {
        if (null !== $this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(UserInvitationVerifyForm::class, options: ['code' => $code]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ?UserInvitation */
            $userInvitation = $this->userInvitationRepository->findOneBy(['code' => $form->get('code')->getData(), 'email' => $form->get('email')->getData()]);

            if (null === $userInvitation) {
                $this->addFlash('danger', 'Invalid invitation code or email address.');

                return $this->redirectToRoute('app_auth_verify', ['code' => $code]);
            }

            if (null !== $user = $this->userRepository->findOneBy(['email' => $form->get('email')->getData()])) {
                $this->addFlash('danger', 'User with this email already exists. Please login and accept the invitation.');

                return $this->redirectToRoute('app_auth_verify', ['code' => $code]);
            }

            $user = (new User())
                ->setEmail($form->get('email')->getData())
                ->setSelectedProject($userInvitation->getProject())
                ->setCreatedAt(new \DateTimeImmutable())
                ->setVerified(true);
            $user->setPassword($this->passwordHasher->hashPassword($user, $form->get('password')->getData()));
            $this->em->persist($user);

            if (null !== $userInvitation->getProject()) {
                // the password can only be anonymous, i.e. without any particular project context.
                // only if the project is given we create a project user when verifying the user.
                $projectUser = (new ProjectUser())
                    ->setUser($user)
                    ->setProject($userInvitation->getProject())
                    ->setCreatedAt(new \DateTime());
                $this->em->persist($projectUser);
            }

            // now remove the invitation to prevent re-use
            $this->em->remove($userInvitation);
            $this->em->flush();

            // eventually, log in the user.
            $this->security->login($user);

            // authenticated the user, now redirect to the home page
            return $this->redirectToRoute('home');
        }

        return $this->render('auth/verify.html.twig', [
            'navigationRoutes' => [],
            'form' => $form->createView(),
        ]);
    }
}
