<?php

namespace App\Controller\Api\User;

use App\Controller\Api\CrudApiController;
use App\Entity\Project\Project;
use App\Entity\User\UserInvitation;
use App\Form\User\UserInvitationForm;
use App\Repository\UserInvitationRepository;
use App\Serializer\SerializerContext;
use App\Service\Helper\ApiControllerHelperService;
use App\Service\MailerService;
use App\Service\User\UserInvitationService;
use App\Service\UserService;
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
        private UserInvitationService $userInvitationService,
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
                return $this->userInvitationService->createInvitation($userInvitation->getEmail(), $userInvitation->getProject());
            },
        );
    }

    #[Route('/accept/{userInvitation}', name: 'api_user_invitation_accept', methods: ['POST'])]
    public function accept(UserInvitation $userInvitation)
    {
        $this->checkUserAccess($userInvitation);
        $createdProjectUser = $this->userService->acceptUserInvitation($userInvitation);
        $this->em->flush();

        return $this->jsonSerialize($createdProjectUser);
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

    #[Route('/list', name: 'api_user_invitation_user_list', methods: ['GET'])]
    public function userInvitationList()
    {
        return $this->crudList(['user' => $this->getUser()], serializerContext: SerializerContext::INVITATION);
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