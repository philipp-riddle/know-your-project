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
    public const COLLECTION_USERDATA = 'userData';
    private const HOST = 'https://690c65cc-33c0-4c41-bd09-6ea69266d0e4.us-west-1-0.aws.cloud.qdrant.io';

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
        return $this->searchUserContentWithEmbedding(
            $this->openAIIntegration->createEmbedding($input),
            $filter,
            $limit
        );
    }

    /**
     * Searches for similar user content in the Qdrant vector database using an existing vector embedding.
     */
    public function searchUserContentWithEmbedding(array $embedding, Filter $filter, int $limit = 10): array
    {
        $client = $this->getClient();

        $searchRequest = (new SearchRequest(new VectorStruct($embedding, 'content')))
            ->setFilter($filter)
            ->setLimit($limit)
            ->setParams([
                'hnsw_ef' => 128, // the larger the value, the more accurate the search results; but also the slower the search
                'exact' => false, // if true, the search will be exact; if false, the search will be approximate but much faster
            ])
            ->setWithPayload(true);
        $searchResult = $client->collections($this->getUserDataCollectionName())->points()->search($searchRequest);

        return $searchResult->__toArray();
    }

    /**
     * Inserts a user content into the Qdrant vector database.
     * Before inserting we convert the input to an 'embedding', i.e. an array of float numbers/vectors which can later be used e.g. to search for similar content.
     * 
     * @return float[] the generated embedding
     */
    public function insertUserContent(int|string $uniqueId, string $input, array $metaAttributes = []): array
    {
        $client = $this->getClient();
        $embedding = $this->openAIIntegration->createEmbedding($input);

        // add the current time to the meta attributes
        // we can use this to prioritize newer content in the search results
        $metaAttributes['time'] = \time();

        $points = new PointsStruct();
        $points->addPoint(
            new PointStruct(
                $uniqueId,
                new VectorStruct($embedding, 'content'),
                $metaAttributes,
            )
        );

        $client->collections($this->getUserDataCollectionName())->points()->upsert($points);

        return $embedding;
    }

    public function deleteUserContent(int|string $uniqueId): void
    {
        $client = $this->getClient();
        $client->collections($this->getUserDataCollectionName())->points()->delete([$uniqueId]);
    }

    public function createCollection(string $collectionName, int $dimension = 1536): Response
    {
        // always make sure to use the correct environment in the collection name;
        // this avoids pushing data from different environments (dev / production / testdata) into one database.
        $collectionName .= '_'.$_ENV['APP_ENV'];

        $client = $this->getClient();
        $createCollection = new CreateCollection();
        $createCollection->addVector(new VectorParams($dimension, VectorParams::DISTANCE_COSINE), 'content');
        $createResponse = $client->collections($collectionName)->create($createCollection);

        return $createResponse;
    }

    private function getUserDataCollectionName(): string
    {
        // We use different collections for different environments;
        // This is done to make sure to not push from different environments (dev / production / testdata) into one database.
        return \sprintf('%s_%s', static::COLLECTION_USERDATA, $_ENV['APP_ENV']);
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