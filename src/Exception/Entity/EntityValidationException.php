<?php

namespace App\Exception\Entity;

use App\Exception\BadRequestException;

/**
 * This exception is thrown whenever an entity fails validation.
 * This class extends the BadRequestException and should be used for all entity validation errors.
 */
class EntityValidationException extends BadRequestException
{
    public function getProductionMessage(): string
    {
        return 'Entity validation failed';
    }
}