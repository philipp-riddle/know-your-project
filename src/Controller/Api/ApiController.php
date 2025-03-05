<?php

namespace App\Controller\Api;

use App\Entity\Interface\AccessContext;
use App\Entity\Interface\UserPermissionInterface;
use App\Entity\User\User;
use App\Exception\AccessDeniedException;
use App\Repository\UserRepository;
use App\Serializer\SerializerContext;
use App\Service\Helper\ApiControllerHelperService;
use App\Service\Helper\DefaultNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * This is the base class for all API controllers.
 * It provides common functionality to serialise/normalise objects to JSON, to get the currently logged in user, to persist and flush entities and to dispatch events.
 * 
 * Unlike the HomeController or the authentication controllers it does not extend App\Controller\Controller since the content security policy is not relevant in the API (not rendering HTML).
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

    protected function checkUserAccess(UserPermissionInterface $userPermissionInterface, AccessContext $accessContext = AccessContext::READ): void
    {
        if (!$userPermissionInterface->hasUserAccess($this->getUser(), $accessContext)) {
            throw new AccessDeniedException('You do not have access to this '.\get_class($userPermissionInterface));
        }
    }

    /**
     * Serialises an object to JSON.
     * 
     * @param mixed $object The object(s) to serialise.
     * @param array $additionalData Additional data to include in the JSON response; on same level as the object.
     * @param SerializerContext|null $serializerContext The context to use for serialisation; if not passed the default serialisation context is used.
     * 
     * @return JsonResponse The JSON response.
     */
    protected function jsonSerialize(mixed $object, array $additionalData = [], ?SerializerContext $serializerContext = null): JsonResponse
    {
        return $this->createJsonResponse($this->normalize($object, $additionalData, serializerContext: $serializerContext));
    }

    protected function normalize(mixed $object, array $additionalData = [], int $maxDepth = 999, ?SerializerContext $serializerContext = null): array|null
    {   
        // merge the normalized data with the additional data
        return [
            ...$this->normalizer->normalize($this->getUser(), $object, $maxDepth, $serializerContext ?? SerializerContext::DEFAULT),
            ...$additionalData,
        ];
    }

    protected function createJsonResponse($data): JsonResponse
    {
        return new JsonResponse($data, 200);
    }

    /**
     * Standard function to handle a request and a form with its data.
     * If the form submission is successful, the form is returned.
     * If the form submission is not successful, a JSON response with the error message is returned.
     * 
     * @todo change the return type to FormInterface exclusively; if the form is not valid we should work with exceptions. this makes integrating this method less error-prone.
     */
    protected function handleFormRequest(FormInterface $form, Request $request): JsonResponse|FormInterface
    {
        if ($request->getContent() === '') {
            // if the body is empty form data parts could have been sent which we can process with ->handleRequest
            $form->handleRequest($request);
        } else {
            // otherwise we submit the request data by serializing the request body to an array
            $form->submit($request->toArray());
        }

        // now check if the form submission is valid or if there are any errors
        if (!$form->isSubmitted() || !$form->isValid()) {
            $errorContent = [];

            foreach ($form->getErrors(true, true) as $error) {
                $errorContent[] = [
                    'message' => $error->getMessage(),
                    'validation' => $error->getMessageParameters(),
                ];
            }

            // there could be no validation errors -
            // in this case we check if the form was submitted and if the request body was empty or invalid.
            if (\count($errorContent) === 0) {
                if (!$form->isSubmitted()) {
                    $errorContent = ['error' => 'Form was not submitted'];
                } elseif (!$form->isValid()) {
                    $errorContent = ['error' => 'Invalid form data'];
                } else {
                    $errorContent = ['error' => 'Invalid request body'];
                }
            }

            return $this->json($errorContent, 400);
        }

        return $form;
    }
}