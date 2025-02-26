<?php

namespace App\Controller\Api\Project;

use App\Controller\Api\CrudApiController;
use App\Entity\Project\Project;
use App\Entity\Project\ProjectUser;
use App\Form\Project\ProjectForm;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/project')]
class ProjectApiController extends CrudApiController
{
    #[Route('/{project}', name: 'api_project_get', methods: ['GET'])]
    public function get(Project $project): JsonResponse
    {
        return $this->crudGet($project);
    }

    #[Route('', name: 'api_project_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        return $this->crudUpdateOrCreate(
            null,
            $request,
            onProcessEntity: function (Project $project, FormInterface $form) {
                // when creating a project we must assign the owner and add the user to the project
                $project->setOwner($this->getUser());
                $projectUser = (new ProjectUser())
                    ->initialize()
                    ->setUser($this->getUser())
                    ->setProject($project);
                $project->addProjectUser($projectUser);
                $this->em->persist($projectUser);

                if (true === \boolval($form->get('selectAfterCreating')->getData())) {
                    $this->getUser()->setSelectedProject($project);
                }

                return $project;
            },
        );
    }

    /**
     * Selects a project for the current user.
     * This changes the UI to only show content and actions related to the selected project.
     */
    #[Route('/select/{project}', name: 'api_project_select', methods: ['PUT'])]
    public function select(Project $project): JsonResponse
    {
        $this->checkUserAccess($project);
        $this->getUser()->setSelectedProject($project);
        $this->em->flush();

        return $this->jsonSerialize($project);
    }

    #[Route('/{project}', name: 'api_project_delete', methods: ['DELETE'])]
    public function delete(Project $project): JsonResponse
    {
        return $this->crudDelete($project);
    }

    public function getEntityClass(): string
    {
        return Project::class;
    }

    public function getFormClass(): string
    {
        return ProjectForm::class;
    }
}