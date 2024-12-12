<?php

namespace App\Controller\Api;

use App\Controller\Api\ApiController;
use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\UserPermissionInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * This controller serves as a base for all CRUD (Create, Read, Update, Delete) controllers.
 * Standardizing this is useful as we only have one flow in data and form procession + validation.
 * 
 * We also validate the user's authorization to the requested resources by forcing all entities processed by these controllers to implement UserPermissionInterface.
 */
abstract class CrudApiController extends ApiController
{
    public const PAGE_SIZE_MAX = 100;

    /**
     * @return string The entity class name; e.g. App\Entity\Page
     */
    abstract public function getEntityClass(): string;

    /**
     * @return string The form class name; e.g. App\Form\PageForm
     */
    abstract public function getFormClass(): string;

    protected function checkUserAccess(UserPermissionInterface $userPermissionInterface): void
    {
        if (!$userPermissionInterface->hasUserAccess($this->getUser())) {
            throw new AccessDeniedException('You do not have access to this '.$this->getEntityClass());
        }
    }

    protected function crudGet(UserPermissionInterface $userPermissionInterface): JsonResponse
    {
        $this->checkUserAccess($userPermissionInterface);

        return $this->jsonSerialize($userPermissionInterface);
    }

    protected function crudDelete(UserPermissionInterface $userPermissionInterface): JsonResponse
    {
        $this->checkUserAccess($userPermissionInterface);

        $this->em->remove($userPermissionInterface);
        $this->em->flush();

        return $this->json(['success' => true]);
    }

    protected function crudUpdateOrCreate(?UserPermissionInterface $userPermissionInterface, Request $request, ?callable $onProcessEntity = null, ?string $formClass = null): JsonResponse
    {
        if (null !== $userPermissionInterface) {
            $this->checkUserAccess($userPermissionInterface);
        }

        $formClass ??= $this->getFormClass();

        if ($request->getMethod() === 'PUT') {
            $entityForm = $this->createForm($formClass, $userPermissionInterface);
        } else {
            $entityForm = $this->createForm($formClass);
        }

        $entityForm->submit($request->toArray());

        if (!$entityForm->isSubmitted() || !$entityForm->isValid()) {
            $errorContent = [];

            foreach ($entityForm->getErrors(true, true) as $error) {
                $errorContent[] = [
                    'message' => $error->getMessage(),
                    'validation' => $error->getMessageParameters(),
                ];
            }

            return $this->json($errorContent, 400);
        }

        $entity = $entityForm->getData();

        if (!($entity instanceof UserPermissionInterface)) {
            throw new \Exception('Entity must be an instance of UserPermissionInterface');
        } elseif (!($entity instanceof CrudEntityInterface)) {
            throw new \Exception('Entity must be an instance of CrudEntityInterface');
        }

        $entity->initialize();
        $this->checkUserAccess($entity);
        $this->em->persist($entity);

        if (null !== $onProcessEntity) {
            $entity = $onProcessEntity($entity, $entityForm);
            $this->checkUserAccess($entity);
            $this->em->persist($entity);
        }
        
        $this->em->flush();

        return $this->jsonSerialize($entity);
    }

    protected function crudList(array $filters, ?array $orderBy = null): JsonResponse
    {
        $orderBy ??= ['id' => 'ASC'];
        $entities = $this->getRepository()->findBy($filters, $orderBy, limit: 100); // @todo limit of 100 for now - we can add pagination later

        return $this->jsonSerialize($entities);
    }

    protected function getRepository(): EntityRepository
    {
        return $this->em->getRepository($this->getEntityClass());
    }
}