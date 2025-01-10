<?php

namespace App\Service\Search;

use App\Entity\Interface\EntityVectorEmbeddingInterface;
use App\Entity\Project;
use App\Service\Integration\QdrantIntegration;
use Qdrant\Models\Filter\Condition\MatchInt;
use Qdrant\Models\Filter\Filter;
use Ramsey\Uuid\Uuid;

/**
 * This service is the bridge between Qdrant and the entity classes.
 * It is responsible for searching and updating the vector embeddings and does only update the vector database, not MySQL.
 */
class VectorEmbeddingService
{
    public function __construct(
        private QdrantIntegration $qdrant,
    ) { }

    public function search(Project $project, string $search): array
    {
        $filter = (new Filter())->addMust(new MatchInt('project', $project->getId()));
        $searchResult = $this->qdrant->searchUserContent($search, $filter);

        if ($searchResult['status'] !== 'ok') {
            throw new \RuntimeException('Qdrant search failed: Status ='.$searchResult['status']);
        }

        return $searchResult['result'];
    }

    public function handleEmbeddingUpdate(EntityVectorEmbeddingInterface $entity): void
    {
        $metaAttributes = $entity->getMetaAttributes();
        $metaAttributes['type'] = (new \ReflectionClass($entity))->getShortName();

        $this->qdrant->insertUserContent(
            $this->getEntityUuid($entity),
            $entity->getTextForEmbedding(),
            $metaAttributes,
        );
    }

    public function handleEmbeddingDeletion(EntityVectorEmbeddingInterface $entity, int $entityId): void
    {
        $this->qdrant->deleteUserContent($this->getEntityUuid($entity, $entityId));
    }

    /**
     * Returns a UUID for the given entity.
     * This UUID is unique for the entity and can be used for embedding into the vector database and deleting it.
     * 
     * @throws \InvalidArgumentException If the entity does not have an ID (probably because it is not persisted to the database yet).
     * @param EntityVectorEmbeddingInterface $entity The entity for which to generate the unique ID.
     * @param int|null $entityId The ID of the entity. If not provided, the ID of the entity is used.
     * @return string The unique ID.
     */
    private function getEntityUuid(EntityVectorEmbeddingInterface $entity, ?int $entityId = null): string
    {
        $entityId = $entityId ?? $entity->getId();

        if (null === $entityId) {
            throw new \InvalidArgumentException('Entity needs to have an ID to generate a unique embedding ID');
        }

        $entityShortName = (new \ReflectionClass($entity))->getShortName();
        $entityUniqueId = \sprintf('%s_%s', $entityShortName, $entityId);
        $entityUuid = Uuid::uuid3(Uuid::NAMESPACE_DNS, $entityUniqueId)->toString();

        return $entityUuid;
    }
}