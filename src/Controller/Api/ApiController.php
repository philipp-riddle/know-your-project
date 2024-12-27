<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Helper\ApiControllerHelperService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class ApiController extends AbstractController
{
    protected EntityManagerInterface $em;
    protected SerializerInterface $serializer;
    protected UserRepository $userRepository;

    // only inject one service & bundle all required services in this service;
    // this makes it way easier to extend the API controller and its injected services in child classes
    public function __construct(ApiControllerHelperService $apiControllerHelperService)
    {
        $this->em = $apiControllerHelperService->em;
        $this->serializer = $apiControllerHelperService->serializer;
        $this->userRepository = $apiControllerHelperService->userRepository;
    }

    protected function getUser(): ?User
    {
        return parent::getUser();
    }

    protected function persistAndFlush(mixed $object): void
    {
        $this->em->persist($object);
        $this->em->flush();
    }

    /**
     * Serialises an object to JSON.
     * 
     * @param mixed $object The object(s) to serialise.
     * @param array|null $normalizeCallbacks An array of normalisation callbacks; e.g. ['createdAt' => fn($date) => $date->format('Y-m-d H:i:s')]
     * 
     * @return JsonResponse The JSON response.
     */
    protected function jsonSerialize(mixed $object, ?array $normalizeCallbacks = null): JsonResponse
    {
        if (\is_array($object) || $object instanceof \Traversable) {
            return $this->jsonSerializeMany($object, $normalizeCallbacks);
        }
    
        return $this->json($this->normalize($object, $normalizeCallbacks));
    }

    protected function jsonSerializeMany(array|\Traversable $objects, ?array $normalizeCallbacks = null): JsonResponse
    {
        $normalized = [];

        foreach ($objects as $object) {
            $normalized[] = $this->normalize($object, $normalizeCallbacks);
        }

        return $this->json($normalized);
    }

    protected function normalize(mixed $object, ?array $normalizeCallbacks = null): array|null
    {
        $maxDepthHandler = function (object $object): string {
            return $object->getId();
        };
        $circularReferenceHandler = function (array|object $object): string {
            if (\is_array($object)) {
                var_dump($object);
            }
            return $object->getId();
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