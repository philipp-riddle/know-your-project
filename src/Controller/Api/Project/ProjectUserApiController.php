<?php

namespace App\Controller\Api\Project;

use App\Controller\Api\CrudApiController;
use App\Entity\Project\ProjectUser;
use App\Exception\BadRequestException;
use App\Exception\PreconditionFailedException;
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
                if ($projectUser->getUser() === $this->getUser() && $projectUser->getProject()->getOwner() === $this->getUser()) {
                    throw new BadRequestException('You cannot delete yourself from the project, you are the owner. Please delete the project instead.');
                }

                $currentUser = $this->getUser();

                // if the project membership, i.e. its project, the user has currently selected is deleted, select the first project (if available).
                // if there is no other project the user will be thrown back in the setup as there is no project to select.
                if ($projectUser->getProject() === $currentUser->getSelectedProject()) {
                    if (\count($currentUser->getProjectUsers()) > 0) {
                        $currentUser->setSelectedProject($currentUser->getProjectUsers()->first()->getProject());
                    } else {
                        $currentUser->setSelectedProject(null);
                    }
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
        throw new PreconditionFailedException('Not implemented');
    }
}