<?php

namespace App\Service\Integration;

use Qdrant\Config;
use Qdrant\Http\Builder;
use Qdrant\Models\Filter\Filter;
use Qdrant\Models\PointsStruct;
use Qdrant\Models\PointStruct;
use Qdrant\Models\Request\CreateCollection;
use Qdrant\Models\Request\SearchRequest;
use Qdrant\Models\Request\VectorParams;
use Qdrant\Models\VectorStruct;
use Qdrant\Qdrant;
use Qdrant\Response;

/**
 * This class is responsible for all operations with Qdrant.
 * Qdrant is our vector database provider and we communicate with it via a PHP library which uses the Qdrant HTTP REST API.
 * The OpenAI integration is used to create embeddings for the input text.
 * 
 * A lot of this code is inspired by the Qdrant PHP SDK documentation: https://github.com/hkulekci/qdrant-php
 * I used their examples as a base to build this class and then fine-tuned the code to the application's needs.
 */
final class QdrantIntegration
{
    private const HOST = 'https://690c65cc-33c0-4c41-bd09-6ea69266d0e4.us-west-1-0.aws.cloud.qdrant.io';
    private const COLLECTION_USERDATA = 'userData';

    public function __construct(
        private OpenAIIntegration $openAIIntegration,
    ) { }

    /**
     * Searches for similar user content in the Qdrant vector database.
     * This is done by first converting the input to an 'embedding'.
     * With the filter we can ensure that we only search for content of a specific project / page / ... or with any other specific attributes.
     * 
     * @param string $input The input text for which to search similar content.
     * @param Filter $filter The filter to apply to the search.
     * @param int $limit The maximum number of results to return.
     * @return array The search results; each result is an array with the keys 'id', 'score', 'payload' & 'meta'.
     */
    public function searchUserContent(string $input, Filter $filter, int $limit = 10): array
    {
        $client = $this->getClient();
        $embedding = $this->openAIIntegration->createEmbedding($input);

        $searchRequest = (new SearchRequest(new VectorStruct($embedding, 'content')))
            ->setFilter($filter)
            ->setLimit($limit)
            ->setParams([
                'hnsw_ef' => 128,
                'exact' => false,
            ])
            ->setWithPayload(true);
        $searchResult = $client->collections(self::COLLECTION_USERDATA)->points()->search($searchRequest);

        return $searchResult->__toArray();
    }

    /**
     * Inserts a user content into the Qdrant vector database.
     * Before inserting we convert the input to an 'embedding', i.e. an array of float numbers/vectors which can later be used e.g. to search for similar content.
     */
    public function insertUserContent(int|string $uniqueId, string $input, array $metaAttributes = []): void
    {
        $client = $this->getClient();
        $embedding = $this->openAIIntegration->createEmbedding($input);

        $points = new PointsStruct();
        $points->addPoint(
            new PointStruct(
                $uniqueId,
                new VectorStruct($embedding, 'content'),
                $metaAttributes,
            )
        );

        $client->collections(self::COLLECTION_USERDATA)->points()->upsert($points);
    }

    public function deleteUserContent(int|string $uniqueId): void
    {
        $client = $this->getClient();
        $client->collections(self::COLLECTION_USERDATA)->points()->delete([$uniqueId]);
    }

    public function createCollection(string $collectionName, int $dimension = 1536): Response
    {
        $client = $this->getClient();
        $createCollection = new CreateCollection();
        $createCollection->addVector(new VectorParams($dimension, VectorParams::DISTANCE_COSINE), 'content');
        $createResponse = $client->collections($collectionName)->create($createCollection);

        return $createResponse;
    }

    private function getClient(): Qdrant
    {
        $apiKey = $_ENV['QDRANT_API_KEY'] ?? '';

        if (\trim($apiKey) === '') {
            throw new \RuntimeException('QDRANT_API_KEY is not set');
        }

        $config = new Config(static::HOST);
        $config->setApiKey($apiKey);
        $transport = (new Builder())->build($config);

        return new Qdrant($transport);
    }
}