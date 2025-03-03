<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * This exception is thrown whenever a bad request is made.
 * E.g. an input parameter is not of the appropriate type or format.
 */
class BadRequestException extends BadRequestHttpException implements DefaultExceptionInterface
{
    public function getProductionMessage(): string
    {
        return 'Bad request';
    }
}