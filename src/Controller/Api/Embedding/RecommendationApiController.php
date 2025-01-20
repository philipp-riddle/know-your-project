<?php

namespace App\Controller\Api\Embedding;

use App\Controller\Api\ApiController;
use App\Entity\Page\Page;
use App\Service\Helper\ApiControllerHelperService;
use App\Service\Search\SearchEngine;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/recommendation')]
class RecommendationApiController extends ApiController
{
    public function __construct(
        ApiControllerHelperService $apiControllerHelperService,
        private SearchEngine $searchEngine,
    ) {
        parent::__construct($apiControllerHelperService);
    }

    #[Route('/page/{page}', methods: ['POST'], name: 'api_recommendation_page')]
    public function getProjectRecommendations(Page $page, Request $request): JsonResponse
    {
    }
}