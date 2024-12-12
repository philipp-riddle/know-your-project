<?php

namespace App\Controller\Api\Project;

use App\Controller\Api\CrudApiController;
use App\Entity\ProjectUser;
use App\Entity\ProjectUserEmailInvitation;
use App\Form\CreateProjectUserForm;
use App\Form\ProjectUserEmailInvitationForm;
use App\Service\Helper\ApiControllerHelperService;
use App\Service\UserService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/project/user/email-invitation')]
class ProjectUserEmailInvitationApiController extends CrudApiController
{
    public function __construct(
        ApiControllerHelperService $apiControllerHelperService,
        private UserService $userService,
    ) {
        parent::__construct($apiControllerHelperService);
    }

    // create route
    #[Route('', name: 'api_project_user_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        return $this->crudUpdateOrCreate(
            null,
            $request,
            onProcessEntity: function (ProjectUser $projectUser, FormInterface $form) {
                // $projectUser->setUser($this->userService->createUserFromEmail($form->get('userEmail')->getData()));
                // $this->persistAndFlush($projectUser);
            },
        );
    }

    #[Route('/{projectUser}', name: 'api_project_user_delete', methods: ['DELETE'])]
    public function delete(ProjectUser $projectUser): JsonResponse
    {
        return $this->crudDelete($projectUser);
    }

    public function getEntityClass(): string
    {
        return ProjectUserEmailInvitation::class;
    }

    public function getFormClass(): string
    {
        return ProjectUserEmailInvitationForm::class;
    }
}