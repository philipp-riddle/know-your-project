<?php

namespace App\Controller\Api\Embedding;

use App\Controller\Api\ApiController;
use App\Entity\Project;
use App\Service\Helper\ApiControllerHelperService;
use App\Service\Search\SearchEngine;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/search')]
class SearchApiController extends ApiController
{
    public function __construct(
        ApiControllerHelperService $apiControllerHelperService,
        private SearchEngine $searchEngine,
    ) {
        parent::__construct($apiControllerHelperService);
    }

    #[Route('/project/{project}', methods: ['POST'], name: 'api_project_search')]
    public function projectSearch(Project $project, Request $request): JsonResponse
    {
        $this->checkUserAccess($project);

        $payload = $request->toArray();
        $search = \trim($payload['search'] ?? ''); // get search term from request payload

        if ($search === '') {
            return $this->json([]); // no search results when there is no search term
        }

        return $this->json($this->searchEngine->searchProject($this->getUser(), $project, $search));
    }
}