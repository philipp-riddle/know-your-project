<?php

namespace App\Controller\Api\Project;

use App\Controller\Api\CrudApiController;
use App\Entity\ProjectUser;
use App\Form\CreateProjectUserForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/project/user')]
class ProjectUserApiController extends CrudApiController
{
    #[Route('/{projectUser}', name: 'api_project_user_delete', methods: ['DELETE'])]
    public function delete(ProjectUser $projectUser): JsonResponse
    {
        return $this->crudDelete($projectUser);
    }

    public function getEntityClass(): string
    {
        return ProjectUser::class;
    }

    public function getFormClass(): string
    {
        return CreateProjectUserForm::class;
    }
}