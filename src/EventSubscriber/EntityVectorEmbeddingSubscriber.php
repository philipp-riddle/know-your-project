<?php

namespace App\EventSubscriber;

use App\Entity\PageSection;
use App\Service\Search\Entity\EntityVectorEmbeddingInterface;
use App\Event\CreateCrudEntityEvent;
use App\Event\DeleteCrudEntityEvent;
use App\Event\UpdateCrudEntityEvent;
use App\Service\Helper\TestEnvironment;
use App\Service\Search\EntityVectorEmbeddingService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This subscriber is used to convert any entity in case of creation / update / deletion into a vector embedding and to either insert or delete it from the vector database.
 * Putting this into a subscriber makes it very easy to implement it everywhere any CrudEntityEvent is dispatched, i.e. in any ApiController extending CrudApiController.
 */
class EntityVectorEmbeddingSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityVectorEmbeddingService $EntityVectorEmbeddingService,
    ) { }

    public function onModifyCrudEntityEvent(CreateCrudEntityEvent|UpdateCrudEntityEvent $event): void
    {
        $entity = $event->getEntity();

        if ($event instanceof CreateCrudEntityEvent && $entity instanceof PageSection) {
            return; // we do not listen on page section creations as they are created in an empty state
        }

        // only handle entities if they the EntityVectorEmbeddingInterface and we are not in a test environment
        if ($entity instanceof EntityVectorEmbeddingInterface && !TestEnvironment::isActive()) {
            // we want to avoid unnecessary updates at any cost.
            // we can avoid them by checking if the text or attributes have changed of the entity.
            if ($event instanceof UpdateCrudEntityEvent) {

                /** @var EntityVectorEmbeddingInterface */
                $originalEntity = $event->getOriginalEntity();
                $oldTextEmbedding = $originalEntity->getTextForEmbedding();
                $oldMetaAttributes = $originalEntity->getMetaAttributes();

                // @todo does not work currently - somehow the original entity is equals to the entity even though we clone it in the CrudApiController
                // if ($oldTextEmbedding === $entity->getTextForEmbedding() && $oldMetaAttributes === $entity->getMetaAttributes()) {
                //     return;
                // }
            }

            $this->EntityVectorEmbeddingService->updateEmbeddedEntity($entity);
        }
    }

    public function onDeleteCrudEntityEvent(DeleteCrudEntityEvent $event): void
    {
        $entity = $event->getEntity();

        // only handle entities if they the EntityVectorEmbeddingInterface and we are not in a test environment
        if ($entity instanceof EntityVectorEmbeddingInterface && !TestEnvironment::isActive()) {
            $this->EntityVectorEmbeddingService->deleteEmbeddedEntity($entity, $event->getEntityId());
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