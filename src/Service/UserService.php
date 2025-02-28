<?php

namespace App\Service;

use App\Entity\Project\ProjectUser;
use App\Entity\User\User;
use App\Entity\User\UserInvitation;
use App\Repository\UserRepository;
use App\Service\Project\ProjectService;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private ProjectService $projectService,
        private EntityManagerInterface $em,
    ) { }

    public function createUserFromEmail(string $email): User
    {
        $existingUser = $this->userRepository->findOneBy(['email' => $form->get('userEmail')->getData()]);

        if (null !== $existingUser) {
            return $existingUser;
        }
        
        if (null === $userToBeAdded) {
            $user = new User();
        }
        // @todo
    }

    /**
     * Accepts an user invitation by adding the project user to the user and removing the invitation.
     * 
     * @return ProjectUser the created project user
     */
    public function acceptUserInvitation(UserInvitation $invitation): ProjectUser
    {
        $user = $invitation->getUser();
        $project = $invitation->getProject();

        $projectUser = $this->projectService->addUserToProject($user, $project);
        $this->em->remove($invitation);

        return $projectUser;
    }
}