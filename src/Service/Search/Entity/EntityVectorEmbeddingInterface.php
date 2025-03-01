<?php

namespace App\Service\Search\Entity;

use Doctrine\ORM\PersistentCollection;
use Qdrant\Models\Filter\Filter;

/**
 * Any entity which is embedded into the vector database (for search and data analysis purposes) needs to implement this interface.
 */
interface EntityVectorEmbeddingInterface
{
    public function getId(): ?int;

    /**
     * Return the text which should be used for embedding into the vector database.
     * The entity needs to implement some code to convert the entity into a text a vector database and a LLM model can understand.
     * 
     * @return string|null The text for embedding; null if the entity should not be embedded and should be ignored.
     */
    public function getTextForEmbedding(): ?string;

    /**
     * Returns the title which should be used for search results.
     * 
     * @return string|null The title for search results; null if the entity's title should not be shown in search results.
     */
    public function getTitleForSearchResult(): ?string;

    /**
     * Returns all attributes which should be used as meta information for this entity in the vector database.
     * E.g. ['project' => 2] would mean that this entity is part of project 2.
     * 
     * @return array The meta attributes, format ['attribute' => value, ...]
     */
    public function getMetaAttributes(): array;

    /**
     * Process the vector embedding returned by the vector database.
     * The entity can choose to either cache it in-memory or could even store it in the database.
     */
    public function processVectorEmbedding(array $vectorEmbedding): self;

    /**
     * Returns the filter instance for the vector database to search for this entity.
     */
    public function buildVectorDatabaseFilter(): Filter;

    /**
     * Returns the parent entities which should be embedded into the vector database as well.
     * This goes along with updating; if this entity is updated, the related entities should be updated as well.
     * 
     * Example: PageSection: A page section should be embedded into the vector database. If the page section is updated, the page should be updated as well.
     * 
     * @return EntityVectorEmbeddingInterface[] The related entities which should be embedded as well.
     */
    public function getParentEntities(): PersistentCollection|array;

    /**
     * Returns the child entities which should be deleted when this entity is deleted.
     * 
     * Example: Page: A page should be embedded into the vector database. If the page is deleted, the page sections should be deleted as well.
     * 
     * @return EntityVectorEmbeddingInterface[] The related entities which should be deleted as well.
     */
    public function getChildEntities(): PersistentCollection|array;
}