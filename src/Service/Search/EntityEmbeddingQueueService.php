<?php

namespace App\Service\Search;

use App\Entity\Embedding\EntityEmbeddingQueueItem;
use App\Exception\PreconditionFailedException;
use App\Repository\EntityEmbeddingQueueItemRepository;
use App\Service\Helper\ApplicationEnvironment;
use App\Service\Helper\Debug;
use App\Service\Integration\QdrantIntegration;
use App\Service\Search\Entity\EntityVectorEmbeddingInterface;
use Doctrine\ORM\EntityManagerInterface;

final class EntityEmbeddingQueueService
{
    // always wait 5 seconds before processing the queue item; this can accumulate multiple updates into one batch in worst case scenarios
    public const QUEUE_DELAY_SECONDS = 5;

    public function __construct(
        private EntityVectorEmbeddingService $entityVectorEmbeddingService,
        private QdrantIntegration $qdrantIntegration,
        private EntityEmbeddingQueueItemRepository $entityEmbeddingQueueItemRepository,
        private EntityManagerInterface $em,
    ) {
    }

    public function addToQueue(EntityVectorEmbeddingInterface $entity, bool $delete = false, ?int $entityId = null): ?EntityEmbeddingQueueItem
    {
        // we need to check if the entity should be embedded at all -
        // if the entity's getTextForEmbedding(..) returns null, it should not be embedded.
        if (null === $embeddingText = $entity->getTextForEmbedding()) {
            return null;
        }

        if ($entityId === null && \method_exists($entity, 'getId')) {
            $entityId = $entity->getId();
        }

        if (null === $entityId) {
            if (ApplicationEnvironment::isDevEnv()) {
                throw new PreconditionFailedException(\sprintf('Entity ID is required for adding entities to the queue (Entity: %s, Delete: %s)', \get_class($entity), $delete ? 'Yes' : 'No'));
            }

            return null;
        }

        // check if the queue item already exists.
        // if not, create a new one.
        // if yes, we will override some attributes in the next step to make sure it is up to date with the latest content.
        if (null === $queueItem = $this->getQueueItem($entity, $entityId)) {
            $queueItem = (new EntityEmbeddingQueueItem())
                ->setUuid($this->entityVectorEmbeddingService->getEntityUuid($entity, $entityId));
            $this->em->persist($queueItem);
        }

        $queueItem
            // always override the created at timestamp to make sure we process the item once at the end of every editing process;
            // otherwise we have hundreds of writes if users actively write and work in the system.
            ->setCreatedAt(new \DateTime())
            // flag to indicate if the entity should be deleted from the embedding database
            ->setDeletion($delete)
            // if we delete the entity, we do not need to store the text for embedding it.
            // if we update we override the text and meta attributes when re-adding it to the queue.
            // through this we get content updates into the queue.
            ->setText($delete ? null : $embeddingText)
            ->setMetaAttributes($entity->getMetaAttributes())
            // the nice name is used to better identify the entity in the queue command and in the database
            ->setNiceName(\sprintf('%s #%d', (new \ReflectionClass($entity::class))->getShortName(), $entityId));

        return $queueItem;
    }

    public function processQueueItem(EntityEmbeddingQueueItem $queueItem): void
    {
        try {
            if ($queueItem->isDeletion()) {
                Debug::print('Deleting embedding for ' . $queueItem->getNiceName(), category: 'queue');
                $this->entityVectorEmbeddingService->deleteEntityContents($queueItem->getUuid());
            } else {
                Debug::print('Updating embedding for ' . $queueItem->getNiceName(), category: 'queue');
                $this->entityVectorEmbeddingService->insertEntityContents(
                    $queueItem->getUuid(),
                    $queueItem->getText(),
                    $queueItem->getMetaAttributes(),
                );
            }
        } catch (\Throwable $e) {
            // catching a throwable means we catch everything, including exceptions and errors. If we do not do this the queue can get locked.
            // then it is better to skip the faulty items and continue with the next ones.
            Debug::print('Error processing queue item: ' . $e->getMessage(), category: 'error');
        } finally {
            $this->em->remove($queueItem);
        }
    }

    /**
     * @return EntityEmbeddingQueueItem[]
     */
    public function getQueueItemsToProcess(int $limit = 10): array
    {
        $olderThan = (new \DateTime())->sub(new \DateInterval('PT' . self::QUEUE_DELAY_SECONDS . 'S'));
        $itemsToProcess = $this->entityEmbeddingQueueItemRepository->getItemsToProcess($olderThan, $limit);
        
        return $itemsToProcess;
    }

    public function getQueueItem(EntityVectorEmbeddingInterface $entity, ?int $entityId = null): ?EntityEmbeddingQueueItem
    {
        return $this->entityEmbeddingQueueItemRepository->findOneBy([
            'uuid' => $this->entityVectorEmbeddingService->getEntityUuid($entity, $entityId)
        ]);
    }
}