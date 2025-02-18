<?php

namespace App\Controller\Api\Mercure;

use App\Controller\Api\ApiController;
use App\Entity\Project\Project;
use App\Service\Helper\ApiControllerHelperService;
use App\Service\Integration\MercureIntegration;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/mercure')]
class MercureApiController extends ApiController
{
    public function __construct(
        ApiControllerHelperService $apiControllerHelperService,
        private MercureIntegration $mercureIntegration,
    ) {
        parent::__construct($apiControllerHelperService);
    }

    /**
     * Returns a new Mercure JWS token for the given project and topics.
     * This endpoint needs to be called before the current JWS token of the user expires.
     * 
     * We require the user to be logged in with a JWS to disallow unauthorized user and topic access to the Mercure hub.
     */
    #[Route('/jws/{project}', methods: ['GET'], name: 'api_mercure_jws')]
    public function getJWS(Project $project, Request $request): JsonResponse
    {
        $this->checkUserAccess($project);
        $topics = \explode(',', $request->query->get('topics', ''));
        $jws = $this->mercureIntegration->createJWS($request, $project, $topics);

        return $this->createJsonResponse(['token' => $jws]);
    }
}