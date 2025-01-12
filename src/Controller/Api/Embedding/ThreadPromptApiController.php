<?php

namespace App\Controller\Api\Embedding;

use App\Controller\Api\CrudApiController;
use App\Entity\PageSectionAIPrompt;
use App\Entity\Thread;
use App\Entity\ThreadItem;
use App\Entity\ThreadItemPrompt;
use App\Entity\ThreadPageSectionContext;
use App\Form\ThreadPromptForm;
use App\Service\Helper\ApiControllerHelperService;
use App\Service\Search\SearchEngine;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The entity "ThreadPrompt" does not directly exists, it means "create a thread with an attached prompt as a start"
 */
#[Route('/api/thread/prompt')]
class ThreadPromptApiController extends CrudApiController
{
    public function __construct(
        ApiControllerHelperService $apiControllerHelperService,
        private SearchEngine $searchEngine,
    ) {
        parent::__construct($apiControllerHelperService);
    }

    #[Route('', methods: ['POST'], name: 'api_thread_prompt_create')]
    public function createThread(Request $request): JsonResponse
    {
        return $this->crudUpdateOrCreate(
            null,
            $request,
            onProcessEntity: function (Thread $thread, FormInterface $form) {
                /** @var PageSectionAIPrompt */
                $pageSectionAIPrompt = $form->get('pageSectionAIPrompt')->getData();

                // create the page section context
                $pageSectionContext = (new ThreadPageSectionContext())
                    ->setPageSection($pageSectionAIPrompt->getPageSection());
                $thread->setPageSectionContext($pageSectionContext);
                $thread->setProject($pageSectionAIPrompt->getPageSection()->getPageTab()->getPage()->getProject());
                $this->em->persist($pageSectionContext);

                // create the thread item with the prompt to start the thread
                $threadItemPrompt = (new ThreadItemPrompt())
                    ->setPrompt($pageSectionAIPrompt->getPrompt());
                $threadItem = (new ThreadItem())
                    ->setItemPrompt($threadItemPrompt)
                    ->setCreatedAt(new \DateTime());
                $thread->addThreadItem($threadItem);

                // save both entities to the database
                $this->em->persist($threadItemPrompt);
                $this->em->persist($threadItem);

                return $thread;
            },
        );
    }

    public function getEntityClass(): string
    {
        return Thread::class;
    }

    public function getFormClass(): string
    {
        return ThreadPromptForm::class;
    }
}