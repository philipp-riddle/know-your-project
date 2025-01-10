<?php

namespace App\Controller\Api\Tag;

use App\Controller\Api\CrudApiController;
use App\Entity\Tag;
use App\Entity\TagPage;
use App\Form\TagPageForm;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tag/page')]
class TagPageApiController extends CrudApiController
{
    #[Route('', methods: ['POST'], name: 'tag_page_create')]
    public function create(Request $request): JsonResponse
    {
        return $this->crudUpdateOrCreate(
            null,
            $request,
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

    #[Route('/{tagPage}', methods: ['DELETE'], name: 'tag_page_delete')]
    public function delete(TagPage $tagPage): JsonResponse
    {
        return $this->crudDelete($tagPage);
    }

    #[Route('/list/{tag}', methods: ['GET'], name: 'tag_page_list')]
    public function list(Tag $tag): JsonResponse
    {
        return $this->crudList(['tag' => $tag]);
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