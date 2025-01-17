<?php

namespace App\Controller\Api;

use App\Entity\Interface\UserPermissionInterface;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Helper\ApiControllerHelperService;
use App\Service\Helper\DefaultNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * This is the base class for all API controllers.
 * It provides common functionality to serialise/normalise objects to JSON, to get the currently logged in user, to persist and flush entities and to dispatch events.
 */
abstract class ApiController extends AbstractController
{
    protected EntityManagerInterface $em;
    protected UserRepository $userRepository;
    protected EventDispatcherInterface $eventDispatcher;
    protected DefaultNormalizer $normalizer;

    // only inject one service & bundle all required services in this service;
    // this makes it way easier to extend the API controller and its injected services in child classes
    public function __construct(ApiControllerHelperService $apiControllerHelperService)
    {
        $this->em = $apiControllerHelperService->em;
        $this->userRepository = $apiControllerHelperService->userRepository;
        $this->eventDispatcher = $apiControllerHelperService->eventDispatcher;
        $this->normalizer = $apiControllerHelperService->defaultNormalizer;
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

    protected function checkUserAccess(UserPermissionInterface $userPermissionInterface): void
    {
        if (!$userPermissionInterface->hasUserAccess($this->getUser())) {
            throw new AccessDeniedException('You do not have access to this '.\get_class($userPermissionInterface));
        }
    }

    /**
     * Serialises an object to JSON.
     * 
     * @param mixed $object The object(s) to serialise.
     * 
     * @return JsonResponse The JSON response.
     */
    protected function jsonSerialize(mixed $object, array $additionalData = []): JsonResponse
    {
        return $this->createJsonResponse($this->normalize($object, $additionalData));
    }

    protected function normalize(mixed $object, array $additionalData = [], int $maxDepth = 5): array|null
    {   
        // merge the normalized data with the additional data
        return [
            ...$this->normalizer->normalize($this->getUser(), $object, maxDepth: $maxDepth),
            ...$additionalData,
        ];
    }

    protected function createJsonResponse($data): JsonResponse
    {
        return new JsonResponse($data, 200);
    }
}