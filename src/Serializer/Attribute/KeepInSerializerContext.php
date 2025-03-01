<?php

namespace App\Serializer\Attribute;

use App\Serializer\SerializerContext;

/**
 * This attribute indicates any Entity properties which should be kept in all cases when a specific serializer context is given.
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class KeepInSerializerContext
{
    public function __construct(
        private SerializerContext $serializerContext,
    ) { }

    public function getSerializerContext(): SerializerContext
    {
        return $this->serializerContext;
    }
}