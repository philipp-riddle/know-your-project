<?php

namespace App\Tests\Application\Embedding;

use App\Entity\Page\PageSection;
use App\Entity\Page\PageSectionText;
use App\Entity\Page\PageTab;
use App\Entity\Task;
use App\Service\Search\SearchEngine;
use App\Tests\Application\Api\ApiControllerTestCase;

class SearchEngineTest extends ApiControllerTestCase
{
    public static array $entityClassesToClear = [
        PageSection::class,
        PageTab::class,
        PageSectionText::class,
    ];

    public function testGroupSearchResults_onePage()
    {
        $pageTab = $this->getPageTab();
        $page = $pageTab->getPage();

        $searchResults = [
            [
                'id' => 'Page:'.$page->getId(),
                'type' => 'Page',
                'result' => [
                    'id' => $page->getId(),
                ],
            ],
        ];

        $groupedSearchResults = $this->getSearchEngine()->groupSearchResults($searchResults);

        $this->assertSame($searchResults, $groupedSearchResults);
    }

    public function testGroupSearchResults_pageAndPageSection()
    {
        $pageTab = $this->getPageTab();
        $page = $pageTab->getPage();
        $page->setName('API security checkup');
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

        $searchResults = [
            [
                'id' => 'Page:'.$page->getId(),
                'type' => 'Page',
                'result' => [
                    'id' => $page->getId(),
                ],
            ],
            [
                'id' => 'PageSection:'.$pageSection->getId(),
                'type' => 'PageSection',
                'result' => [
                    'id' => $pageSection->getId(),
                    'pageTab' => [
                        'id' => $pageTab->getId(),
                        'page' => [
                            'id' => $page->getId(),
                        ],
                    ]
                ],
            ],
        ];

        $groupedSearchResults = $this->getSearchEngine()->groupSearchResults($searchResults);

        $this->assertCount(1, $groupedSearchResults);
        $this->assertSame('Page:'.$page->getId(), $groupedSearchResults[0]['id']);
        $this->assertSame('Page', $groupedSearchResults[0]['type']);
        $this->assertSame($page->getId(), $groupedSearchResults[0]['result']['id']);

        $this->assertCount(1, $groupedSearchResults[0]['subResults']);
        $this->assertSame('PageSection:'.$pageSection->getId(), $groupedSearchResults[0]['subResults'][0]['id']);
        $this->assertSame('PageSection', $groupedSearchResults[0]['subResults'][0]['type']);
    }

    public function testGroupSearchResults_taskAndPageSection()
    {
        $pageTab = $this->getPageTab();
        $page = $pageTab->getPage();
        $page->setName('API security checkup');
        $task = (new Task())
            ->setStepType('Discover')
            ->setPage($page)
            ->setProject($page->getProject())
            ->setOrderIndex(0)
            ->initialize();
        $pageSection = (new PageSection())
            ->setAuthor(self::$loggedInUser)
            ->setOrderIndex(0)
            ->initialize();
        $pageSectionText = (new PageSectionText())
            ->setContent('API security checkup is important for all applications.');
        $pageSection->setPageSectionText($pageSectionText);
        $pageTab->addPageSection($pageSection);

        self::$em->persist($task);
        self::$em->persist($pageSection);
        self::$em->persist($pageSectionText);
        self::$em->flush();

        $searchResults = [
            [
                'id' => 'Task:'.$task->getId(),
                'type' => 'Task',
                'result' => [
                    'id' => $task->getId(),
                    'page' => [
                        'id' => $page->getId(),
                    ],
                ],
            ],
            [
                'id' => 'PageSection:'.$pageSection->getId(),
                'type' => 'PageSection',
                'result' => [
                    'id' => $pageSection->getId(),
                    'pageTab' => [
                        'id' => $pageTab->getId(),
                        'page' => [
                            'id' => $page->getId(),
                        ],
                    ]
                ],
            ],
        ];

        $groupedSearchResults = $this->getSearchEngine()->groupSearchResults($searchResults);

        $this->assertCount(1, $groupedSearchResults);
        $this->assertSame('Task:'.$task->getId(), $groupedSearchResults[0]['id']);
        $this->assertSame('Task', $groupedSearchResults[0]['type']);
        $this->assertSame($task->getId(), $groupedSearchResults[0]['result']['id']);

        $this->assertCount(1, $groupedSearchResults[0]['subResults']);
        $this->assertSame('PageSection:'.$pageSection->getId(), $groupedSearchResults[0]['subResults'][0]['id']);
        $this->assertSame('PageSection', $groupedSearchResults[0]['subResults'][0]['type']);
    }

    private function getSearchEngine(): SearchEngine
    {
        return self::$client->getContainer()->get(SearchEngine::class);
    }
}