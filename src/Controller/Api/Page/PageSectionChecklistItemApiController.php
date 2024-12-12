<?php

namespace App\Controller\Api\Page;

use App\Controller\Api\CrudApiController;
use App\Entity\PageSectionChecklistItem;
use App\Form\PageSectionChecklistItemForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/page/section/checklist/item')]
class PageSectionChecklistItemApiController extends CrudApiController
{
    #[Route('/{checklistItem}', name: 'api_page_section_checklist_item_get', methods: ['GET'])]
    public function get(PageSectionChecklistItem $checklistItem): JsonResponse
    {
        return $this->crudGet($checklistItem);
    }

    #[Route('/{checklistItem}', name: 'api_page_section_checklist_item_delete', methods: ['DELETE'])]
    public function delete(PageSectionChecklistItem $checklistItem): JsonResponse
    {
        return $this->crudDelete($checklistItem);
    }

    #[Route('/{checklistItem}', name: 'api_page_section_checklist_item_update', methods: ['PUT'])]
    public function update(PageSectionChecklistItem $checklistItem, Request $request): JsonResponse
    {
        return $this->crudUpdateOrCreate($checklistItem, $request);
    }

    #[Route('', name: 'api_page_section_checklist_item_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        return $this->crudUpdateOrCreate(
            null,
            $request
        );
    }

    public function getEntityClass(): string
    {
        return PageSectionChecklistItem::class;
    }

    public function getFormClass(): string
    {
        return PageSectionChecklistItemForm::class;
    }
}