<?php

namespace App\Tests\Application\Api;

use App\Entity\Page\PageSection;
use App\Entity\Page\PageSectionText;
use App\Service\Integration\QdrantIntegration;
use App\Service\Search\EntityVectorEmbeddingService;

class SearchApiControllerTest extends ApiControllerTestCase
{
    public static array $entityClassesToClear = [
        PageSection::class,
        PageSectionText::class,
    ];

    // public function testCreateSearchSimplePage(): void
    // {
    //     $pageTab = $this->getPageTab();
    //     $pageTab->getPage()->setName('API security checkup');
    //     self::$em->flush();

    //     // insert the entity into Qdrant, the vector database and update the vector embedding.
    //     $this->getEntityVectorEmbeddingService()->updateEmbeddedEntity($pageTab->getPage());

    //     $searchResponse = $this->requestJsonApi('POST', '/search/project/'.self::$loggedInUser->getSelectedProject()->getId(), [
    //         'search' => 'API security checkup',
    //     ]);

    //     $this->assertCount(1, $searchResponse);
    //     $this->assertSame('Page:'.$pageTab->getPage()->getId(), $searchResponse[0]['id']);
    //     $this->assertSame('Page', $searchResponse[0]['type']);
    //     $this->assertSame($pageTab->getPage()->getId(), $searchResponse[0]['result']['id']);
    // }

    public function testCreateSearchPageAndPageSectionMatch(): void
    {
        $pageTab = $this->getPageTab();
        $pageTab->getPage()->setName('API security checkup');
        $pageSection = (new PageSection())
            ->setAuthor(self::$loggedInUser)
            ->setOrderIndex(0)
            ->initialize();
        $pageSectionText = (new PageSectionText())
            ->setContent('API security checkup is important for all applications.');
        $pageSection->setPageSectionText($pageSectionText);
        $pageTab->addPageSection($pageSection);

        self::$em->persist($pageSection);
        self::$em->persist($pageSectionText);
        self::$em->flush();

        // insert the entity into Qdrant, the vector database and update the vector embedding.
        $this->getEntityVectorEmbeddingService()->updateEmbeddedEntity($pageTab->getPage());
        $this->getEntityVectorEmbeddingService()->updateEmbeddedEntity($pageSection);

        $searchResponse = $this->requestJsonApi('POST', '/search/project/'.self::$loggedInUser->getSelectedProject()->getId(), [
            'search' => 'API security checkup',
        ]);

        $this->assertCount(1, $searchResponse);
        $this->assertSame('Page:'.$pageTab->getPage()->getId(), $searchResponse[0]['id']);
        $this->assertSame('Page', $searchResponse[0]['type']);
        $this->assertSame($pageTab->getPage()->getId(), $searchResponse[0]['result']['id']);
    }

    public function getQdrantIntegration(): QdrantIntegration
    {
        return self::$client->getContainer()->get(QdrantIntegration::class);
    }

    public function getEntityVectorEmbeddingService(): EntityVectorEmbeddingService
    {
        return self::$client->getContainer()->get(EntityVectorEmbeddingService::class);
    }
}