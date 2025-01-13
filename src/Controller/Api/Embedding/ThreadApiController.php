<?php

namespace App\Controller\Api\Embedding;

use App\Controller\Api\CrudApiController;
use App\Entity\Thread;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/thread')]
class ThreadApiController extends CrudApiController
{
    public function getEntityClass(): string
    {
        return Thread::class;
    }

    public function getFormClass(): string
    {
        throw new \Exception('Form not implemented');
    }
}