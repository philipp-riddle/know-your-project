<?php

namespace App\Controller\Api\Tag;

use App\Controller\Api\CrudApiController;
use App\Entity\Interface\AccessContext;
use App\Entity\Project\Project;
use App\Entity\Tag\Tag;
use App\Form\Tag\TagForm;
use App\Repository\TagRepository;
use App\Service\Helper\ApiControllerHelperService;
use App\Service\OrderListHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/api/tag')]
class TagApiController extends CrudApiController
{
    public function __construct(
        ApiControllerHelperService $apiControllerHelperService,
        private TagRepository $tagRepository,
        private OrderListHandler $orderListHandler,
    ) {
        parent::__construct($apiControllerHelperService);
    }

    #[Route('', methods: ['POST'], name: 'api_tag_create')]
    public function create(Request $request): JsonResponse
    {
        return $this->crudUpdateOrCreate(null, $request);
    }

    #[Route('/{tag}', methods: ['PUT'], name: 'api_tag_edit')]
    public function edit(Tag $tag, Request $request): JsonResponse
    {
        return $this->crudUpdateOrCreate($tag, $request);
    }

    #[Route('/{tag}', methods: ['DELETE'], name: 'api_tag_delete')]
    public function delete(Tag $tag): JsonResponse
    {
        return $this->crudDelete($tag);
    }

    #[Route('/order/{project}/{parentTag}', methods: ['POST'], name: 'api_tag_order')]
    public function order(Request $request, Project $project, ?Tag $parentTag = null): JsonResponse
    {
        $this->checkUserAccess($project, AccessContext::UPDATE);

        if (null === $parentTag) { // wants to order all root tags
            $itemsToOrder = $this->tagRepository->findRootTags($project);
        } else {
            if ($parentTag->getProject() !== $project) {
                throw new AccessDeniedException('Parent tag does not belong to the project.');
            }

            $this->checkUserAccess($parentTag, AccessContext::UPDATE);
            $itemsToOrder = $this->tagRepository->findTagsByParent($parentTag);
        }

        return $this->crudChangeOrder(
            $request,
            $this->orderListHandler,
            itemsToOrder: $itemsToOrder,
            orderListName: $parentTag?->getId(),
        );
    }

    public function getEntityClass(): string
    {
        return Tag::class;
    }

    public function getFormClass(): string
    {
        return TagForm::class;
    }
}