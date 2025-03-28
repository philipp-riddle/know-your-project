<?php

namespace App\Controller\Api\Embedding;

use App\Controller\Api\ApiController;
use App\Entity\Interface\AccessContext;
use App\Entity\Page\Page;
use App\Entity\Project\Project;
use App\Entity\Tag\Tag;
use App\Event\UpdateCrudEntityEvent;
use App\Exception\AccessDeniedException;
use App\Exception\BadRequestException;
use App\Form\Embedding\GenerationAskForm;
use App\Form\Embedding\GenerationCreateForm;
use App\Form\Embedding\GenerationSaveForm;
use App\Service\Helper\ApiControllerHelperService;
use App\Service\Page\PageGenerationService;
use App\Service\PageService;
use App\Service\Search\GenerationEngine;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/generation')]
class GenerationApiController extends ApiController
{
    public function __construct(
        ApiControllerHelperService $apiControllerHelperService,
        private PageService $pageService,
        private GenerationEngine $generationEngine,
        private PageGenerationService $pageGenerationService,
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
            $this->generationEngine->generateAnswer($this->getUser(), $project, $question),
        );
    }

    #[Route('/create/{page}', methods: ['POST'], name: 'api_generation_create')]
    public function create(Page $page, Request $request): JsonResponse
    {
        $this->checkUserAccess($page, AccessContext::CREATE);

        $form = $this->createForm(GenerationCreateForm::class);
        $form = $this->handleFormRequest($form, $request);

        // if the handle returns a response this means that the form submission was not valid
        if ($form instanceof JsonResponse) {
            return $form;
        }

        $text = \trim($form->get('text')->getData() ?? '');

        if ($text === '') {
            throw new BadRequestException('The "text" field is required and must not be empty');
        }

        return $this->createJsonResponse(
            $this->generationEngine->generateCreationPrompt($this->getUser(), $page, $text),
        );
    }

    #[Route('/save/{project}/{page}', methods: ['POST'], name: 'api_generation_save')]
    public function save(Request $request, Project $project, ?Page $page = null): JsonResponse
    {
        $this->checkUserAccess($project);

        if (null === $page) {
            $page = $this->pageService->createEmptyPage($this->getUser(), $project, 'New Page');
            $this->em->flush();
        } else {
            $this->checkUserAccess($page);

            if ($page->getProject() !== $project) {
                throw new BadRequestException('The page does not belong to the specified project');
            }
        }

        $form = $this->createForm(GenerationSaveForm::class);
        $form = $this->handleFormRequest($form, $request);

        // if the handle returns a response this means that the form submission was not valid
        if ($form instanceof JsonResponse) {
            return $form;
        }

        $title = \trim($form->get('title')->getData() ?? '');
        $content = \trim($form->get('content')->getData() ?? '');
        $checklistItems = $form->get('checklistItems')->getData();

        /** @var ?Tag */
        $tag = $form->get('tag')->getData();

        if (null !== $tag) {
            $this->checkUserAccess($tag);

            if ($tag->getProject() !== $project) {
                throw new BadRequestException('The tag does not belong to the specified project');
            }
        }

        $pageGeneration = $this->pageGenerationService->generatePage($this->getUser(), $page, $title, $content, $tag, $checklistItems);

        // dispatch an event to also update it for other users via Mercure
        $this->eventDispatcher->dispatch(new UpdateCrudEntityEvent($page, $this->getUser(), $page));

        return $this->jsonSerialize($pageGeneration);
    }
}