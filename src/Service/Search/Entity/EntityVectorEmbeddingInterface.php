<?php

namespace App\Service\Search\Entity;

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
     * @return string The text for embedding; null if the entity should not be embedded and should be ignored.
     */
    public function getTextForEmbedding(): string;

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
    public function getFilter(): Filter;
}