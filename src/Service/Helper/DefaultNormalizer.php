<?php

namespace App\Service\Helper;

use App\Serializer\NormalizeDepthHandler;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * This service is used to normalise any object into an array.
 * It is used to avoid code duplication in the API controllers, service, etc.
 */
final class DefaultNormalizer
{
    public function __construct(
        private NormalizeDepthHandler $normalizeDepthHandler,
    ) { }

    public function normalize($object, ?array $normalizeCallbacks = null): array
    {
        $maxDepthHandler = function (object $object): string {
            return $object->getId();
        };
        $circularReferenceHandler = function (array|object|null $object): string {
            return $object->getId() ?? 'n/a';
        };
        // $normalizeCallbacks = [
        //     ...$normalizeCallbacks,
        //     ...$this->normalizeDepthHandler->generateNormalizeCallbacks($object, maxDepth: 0), // by default we go 3 levels deep max in the object graph
        // ];

        $normalizer = new ObjectNormalizer(defaultContext: [
            AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
            AbstractObjectNormalizer::MAX_DEPTH_HANDLER => $maxDepthHandler,
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => $circularReferenceHandler,
            AbstractObjectNormalizer::IGNORED_ATTRIBUTES => ['__initializer__', '__cloner__', '__isInitialized__', 'password'],
            AbstractNormalizer::CALLBACKS => $normalizeCallbacks ?? [],
        ]);
        $serializer = new Serializer([new DateTimeNormalizer(), $normalizer]);

        return $serializer->normalize($object);
    }
}