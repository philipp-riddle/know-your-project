<?php

namespace App\Controller\Api;

use App\Entity\Project;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/user')]
class UserApiController extends ApiController
{
    #[Route('', name: 'api_user_info', methods: ['GET'])]
    public function getUserInfo(): JsonResponse
    {
        $user = $this->getUser();

        return $this->jsonSerialize($user, normalizeCallbacks: [
            'pages' => fn() => [],
            'projectUsers' => fn() => [],
            'tasks' => fn() => [],
        ]);
    }

    #[Route('/selected-project/{project}', name: 'api_user_selectProject', methods: ['PUT'])]
    public function selectProject(Project $project, Request $request): JsonResponse
    {
        // return $this->jsonSerialize($projectOrganizationUser->getSelectedWorkflow());
    }
}