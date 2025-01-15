<?php

namespace App\Service\Search\Entity;

use Qdrant\Models\Filter\Condition\MatchInt;
use Qdrant\Models\Filter\Filter;

/**
 * Any entity which embeds a vector embedding and stores the embedding in-memory should extend this class.
 */
abstract class CachedEntityVectorEmbedding implements EntityVectorEmbeddingInterface
{
    protected ?array $cachedEmbedding = null;

    /**
     * @return float[]|null The cached embedding of the entity if already generated
     */
    public function getCachedEmbedding(): ?array
    {
        return $this->cachedEmbedding;
    }

    public function processVectorEmbedding(array $vectorEmbedding): EntityVectorEmbeddingInterface
    {
        $this->cachedEmbedding = $vectorEmbedding;

        return $this;
    }

    /**
     * By default this filter returns all entities with the same ID as this entity.
     * Child classes can override this method to add more filters; make sure to call and use parent::buildVectorDatabaseFilter() in the child class.
     */
    public function buildVectorDatabaseFilter(): Filter
    {
        $shortName = \strtolower((new \ReflectionClass($this))->getShortName());

        return (new Filter())->addMust(new MatchInt($shortName, $this->getId()));
    }
}