<?php

namespace App\Serializer\Attribute;

/**
 * This attribute indicates any Entity properties which should not be serialized if they are nested within another Entity.
 * This makes it easy to reduce payloads in which certain nested context is not required but is required when fetching the Entity individually.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class IgnoreWhenNested
{
}