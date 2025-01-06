<?php

namespace App\Controller\Api;

use App\Controller\Api\ApiController;
use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\CrudEntityValidationInterface;
use App\Entity\Interface\OrderListItemInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Service\OrderListHandler;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

    protected function crudGet(UserPermissionInterface $userPermissionInterface, ?array $normalizeCallbacks = null): JsonResponse
    {
        $this->checkUserAccess($userPermissionInterface);

        return $this->jsonSerialize($userPermissionInterface, $normalizeCallbacks);
    }

    protected function crudDelete(UserPermissionInterface $userPermissionInterface, ?callable $onProcessEntity = null): JsonResponse
    {
        $this->checkUserAccess($userPermissionInterface);

        if (null !== $onProcessEntity) {
            $onProcessEntity($userPermissionInterface);
        }

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

        if ($request->getContent() === '') {
            // if the body is empty form data parts could have been sent which we can process with ->handleRequest
            $entityForm->handleRequest($request);
        } else {
            // otherwise we submit the request data by serializing the request body to an array
            $entityForm->submit($request->toArray());
        }

        if (!$entityForm->isSubmitted() || !$entityForm->isValid()) {
            $errorContent = [];

            foreach ($entityForm->getErrors(true, true) as $error) {
                $errorContent[] = [
                    'message' => $error->getMessage(),
                    'validation' => $error->getMessageParameters(),
                ];
            }

            if (\count($errorContent) === 0) {
                if (!$entityForm->isSubmitted()) {
                    $errorContent = ['error' => 'Form was not submitted'];
                } elseif (!$entityForm->isValid()) {
                    $errorContent = ['error' => 'Invalid form data'];
                } else {
                    $errorContent = ['error' => 'Invalid request body'];
                }
            }

            return $this->json($errorContent, 400);
        }

        $entity = $entityForm->getData();

        if (!($entity instanceof UserPermissionInterface)) {
            throw new \Exception('Entity must be an instance of UserPermissionInterface');
        } elseif (!($entity instanceof CrudEntityInterface)) {
            throw new \Exception('Entity must be an instance of CrudEntityInterface');
        }

        if (null !== $onProcessEntity) {
            $entity = $onProcessEntity($entity, $entityForm);
        }

        // initialize the entity after the form and the processing has been applied - e.g. createdAt dates
        $entity->initialize();
        $this->checkUserAccess($entity); // check if the user has access to the entity after all fields have been initialized

        // entities can implement the CrudEntityValidationInterface to validate their data before being persisted;
        // this is useful for entities that have complex validation rules that cannot be expressed in the form.
        if ($entity instanceof CrudEntityValidationInterface) {
            $entity->validate();
        }

        if ($persist) {
            $this->em->persist($entity);
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
                throw new \Exception('All items in the ordered list must be an instance of UserPermissionInterface');
            }

            $this->checkUserAccess($itemToOrder);
        }

        $idOrder = $content['idOrder'];
        $orderListHandler->applyIdOrder($itemsToOrder, $idOrder);
        $this->em->flush();

        return $this->jsonSerialize($itemsToOrder);
    }

    /**
     * Standard CRUD operation to update or create an OrderListItemInterface entity (=> has an order index and can be rearranged in the frontend).
     * This automatically adds the entity to the OrderListHandler, i.e. setting the order index based on the current list.
     * 
     * @param UserPermissionInterface|null $userPermissionInterface The entity to update or null to create a new entity
     * @param Request $request The request object
     * @param OrderListHandler $orderListHandler The OrderListHandler service; used to add the entity to the list properly to the list with a correct order index etc
     * @param callable $itemsToOrder A callback that returns an array of items to order, the entity being processed is passed as the first argument
     * @param callable|null $onProcessEntity A callback that processes the entity before it's added to the OrderListHandler; optional, pass null if not needed
     * 
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
                    throw new \Exception(\sprintf('Processed entity must be an instance of OrderListItemInterface (found: %s)', \get_class($entity)));
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