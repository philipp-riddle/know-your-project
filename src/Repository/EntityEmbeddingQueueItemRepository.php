<?php

namespace App\Repository;

use App\Entity\Embedding\EntityEmbeddingQueueItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QueueItemVectorEmbedding>
 */
class EntityEmbeddingQueueItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntityEmbeddingQueueItem::class);
    }

    /**
     * @return EmbeddingQueueEntityItem[]
     */
    public function getItemsToProcess(\DateTime $olderThan, int $limit = 10): array
    {
        return $this->createQueryBuilder('q')
            ->where('q.createdAt < :olderThan')
            ->setParameter('olderThan', $olderThan)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
