<?php

namespace App\Controller\Authentication;

use App\Entity\Project\ProjectUser;
use App\Entity\User\User;
use App\Entity\User\UserInvitation;
use App\Form\User\UserInvitationVerifyForm;
use App\Repository\UserInvitationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/auth/verify')]
class VerifyController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserInvitationRepository $userInvitationRepository,
        private UserRepository $userRepository,
    ) { }

    #[Route(path: '/{code}', name: 'app_auth_verify')]
    public function verify(Request $request, UserPasswordHasherInterface $passwordHasher, string $code): Response
    {
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
            $user->setPassword($passwordHasher->hashPassword($user, $form->get('password')->getData()));
            $this->em->persist($user);

            $projectUser = (new ProjectUser())
                ->setUser($user)
                ->setProject($userInvitation->getProject())
                ->setCreatedAt(new \DateTime());
            $this->em->persist($projectUser);

            // now remove the invitation to prevent re-use
            $this->em->remove($userInvitation);

            $this->em->flush();

            $this->addFlash('success', 'Your account has been created successfully! You can now login.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('auth/verify.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
