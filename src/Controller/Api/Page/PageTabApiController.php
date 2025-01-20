<?php

namespace App\Controller\Api\Page;

use App\Controller\Api\CrudApiController;
use App\Entity\Page\PageTab;
use App\Form\Page\PageTabForm;
use App\Repository\PageTabRepository;
use App\Service\Helper\ApiControllerHelperService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/page/tab')]
class PageTabApiController extends CrudApiController
{
    public function __construct(
        ApiControllerHelperService $apiControllerHelperService,
        private PageTabRepository $taskRepository,
    ) {
        parent::__construct($apiControllerHelperService);
    }

    #[Route('/{pageTab}', name: 'api_pageTab_get', methods: ['GET'])]
    public function get(PageTab $pageTab): JsonResponse
    {
        return $this->crudGet($pageTab);
    }

    #[Route('/{pageTab}', name: 'api_pageTab_delete', methods: ['DELETE'])]
    public function delete(PageTab $pageTab): JsonResponse
    {
        return $this->crudDelete($pageTab);
    }

    #[Route('', name: 'api_pageTab_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        return $this->crudUpdateOrCreate(null, $request);
    }

    #[Route('/{pageTab}', name: 'api_pageTab_update', methods: ['PUT'])]
    public function update(PageTab $pageTab, Request $request): JsonResponse
    {
        return $this->crudUpdateOrCreate($pageTab, $request);
    }

    public function getEntityClass(): string
    {
        return PageTab::class;
    }

    public function getFormClass(): string
    {
        return PageTabForm::class;
    }
}