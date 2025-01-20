<?php

namespace App\Controller\Api\Project;

use App\Controller\Api\CrudApiController;
use App\Entity\Project\ProjectUser;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/project/user')]
class ProjectUserApiController extends CrudApiController
{
    #[Route('/{projectUser}', name: 'api_project_user_delete', methods: ['DELETE'])]
    public function delete(ProjectUser $projectUser): JsonResponse
    {
        return $this->crudDelete(
            $projectUser,
            onProcessEntity: function (ProjectUser $projectUser) {
                if ($projectUser->getUser() === $this->getUser()) {
                    throw new BadRequestException('You cannot delete yourself from the project');
                }
            }
        );
    }

    #[Route('/{projectUser}', name: 'api_project_user_get', methods: ['GET'])]
    public function get(ProjectUser $projectUser): JsonResponse
    {
        return $this->crudGet($projectUser);
    }

    public function getEntityClass(): string
    {
        return ProjectUser::class;
    }

    public function getFormClass(): string
    {
        throw new \RuntimeException('Not implemented');
    }
}