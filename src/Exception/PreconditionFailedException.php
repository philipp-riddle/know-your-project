<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;

/**
 * This exception is thrown whenever a precondition is not met.
 * This could be a configuration exception or any other exceptions which concern the application's setup.
 * E.g. integration / mercure cannnot connect because the set config URLs are not reachable.
 */
class PreconditionFailedException extends PreconditionFailedHttpException implements DefaultExceptionInterface
{
    public function getProductionMessage(): string
    {
        return 'Precondition failed';
    }
}