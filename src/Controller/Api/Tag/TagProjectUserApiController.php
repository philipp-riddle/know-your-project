<?php

namespace App\Controller\Api\Tag;

use App\Controller\Api\CrudApiController;
use App\Entity\Tag\Tag;
use App\Entity\Tag\TagProjectUser;
use App\Exception\BadRequestException;
use App\Form\Tag\TagProjectUserForm;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tag/project-user')]
class TagProjectUserApiController extends CrudApiController
{
    #[Route('', methods: ['POST'], name: 'api_tag_projectUser_create')]
    public function create(Request $request): JsonResponse
    {
        return $this->crudUpdateOrCreate(
            null,
            $request,
            onProcessEntity: function(TagProjectUser $tagProjectUser, FormInterface $form) {
                if (null === $tagProjectUser->getTag()) {
                    if (null === $newTagName = $form->get('tagName')->getData()) {
                        throw new BadRequestException('Either an existing tag or a new tag name must be provided.');
                    }

                    // if the user chooses to create a new tag with a provided name we must build, initialize, and persist a new entity
                    $newTag = (new Tag())
                        ->setProject($tagProjectUser->getProjectUser()->getProject())
                        ->setName($newTagName)
                        ->setParent($form->get('parent')->getData())
                        ->initialize();
                    $this->em->persist($newTag);

                    $tagProjectUser->setTag($newTag);
                    $this->em->persist($tagProjectUser);
                } else {
                    foreach ($tagProjectUser->getProjectUser()->getTags() as $tag) {
                        if ($tag->getTag()->getId() === $tagProjectUser->getTag()->getId()) {
                            throw new BadRequestException('The tag is already associated with this user.');
                        }
                    }
                }

                return $tagProjectUser;
            },
        );
    }

    #[Route('/{tagProjectUser}', methods: ['DELETE'], name: 'tag_projectUser_delete')]
    public function delete(TagProjectUser $tagProjectUser): JsonResponse
    {
        return $this->crudDelete($tagProjectUser);
    }

    public function getEntityClass(): string
    {
        return TagProjectUser::class;
    }

    public function getFormClass(): string
    {
        return TagProjectUserForm::class;
    }
}