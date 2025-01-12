<?php

namespace App\Controller\Api\Embedding;

use App\Controller\Api\CrudApiController;
use App\Entity\Thread;
use App\Entity\ThreadItem;
use App\Entity\ThreadItemPrompt;
use App\Form\ThreadItemPromptForm;
use App\Service\Helper\ApiControllerHelperService;
use App\Service\Search\GenerationEngine;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/thread/prompt/item')]
class ThreadItemPromptApiController extends CrudApiController
{
    public function __construct(
        ApiControllerHelperService $apiControllerHelperService,
        private GenerationEngine $generationEngine,
    ) {
        parent::__construct($apiControllerHelperService);
    }

    #[Route('', methods: ['POST'], name: 'api_thread_prompt_item_create')]
    public function createThreadPromptItem(Request $request): JsonResponse
    {
        return $this->crudUpdateOrCreate(
            null,
            $request,
            onProcessEntity: function (ThreadItemPrompt $threadItemPrompt, FormInterface $form) {
                /** @var Thread */
                $thread = $form->get('thread')->getData();

                // we must assign the project and user to the prompt
                $threadItemPrompt
                    ->getPrompt()
                    ->setProject($thread->getProject())
                    ->setUser($this->getUser())
                    ->initialize();
                $this->em->persist($threadItemPrompt->getPrompt());

                // when creating a response in a thread only the thread id is specified - we must create a new thread item.
                $threadItem = (new ThreadItem())
                    ->setItemPrompt($threadItemPrompt)
                    ->setThread($thread)
                    ->initialize();
                $this->em->persist($threadItem);

                // generate the chat response right away
                $this->generationEngine->generateThreadItemPrompt($threadItemPrompt);

                return $threadItemPrompt;
            },
        );
    }

    public function getEntityClass(): string
    {
        return ThreadItemPrompt::class;
    }

    public function getFormClass(): string
    {
        return ThreadItemPromptForm::class;
    }
}