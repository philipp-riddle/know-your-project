<?php

namespace App\Controller\Api\Page;

use App\Controller\Api\CrudApiController;
use App\Entity\PageSection;
use App\Entity\PageTab;
use App\Form\PageSectionForm;
use App\Service\OrderListHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/page/section')]
class PageSectionApiController extends CrudApiController
{
    #[Route('/{pageSection}', name: 'api_page_section_get', methods: ['GET'])]
    public function get(PageSection $pageSection): JsonResponse
    {
        return $this->crudGet($pageSection);
    }

    #[Route('/{pageSection}', name: 'api_page_section_delete', methods: ['DELETE'])]
    public function delete(PageSection $pageSection): JsonResponse
    {
        return $this->crudDelete($pageSection);
    }

    #[Route('/{pageSection}', name: 'api_page_section_update', methods: ['PUT'])]
    public function update(PageSection $pageSection, Request $request): JsonResponse
    {
        return $this->crudUpdateOrCreate($pageSection, $request);
    }

    #[Route('', name: 'api_page_section_create', methods: ['POST'])]
    public function create(Request $request, OrderListHandler $orderListHandler): JsonResponse
    {
        return $this->crudUpdateOrCreateOrderListItem(
            null,
            $request,
            $orderListHandler,
            itemsToOrder: function (PageSection $pageSection) {
                return $pageSection->getPageTab()->getPageSections();
            },
            onProcessEntity: function (PageSection $pageSection) {
                $pageSection->setAuthor($this->getUser());

                // make sure to persist any child entities as well - in this case the checklist items.
                if (null !== $pageSection->getPageSectionChecklist()) {
                    foreach ($pageSection->getPageSectionChecklist()->getPageSectionChecklistItems() as $item) {
                        $this->em->persist($item);
                    }
                }

                return $pageSection;
            }
        );
    }

    #[Route('/order/{pageTab}', name: 'api_page_section_changeOrder', methods: ['PUT'])]
    public function changeOrder(PageTab $pageTab, Request $request, OrderListHandler $orderListHandler): JsonResponse
    {
        return $this->crudChangeOrder($request, $orderListHandler, \iterator_to_array($pageTab->getPageSections()));
    }

    public function getEntityClass(): string
    {
        return PageSection::class;
    }

    public function getFormClass(): string
    {
        return PageSectionForm::class;
    }
}