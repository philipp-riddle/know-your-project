<?php

namespace App\Controller\Api;

use App\Controller\Api\ApiController;
use App\Entity\Interface\AccessContext;
use App\Entity\Interface\CrudEntityInterface;
use App\Entity\Interface\CrudEntityValidationInterface;
use App\Entity\Interface\OrderListInterface;
use App\Entity\Interface\OrderListItemInterface;
use App\Entity\Interface\UserPermissionInterface;
use App\Event\CreateCrudEntityEvent;
use App\Event\DeleteCrudEntityEvent;
use App\Event\OrderCrudEntitiesEvent;
use App\Event\UpdateCrudEntityEvent;
use App\Exception\PreconditionFailedException;
use App\Serializer\SerializerContext;
use App\Service\OrderListHandler;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormInterface;
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
    /**
     * @return string The entity class name; e.g. App\Entity\Page\Page
     */
    abstract public function getEntityClass(): string;

    /**
     * @return string The form class name; e.g. App\Form\PageForm
     */
    abstract public function getFormClass(): string;

    /**
     * Standard CRUD operation to get an entity.
     * 
     * @param UserPermissionInterface $userPermissionInterface The entity to get; must implement UserPermissionInterface
     * 
     * @return JsonResponse JSON response containing the entity
     */
    protected function crudGet(UserPermissionInterface $userPermissionInterface): JsonResponse
    {
        $this->checkUserAccess($userPermissionInterface);

        return $this->jsonSerialize($userPermissionInterface);
    }

    /**
     * Standard CRUD operation to delete an entity.
     * 
     * @param UserPermissionInterface|CrudEntityInterface $userPermissionInterface The entity to delete; must implement both UserPermissionInterface and CrudEntityInterface
     * @param callable|null $onProcessEntity A callback that processes the entity before it's deleted; optional, pass null if not needed
     */
    protected function crudDelete(UserPermissionInterface|CrudEntityInterface $userPermissionInterface, ?callable $onProcessEntity = null): JsonResponse
    {
        if (!($userPermissionInterface instanceof CrudEntityInterface) || !($userPermissionInterface instanceof UserPermissionInterface)) {
            throw new PreconditionFailedException('Entity must implement both CrudEntityInterface and UserPermissionInterface');
        }

        $this->checkUserAccess($userPermissionInterface, AccessContext::DELETE);

        if (null !== $onProcessEntity) {
            $onProcessEntity($userPermissionInterface);
        }

        // save the entity ID as it will be removed after the EM flush; the ID will later be passed on to the event
        $entityId = $userPermissionInterface->getId();
        $this->em->remove($userPermissionInterface);
        $this->em->flush();

        $this->eventDispatcher->dispatch(new DeleteCrudEntityEvent($userPermissionInterface, $this->getUser(), $entityId));
        $this->em->flush();

        return $this->json(['success' => true]);
    }

    /**
     * Standard CRUD operation to update or create an entity.
     * 
     * @param UserPermissionInterface|CrudEntityInterface|null $userPermissionInterface The entity to update or null to create a new entity; if it's passed it must implement both UserPermissionInterface and CrudEntityInterface
     * @param Request $request The request object
     * @param callable|null $onProcessEntity A callback that processes the entity before it's persisted; optional, pass null if not needed. gets called with the entity and the form (to retrieve form data which cannot be directly mapped to the entity)
     * @param callable|null $afterProcessEntity A callback that processes the entity after it's persisted; optional, pass null if not needed. gets called with the updated entity and the original entity
     * @param string|null $formClass The form class to use; if null, the form class is determined by the getFormClass method
     * 
     * @return JsonResponse JSON response containing the updated or created entity
     */
    protected function crudUpdateOrCreate(UserPermissionInterface|CrudEntityInterface|null $userPermissionInterface, Request $request, ?callable $onProcessEntity = null, ?callable $afterProcessEntity = null, ?string $formClass = null,): JsonResponse
    {
        if (null !== $userPermissionInterface) {
            $this->checkUserAccess($userPermissionInterface, AccessContext::UPDATE);
        }

        $originalEntity = $userPermissionInterface !== null ? clone $userPermissionInterface : null;
        $formClass ??= $this->getFormClass();

        if ($request->getMethod() === 'PUT') {
            $entityForm = $this->createForm($formClass, $userPermissionInterface);
        } else {
            $entityForm = $this->createForm($formClass);
        }

        // this submits the form and checks if it's valid; if not it throws an exception and the exception handler will return a JSON response
        $entityForm = $this->handleFormRequest($entityForm, $request);

        // if the handle returned a response this means that the form submission was not successful
        if ($entityForm instanceof JsonResponse) {
            return $entityForm;
        }

        $entity = $entityForm->getData();

        // Each entity must implement both interfaces to work with this controller action.
        // This is enforced by the controller to ensure that all entities are checked for user access and for further validation + initialisation.
        if (!($entity instanceof UserPermissionInterface)) {
            throw new PreconditionFailedException('Entity must be an instance of UserPermissionInterface');
        } elseif (!($entity instanceof CrudEntityInterface)) {
            throw new PreconditionFailedException('Entity must be an instance of CrudEntityInterface');
        }

        if (null !== $onProcessEntity) {
            $entity = $onProcessEntity($entity, $entityForm);
        }

        // initialize the entity after the form and the processing has been applied - e.g. createdAt dates
        $entity->initialize();
        $this->checkUserAccess($entity, AccessContext::UPDATE); // check if the user has access to the entity after all fields have been initialized

        // entities can implement the CrudEntityValidationInterface to validate their data before being persisted;
        // this is useful for entities that have complex validation rules that cannot be expressed in the form.
        if ($entity instanceof CrudEntityValidationInterface) {
            $entity->validate();
        }

        $this->em->persist($entity);
        $this->em->flush();

        // Dispatch the event after the entity has been persisted to the database.
        // We do this to ensure that all contents are saved, even when the events and their subscribed services fail the operation.
        if (null === $userPermissionInterface)  {
            $this->eventDispatcher->dispatch(new CreateCrudEntityEvent($entity, $this->getUser()));
        } else {
            $this->eventDispatcher->dispatch(new UpdateCrudEntityEvent($entity, $this->getUser(), $originalEntity));
        }

        // flush after the event has been dispatched to ensure that all changes made in event subscribers are saved to the database 
        $this->em->flush();

        if (null !== $afterProcessEntity) {
            $entity = $afterProcessEntity($entity, $originalEntity);
        }

        return $this->jsonSerialize($entity);
    }

    /**
     * Standard CRUD operation to list entities.
     * 
     * @param array $filters An array of filters to apply to the list. Each filter is checked if it implements UserPermissionInterface to ensure the user has access to the returned results.
     * @param array|null $orderBy An array of order by clauses; if null, the order by is determined by the entity class and its implemented interfaces
     */
    protected function crudList(array $filters, ?array $orderBy = null, ?SerializerContext $serializerContext = null): JsonResponse
    {
        // check if the user has access to all entities in the list by iterating over all filters and checking if they implement UserPermissionInterface
        foreach ($filters as $filter) {
            if ($filter instanceof UserPermissionInterface) {
                $this->checkUserAccess($filter);
            }
        }

        // when no order by is given we determine the order clause by the given entity class and its implemented interfaces.
        if (null === $orderBy) {
            // check if the class implements an interface
            $reflectionClass = new \ReflectionClass($this->getEntityClass());

            if ($reflectionClass->implementsInterface(OrderListInterface::class)) {
                $orderBy = ['orderIndex' => 'ASC']; // if the entity implements OrderListInterface, we order by the order index
            } else {
                $orderBy = ['id' => 'ASC']; // for entities which cannot be ordered by the user we order by the ID
            }
        }

        $entities = $this->getRepository()->findBy($filters, $orderBy, limit: 100); // limit of 100 for now - we can add pagination later

        return $this->jsonSerialize($entities, serializerContext: $serializerContext);
    }

    /**
     * Standard CRUD operation to change the order of a list of entities.
     * 
     * @param Request $request The request object
     * @param OrderListHandler $orderListHandler The OrderListHandler service; used to apply the new order to the list
     * @param array $itemsToOrder An array of items to order; each item must implement UserPermissionInterface
     * @param string|null $orderListName The name of the order list; optional, pass null if not needed.
     * This is used to differentiate the Mercure event in the frontend.
     * E.g. 'Discover' to differentiate between the order of tasks in the different steps of the double diamond.
     * 
     * @return JsonResponse JSON response containing the updated list of items
     */
    protected function crudChangeOrder(Request $request, OrderListHandler $orderListHandler, array $itemsToOrder, ?string $orderListName = null): JsonResponse
    {
        $content = $request->toArray();
        
        if (!\array_key_exists('idOrder', $content) || !\is_array($content['idOrder'])) {
            return $this->json(['error' => 'Invalid request body'], 400);
        }

        foreach ($itemsToOrder as $itemToOrder) {
            if (!($itemToOrder instanceof UserPermissionInterface)) {
                throw new PreconditionFailedException('All items in the ordered list must be an instance of UserPermissionInterface');
            }

            $this->checkUserAccess($itemToOrder, AccessContext::UPDATE);
        }

        $idOrder = $content['idOrder'];
        $orderListHandler->applyIdOrder($itemsToOrder, $idOrder);
        $this->em->flush();

        // dispatch the event after the order has been changed;
        // e.g. this updates the order for other users as well.
        $this->eventDispatcher->dispatch(new OrderCrudEntitiesEvent($this->getEntityClass(), $orderListName, $itemsToOrder, $this->getUser()));
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
            throw new PreconditionFailedException('Entity must be an instance of OrderListItemInterface');
        }

        return $this->crudUpdateOrCreate(
            $userPermissionInterface,
            $request,
            onProcessEntity: function(UserPermissionInterface $entity, FormInterface $form) use ($orderListHandler, $itemsToOrder, $onProcessEntity) {
                if (null !== $onProcessEntity) {
                    $entity = $onProcessEntity($entity, $form);
                }

                if (!($entity instanceof OrderListItemInterface)) {
                    throw new PreconditionFailedException(\sprintf('Processed entity must be an instance of OrderListItemInterface (found: %s)', \get_class($entity)));
                }

                $entityItemsToOrder = $itemsToOrder($entity);

                // this is the case for doctrine collections.
                // this automatic conversion allows us to use the same method everywhere without thinking about what to return in the itemsToOrder callback
                if ($entityItemsToOrder instanceof \Traversable) {
                    $entityItemsToOrder = \iterator_to_array($entityItemsToOrder);
                }

                if (!\is_array($entityItemsToOrder)) {
                    throw new PreconditionFailedException('Items to order must return an array');
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