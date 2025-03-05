<?php

namespace App\Service\User;

use App\Entity\Project\Project;
use App\Entity\User\UserInvitation;
use App\Exception\BadRequestException;
use App\Repository\UserInvitationRepository;
use App\Repository\UserRepository;
use App\Service\Helper\ApplicationEnvironment;
use App\Service\MailerService;

class UserInvitationService
{
    public function __construct(
        private MailerService $mailerService,
        private UserRepository $userRepository,
        private UserInvitationRepository $userInvitationRepository,
    ) { }

    /**
     * Creates a new user invitation.
     * If the project is given it is preselected for the user when logging in.
     * If the project is not given the user starts in the setup step; can be leveraged for sending out beta invitations.
     * 
     * @param string $email The email of the user to invite
     * @param Project|null $project The project to preselect for the user when logging in; if not given the user starts in the setup step
     * @return UserInvitation The created user invitation
     * 
     * @throws BadRequestException When the user was already invited to the project
     * @throws BadRequestException When the user with the given email was already invited
     * @throws BadRequestException When the user with the given email already exists
     * 
     * @return UserInvitation The created user invitation
     */
    public function createInvitation(string $email, ?Project $project): UserInvitation
    {
        $userInvitation = (new UserInvitation())
            ->setEmail($email)
            ->setProject($project);

        // check if the user of the given invitation email already exists;
        // if so, attach the user to the invitation.
        if (null !== $user = $this->userRepository->findOneBy(['email' => $email])) {
            $userInvitation->setUser($user);

            if (null !== $project?->getProjectUser($user)) {
                throw new BadRequestException(\sprintf('User %s is already a member of project %s', $user->getEmail(), $project->getName()));
            }
        }

        if (null !== $user && null !== $this->userInvitationRepository->findOneBy(['user' => $userInvitation->getUser(), 'project' => $userInvitation->getProject()])) {
            throw new BadRequestException(\sprintf('User %s was already invited to project %s', $userInvitation->getEmail(), $userInvitation->getProject()->getName()));
        }

        if ($this->userInvitationRepository->findOneBy(['project' => $userInvitation->getProject(), 'email' => $userInvitation->getEmail()])) {
            throw new BadRequestException(\sprintf('User with email %s was already invited to project %s', $userInvitation->getEmail(), $userInvitation->getProject()->getName()));
        }

        // attach random code to user invitation so that it can be used to verify the user
        $userInvitation->setCode(\bin2hex(\openssl_random_pseudo_bytes(10)));

        // send emails only when not in the test env
        if (!ApplicationEnvironment::isTestEnv()) {
            if (null === $user) {
                $this->mailerService->sendUserInvitationToNewEmail($userInvitation);
            } else {
                $this->mailerService->sendUserInvitationToExistingUser($userInvitation);
            }
        }

        return $userInvitation;
    }
}