<?php

namespace App\Service\Helper;

use App\Entity\User\User;
use App\Serializer\EntitySerializer;
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
        private EntitySerializer $entitySerializer,
    ) { }

    /**
     * Normalizes data by using our own entity serializer.
     */
    public function normalize(User $currentUser, $object, ?int $maxDepth = null): array
    {
        return $this->symfonyNormalize($object);

        // @todo: Bugs in the serializer caused me to use the Symfony normalizer instead...
        // Maybe we fix it later, but it's not worth much extra time as it only saves 5ms (!) per request.
        // return $this->entitySerializer->serialize($currentUser, $object, maxDepth: $maxDepth ?? 5);        
    }

    /**
     * Normalizes data by using Symfony's normalizer.
     * The object this function generates is unfiltered and  much larger in size than the one you get from the optimised entity serializer.
     */
    public function symfonyNormalize($object, array $normalizeCallbacks = [])
    {
        $maxDepthHandler = function (object $object): string {
            return $object->getId();
        };
        $circularReferenceHandler = function (array|object|null $object): string {
            return $object->getId() ?? 'n/a';
        };

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