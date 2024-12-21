<?php

namespace App\Controller\Api;

use App\Controller\Api\ApiController;
use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\OrderListItemInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Service\OrderListHandler;
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

    protected function crudUpdateOrCreate(?UserPermissionInterface $userPermissionInterface, Request $request, ?callable $onProcessEntity = null, ?string $formClass = null, bool $persist = true, bool $returnEntity = false): JsonResponse
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

        if ($persist) {
            $this->em->persist($entity);
        }

        if (null !== $onProcessEntity) {
            $entity = $onProcessEntity($entity, $entityForm);
            $this->checkUserAccess($entity);

            if ($persist) {
                $this->em->persist($entity);
            }
        }

        $this->em->flush();

        return $returnEntity ? $entity : $this->jsonSerialize($entity);
    }

    protected function crudList(array $filters, ?array $orderBy = null): JsonResponse
    {
        $orderBy ??= ['id' => 'ASC'];
        $entities = $this->getRepository()->findBy($filters, $orderBy, limit: 100); // @todo limit of 100 for now - we can add pagination later

        return $this->jsonSerialize($entities);
    }

    protected function crudChangeOrder(Request $request, OrderListHandler $orderListHandler, array $itemsToOrder): JsonResponse
    {
        $content = $request->toArray();
        
        if (!\array_key_exists('idOrder', $content) || !\is_array($content['idOrder'])) {
            return $this->json(['error' => 'Invalid request body'], 400);
        }

        foreach ($itemsToOrder as $itemToOrder) {
            if (!($itemToOrder instanceof UserPermissionInterface)) {
                throw new \Exception('Entity must be an instance of UserPermissionInterface');
            }

            $this->checkUserAccess($itemToOrder);
        }

        $idOrder = $content['idOrder'];
        $orderListHandler->applyIdOrder($itemsToOrder, $idOrder);
        $this->em->flush();

        return $this->jsonSerialize($itemsToOrder);
    }

    /**
     * Standard CRUD operation to update or create an OrderListItemInterface entity.
     * This automatically adds the entity to the OrderListHandler, i.e. setting the order index based on the current list.
     */
    protected function crudUpdateOrCreateOrderListItem(?UserPermissionInterface $userPermissionInterface, Request $request, OrderListHandler $orderListHandler, callable $itemsToOrder, ?callable $onProcessEntity = null): JsonResponse
    {
        if (null !== $userPermissionInterface && !($userPermissionInterface instanceof OrderListItemInterface)) {
            throw new \Exception('Entity must be an instance of OrderListItemInterface');
        }

        return $this->crudUpdateOrCreate(
            $userPermissionInterface,
            $request,
            onProcessEntity: function(UserPermissionInterface $entity) use ($orderListHandler, $itemsToOrder, $onProcessEntity) {
                if (null !== $onProcessEntity) {
                    $entity = $onProcessEntity($entity);
                }

                if (!($entity instanceof OrderListItemInterface)) {
                    throw new \Exception('Processed entity must be an instance of OrderListItemInterface');
                }

                $entityItemsToOrder = $itemsToOrder($entity);

                // this is the case for doctrine collections.
                // this automatic conversion allows us to use the same method everywhere without thinking about what to return in the itemsToOrder callback
                if ($entityItemsToOrder instanceof \Traversable) {
                    $entityItemsToOrder = \iterator_to_array($entityItemsToOrder);
                }

                if (!\is_array($entityItemsToOrder)) {
                    throw new \Exception('Items to order must return an array');
                }

                $orderListHandler->add($entity, $entityItemsToOrder);

                return $entity;
            }
        );
    }

    protected function getRepository(): EntityRepository
    {
        return $this->em->getRepository($this->getEntityClass());
    }
}