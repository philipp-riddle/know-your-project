<?php

namespace App\EventSubscriber;

use App\Service\Search\Entity\EntityVectorEmbeddingInterface;
use App\Event\CreateCrudEntityEvent;
use App\Event\DeleteCrudEntityEvent;
use App\Event\UpdateCrudEntityEvent;
use App\Service\Search\EntityEmbeddingQueueService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This subscriber is used to convert any entity in case of creation / update / deletion into a vector embedding and to either insert or delete it from the vector database.
 * Putting this into a subscriber makes it very easy to implement it everywhere any CrudEntityEvent is dispatched, i.e. in any ApiController extending CrudApiController.
 * We do not do this in sync but instead use a queue to avoid performance issues. The queue is processed by a command (php bin/console queue:entity-embedding)
 */
class EntityEmbeddingQueueSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityEmbeddingQueueService $entityEmbeddingQueueService,
    ) { }

    public function onModifyCrudEntityEvent(CreateCrudEntityEvent|UpdateCrudEntityEvent $event): void
    {
        $entity = $event->getEntity();

        // only handle entities if they implement the EntityVectorEmbeddingInterface
        if ($entity instanceof EntityVectorEmbeddingInterface) {
            $this->entityEmbeddingQueueService->addToQueue($entity);

            // also add all parent entities via the queue; e.g. page section => page
            foreach ($entity->getParentEntities() as $parentEntity) {
                $this->entityEmbeddingQueueService->addToQueue($parentEntity);
            }
        }
    }

    public function onDeleteCrudEntityEvent(DeleteCrudEntityEvent $event): void
    {
        $entity = $event->getEntity();

        // only handle entities if they implement the EntityVectorEmbeddingInterface
        if ($entity instanceof EntityVectorEmbeddingInterface) {
            $this->entityEmbeddingQueueService->addToQueue($entity, delete: true, entityId: $event->getEntityId());

            // also delete all child entities via the queue, e.g. page => page sections
            foreach ($entity->getChildEntities() as $childEntity) {
                $this->entityEmbeddingQueueService->addToQueue($childEntity, delete: true, entityId: $event->getEntityId());
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CreateCrudEntityEvent::class => 'onModifyCrudEntityEvent',
            UpdateCrudEntityEvent::class => 'onModifyCrudEntityEvent',
            DeleteCrudEntityEvent ::class => 'onDeleteCrudEntityEvent',
        ];
    }
}