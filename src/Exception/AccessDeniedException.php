<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * This exception is thrown whenever a user tries to access a resource they are not allowed to.
 */
class AccessDeniedException extends AccessDeniedHttpException implements DefaultExceptionInterface
{
    public function getProductionMessage(): string
    {
        return 'Access denied';
    }
}