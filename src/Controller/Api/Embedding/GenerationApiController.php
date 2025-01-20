<?php

namespace App\Controller\Api\Embedding;

use App\Controller\Api\ApiController;
use App\Entity\Project;
use App\Form\Embedding\GenerationAskForm;
use App\Service\Helper\ApiControllerHelperService;
use App\Service\Search\GenerationEngine;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/generation')]
class GenerationApiController extends ApiController
{
    public function __construct(
        ApiControllerHelperService $apiControllerHelperService,
        private GenerationEngine $generationEngine,
    ) {
        parent::__construct($apiControllerHelperService);
    }

    #[Route('/ask/{project}', methods: ['POST'], name: 'api_generation_ask')]
    public function ask(Project $project, Request $request): JsonResponse
    {
        $this->checkUserAccess($project);

        $form = $this->createForm(GenerationAskForm::class);
        $form = $this->handleFormRequest($form, $request);

        // if the handle returns a response this means that the form submission was not valid
        if ($form instanceof JsonResponse) {
            return $form;
        }

        $question = $form->get('question')->getData();

        return $this->createJsonResponse(
            $this->generationEngine->answerQuestion($this->getUser(), $project, $question),
        );
    }
}