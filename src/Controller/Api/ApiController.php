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
     * @param array|null $normalizeCallbacks An array of normalisation callbacks; e.g. ['createdAt' => fn($date) => $date->format('Y-m-d H:i:s')]
     * 
     * @return JsonResponse The JSON response.
     */
    protected function jsonSerialize(mixed $object, ?array $normalizeCallbacks = null, array $additionalData = []): JsonResponse
    {
        if (\is_array($object) || $object instanceof \Traversable) {
            return $this->jsonSerializeMany($object, $normalizeCallbacks, $additionalData);
        }
    
        return $this->json($this->normalize($object, $normalizeCallbacks, $additionalData));
    }

    protected function jsonSerializeMany(array|\Traversable $objects, ?array $normalizeCallbacks = null, array $additionalData = []): JsonResponse
    {
        $normalized = [];

        foreach ($objects as $object) {
            $normalized[] = $this->normalize($object, $normalizeCallbacks, $additionalData);
        }

        return $this->json($normalized);
    }

    protected function normalize(mixed $object, ?array $normalizeCallbacks = null, array $additionalData = []): array|null
    {   
        $normalizeCallbacks = [
            ...$this->getDefaultNormalizeCallbacks(),
            ...($normalizeCallbacks ?? []),
        ];
        $normalizedData = $this->normalizer->normalize($object, $normalizeCallbacks);

        // merge the normalized data with the additional data
        return [
            ...$normalizedData,
            ...$additionalData,
        ];
    }

    /**
     * Returns the default normalisation callbacks for this controller.
     * Child classes can override this method to add more default normalisation callbacks.
     * 
     * @return array The default normalisation callbacks.
     */
    protected function getDefaultNormalizeCallbacks(): array
    {
        return [];
    }
}