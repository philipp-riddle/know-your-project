<?php

namespace App\Controller\Api\Tag;

use App\Controller\Api\CrudApiController;
use App\Entity\TagPageProjectUser;
use App\Form\Tag\TagPageProjectUserForm;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tag/page/project-user')]
class TagPageProjectUserApiController extends CrudApiController
{
    #[Route('', methods: ['POST'], name: 'api_tag_page_projectUser_create')]
    public function create(Request $request): JsonResponse
    {
        return $this->crudUpdateOrCreate(
            null,
            $request,
            onProcessEntity: function(TagPageProjectUser $tagPageProjectUser) {
                foreach ($tagPageProjectUser->getTagPage()->getUsers() as $user) {
                    if ($user->getProjectUser()->getUser()->getId() === $tagPageProjectUser->getProjectUser()->getUser()->getId()) {
                        throw new BadRequestException('The user is already associated with this tag and page.');
                    }
                }

                return $tagPageProjectUser;
            },
        );
    }

    #[Route('/{tagPageProjectUser}', methods: ['DELETE'], name: 'api_tag_page_projectUser_delete')]
    public function delete(TagPageProjectUser $tagPageProjectUser): JsonResponse
    {
        return $this->crudDelete($tagPageProjectUser);
    }

    public function getEntityClass(): string
    {
        return TagPageProjectUser::class;
    }

    public function getFormClass(): string
    {
        return TagPageProjectUserForm::class;
    }
}