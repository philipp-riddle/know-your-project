<?php

namespace App\Controller\Api\Thread;

use App\Controller\Api\CrudApiController;
use App\Entity\Thread;
use App\Entity\ThreadItem;
use App\Entity\ThreadItemComment;
use App\Entity\User;
use App\Form\ThreadItemCommentForm;
use App\Service\Helper\ApiControllerHelperService;
use App\Service\Search\GenerationEngine;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/thread/item/comment')]
class ThreadItemCommentApiController extends CrudApiController
{
    public function __construct(
        ApiControllerHelperService $apiControllerHelperService,
        private GenerationEngine $generationEngine,
    ) {
        parent::__construct($apiControllerHelperService);
    }

    #[Route('', methods: ['POST'], name: 'api_thread_item_comment_create')]
    public function createThreadCommentItem(Request $request): JsonResponse
    {
        return $this->crudUpdateOrCreate(
            null,
            $request,
            onProcessEntity: function (ThreadItemComment $threadItemComment, FormInterface $form) {
                /** @var Thread */
                $thread = $form->get('thread')->getData();

                // we need to create a new thread item for the comment
                $threadItem = (new ThreadItem())
                    ->setUser($this->getUser())
                    ->setThread($thread)
                    ->setThreadItemComment($threadItemComment)
                    ->initialize();
                $threadItemComment->setThreadItem($threadItem);
                $this->em->persist($threadItem);

                return $threadItemComment;
            },
            // we need to drastically decrease the normalization depth for faster response
            normalizeCallbacks: [
                'pageTabs' => fn() => [],
                'project' => fn() => [],
            ],
        );
    }

    #[Route('/{threadItemComment}', methods: ['PUT'], name: 'api_thread_item_comment_update')]
    public function updateThreadCommentItem(Request $request, ThreadItemComment $threadItemComment): JsonResponse
    {
        return $this->crudUpdateOrCreate(
            $threadItemComment,
            $request,
        );
    }

    public function getEntityClass(): string
    {
        return ThreadItemComment::class;
    }

    public function getFormClass(): string
    {
        return ThreadItemCommentForm::class;
    }
}