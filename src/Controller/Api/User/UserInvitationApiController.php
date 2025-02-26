<?php

namespace App\Controller\Api\User;

use App\Controller\Api\CrudApiController;
use App\Entity\Project\Project;
use App\Entity\User\UserInvitation;
use App\Form\User\UserInvitationForm;
use App\Repository\UserInvitationRepository;
use App\Service\Helper\ApiControllerHelperService;
use App\Service\Helper\TestEnvironment;
use App\Service\MailerService;
use App\Service\UserService;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/user/invitation')]
class UserInvitationApiController extends CrudApiController
{
    public function __construct(
        ApiControllerHelperService $apiControllerHelperService,
        private UserService $userService,
        private MailerService $mailerService,
        private UserInvitationRepository $userInvitationRepository,
    ) {
        parent::__construct($apiControllerHelperService);
    }

    #[Route('', name: 'api_user_invitation_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        return $this->crudUpdateOrCreate(
            null,
            $request,
            onProcessEntity: function (UserInvitation $userInvitation) {
                if (null !== $user = $this->userRepository->findOneBy(['email' => $userInvitation->getEmail()])) {
                    $userInvitation->setUser($user);
                    $isNewUser = false;
                } else {
                    $isNewUser = true;
                }

                if (null !== $this->userInvitationRepository->findOneBy(['user' => $userInvitation->getUser(), 'project' => $userInvitation->getProject()])) {
                    throw new BadRequestException('User was already invited to this project');
                }

                if ($this->userInvitationRepository->findOneBy(['project' => $userInvitation->getProject(), 'email' => $userInvitation->getEmail()])) {
                    throw new BadRequestException('User with this email was already invited');
                }

                $userInvitation->setCode(bin2hex(openssl_random_pseudo_bytes(10))); // attach random code to user invitation so that it can be used to verify the user

                // send emails only when not in the test env
                if (TestEnvironment::isActive()) {
                    if ($isNewUser) {
                        $this->mailerService->sendUserInvitationToNewEmail($userInvitation);
                    } else {
                        $this->mailerService->sendUserInvitationToExistingUser($userInvitation);
                    }
                }

                return $userInvitation;
            },
        );
    }

    #[Route('/{userInvitation}', name: 'api_user_invitation_delete', methods: ['DELETE'])]
    public function delete(UserInvitation $userInvitation): JsonResponse
    {
        return $this->crudDelete($userInvitation);
    }

    #[Route('/project/list/{project}', name: 'api_user_invitation_project_list', methods: ['GET'])]
    public function projectInvitationList(Project $project)
    {
        return $this->crudList(['project' => $project]);
    }

    public function getEntityClass(): string
    {
        return UserInvitation::class;
    }

    public function getFormClass(): string
    {
        return UserInvitationForm::class;
    }
}