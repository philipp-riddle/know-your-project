<?php

namespace App\Controller\Api\Page;

use App\Controller\Api\CrudApiController;
use App\Entity\Page\PageUser;
use App\Form\Page\PageUserForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/page/user')]
class PageUserApiController extends CrudApiController
{
    #[Route('', name: 'api_pageTab_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        return $this->crudUpdateOrCreate(null, $request);
    }

    #[Route('/{pageUser}', name: 'api_page_user_delete', methods: ['DELETE'])]
    public function delete(PageUser $pageUser): JsonResponse
    {
        return $this->crudDelete($pageUser);
    }

    public function getEntityClass(): string
    {
        return PageUser::class;
    }

    public function getFormClass(): string
    {
        return PageUserForm::class;
    }
}