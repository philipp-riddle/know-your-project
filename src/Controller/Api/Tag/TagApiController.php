<?php

namespace App\Controller\Api\Tag;

use App\Controller\Api\CrudApiController;
use App\Entity\Tag\Tag;
use App\Form\Tag\TagForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tag')]
class TagApiController extends CrudApiController
{
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

    public function getEntityClass(): string
    {
        return Tag::class;
    }

    public function getFormClass(): string
    {
        return TagForm::class;
    }
}