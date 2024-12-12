<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Serializer\EntityNormalizer;
use App\Service\Helper\ApiControllerHelperService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
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

    protected function jsonSerialize(mixed $object): JsonResponse
    {
        if (\is_array($object)) {
            return $this->jsonSerializeMany($object);
        }
    
        return $this->json($this->normalize($object));
    }

    private function jsonSerializeMany(array $objects): JsonResponse
    {
        $normalized = [];

        foreach ($objects as $object) {
            $normalized[] = $this->normalize($object);
        }

        return $this->json($normalized);
    }

    private function normalize(mixed $object): array|null
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
        ]);
        $serializer = new Serializer([new DateTimeNormalizer(), $normalizer]);

        return $serializer->normalize($object);
    }
}