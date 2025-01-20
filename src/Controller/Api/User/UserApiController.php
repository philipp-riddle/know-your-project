<?php

namespace App\Controller\Api\User;

use App\Controller\Api\CrudApiController;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/user')]
class UserApiController extends CrudApiController
{
    #[Route('', name: 'api_user_info', methods: ['GET'])]
    public function getUserInfo(): JsonResponse
    {
        return $this->crudGet($this->getUser());
    }

    public function getEntityClass(): string
    {
        return User::class;
    }

    public function getFormClass(): string
    {
        throw new \RuntimeException('Form not implemented for User entity.');
    }
}