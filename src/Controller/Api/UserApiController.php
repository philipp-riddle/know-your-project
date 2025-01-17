<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/user')]
class UserApiController extends ApiController
{
    #[Route('', name: 'api_user_info', methods: ['GET'])]
    public function getUserInfo(): JsonResponse
    {
        return $this->jsonSerialize($this->getUser());
    }
}