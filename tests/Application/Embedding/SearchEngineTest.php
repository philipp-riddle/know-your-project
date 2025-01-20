<?php

namespace App\Tests\Application\Embedding;

use App\Entity\Page;
use App\Service\Integration\QdrantIntegration;
use App\Service\Search\Entity\CachedEntityVectorEmbedding;
use App\Service\Search\SearchEngine;
use App\Tests\Application\ApplicationTestCase;
use Qdrant\Exception\InvalidArgumentException;
use Qdrant\Models\Filter\Condition\MatchInt;
use Qdrant\Models\Filter\Filter;

class SearchEngineTest extends ApplicationTestCase
{
    public function testSearch_default()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');

        // try {
        //     $this->getQdrantIntegration()->createCollection(QdrantIntegration::COLLECTION_USERDATA);
        // } catch (InvalidArgumentException $ex) {} // ignore exception that the collection already exists

        // $mockedEmbeddedEntity = $this->getMockedEmbeddedEntity(Page::class, 'Guidelines');
        // $embeddingService = $this->getEmbeddingService();
        // $embeddingService->updateEmbeddedEntity($mockedEmbeddedEntity);
    }

    private function getMockedEmbeddedEntity(string $entityClass, string $embeddedText, array $metaAttributes = []): CachedEntityVectorEmbedding
    {
        $mock = $this->createMock(CachedEntityVectorEmbedding::class);

        $randomId = \random_int(1, 100000000);
        $mock
            ->method('getId')
            ->willReturn($randomId);

        $mock
            ->method('getTextForEmbedding')
            ->willReturn($embeddedText);

        $mock
            ->method('buildVectorDatabaseFilter')
            ->willReturn((new Filter())->addMust(new MatchInt(\strtolower((new \ReflectionClass($entityClass))->getShortName()), $randomId)));

        $mock
            ->method('getMetaAttributes')
            ->willReturn($metaAttributes);

        return $mock;
    }

    private function getQdrantIntegration(): QdrantIntegration
    {
        return self::$client->getContainer()->get(QdrantIntegration::class);
    }

    private function getEmbeddingService(): SearchEngine
    {
        return self::$client->getContainer()->get(SearchEngine::class);
    }
}