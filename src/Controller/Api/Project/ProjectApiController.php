<?php

namespace App\Controller\Api\Project;

use App\Controller\Api\CrudApiController;
use App\Entity\Project;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/project')]
class ProjectApiController extends CrudApiController
{
    #[Route('/{project}', name: 'api_project_get', methods: ['GET'])]
    public function getProject(Project $project): JsonResponse
    {
        return $this->crudGet($project);
    }

    #[Route('', name: 'api_project_create', methods: ['POST'])]
    public function createProject(Project $project): JsonResponse
    {
        $this->persistAndFlush($project);

        return $this->jsonSerialize($project);
    }

    public function getEntityClass(): string
    {
        return Project::class;
    }

    public function getFormClass(): string
    {
        throw new \Exception('Not implemented (@todo)');
    }
}