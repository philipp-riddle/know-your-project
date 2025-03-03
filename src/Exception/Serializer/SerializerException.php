<?php

namespace App\Exception\Serializer;

use App\Exception\PreconditionFailedException;

class SerializerException extends PreconditionFailedException
{
    public function getProductionMessage(): string
    {
        return 'Serializer error: Could not serialize the given data.';
    }
}