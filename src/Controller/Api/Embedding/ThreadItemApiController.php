<?php

namespace App\Controller\Api\Embedding;

use App\Controller\Api\CrudApiController;
use App\Entity\Thread;
use App\Entity\ThreadItem;
use App\Entity\ThreadItemPrompt;
use App\Form\ThreadItemPromptForm;
use App\Service\Helper\ApiControllerHelperService;
use App\Service\Search\SearchEngine;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/thread/item')]
class ThreadItemApiController extends CrudApiController
{
    public function __construct(
        ApiControllerHelperService $apiControllerHelperService,
        private SearchEngine $searchEngine,
    ) {
        parent::__construct($apiControllerHelperService);
    }

    #[Route('/{threadItem}', methods: ['DELETE'], name: 'api_thread_item_delete')]
    public function deleteThreadItem(ThreadItem $threadItem): JsonResponse
    {
        return $this->crudDelete($threadItem);
    }

    public function getEntityClass(): string
    {
        return ThreadItem::class;
    }

    public function getFormClass(): string
    {
        throw new \Exception('Not implemented'); // we do not need a form for this controller
    }
}