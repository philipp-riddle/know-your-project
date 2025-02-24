<?php

namespace App\Controller\Api\Tag;

use App\Controller\Api\CrudApiController;
use App\Entity\Interface\AccessContext;
use App\Entity\Project\Project;
use App\Entity\Tag\Tag;
use App\Entity\Tag\TagPage;
use App\Form\Tag\TagPageForm;
use App\Repository\PageRepository;
use App\Repository\TagPageRepository;
use App\Service\Helper\ApiControllerHelperService;
use App\Service\OrderListHandler;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/api/tag/page')]
class TagPageApiController extends CrudApiController
{
    public function __construct(
        private OrderListHandler $orderListHandler,
        private PageRepository $pageRepository,
        private TagPageRepository $tagPageRepository,
        ApiControllerHelperService $apiControllerHelperService,
    ) {
        parent::__construct($apiControllerHelperService);
    }

    #[Route('', methods: ['POST'], name: 'api_tag_page_create')]
    public function create(Request $request): JsonResponse
    {
        return $this->crudUpdateOrCreateOrderListItem(
            null,
            $request,
            $this->orderListHandler,
            itemsToOrder: function (TagPage $tagPage) {
                return $this->pageRepository->findProjectPages(
                    $tagPage->getPage()->getProject(),
                    tags: [$tagPage->getTag()->getId()],
                );
            },
            onProcessEntity: function(TagPage $tagPage, FormInterface $form) {
                if (null === $tagPage->getTag()) {
                    if (null === $newTagName = $form->get('tagName')->getData()) {
                        throw new \InvalidArgumentException('Either an existing tag or a new tag name must be provided.');
                    }

                    // if the user chooses to create a new tag with a provided name we must build, initialize, and persist a new entity
                    $newTag = (new Tag())
                        ->setProject($tagPage->getPage()->getProject())
                        ->setName($newTagName)
                        ->setParent($form->get('parent')->getData())
                        ->initialize();
                    $this->em->persist($newTag);

                    $tagPage->setTag($newTag);
                    $this->em->persist($tagPage);
                } else {
                    foreach ($tagPage->getPage()->getTags() as $tag) {
                        if ($tag->getTag()->getId() === $tagPage->getTag()->getId()) {
                            throw new \InvalidArgumentException('The tag is already associated with the page.');
                        }
                    }
                }

                return $tagPage;
            },
        );
    }

    #[Route('/{tagPage}', methods: ['DELETE'], name: 'api_tag_page_delete')]
    public function delete(TagPage $tagPage): JsonResponse
    {
        return $this->crudDelete($tagPage);
    }

    #[Route('/list/{tag}', methods: ['GET'], name: 'api_tag_page_list')]
    public function list(Tag $tag): JsonResponse
    {
        return $this->crudList(['tag' => $tag]);
    }

    #[Route('/order/{project}/{tag}', methods: ['POST'], name: 'api_tag_page_order')]
    public function order(Request $request, OrderListHandler $orderListHandler, Project $project, ?Tag $tag = null): JsonResponse
    {
        $this->checkUserAccess($project);

        if (null === $tag) {
            $itemsToOrder = $this->pageRepository->findProjectPages(
                $project,
                tags: [], // get all uncategorized pages
            );
        } else {
            if ($tag->getProject() !== $project) {
                throw new AccessDeniedException('The tag does not belong to the project.');
            }

            $this->checkUserAccess($tag, AccessContext::UPDATE);
            $itemsToOrder = $this->tagPageRepository->findAllTagPagesByTag($tag); // all tagged pages in this tag must be ordered
        }

        return $this->crudChangeOrder(
            $request,
            $orderListHandler,
            $itemsToOrder,
            orderListName: $tag?->getId() ?? -1,
        );
    }


    public function getEntityClass(): string
    {
        return TagPage::class;
    }

    public function getFormClass(): string
    {
        return TagPageForm::class;
    }
}