<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotFoundException extends NotFoundHttpException implements DefaultExceptionInterface
{
    public function getProductionMessage(): string
    {
        return 'Not found';
    }
}